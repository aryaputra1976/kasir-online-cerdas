<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessOnlineOrderService
{
    public function process(OnlineOrder $order): OnlineOrder
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = OnlineOrder::query()
                ->with('items')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateOrderCanBeProcessed($lockedOrder);
            $this->deductStockForLockedOrder($lockedOrder);

            $lockedOrder->refresh();

            $lockedOrder->update([
                'status' => OnlineOrder::STATUS_PROCESSING,
                'processed_at' => $lockedOrder->processed_at ?: now(),
            ]);

            return $lockedOrder->refresh();
        });
    }

    private function validateOrderCanBeProcessed(OnlineOrder $order): void
    {
        if (! $order->canProcess()) {
            throw ValidationException::withMessages([
                'status' => 'Order belum bisa diproses. Pastikan pembayaran sudah dikonfirmasi.',
            ]);
        }

        if (
            $order->payment_method !== Sale::PAYMENT_CASH
            && $order->payment_status !== OnlineOrder::PAYMENT_PAID
        ) {
            throw ValidationException::withMessages([
                'payment_status' => 'Order non-COD hanya bisa diproses setelah pembayaran dibayar.',
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
                'created_by' => auth()->id(),
                'source_type' => StockMovement::SOURCE_ONLINE_ORDER,
                'source_id' => $order->id,
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
}
