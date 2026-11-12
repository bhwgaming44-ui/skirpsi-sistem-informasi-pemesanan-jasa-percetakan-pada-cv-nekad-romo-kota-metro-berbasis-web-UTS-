<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'store' => StoreSetting::current(),
        ]);
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $store = StoreSetting::current();

        $validated = $request->validate([
            'nama_perusahaan' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string', 'max:500'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'email_kontak' => ['nullable', 'email', 'max:100'],
            'jam_operasional' => ['required', 'string', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'biaya_kirim' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = 'logo-'.Str::slug($request->string('nama_perusahaan')->toString()).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images'), $name);
            $validated['logo'] = $name;
        } else {
            unset($validated['logo']);
        }

        $store->fill($validated)->save();

        return back()->with('success', 'Profil toko dan informasi kontak berhasil diperbarui.');
    }

    public function updatePayment(Request $request): RedirectResponse
    {
        $payment = PaymentSetting::query()->latest('id_konfigurasi')->first() ?? new PaymentSetting;

        $validated = $request->validate([
            'nama_gateway' => ['required', 'string', 'max:50'],
            'server_key' => ['nullable', 'string', 'max:255'],
            'client_key' => ['nullable', 'string', 'max:255'],
            'merchant_id' => ['nullable', 'string', 'max:50'],
        ]);

        if (blank($validated['server_key'] ?? null)) {
            unset($validated['server_key']);
        }

        if (blank($validated['client_key'] ?? null)) {
            unset($validated['client_key']);
        }

        $validated['is_production'] = $request->boolean('is_production');
        $validated['is_active'] = $request->boolean('is_active');

        $payment->fill($validated)->save();

        if ($payment->is_active) {
            PaymentSetting::query()
                ->whereKeyNot($payment->id_konfigurasi)
                ->update(['is_active' => false]);
        }

        $message = $payment->isConfigured()
            ? 'Konfigurasi Midtrans tersimpan. Payment gateway siap digunakan.'
            : 'Konfigurasi tersimpan. Kunci masih placeholder, sistem berjalan dalam mode simulasi pembayaran.';

        return back()->with('success', $message);
    }
}
