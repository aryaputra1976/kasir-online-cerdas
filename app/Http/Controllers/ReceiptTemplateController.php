<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;

class ReceiptTemplateController extends Controller
{
    public function edit()
    {
        $setting = StoreSetting::current();

        return view('receipt-template-setting', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request)
    {
        $setting = StoreSetting::current();

        $validated = $request->validate([
            'receipt_footer' => ['nullable', 'string', 'max:1000'],
            'receipt_policy_text' => ['nullable', 'string', 'max:1000'],
            'receipt_show_logo' => ['nullable', 'boolean'],
            'receipt_show_sku' => ['nullable', 'boolean'],
            'receipt_show_powered_by' => ['nullable', 'boolean'],
        ]);

        $setting->update([
            'receipt_footer' => $validated['receipt_footer'] ?? null,
            'receipt_policy_text' => $validated['receipt_policy_text'] ?? null,
            'receipt_show_logo' => $request->boolean('receipt_show_logo'),
            'receipt_show_sku' => $request->boolean('receipt_show_sku'),
            'receipt_show_powered_by' => $request->boolean('receipt_show_powered_by'),
        ]);

        return redirect()
            ->route('settings.receipt-template')
            ->with('success', 'Template struk berhasil disimpan.');
    }
}