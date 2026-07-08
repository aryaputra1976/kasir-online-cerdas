<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentMethodSettingController extends Controller
{
    public function edit()
    {
        $setting = StoreSetting::current();

        return view('payment-method-setting', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request)
    {
        $setting = StoreSetting::current();

        $validated = $request->validate([
            'payment_cash_enabled' => ['nullable', 'boolean'],
            'payment_qris_enabled' => ['nullable', 'boolean'],
            'payment_transfer_enabled' => ['nullable', 'boolean'],
            'payment_edc_enabled' => ['nullable', 'boolean'],

            'qris_merchant_name' => ['nullable', 'string', 'max:150'],
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'qris_note' => ['nullable', 'string', 'max:1000'],

            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'transfer_note' => ['nullable', 'string', 'max:1000'],

            'edc_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $cashEnabled = $request->boolean('payment_cash_enabled');
        $qrisEnabled = $request->boolean('payment_qris_enabled');
        $transferEnabled = $request->boolean('payment_transfer_enabled');
        $edcEnabled = $request->boolean('payment_edc_enabled');

        if (! $cashEnabled && ! $qrisEnabled && ! $transferEnabled && ! $edcEnabled) {
            return back()
                ->withInput()
                ->withErrors([
                    'payment_method' => 'Minimal satu metode pembayaran harus aktif.',
                ]);
        }

        $data = [
            'payment_cash_enabled' => $cashEnabled,
            'payment_qris_enabled' => $qrisEnabled,
            'payment_transfer_enabled' => $transferEnabled,
            'payment_edc_enabled' => $edcEnabled,

            'qris_merchant_name' => $validated['qris_merchant_name'] ?? null,
            'qris_note' => $validated['qris_note'] ?? null,

            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'transfer_note' => $validated['transfer_note'] ?? null,

            'edc_note' => $validated['edc_note'] ?? null,
        ];

        if ($request->hasFile('qris_image')) {
            if ($setting->qris_image_path && Storage::disk('public')->exists($setting->qris_image_path)) {
                Storage::disk('public')->delete($setting->qris_image_path);
            }

            $data['qris_image_path'] = $request->file('qris_image')->store('payment-qris', 'public');
        }

        $setting->update($data);

        return redirect()
            ->route('settings.payment-methods')
            ->with('success', 'Metode pembayaran berhasil disimpan.');
    }

    public function removeQris()
    {
        $setting = StoreSetting::current();

        if ($setting->qris_image_path && Storage::disk('public')->exists($setting->qris_image_path)) {
            Storage::disk('public')->delete($setting->qris_image_path);
        }

        $setting->update([
            'qris_image_path' => null,
        ]);

        return redirect()
            ->route('settings.payment-methods')
            ->with('success', 'Gambar QRIS berhasil dihapus.');
    }
}