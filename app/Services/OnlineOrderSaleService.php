<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OnlineOrderSaleService
{
    public function convertCompletedOrder(OnlineOrder $order): Sale
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = OnlineOrder::query()
                ->with(['items', 'sale'])
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->sale_id && $lockedOrder->sale) {
                return $lockedOrder->sale;
            }

            if ($lockedOrder->status !== OnlineOrder::STATUS_COMPLETED) {
                throw ValidationException::withMessages([
                    'status' => 'Order hanya bisa masuk ke laporan penjualan setelah status order selesai.',
                ]);
            }

            if ($lockedOrder->payment_status !== OnlineOrder::PAYMENT_PAID) {
                throw ValidationException::withMessages([
                    'payment_status' => 'Order hanya bisa masuk ke laporan penjualan setelah pembayaran dibayar.',
                ]);
            }

            if ($lockedOrder->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Order tidak memiliki item untuk dikonversi ke penjualan.',
                ]);
            }

            $sale = Sale::create([
                'customer_id' => $lockedOrder->customer_id,
                'invoice_no' => $this->generateOnlineSaleInvoiceNo(),
                'sale_date' => $lockedOrder->completed_at ?: now(),
                'customer_name' => $lockedOrder->customer_name ?: 'Customer Online',
                'subtotal_amount' => $lockedOrder->subtotal_amount,
                'discount_amount' => $lockedOrder->discount_amount,
                'tax_amount' => $lockedOrder->tax_amount,
                'total_amount' => $lockedOrder->total_amount,
                'payment_method' => $lockedOrder->payment_method,
                'paid_amount' => $lockedOrder->total_amount,
                'change_amount' => 0,
                'status' => Sale::STATUS_COMPLETED,
                'note' => trim(
                    "Konversi dari order online {$lockedOrder->order_no}." .
                    ($lockedOrder->note ? " Catatan order: {$lockedOrder->note}" : '')
                ),
            ]);

            foreach ($lockedOrder->items as $item) {
                $sale->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'sku' => $item->sku,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal_amount' => $item->subtotal_amount,
                ]);
            }

            $lockedOrder->update([
                'sale_id' => $sale->id,
                'converted_to_sale_at' => now(),
            ]);

            return $sale;
        });
    }

    private function generateOnlineSaleInvoiceNo(): string
    {
        $prefix = 'ONL-' . now()->format('Ymd') . '-';

        $lastNumber = Sale::query()
            ->where('invoice_no', 'like', $prefix . '%')
            ->count() + 1;

        do {
            $invoiceNo = $prefix . str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
            $lastNumber++;
        } while (Sale::where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }
}