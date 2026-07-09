<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Sale;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicOrderTrackingController extends Controller
{
    public function show(string $token): View
    {
        $order = OnlineOrder::query()
            ->with('items')
            ->where('tracking_token', $token)
            ->firstOrFail();

        $storeSetting = StoreSetting::current();
        $paymentMethods = $this->availableOnlinePaymentMethods($storeSetting);

        return view('public-order-tracking', [
            'order' => $order,
            'storeSetting' => $storeSetting,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function uploadPaymentProof(Request $request, string $token): RedirectResponse
    {
        $order = OnlineOrder::query()
            ->where('tracking_token', $token)
            ->firstOrFail();

        $storeSetting = StoreSetting::current();
        $paymentMethods = $this->availableOnlinePaymentMethods($storeSetting);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys($paymentMethods))],
            'payment_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'payment_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
        ]);

        $paymentMethod = $validated['payment_method'];

        if ($paymentMethod !== Sale::PAYMENT_CASH && ! $request->hasFile('payment_proof') && ! $order->payment_proof_path) {
            return back()
                ->withInput()
                ->withErrors([
                    'payment_proof' => 'Bukti pembayaran wajib diupload untuk QRIS, Transfer, atau EDC.',
                ]);
        }

        $data = [
            'payment_method' => $paymentMethod,
            'payment_note' => $validated['payment_note'] ?? null,
            'admin_payment_note' => null,
            'payment_confirmed_at' => null,
            'payment_rejected_at' => null,
        ];

        if ($paymentMethod === Sale::PAYMENT_CASH) {
            if ($order->payment_proof_path && Storage::disk('public')->exists($order->payment_proof_path)) {
                Storage::disk('public')->delete($order->payment_proof_path);
            }

            $data['payment_status'] = OnlineOrder::PAYMENT_UNPAID;
            $data['payment_proof_path'] = null;
        } else {
            $data['payment_status'] = OnlineOrder::PAYMENT_WAITING_CONFIRMATION;
        }

        if ($request->hasFile('payment_proof')) {
            if ($order->payment_proof_path && Storage::disk('public')->exists($order->payment_proof_path)) {
                Storage::disk('public')->delete($order->payment_proof_path);
            }

            $data['payment_proof_path'] = $request->file('payment_proof')
                ->store('payment-proofs', 'public');
        }

        $order->update($data);

        $message = $paymentMethod === Sale::PAYMENT_CASH
            ? 'Metode pembayaran Tunai / COD berhasil dipilih. Pembayaran akan dikonfirmasi saat pesanan diterima.'
            : 'Bukti pembayaran berhasil dikirim. Silakan tunggu konfirmasi admin.';

        return redirect()
            ->route('public.tracking', $order->tracking_token)
            ->with('success', $message);
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
            $methods[Sale::PAYMENT_TRANSFER] = 'Transfer';
        }

        if ($storeSetting->payment_edc_enabled) {
            $methods[Sale::PAYMENT_EDC] = 'EDC / Kartu';
        }

        if (empty($methods)) {
            $methods[Sale::PAYMENT_CASH] = 'Tunai / COD';
        }

        return $methods;
    }
}