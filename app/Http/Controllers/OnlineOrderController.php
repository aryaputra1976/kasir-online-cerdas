<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Sale;
use App\Services\OnlineOrderStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnlineOrderController extends Controller
{
    public function __construct(
        private readonly OnlineOrderStockService $onlineOrderStockService
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $paymentStatus = $request->input('payment_status');
        $status = $request->input('status');

        $orders = OnlineOrder::query()
            ->withCount('items')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('order_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($paymentStatus, function ($query) use ($paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total' => OnlineOrder::count(),
            'waiting' => OnlineOrder::where('payment_status', OnlineOrder::PAYMENT_WAITING_CONFIRMATION)->count(),
            'paid' => OnlineOrder::where('payment_status', OnlineOrder::PAYMENT_PAID)->count(),
            'rejected' => OnlineOrder::where('payment_status', OnlineOrder::PAYMENT_REJECTED)->count(),
        ];

        return view('online-orders', [
            'orders' => $orders,
            'summary' => $summary,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
            'status' => $status,
        ]);
    }

    public function show(OnlineOrder $order): View
    {
        $order->load('items');

        return view('online-order-detail', [
            'order' => $order,
        ]);
    }

    public function confirmPayment(Request $request, OnlineOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'admin_payment_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $confirmedOrder = $this->onlineOrderStockService
            ->confirmPaymentAndDeductStock(
                $order,
                $validated['admin_payment_note'] ?? null
            );

        return back()->with('success', "Pembayaran {$confirmedOrder->order_no} berhasil dikonfirmasi dan stok produk otomatis dikurangi.");
    }

    public function rejectPayment(Request $request, OnlineOrder $order): RedirectResponse
    {
        if (! $order->canRejectPayment()) {
            return back()->with('error', 'Pembayaran order ini tidak dalam status menunggu konfirmasi.');
        }

        $validated = $request->validate([
            'admin_payment_note' => ['required', 'string', 'max:1000'],
        ], [
            'admin_payment_note.required' => 'Catatan penolakan wajib diisi.',
        ]);

        $order->update([
            'payment_status' => OnlineOrder::PAYMENT_REJECTED,
            'payment_rejected_at' => now(),
            'payment_confirmed_at' => null,
            'admin_payment_note' => $validated['admin_payment_note'],
        ]);

        return back()->with('success', "Pembayaran {$order->order_no} ditolak.");
    }

    public function process(OnlineOrder $order): RedirectResponse
    {
        if (! $order->canProcess()) {
            return back()->with('error', 'Order belum bisa diproses. Pastikan pembayaran sudah dikonfirmasi.');
        }

        if (
            $order->payment_method !== Sale::PAYMENT_CASH
            && $order->payment_status !== OnlineOrder::PAYMENT_PAID
        ) {
            return back()->with('error', 'Order non-COD hanya bisa diproses setelah pembayaran dibayar.');
        }

        $this->onlineOrderStockService->deductStock($order);

        $order->refresh();

        $order->update([
            'status' => OnlineOrder::STATUS_PROCESSING,
            'processed_at' => now(),
        ]);

        return back()->with('success', "Order {$order->order_no} mulai diproses.");
    }

    public function complete(OnlineOrder $order): RedirectResponse
    {
        if (! $order->canComplete()) {
            return back()->with('error', 'Order ini tidak bisa diselesaikan.');
        }

        if (
            $order->payment_method !== Sale::PAYMENT_CASH
            && $order->payment_status !== OnlineOrder::PAYMENT_PAID
        ) {
            return back()->with('error', 'Order non-COD hanya bisa diselesaikan setelah pembayaran dibayar.');
        }

        $this->onlineOrderStockService->deductStock($order);

        $order->refresh();

        $updateData = [
            'status' => OnlineOrder::STATUS_COMPLETED,
            'processed_at' => $order->processed_at ?: now(),
            'completed_at' => now(),
        ];

        if ($order->payment_method === Sale::PAYMENT_CASH) {
            $updateData['payment_status'] = OnlineOrder::PAYMENT_PAID;
            $updateData['paid_at'] = $order->paid_at ?: now();
            $updateData['payment_confirmed_at'] = $order->payment_confirmed_at ?: now();
            $updateData['payment_rejected_at'] = null;
            $updateData['admin_payment_note'] = $order->admin_payment_note ?: 'Pembayaran Tunai / COD diterima saat order selesai.';
        }

        $order->update($updateData);

        return back()->with('success', "Order {$order->order_no} selesai.");
    }

    public function cancel(OnlineOrder $order): RedirectResponse
    {
        if (! $order->canCancel()) {
            return back()->with('error', 'Order tidak bisa dibatalkan karena stok sudah dikurangi atau status order sudah berubah.');
        }

        $order->update([
            'status' => OnlineOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return back()->with('success', "Order {$order->order_no} dibatalkan.");
    }
}