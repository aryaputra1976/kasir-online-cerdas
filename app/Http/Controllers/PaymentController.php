<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $paymentStatus = $request->input('payment_status', OnlineOrder::PAYMENT_WAITING_CONFIRMATION);
        $paymentMethod = $request->input('payment_method');

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
            ->when($paymentMethod, function ($query) use ($paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'waiting' => OnlineOrder::where('payment_status', OnlineOrder::PAYMENT_WAITING_CONFIRMATION)->count(),
            'paid' => OnlineOrder::where('payment_status', OnlineOrder::PAYMENT_PAID)->count(),
            'rejected' => OnlineOrder::where('payment_status', OnlineOrder::PAYMENT_REJECTED)->count(),
            'unpaid' => OnlineOrder::where('payment_status', OnlineOrder::PAYMENT_UNPAID)->count(),
        ];

        return view('payments', [
            'orders' => $orders,
            'summary' => $summary,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function show(OnlineOrder $order): View
    {
        $order->load('items');

        return view('online-order-detail', [
            'order' => $order,
            'fromPaymentPage' => true,
        ]);
    }

    public function confirm(OnlineOrder $order): RedirectResponse
    {
        if (! $order->canConfirmPayment()) {
            return back()->with('error', 'Pembayaran order ini tidak dalam status menunggu konfirmasi.');
        }

        $order->update([
            'payment_status' => OnlineOrder::PAYMENT_PAID,
            'paid_at' => now(),
            'payment_confirmed_at' => now(),
            'payment_rejected_at' => null,
            'admin_payment_note' => null,
        ]);

        return redirect()
            ->route('payments.show', $order)
            ->with('success', "Pembayaran {$order->order_no} berhasil dikonfirmasi.");
    }

    public function reject(Request $request, OnlineOrder $order): RedirectResponse
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

        return redirect()
            ->route('payments.show', $order)
            ->with('success', "Pembayaran {$order->order_no} ditolak.");
    }
}