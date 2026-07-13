<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Sale;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if (! $order->canUpdatePublicPayment()) {
            return back()
                ->withErrors([
                    'payment_method' => 'Pembayaran order ini sudah tidak bisa diubah dari halaman tracking.',
                ]);
        }

        $storeSetting = StoreSetting::current();
        $paymentMethods = $this->availableOnlinePaymentMethods($storeSetting);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys($paymentMethods))],
            'payment_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048', Rule::dimensions()->maxWidth(6000)->maxHeight(6000)],
            'payment_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
            'payment_proof.dimensions' => 'Dimensi bukti pembayaran maksimal 6000x6000 piksel.',
        ]);

        $paymentMethod = $validated['payment_method'];

        if ($paymentMethod !== Sale::PAYMENT_CASH && ! $request->hasFile('payment_proof') && ! $order->payment_proof_path) {
            return back()
                ->withInput()
                ->withErrors([
                    'payment_proof' => 'Bukti pembayaran wajib diupload untuk QRIS, Transfer, atau EDC.',
                ]);
        }

        if ($request->hasFile('payment_proof')) {
            $imageSize = @getimagesize($request->file('payment_proof')->getRealPath());

            if (! $imageSize || $imageSize[0] > 6000 || $imageSize[1] > 6000) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'payment_proof' => 'Dimensi bukti pembayaran maksimal 6000x6000 piksel.',
                    ]);
            }
        }

        $data = [
            'payment_method' => $paymentMethod,
            'payment_note' => $validated['payment_note'] ?? null,
            'admin_payment_note' => null,
            'payment_confirmed_at' => null,
            'payment_rejected_at' => null,
        ];

        $oldProofPath = $order->payment_proof_path;
        $newProofPath = null;

        if ($request->hasFile('payment_proof')) {
            $newProofPath = $request->file('payment_proof')->store('', 'payment_proofs');

            if (! $newProofPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'payment_proof' => 'Bukti pembayaran gagal disimpan. Silakan coba lagi.',
                    ]);
            }
        }

        if ($paymentMethod === Sale::PAYMENT_CASH) {
            $data['payment_status'] = OnlineOrder::PAYMENT_UNPAID;
            $data['payment_proof_path'] = null;
        } else {
            $data['payment_status'] = OnlineOrder::PAYMENT_WAITING_CONFIRMATION;
        }

        if ($newProofPath) {
            $data['payment_proof_path'] = $newProofPath;
        }

        try {
            DB::transaction(fn () => $order->update($data));
        } catch (\Throwable $exception) {
            if ($newProofPath) {
                $this->deletePaymentProof($newProofPath);
            }

            throw $exception;
        }

        if (($paymentMethod === Sale::PAYMENT_CASH || $newProofPath) && $oldProofPath) {
            $this->deletePaymentProof($oldProofPath);
        }

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

    private function deletePaymentProof(?string $path): void
    {
        if (! $path) {
            return;
        }

        // Legacy local/public lookup is retained only so existing private files can be cleaned up.
        foreach (['payment_proofs', 'local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }
}
