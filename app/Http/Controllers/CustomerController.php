<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));
        $status = $request->input('status');

        $customersQuery = Customer::query()
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

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $inactiveCustomers = Customer::where('is_active', false)->count();

        $customersWithTransactions = Customer::query()
            ->whereIn('name', function ($query) {
                $query->select('customer_name')
                    ->from('sales')
                    ->whereNotNull('customer_name')
                    ->where('customer_name', '!=', '')
                    ->where('customer_name', '!=', 'Umum')
                    ->where('customer_name', '!=', 'Customer Umum');
            })
            ->count();

        $totalCustomerOmzet = Sale::query()
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->where('customer_name', '!=', 'Umum')
            ->where('customer_name', '!=', 'Customer Umum')
            ->sum('total_amount');

        return view('customers', compact(
            'customers',
            'search',
            'status',
            'totalCustomers',
            'activeCustomers',
            'inactiveCustomers',
            'customersWithTransactions',
            'totalCustomerOmzet'
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
        $sales = Sale::query()
            ->with('items')
            ->where('customer_name', $customer->name)
            ->latest('sale_date')
            ->paginate(10)
            ->withQueryString();

        $totalTransactions = (clone $sales->getCollection())->count();

        $customerStatsQuery = Sale::query()
            ->where('customer_name', $customer->name);

        $totalOmzet = (clone $customerStatsQuery)->sum('total_amount');
        $transactionCount = (clone $customerStatsQuery)->count();
        $lastSale = (clone $customerStatsQuery)->latest('sale_date')->first();

        return view('customer-details', compact(
            'customer',
            'sales',
            'totalOmzet',
            'transactionCount',
            'lastSale'
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