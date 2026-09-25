<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoiceJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\DocumentPdfService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Order::query()
            ->select([
                'id', 'order_number', 'customer_id', 'user_id',
                'status', 'payment_status', 'grand_total', 'total', 'created_at',
            ])
            ->with([
                'customer:id,full_name,mobile,email',
                'user:id,name,email,phone',
            ])
            ->withCount('items');

        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', $s)
                    ->orWhereIn('customer_id', Customer::query()
                        ->where(function ($c) use ($s) {
                            $c->where('full_name', 'like', $s)->orWhere('mobile', 'like', $s);
                        })
                        ->select('id'))
                    ->orWhereIn('user_id', User::query()
                        ->where(function ($u) use ($s) {
                            $u->where('name', 'like', $s)->orWhere('email', 'like', $s);
                        })
                        ->select('id'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 10), 50);

        $paginator = $query->latest('id')->simplePaginate($perPage);

        $paginator->getCollection()->transform(fn (Order $o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'customer_name' => $o->customer?->full_name ?? $o->user?->name ?? 'Guest',
            'mobile' => $o->customer?->mobile ?? $o->user?->phone ?? '—',
            'email' => $o->customer?->email ?? $o->user?->email ?? '—',
            'items_count' => $o->items_count,
            'grand_total' => $o->displayTotal(),
            'payment_status' => $o->payment_status,
            'status' => $o->status,
            'created_at' => $o->created_at?->format('M d, Y H:i'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $paginator,
        ]);
    }

    public function show(Order $order, OrderService $orders): View
    {
        $order->load([
            'customer', 'user', 'items.product', 'invoiceRecord', 'statusLogs',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
            'timeline' => $orders->timeline($order),
        ]);
    }

    public function confirm(Order $order, OrderService $orders): JsonResponse
    {
        $orders->confirm($order, 'admin');
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} confirmed");

        return response()->json(['success' => true, 'message' => 'Order confirmed and stock reserved.', 'status' => $order->fresh()->status]);
    }

    public function syncPayment(Order $order, RazorpayService $razorpay): JsonResponse
    {
        if ($order->payment_status === 'paid') {
            return response()->json(['success' => true, 'message' => 'Order is already marked as paid.', 'reload' => true]);
        }

        if ($order->payment_method !== 'online') {
            return response()->json(['success' => false, 'message' => 'Payment sync is only for online Razorpay orders.'], 422);
        }

        if (! $order->razorpay_order_id) {
            return response()->json(['success' => false, 'message' => 'No Razorpay order ID on this order.'], 422);
        }

        if ($razorpay->syncPaymentStatus($order, 'admin')) {
            ActivityLogger::log('updated', 'orders', $order, "Payment synced for {$order->order_number}");

            return response()->json(['success' => true, 'message' => 'Payment confirmed from Razorpay.', 'reload' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No successful payment found on Razorpay yet. Ask customer to pay or check Razorpay dashboard.',
        ], 422);
    }

    public function updateStatus(Request $request, Order $order, OrderService $orders): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,packing,packed,shipped,out_for_delivery,delivered,cancelled,returned,refunded,completed'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $label = ucwords(str_replace('_', ' ', $data['status']));
        $orders->updateStatus($order, $data['status'], $label, $data['remarks'] ?? null, 'admin');
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} status manually set to {$data['status']}");

        return response()->json([
            'success' => true,
            'message' => "Order status updated to {$label}.",
            'status' => $order->fresh()->status,
        ]);
    }

    public function generateInvoice(Order $order): JsonResponse
    {
        GenerateInvoiceJob::dispatchSync($order->id, auth()->id());

        return response()->json(['success' => true, 'message' => 'Invoice generated.', 'reload' => true]);
    }

    public function printInvoice(Order $order, OrderService $orders, DocumentPdfService $pdfs): Response
    {
        $invoice = $orders->generateInvoice($order);

        return $pdfs->invoicePdf($order)->download($invoice->invoice_no.'.pdf');
    }

    public function packingSlip(Order $order, DocumentPdfService $pdfs): Response
    {
        $order->loadMissing(['items', 'customer', 'user']);

        return $pdfs->packingSlipPdf($order)->download('packing-slip-'.$order->order_number.'.pdf');
    }

    public function cancel(Order $order, OrderService $orders): JsonResponse
    {
        $orders->cancel($order);
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} cancelled");

        return response()->json(['success' => true, 'message' => 'Order cancelled.']);
    }

    public function refund(Order $order, OrderService $orders): JsonResponse
    {
        $orders->refund($order);
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} refunded");

        return response()->json(['success' => true, 'message' => 'Refund processed.']);
    }

    public function returnOrder(Order $order, OrderService $orders): JsonResponse
    {
        $orders->updateStatus($order, 'returned', 'Return Initiated', 'Return marked manually', 'admin');
        ActivityLogger::log('updated', 'orders', $order, "Return initiated for {$order->order_number}");

        return response()->json(['success' => true, 'message' => 'Return initiated.']);
    }
}
