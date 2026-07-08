<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreSettingController extends Controller
{
    public function edit()
    {
        $setting = StoreSetting::current();

        return view('store-setting', [
            'setting' => $setting,
        ]);
    }

    public function update(Request $request)
    {
        $setting = StoreSetting::current();

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:150'],
            'owner_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = [
            'store_name' => $validated['store_name'],
            'owner_name' => $validated['owner_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'tax_percentage' => $validated['tax_percentage'] ?? 0,
            'receipt_footer' => $validated['receipt_footer'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('store-logos', 'public');
        }

        $setting->update($data);

        return redirect()
            ->route('settings.store')
            ->with('success', 'Pengaturan toko berhasil disimpan.');
    }

    public function removeLogo()
    {
        $setting = StoreSetting::current();

        if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
            Storage::disk('public')->delete($setting->logo_path);
        }

        $setting->update([
            'logo_path' => null,
        ]);

        return redirect()
            ->route('settings.store')
            ->with('success', 'Logo toko berhasil dihapus.');
    }
}