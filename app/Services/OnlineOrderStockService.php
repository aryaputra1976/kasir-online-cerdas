<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OnlineOrderStockService
{
    public function confirmPaymentAndDeductStock(OnlineOrder $order, ?string $adminPaymentNote = null): OnlineOrder
    {
        return DB::transaction(function () use ($order, $adminPaymentNote) {
            $lockedOrder = OnlineOrder::query()
                ->with('items')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedOrder->canConfirmPayment()) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Pembayaran order ini tidak dalam status menunggu konfirmasi.',
                ]);
            }

            $lockedOrder->update([
                'payment_status' => OnlineOrder::PAYMENT_PAID,
                'paid_at' => now(),
                'payment_confirmed_at' => now(),
                'payment_rejected_at' => null,
                'admin_payment_note' => $adminPaymentNote,
            ]);

            $this->deductStockForLockedOrder($lockedOrder);

            return $lockedOrder->refresh();
        });
    }

    public function deductStock(OnlineOrder $order): OnlineOrder
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = OnlineOrder::query()
                ->with('items')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->deductStockForLockedOrder($lockedOrder);

            return $lockedOrder->refresh();
        });
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

            $product = Product::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    'stock' => "Produk {$item->product_name} tidak ditemukan.",
                ]);
            }

            $quantity = (int) $item->quantity;
            $stockBefore = (int) $product->stock;

            if ($quantity < 1) {
                continue;
            }

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
        }

        $order->update([
            'stock_deducted_at' => now(),
        ]);
    }
}