<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\Customer;

class PublicOrderController extends Controller
{
    private const CART_KEY = 'public_order_cart';

    public function menu(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $categoryId = $request->integer('category_id');

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->transform(function (Product $product) {
            $product->setAttribute('available_stock', $this->resolveProductStock($product));

            return $product;
        });

        $categories = \App\Models\Category::query()
            ->orderBy('name')
            ->get();

        $storeSetting = StoreSetting::current();

        $cartItems = $this->cartItems();
        $totals = $this->calculateTotals($cartItems, (float) $storeSetting->tax_percentage);

        return view('public-menu', [
            'products' => $products,
            'categories' => $categories,
            'storeSetting' => $storeSetting,
            'cartItems' => $cartItems,
            'totals' => $totals,
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        if (! $product->is_active) {
            return back()->with('error', 'Produk tidak aktif.');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $quantity = (int) $validated['quantity'];
        $stock = $this->resolveProductStock($product);

        if ($stock <= 0) {
            return back()->with('error', 'Stok produk sedang kosong.');
        }

        $cart = $this->getCart();
        $currentQuantity = (int) ($cart[$product->id] ?? 0);
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $stock) {
            return back()->with('error', "Stok {$product->name} hanya tersedia {$stock} {$product->unit}.");
        }

        $cart[$product->id] = $newQuantity;
        session()->put(self::CART_KEY, $cart);

        return back()->with('success', "{$product->name} berhasil ditambahkan ke keranjang.");
    }

    public function updateCart(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $quantity = (int) $validated['quantity'];

        $cart = $this->getCart();

        if ($quantity <= 0) {
            unset($cart[$product->id]);
            session()->put(self::CART_KEY, $cart);

            return back()->with('success', "{$product->name} dihapus dari keranjang.");
        }

        $stock = $this->resolveProductStock($product);

        if ($quantity > $stock) {
            return back()->with('error', "Stok {$product->name} hanya tersedia {$stock} {$product->unit}.");
        }

        $cart[$product->id] = $quantity;
        session()->put(self::CART_KEY, $cart);

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    public function removeCart(Product $product): RedirectResponse
    {
        $cart = $this->getCart();

        unset($cart[$product->id]);

        session()->put(self::CART_KEY, $cart);

        return back()->with('success', "{$product->name} dihapus dari keranjang.");
    }

    public function clearCart(): RedirectResponse
    {
        session()->forget(self::CART_KEY);

        return back()->with('success', 'Keranjang berhasil dikosongkan.');
    }

    public function checkout(): View|RedirectResponse
    {
        $storeSetting = StoreSetting::current();
        $cartItems = $this->cartItems();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('public.menu')
                ->with('error', 'Keranjang masih kosong. Pilih produk terlebih dahulu.');
        }

        $totals = $this->calculateTotals($cartItems, (float) $storeSetting->tax_percentage);
        $paymentMethods = $this->availableOnlinePaymentMethods($storeSetting);

        return view('public-checkout', [
            'storeSetting' => $storeSetting,
            'cartItems' => $cartItems,
            'totals' => $totals,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $storeSetting = StoreSetting::current();
        $paymentMethods = $this->availableOnlinePaymentMethods($storeSetting);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'customer_address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(array_keys($paymentMethods))],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'customer_name.required' => 'Nama customer wajib diisi.',
            'customer_phone.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'customer_address.required' => 'Alamat pengantaran wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $cartItems = $this->cartItems();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('public.menu')
                ->with('error', 'Keranjang masih kosong.');
        }

        foreach ($cartItems as $cartItem) {
            $product = $cartItem['product'];
            $quantity = (int) $cartItem['quantity'];
            $availableStock = (int) $cartItem['available_stock'];

            if (! $product->is_active) {
                return back()->with('error', "Produk {$product->name} sudah tidak aktif.");
            }

            if ($quantity > $availableStock) {
                return back()->with('error', "Stok {$product->name} hanya tersedia {$availableStock} {$product->unit}.");
            }
        }

        $totals = $this->calculateTotals($cartItems, (float) $storeSetting->tax_percentage);

        $order = DB::transaction(function () use ($validated, $cartItems, $totals) {
            $order = OnlineOrder::create([
                'customer_id' => $customer->id,
                'order_no' => $this->generateOrderNo(),
                'tracking_token' => $this->generateTrackingToken(),

                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_address' => $validated['customer_address'],

                'subtotal_amount' => $totals['subtotal'],
                'discount_amount' => 0,
                'tax_amount' => $totals['tax_amount'],
                'shipping_amount' => 0,
                'total_amount' => $totals['total'],

                'payment_method' => $validated['payment_method'],
                'payment_status' => OnlineOrder::PAYMENT_UNPAID,
                'status' => OnlineOrder::STATUS_NEW,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $cartItem['product'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'quantity' => $cartItem['quantity'],
                    'unit_price' => $product->selling_price,
                    'subtotal_amount' => $cartItem['subtotal'],
                ]);
            }

            return $order;
        });

        session()->forget(self::CART_KEY);

        $message = $validated['payment_method'] === Sale::PAYMENT_CASH
            ? 'Order berhasil dibuat. Silakan lakukan pembayaran Tunai / COD saat pesanan diterima.'
            : 'Order berhasil dibuat. Silakan lanjut upload bukti pembayaran.';

        return redirect()
            ->route('public.tracking', $order->tracking_token)
            ->with('success', $message);
    }

    private function getCart(): array
    {
        $cart = session()->get(self::CART_KEY, []);

        if (! is_array($cart)) {
            return [];
        }

        return collect($cart)
            ->mapWithKeys(function ($quantity, $productId) {
                return [(int) $productId => max(0, (int) $quantity)];
            })
            ->filter(fn ($quantity) => $quantity > 0)
            ->all();
    }

    private function cartItems()
    {
        $cart = $this->getCart();

        if (empty($cart)) {
            return collect();
        }

        $products = Product::query()
            ->with('category')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(function ($quantity, $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                $availableStock = $this->resolveProductStock($product);
                $product->setAttribute('available_stock', $availableStock);

                return [
                    'product' => $product,
                    'quantity' => (int) $quantity,
                    'available_stock' => $availableStock,
                    'subtotal' => (float) $product->selling_price * (int) $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    private function resolveProductStock(Product $product): int
    {
        $latestMovementStock = StockMovement::query()
            ->where('product_id', $product->id)
            ->latest('id')
            ->value('stock_after');

        if ($latestMovementStock !== null) {
            return max(0, (int) $latestMovementStock);
        }

        foreach (['current_stock', 'stock', 'stock_quantity', 'quantity'] as $column) {
            $value = $product->getAttribute($column);

            if ($value !== null) {
                return max(0, (int) $value);
            }
        }

        return 0;
    }

    private function calculateTotals($cartItems, float $taxPercentage): array
    {
        $subtotal = (float) $cartItems->sum('subtotal');
        $taxAmount = round($subtotal * ($taxPercentage / 100), 2);
        $total = $subtotal + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'shipping' => 0,
            'total' => $total,
            'total_items' => (int) $cartItems->sum('quantity'),
            'cart_count' => $cartItems->count(),
        ];
    }

    private function availableOnlinePaymentMethods(StoreSetting $storeSetting): array
    {
        $methods = [];

        if ($storeSetting->payment_cash_enabled) {
            $methods[Sale::PAYMENT_CASH] = 'Tunai / COD';
        }

        if ($storeSetting->payment_qris_enabled) {
            $methods[Sale::PAYMENT_QRIS] = 'QRIS';
        }

        if ($storeSetting->payment_transfer_enabled) {
            $methods[Sale::PAYMENT_TRANSFER] = 'Transfer Bank';
        }

        if ($storeSetting->payment_edc_enabled) {
            $methods[Sale::PAYMENT_EDC] = 'EDC / Kartu';
        }

        if (empty($methods)) {
            $methods[Sale::PAYMENT_CASH] = 'Tunai / COD';
        }

        return $methods;
    }

    private function syncCustomerFromOnlineOrder(array $data): Customer
    {
        $phone = trim((string) $data['customer_phone']);
        $email = trim((string) ($data['customer_email'] ?? ''));
        $name = trim((string) $data['customer_name']);
        $address = trim((string) $data['customer_address']);

        $customer = Customer::query()
            ->where('phone', $phone)
            ->when($email !== '', function ($query) use ($email) {
                $query->orWhere('email', $email);
            })
            ->first();

        if (! $customer) {
            return Customer::create([
                'customer_code' => $this->generateCustomerCode(),
                'name' => $name,
                'phone' => $phone,
                'email' => $email !== '' ? $email : null,
                'address' => $address,
                'is_active' => true,
                'last_transaction_at' => now(),
            ]);
        }

        $customer->update([
            'name' => $name ?: $customer->name,
            'phone' => $phone ?: $customer->phone,
            'email' => $email !== '' ? $email : $customer->email,
            'address' => $address ?: $customer->address,
            'is_active' => true,
            'last_transaction_at' => now(),
        ]);

        return $customer;
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

    private function generateOrderNo(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        $lastNumber = OnlineOrder::query()
            ->where('order_no', 'like', $prefix . '%')
            ->count() + 1;

        do {
            $orderNo = $prefix . str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
            $lastNumber++;
        } while (OnlineOrder::where('order_no', $orderNo)->exists());

        return $orderNo;
    }

    private function generateTrackingToken(): string
    {
        do {
            $token = Str::random(40);
        } while (OnlineOrder::where('tracking_token', $token)->exists());

        return $token;
    }
}