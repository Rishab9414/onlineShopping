<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\DocumentPdfService;
use App\Services\OrderService;
use App\Services\ProductReviewService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private ProductReviewService $reviews,
        private RazorpayService $razorpay,
    ) {}

    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);

        return view('shop.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        if ($order->payment_method === 'online' && $order->payment_status !== 'paid') {
            $this->razorpay->syncPaymentStatus($order, 'sync');
            $order->refresh();
        }

        $order->load([
            'items.product',
        ]);
        $customer = Customer::where('user_id', auth()->id())->first();
        $reviewableItems = $customer
            ? $this->reviews->reviewableItemsForOrder($order, $customer)
            : collect();

        return view('shop.orders.show', compact('order', 'reviewableItems'));
    }

    public function invoice(Order $order, OrderService $orders, DocumentPdfService $pdfs): Response
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_if($order->payment_method === 'online' && $order->payment_status !== 'paid', 403);

        $invoice = $orders->generateInvoice($order);

        return $pdfs->invoicePdf($order)->download($invoice->invoice_no.'.pdf');
    }

    public function confirmation(Order $order): View|RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        if ($order->payment_method === 'online' && $order->payment_status !== 'paid') {
            if ($this->razorpay->syncPaymentStatus($order, 'sync')) {
                $order->refresh();
            } else {
                return redirect()
                    ->route('orders.payment', $order)
                    ->with('error', 'Please complete payment to view order confirmation.');
            }
        }

        $order->load('items');

        return view('shop.orders.confirmation', compact('order'));
    }
}
