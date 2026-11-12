<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly PaymentService $payments,
    ) {}

    public function show(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['detail.finishing', 'transaksiTerakhir', 'user']);
        $setting = PaymentSetting::active();
        $configured = $setting !== null && $setting->isConfigured();

        $channels = collect($setting?->paymentChannels() ?? [])
            ->map(function (string $code): ?array {
                $channel = PaymentSetting::PAYMENT_CHANNELS[$code] ?? null;

                return $channel === null ? null : ['code' => $code] + $channel;
            })
            ->filter()
            ->values();

        $expiresAt = ($order->transaksiTerakhir?->created_at ?? $order->tgl_pesan)
            ->copy()
            ->addHours(max(1, (int) ($setting?->expiry_hours ?? 24)));

        return view('payment.show', [
            'order' => $order,
            'setting' => $setting,
            'configured' => $configured,
            'transaction' => $order->transaksiTerakhir,
            'channels' => $channels,
            'expiresAt' => $expiresAt,
        ]);
    }

    public function snapToken(Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        if ($order->isPaid()) {
            return response()->json(['message' => 'Pesanan sudah lunas.'], 422);
        }

        $token = $this->midtrans->createSnapToken($order);

        if ($token === null) {
            return response()->json(['message' => 'Gagal membuat sesi pembayaran Midtrans.'], 502);
        }

        return response()->json(['token' => $token]);
    }

    public function status(Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        if (! $order->isPaid() && $this->midtrans->isReady()) {
            $cacheKey = 'payment-status-check:'.$order->id_pesanan;

            if (Cache::add($cacheKey, true, 8)) {
                $this->midtrans->syncStatus($order);
                $order->refresh();
            }
        }

        return response()->json([
            'paid' => $order->isPaid(),
            'status_pembayaran' => $order->status_pembayaran,
            'status_pesanan' => $order->status_pesanan,
            'transaction_status' => $order->transaksiTerakhir?->transaction_status,
        ]);
    }

    public function simulate(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        abort_if($this->midtrans->isReady(), 403, 'Simulasi hanya tersedia ketika Midtrans belum dikonfigurasi.');

        $validated = $request->validate([
            'hasil' => ['required', 'in:sukses,gagal'],
        ]);

        $this->payments->demoTransaction($order, $validated['hasil'] === 'sukses');

        return redirect()
            ->route('payments.show', $order)
            ->with(
                $validated['hasil'] === 'sukses' ? 'success' : 'error',
                $validated['hasil'] === 'sukses'
                    ? 'Pembayaran simulasi berhasil. Pesanan masuk antrean cetak!'
                    : 'Simulasi pembayaran gagal. Silakan ulangi pembayaran.'
            );
    }

    public function finish(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        if (! $order->isPaid() && $this->midtrans->isReady()) {
            $this->midtrans->syncStatus($order);
        }

        return redirect()
            ->route('payments.show', $order)
            ->with('success', $order->fresh()->isPaid()
                ? 'Pembayaran berhasil! Pesanan Anda sudah masuk antrean produksi.'
                : 'Kami sedang memverifikasi pembayaran Anda. Halaman ini akan diperbarui otomatis.');
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            $handled = $this->midtrans->handleNotification($payload);
        } catch (\Throwable $exception) {
            Log::error('Webhook Midtrans gagal diproses.', [
                'message' => $exception->getMessage(),
                'order_id' => $payload['order_id'] ?? null,
            ]);

            return response()->json(['message' => 'Webhook gagal diproses.'], 500);
        }

        return response()->json(
            ['message' => $handled ? 'OK' : 'Ditolak'],
            $handled ? 200 : 401
        );
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->id_user === auth()->id(), 403, 'Pesanan ini bukan milik akun Anda.');
    }
}
