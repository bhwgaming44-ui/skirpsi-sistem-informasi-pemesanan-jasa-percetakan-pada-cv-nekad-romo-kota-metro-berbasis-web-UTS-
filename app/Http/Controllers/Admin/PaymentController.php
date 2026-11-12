<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatewayTransaction;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Services\MidtransService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    public function index(): View
    {
        $settings = PaymentSetting::allModes();
        $active = PaymentSetting::active();

        return view('admin.payments.index', [
            'settings' => $settings,
            'active' => $active,
            'channels' => PaymentSetting::PAYMENT_CHANNELS,
            'stats' => [
                'gateway_paid' => GatewayTransaction::query()
                    ->whereIn('transaction_status', ['settlement', 'capture'])
                    ->count(),
                'gateway_pending' => GatewayTransaction::query()
                    ->where('transaction_status', 'pending')
                    ->count(),
                'gateway_revenue' => (float) Order::query()
                    ->where('status_pembayaran', 'Lunas')
                    ->whereHas('transaksi')
                    ->sum('grand_total'),
            ],
            'recentTransactions' => GatewayTransaction::query()
                ->with('pesanan')
                ->latest('id_transaksi_gateway')
                ->limit(6)
                ->get(),
        ]);
    }

    public function update(Request $request, PaymentSetting $payment): RedirectResponse
    {
        $validated = $request->validate([
            'merchant_name' => ['required', 'string', 'max:100'],
            'server_key' => ['nullable', 'string', 'max:255'],
            'client_key' => ['nullable', 'string', 'max:255'],
            'signature_key' => ['nullable', 'string', 'max:255'],
            'merchant_id' => ['nullable', 'string', 'max:50'],
            'expiry_hours' => ['required', 'integer', 'min:1', 'max:168'],
            'enabled_payments' => ['nullable', 'array'],
            'enabled_payments.*' => ['string', Rule::in(array_keys(PaymentSetting::PAYMENT_CHANNELS))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $keysChanged = false;

        foreach (['server_key', 'client_key', 'signature_key'] as $key) {
            if (blank($validated[$key] ?? null)) {
                unset($validated[$key]);

                continue;
            }

            if ($payment->{$key} !== $validated[$key]) {
                $keysChanged = true;
            }
        }

        $validated['enabled_payments'] = array_values(Arr::wrap($validated['enabled_payments'] ?? []));
        $validated['merchant_id'] = filled($validated['merchant_id'] ?? null) ? $validated['merchant_id'] : null;

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        } else {
            unset($validated['is_active']);
        }

        if ($validated['enabled_payments'] === []) {
            $validated['enabled_payments'] = PaymentSetting::defaultChannels();
        }

        if ($keysChanged) {
            $validated['last_test_status'] = null;
            $validated['last_tested_at'] = null;
        }

        $payment->fill($validated)->save();

        if ($payment->is_active) {
            $payment->activate();
        }

        return back()->with(
            'success',
            $payment->isConfigured()
                ? 'Konfigurasi Midtrans '.$payment->modeLabel().' berhasil disimpan.'
                : 'Konfigurasi tersimpan, tetapi kunci belum cocok dengan mode '.$payment->modeLabel().'.'
        );
    }

    public function activate(PaymentSetting $payment): RedirectResponse
    {
        if (! $payment->isConfigured()) {
            return back()->with('error', 'Mode '.$payment->modeLabel().' belum memiliki kunci yang valid.');
        }

        $payment->activate();

        return back()->with('success', 'Midtrans mode '.$payment->modeLabel().' sekarang aktif digunakan untuk pembayaran pelanggan.');
    }

    public function test(PaymentSetting $payment): RedirectResponse
    {
        if (! $payment->isConfigured()) {
            return back()->with('error', 'Isi Server Key dan Client Key yang cocok dengan mode '.$payment->modeLabel().' terlebih dahulu.');
        }

        $result = $this->midtrans->testConnection($payment);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
