<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteOnlineOrderService
{
    public function __construct(
        private readonly InvoiceNumberService $invoiceNumberService
    ) {
    }

    public function complete(OnlineOrder $order, bool $codPaymentReceived = false): Sale
    {
        return DB::transaction(function () use ($order, $codPaymentReceived) {
            $lockedOrder = OnlineOrder::query()
                ->with(['items', 'sale'])
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateOrderCanBeCompleted($lockedOrder, $codPaymentReceived);
            $this->deductStockForLockedOrder($lockedOrder);

            $lockedOrder->refresh();
            $completedAt = now();

            $updateData = [
                'status' => OnlineOrder::STATUS_COMPLETED,
                'processed_at' => $lockedOrder->processed_at ?: $completedAt,
                'completed_at' => $completedAt,
            ];

            if ($lockedOrder->payment_method === Sale::PAYMENT_CASH) {
                $updateData['payment_status'] = OnlineOrder::PAYMENT_PAID;
                $updateData['paid_at'] = $lockedOrder->paid_at ?: $completedAt;
                $updateData['payment_confirmed_at'] = $lockedOrder->payment_confirmed_at ?: $completedAt;
                $updateData['payment_rejected_at'] = null;
                $updateData['admin_payment_note'] = $lockedOrder->admin_payment_note
                    ?: 'Pembayaran Tunai / COD diterima saat order selesai.';
            }

            $lockedOrder->update($updateData);
            $lockedOrder->refresh();

            return $this->createSaleForLockedOrder($lockedOrder);
        });
    }

    private function validateOrderCanBeCompleted(OnlineOrder $order, bool $codPaymentReceived): void
    {
        if (! $order->canComplete()) {
            throw ValidationException::withMessages([
                'status' => 'Order ini tidak bisa diselesaikan.',
            ]);
        }

        if ($order->payment_method === Sale::PAYMENT_CASH && ! $codPaymentReceived) {
            throw ValidationException::withMessages([
                'cod_payment_received' => 'Konfirmasi penerimaan pembayaran COD wajib dicentang.',
            ]);
        }

        if (
            $order->payment_method !== Sale::PAYMENT_CASH
            && $order->payment_status !== OnlineOrder::PAYMENT_PAID
        ) {
            throw ValidationException::withMessages([
                'payment_status' => 'Order non-COD hanya bisa diselesaikan setelah pembayaran dibayar.',
            ]);
        }
    }

    private function deductStockForLockedOrder(OnlineOrder $order): void
    {
        if ($order->stock_deducted_at) {
            return;
        }

        if ($order->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Order tidak memiliki item produk.',
            ]);
        }

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                throw ValidationException::withMessages([
                    'stock' => "Produk {$item->product_name} tidak memiliki relasi produk aktif.",
                ]);
            }

            if ((int) $item->quantity < 1) {
                throw ValidationException::withMessages([
                    'items' => "Jumlah item {$item->product_name} tidak valid.",
                ]);
            }
        }

        $productIds = $order->items
            ->pluck('product_id')
            ->unique()
            ->sort()
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $products->get($item->product_id);

            if (! $product) {
                throw ValidationException::withMessages([
                    'stock' => "Produk {$item->product_name} tidak ditemukan.",
                ]);
            }

            $quantity = (int) $item->quantity;
            $stockBefore = (int) $product->stock;

            if ($quantity > $stockBefore) {
                throw ValidationException::withMessages([
                    'stock' => "Stok {$product->name} tidak cukup. Stok tersedia {$stockBefore} {$product->unit}, dibutuhkan {$quantity} {$product->unit}.",
                ]);
            }

            $stockAfter = $stockBefore - $quantity;

            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => StockMovement::TYPE_OUT,
                'quantity_change' => $quantity * -1,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'movement_date' => now()->toDateString(),
                'reference_no' => $order->order_no,
                'note' => "Stok keluar otomatis dari order online {$order->order_no} atas nama {$order->customer_name}.",
            ]);

            $product->update([
                'stock' => $stockAfter,
            ]);

            $product->stock = $stockAfter;
        }

        $order->update([
            'stock_deducted_at' => now(),
        ]);
    }

    private function createSaleForLockedOrder(OnlineOrder $order): Sale
    {
        if ($order->sale_id && $order->sale) {
            return $order->sale;
        }

        if ($order->payment_status !== OnlineOrder::PAYMENT_PAID) {
            throw ValidationException::withMessages([
                'payment_status' => 'Order hanya bisa masuk ke laporan penjualan setelah pembayaran dibayar.',
            ]);
        }

        if ($order->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Order tidak memiliki item untuk dikonversi ke penjualan.',
            ]);
        }

        $sale = Sale::create([
            'customer_id' => $order->customer_id,
            'created_by' => auth()->id(),
            'invoice_no' => $this->invoiceNumberService->next('ONL'),
            'sale_date' => $order->completed_at ?: now(),
            'customer_name' => $order->customer_name ?: 'Customer Online',
            'subtotal_amount' => $order->subtotal_amount,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'paid_amount' => $order->total_amount,
            'change_amount' => 0,
            'status' => Sale::STATUS_COMPLETED,
            'note' => trim(
                "Konversi dari order online {$order->order_no}." .
                ($order->note ? " Catatan order: {$order->note}" : '')
            ),
        ]);

        $products = Product::query()
            ->whereIn('id', $order->items->pluck('product_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $products->get($item->product_id);

            $sale->items()->create([
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'purchase_price' => $product?->purchase_price,
                'subtotal_amount' => $item->subtotal_amount,
            ]);
        }

        $order->update([
            'sale_id' => $sale->id,
            'converted_to_sale_at' => now(),
        ]);

        return $sale;
    }
}
