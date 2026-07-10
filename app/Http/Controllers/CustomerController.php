<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OnlineOrder;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));
        $status = $request->input('status');

        $customersQuery = Customer::query()
            ->withCount(['sales', 'onlineOrders'])
            ->withSum('sales as sales_total_omzet', 'total_amount')
            ->withSum([
                'onlineOrders as online_orders_total_omzet' => function ($query) {
                    $query->where('status', '!=', OnlineOrder::STATUS_CANCELLED);
                },
            ], 'total_amount')
            ->withMax('sales as last_sale_at', 'sale_date')
            ->withMax('onlineOrders as last_online_order_at', 'created_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('customer_code', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('city', 'like', '%' . $search . '%');
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false));

        $customers = (clone $customersQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $customers->getCollection()->transform(function (Customer $customer) {
            $salesOmzet = (float) ($customer->sales_total_omzet ?? 0);
            $onlineOmzet = (float) ($customer->online_orders_total_omzet ?? 0);

            $lastSaleAt = $customer->last_sale_at
                ? Carbon::parse($customer->last_sale_at)
                : null;

            $lastOnlineOrderAt = $customer->last_online_order_at
                ? Carbon::parse($customer->last_online_order_at)
                : null;

            $lastActivityAt = collect([$lastSaleAt, $lastOnlineOrderAt])
                ->filter()
                ->sortDesc()
                ->first();

            $customer->setAttribute('total_activity_count', (int) $customer->sales_count + (int) $customer->online_orders_count);
            $customer->setAttribute('total_omzet', $salesOmzet + $onlineOmzet);
            $customer->setAttribute('last_activity_at', $lastActivityAt);

            return $customer;
        });

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $inactiveCustomers = Customer::where('is_active', false)->count();

        $customersWithTransactions = Customer::query()
            ->whereHas('sales')
            ->orWhereHas('onlineOrders')
            ->count();

        $totalSalesOmzet = Sale::query()
            ->whereNotNull('customer_id')
            ->sum('total_amount');

        $totalOnlineOrderOmzet = OnlineOrder::query()
            ->whereNotNull('customer_id')
            ->where('status', '!=', OnlineOrder::STATUS_CANCELLED)
            ->sum('total_amount');

        $totalCustomerOmzet = (float) $totalSalesOmzet + (float) $totalOnlineOrderOmzet;

        $topCustomers = Customer::query()
            ->withCount(['sales', 'onlineOrders'])
            ->withSum('sales as sales_total_omzet', 'total_amount')
            ->withSum([
                'onlineOrders as online_orders_total_omzet' => function ($query) {
                    $query->where('status', '!=', OnlineOrder::STATUS_CANCELLED);
                },
            ], 'total_amount')
            ->get()
            ->map(function (Customer $customer) {
                $customer->total_omzet = (float) ($customer->sales_total_omzet ?? 0)
                    + (float) ($customer->online_orders_total_omzet ?? 0);

                $customer->total_activity_count = (int) $customer->sales_count
                    + (int) $customer->online_orders_count;

                return $customer;
            })
            ->filter(fn (Customer $customer) => $customer->total_omzet > 0 || $customer->total_activity_count > 0)
            ->sortByDesc('total_omzet')
            ->take(5)
            ->values();

        $newestCustomers = Customer::query()
            ->latest()
            ->limit(5)
            ->get();

        return view('customers', compact(
            'customers',
            'search',
            'status',
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers',
            'customersWithTransactions',
            'totalCustomerOmzet',
            'topCustomers',
            'newestCustomers'
        ));
    }

    public function create(): View
    {
        $customer = new Customer([
            'is_active' => true,
        ]);

        return view('customer-form', [
            'customer' => $customer,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCustomer($request);

        $validated['customer_code'] = $this->generateCustomerCode();

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $salesBaseQuery = Sale::query()
            ->with('items')
            ->where(function ($query) use ($customer) {
                $query->where('customer_id', $customer->id)
                    ->orWhere(function ($fallbackQuery) use ($customer) {
                        $fallbackQuery
                            ->whereNull('customer_id')
                            ->where('customer_name', $customer->name);
                    });
            });

        $onlineOrdersBaseQuery = OnlineOrder::query()
            ->with(['items', 'sale'])
            ->where(function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);

                if ($customer->phone) {
                    $query->orWhere(function ($phoneQuery) use ($customer) {
                        $phoneQuery
                            ->whereNull('customer_id')
                            ->where('customer_phone', $customer->phone);
                    });
                }

                if ($customer->email) {
                    $query->orWhere(function ($emailQuery) use ($customer) {
                        $emailQuery
                            ->whereNull('customer_id')
                            ->where('customer_email', $customer->email);
                    });
                }
            });

        $sales = (clone $salesBaseQuery)
            ->latest('sale_date')
            ->paginate(5, ['*'], 'sales_page')
            ->withQueryString();

        $onlineOrders = (clone $onlineOrdersBaseQuery)
            ->latest()
            ->paginate(5, ['*'], 'orders_page')
            ->withQueryString();

        $salesCount = (clone $salesBaseQuery)->count();
        $onlineOrderCount = (clone $onlineOrdersBaseQuery)->count();

        $totalSalesOmzet = (clone $salesBaseQuery)->sum('total_amount');

        $totalOnlineOrderOmzet = (clone $onlineOrdersBaseQuery)
            ->where('status', '!=', OnlineOrder::STATUS_CANCELLED)
            ->sum('total_amount');

        $totalOmzet = (float) $totalSalesOmzet + (float) $totalOnlineOrderOmzet;

        $lastSale = (clone $salesBaseQuery)
            ->latest('sale_date')
            ->first();

        $lastOnlineOrder = (clone $onlineOrdersBaseQuery)
            ->latest()
            ->first();

        $lastActivityAt = collect([
            $lastSale?->sale_date,
            $lastOnlineOrder?->created_at,
        ])
            ->filter()
            ->sortDesc()
            ->first();

        return view('customer-details', compact(
            'customer',
            'sales',
            'onlineOrders',
            'salesCount',
            'onlineOrderCount',
            'totalSalesOmzet',
            'totalOnlineOrderOmzet',
            'totalOmzet',
            'lastSale',
            'lastOnlineOrder',
            'lastActivityAt'
        ));
    }

    public function edit(Customer $customer): View
    {
        return view('customer-form', [
            'customer' => $customer,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $this->validateCustomer($request, $customer);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('customers', 'phone')->ignore($customer?->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('customers', 'email')->ignore($customer?->id),
            ],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function generateCustomerCode(): string
    {
        $prefix = 'CUST-' . now()->format('Ymd') . '-';

        $lastNumber = Customer::query()
            ->where('customer_code', 'like', $prefix . '%')
            ->count() + 1;

        do {
            $code = $prefix . str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
            $lastNumber++;
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}