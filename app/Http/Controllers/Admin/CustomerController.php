<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\CustomerStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function __construct(private CustomerStatsService $stats) {}

    public function index(): View
    {
        return view('admin.customers.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Customer::query()
            ->select([
                'id', 'user_id', 'customer_code', 'full_name', 'email', 'mobile',
                'country_code', 'account_status', 'profile_image',
                'last_login', 'created_at',
            ]);

        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $query->where(fn ($q) => $q->where('full_name', 'like', $s)
                ->orWhere('email', 'like', $s)
                ->orWhere('mobile', 'like', $s)
                ->orWhere('customer_code', 'like', $s));
        }

        if ($request->filled('status')) {
            $query->where('account_status', $request->status);
        }

        if ($request->filled('verified')) {
            $verified = $request->verified === 'yes';
            $query->where('email_verified', $verified)->where('mobile_verified', $verified);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 10), 50);
        $customers = $query->latest('id')->simplePaginate($perPage);
        $orderStats = $this->stats->listStats($customers->getCollection());

        $customers->getCollection()->transform(function (Customer $c) use ($orderStats) {
            $stats = $orderStats[$c->id] ?? ['total_orders' => 0, 'total_spend' => 0.0];

            return [
                'id' => $c->id,
                'customer_code' => $c->customer_code,
                'name' => $c->full_name,
                'mobile' => $c->country_code.' '.$c->mobile,
                'email' => $c->email,
                'registered_at' => $c->created_at?->format('M d, Y'),
                'last_login' => $c->last_login?->format('M d, Y H:i') ?? '—',
                'total_orders' => $stats['total_orders'],
                'total_spend' => $stats['total_spend'],
                'status' => $c->account_status,
                'profile_image' => $c->profile_image ? asset('storage/'.$c->profile_image) : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $customers]);
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new Customer([
            'country_code' => '+91',
            'registration_source' => 'admin',
            'login_type' => 'email',
            'account_status' => 'active',
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCustomer($request);
        $data = $this->normalizeBooleans($request, $data);
        $customer = Customer::create($data);

        ActivityLogger::log('created', 'customers', $customer, "Customer {$customer->customer_code} created");

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'addresses', 'wishlists.product', 'cartItems.product', 'reviews.product',
            'loginLogs' => fn ($q) => $q->latest()->limit(10),
            'supportTickets' => fn ($q) => $q->latest()->limit(5),
        ]);

        $stats = $this->stats->stats($customer);
        $orders = Order::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if ($customer->user_id) {
                    $q->orWhere('user_id', $customer->user_id);
                }
            })
            ->latest()->limit(10)->get();

        return view('admin.customers.show', compact('customer', 'stats', 'orders'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validateCustomer($request, $customer->id);
        $data = $this->normalizeBooleans($request, $data);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $customer->update($data);

        ActivityLogger::log('updated', 'customers', $customer, "Customer {$customer->customer_code} updated");

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function toggleBlock(Customer $customer): JsonResponse
    {
        $customer->update([
            'account_status' => $customer->account_status === 'blocked' ? 'active' : 'blocked',
        ]);

        ActivityLogger::log('updated', 'customers', $customer, "Customer {$customer->customer_code} status changed to {$customer->account_status}");

        return response()->json([
            'success' => true,
            'message' => 'Customer status updated.',
            'status' => $customer->account_status,
        ]);
    }

    public function resetPassword(Request $request, Customer $customer): JsonResponse
    {
        $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $customer->update(['password' => $request->password]);

        if ($customer->user) {
            $customer->user->update(['password' => $request->password]);
        }

        ActivityLogger::log('updated', 'customers', $customer, "Password reset for {$customer->customer_code}");

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    public function verifyEmail(Customer $customer): JsonResponse
    {
        $customer->update(['email_verified' => true]);

        return response()->json(['success' => true, 'message' => 'Email marked as verified.']);
    }

    public function verifyMobile(Customer $customer): JsonResponse
    {
        $customer->update(['mobile_verified' => true]);

        return response()->json(['success' => true, 'message' => 'Mobile marked as verified.']);
    }

    public function export(): StreamedResponse
    {
        $filename = 'customers-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Code', 'Name', 'Email', 'Mobile', 'Status', 'Registered', 'Orders', 'Total Spend']);

            Customer::query()->chunk(100, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    $stats = app(CustomerStatsService::class)->stats($c);
                    fputcsv($handle, [
                        $c->customer_code, $c->full_name, $c->email, $c->mobile,
                        $c->account_status, $c->created_at?->format('Y-m-d'),
                        $stats['total_orders'], $stats['total_spend'],
                    ]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateCustomer(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,'.$id],
            'mobile' => ['required', 'string', 'max:20', 'unique:customers,mobile,'.$id],
            'country_code' => ['required', 'string', 'max:10'],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'anniversary_date' => ['nullable', 'date'],
            'registration_source' => ['required', 'in:website,app,admin'],
            'login_type' => ['required', 'in:email,mobile,google,facebook'],
            'account_status' => ['required', 'in:active,inactive,blocked'],
            'customer_type' => ['nullable', 'string', 'max:50'],
            'newsletter_subscription' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
            'mobile_verified' => ['nullable', 'boolean'],
        ]);
    }

    private function normalizeBooleans(Request $request, array $data): array
    {
        $data['newsletter_subscription'] = $request->boolean('newsletter_subscription');
        $data['email_verified'] = $request->boolean('email_verified');
        $data['mobile_verified'] = $request->boolean('mobile_verified');

        return $data;
    }
}
