<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\Customer;

class PosController extends Controller
{
    private const CART_SESSION_KEY = 'pos.cart';

    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
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

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'customer_code', 'name', 'phone']);

        $storeSetting = StoreSetting::current();
        $taxPercentage = (float) $storeSetting->tax_percentage;
        $paymentMethods = $this->availablePaymentMethods($storeSetting);

        $cart = $this->getCart();
        $totals = $this->calculateCartTotals($cart, $taxPercentage);

        return view('pos-system', compact(
            'products',
            'categories',
            'customers',
            'cart',
            'totals',
            'search',
            'categoryId',
            'taxPercentage',
            'paymentMethods',
            'storeSetting'
        ));
    }
    public function addToCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'quantity.required' => 'Jumlah produk wajib diisi.',
            'quantity.min' => 'Jumlah produk minimal 1.',
        ]);

        $product = Product::query()
            ->where('is_active', true)
            ->findOrFail($validated['product_id']);

        $cart = $this->getCart();
        $productId = (string) $product->id;

        $currentQuantity = $cart[$productId]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + (int) $validated['quantity'];

        if ($newQuantity > $product->stock) {
            return redirect()
                ->route('pos.index')
                ->with('error', "Stok {$product->name} tidak mencukupi. Stok tersedia {$product->stock} {$product->unit}.");
        }

        $cart[$productId] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'selling_price' => (float) $product->selling_price,
            'quantity' => $newQuantity,
            'stock' => $product->stock,
        ];

        session()->put(self::CART_SESSION_KEY, $cart);

        return redirect()
            ->route('pos.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function updateCart(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'quantity.required' => 'Jumlah produk wajib diisi.',
            'quantity.min' => 'Jumlah produk minimal 1.',
        ]);

        $cart = $this->getCart();
        $productId = (string) $product->id;

        if (! isset($cart[$productId])) {
            return redirect()
                ->route('pos.index')
                ->with('error', 'Produk tidak ditemukan di keranjang.');
        }

        if ((int) $validated['quantity'] > $product->stock) {
            return redirect()
                ->route('pos.index')
                ->with('error', "Stok {$product->name} tidak mencukupi. Stok tersedia {$product->stock} {$product->unit}.");
        }

        $cart[$productId]['quantity'] = (int) $validated['quantity'];
        $cart[$productId]['stock'] = $product->stock;

        session()->put(self::CART_SESSION_KEY, $cart);

        return redirect()
            ->route('pos.index')
            ->with('success', 'Jumlah produk di keranjang berhasil diperbarui.');
    }

    public function removeCart(Product $product): RedirectResponse
    {
        $cart = $this->getCart();
        $productId = (string) $product->id;

        unset($cart[$productId]);

        session()->put(self::CART_SESSION_KEY, $cart);

        return redirect()
            ->route('pos.index')
            ->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    public function clearCart(): RedirectResponse
    {
        session()->forget(self::CART_SESSION_KEY);

        return redirect()
            ->route('pos.index')
            ->with('success', 'Keranjang berhasil dikosongkan.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = $this->getCart();

        if (empty($cart)) {
            return redirect()
                ->route('pos.index')
                ->with('error', 'Keranjang masih kosong.');
        }

        $storeSetting = StoreSetting::current();
        $availablePaymentMethodKeys = array_keys($this->availablePaymentMethods($storeSetting));

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')],
            'customer_name' => ['nullable', 'string', 'max:191'],
            'payment_method' => [
                'required',
                Rule::in($availablePaymentMethodKeys),
            ],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $sale = DB::transaction(function () use ($cart, $validated) {
            $subtotalAmount = 0;

            foreach ($cart as $item) {
                $subtotalAmount += (float) $item['selling_price'] * (int) $item['quantity'];
            }

            $discountAmount = (float) ($validated['discount_amount'] ?? 0);

            if ($discountAmount > $subtotalAmount) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Diskon tidak boleh lebih besar dari subtotal transaksi.',
                ]);
            }

            $storeSetting = StoreSetting::current();
            $taxPercentage = (float) $storeSetting->tax_percentage;

            $taxableAmount = max(0, $subtotalAmount - $discountAmount);
            $taxAmount = round($taxableAmount * ($taxPercentage / 100), 2);
            $totalAmount = max(0, $taxableAmount + $taxAmount);

            $paymentMethod = $validated['payment_method'];

            $paidAmount = (float) ($validated['paid_amount'] ?? 0);

            if ($paymentMethod !== Sale::PAYMENT_CASH && $paidAmount <= 0) {
                $paidAmount = $totalAmount;
            }

            if ($paidAmount < $totalAmount) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Jumlah bayar tidak boleh kurang dari total transaksi.',
                ]);
            }

            $selectedCustomer = null;

            if (! empty($validated['customer_id'])) {
                $selectedCustomer = Customer::query()
                    ->where('is_active', true)
                    ->find($validated['customer_id']);
            }

            $customerName = $selectedCustomer?->name
                ?: ($validated['customer_name'] ?? null);

            $sale = Sale::create([
                'customer_id' => $selectedCustomer?->id,
                'invoice_no' => $this->generateInvoiceNo(),
                'sale_date' => now(),
                'customer_name' => $customerName,
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $totalAmount,
                'status' => Sale::STATUS_COMPLETED,
                'note' => $validated['note'] ?? null,
            ]);

            if ($selectedCustomer) {
                $selectedCustomer->update([
                    'last_transaction_at' => now(),
                ]);
            }

            foreach ($cart as $item) {
                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = (int) $item['quantity'];

                if ($quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->name} tidak mencukupi. Stok tersedia {$product->stock} {$product->unit}.",
                    ]);
                }

                $unitPrice = (float) $product->selling_price;
                $itemSubtotal = $unitPrice * $quantity;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal_amount' => $itemSubtotal,
                ]);

                $stockBefore = (int) $product->stock;
                $stockAfter = $stockBefore - $quantity;

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => StockMovement::TYPE_OUT,
                    'quantity_change' => $quantity * -1,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'movement_date' => now()->toDateString(),
                    'reference_no' => $sale->invoice_no,
                    'note' => 'Penjualan melalui Kasir POS',
                ]);

                $product->update([
                    'stock' => $stockAfter,
                ]);
            }

            return $sale;
        });

        session()->forget(self::CART_SESSION_KEY);

        return redirect()
            ->route('pos.receipt', $sale)
            ->with('success', "Transaksi {$sale->invoice_no} berhasil disimpan. Stok produk sudah diperbarui.");

    }

    public function receipt(Sale $sale): View
    {
        $sale->load('items');

        return view('pos-receipt', compact('sale'));
    }
        
    private function getCart(): array
    {
        return session()->get(self::CART_SESSION_KEY, []);
    }

    private function calculateCartTotals(array $cart, float $taxPercentage = 0): array
    {
        $subtotal = 0;
        $totalItems = 0;

        foreach ($cart as $item) {
            $quantity = (int) $item['quantity'];
            $subtotal += (float) $item['selling_price'] * $quantity;
            $totalItems += $quantity;
        }

        $discount = 0;
        $taxableAmount = max(0, $subtotal - $discount);
        $taxAmount = round($taxableAmount * ($taxPercentage / 100), 2);
        $total = max(0, $taxableAmount + $taxAmount);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'total_items' => $totalItems,
            'cart_count' => count($cart),
        ];
    }

    private function availablePaymentMethods(?StoreSetting $storeSetting = null): array
    {
        $storeSetting ??= StoreSetting::current();

        $methods = [];

        if ($storeSetting->payment_cash_enabled) {
            $methods[Sale::PAYMENT_CASH] = 'Tunai';
        }

        if ($storeSetting->payment_qris_enabled) {
            $methods[Sale::PAYMENT_QRIS] = 'QRIS';
        }

        if ($storeSetting->payment_transfer_enabled) {
            $methods[Sale::PAYMENT_TRANSFER] = 'Transfer';
        }

        if ($storeSetting->payment_edc_enabled) {
            $methods[Sale::PAYMENT_EDC] = 'EDC / Kartu';
        }

        if (empty($methods)) {
            $methods[Sale::PAYMENT_CASH] = 'Tunai';
        }

        return $methods;
    }

    private function generateInvoiceNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "POS-{$date}";

        $latestCount = Sale::query()
            ->whereDate('sale_date', now()->toDateString())
            ->count() + 1;

        do {
            $invoiceNo = $prefix . '-' . str_pad((string) $latestCount, 4, '0', STR_PAD_LEFT);
            $latestCount++;
        } while (Sale::where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }
}
