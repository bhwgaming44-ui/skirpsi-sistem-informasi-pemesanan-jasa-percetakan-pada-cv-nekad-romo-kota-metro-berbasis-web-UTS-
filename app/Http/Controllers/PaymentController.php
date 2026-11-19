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
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly PaymentService $payments,
    ) {}

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        return $this->renderPaymentPage($order, shared: false);
    }

    /**
     * Halaman pembayaran yang bisa dibuka dari perangkat mana pun lewat link
     * bertanda tangan, sehingga pesanan dari HP tetap bisa dibayar di laptop.
     */
    public function showShared(Order $order): View
    {
        return $this->renderPaymentPage($order, shared: true);
    }

    private function renderPaymentPage(Order $order, bool $shared): View
    {
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

        $selectedChannel = $channels->firstWhere('code', $order->metode_pembayaran);

        $expiresAt = ($order->transaksiTerakhir?->created_at ?? $order->tgl_pesan)
            ->copy()
            ->addHours(max(1, (int) ($setting?->expiry_hours ?? 24)));

        return view('payment.show', [
            'order' => $order,
            'setting' => $setting,
            'configured' => $configured,
            'transaction' => $order->transaksiTerakhir,
            'channels' => $channels,
            'selectedChannel' => $selectedChannel,
            'expiresAt' => $expiresAt,
            'shared' => $shared,
            'canSelectMethod' => ! $shared
                && auth()->check()
                && (int) $order->id_user === (int) auth()->id(),
        ]);
    }

    /**
     * Menyimpan metode pembayaran yang dipilih pelanggan. Token Snap lama
     * dikosongkan agar sesi berikutnya hanya menampilkan kanal yang dipilih.
     */
    public function selectMethod(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);

        if ($order->isPaid()) {
            return redirect()
                ->route('payments.show', $order)
                ->with('error', 'Pesanan ini sudah lunas, metode pembayaran tidak dapat diubah.');
        }

        $setting = PaymentSetting::active();
        $available = $setting?->paymentChannels() ?? PaymentSetting::defaultChannels();

        $validated = $request->validate([
            'metode' => ['required', 'string', Rule::in($available)],
        ], [
            'metode.required' => 'Pilih salah satu metode pembayaran.',
            'metode.in' => 'Metode pembayaran tersebut tidak tersedia. Silakan pilih metode lain.',
        ]);

        $order->forceFill([
            'metode_pembayaran' => $validated['metode'],
            'snap_token' => null,
        ])->save();

        $label = PaymentSetting::PAYMENT_CHANNELS[$validated['metode']]['label'] ?? $validated['metode'];

        return redirect()
            ->route('payments.show', $order)
            ->with('success', 'Metode pembayaran '.$label.' dipilih. Lanjutkan pembayaran atau ganti metode kapan saja.');
    }

    /**
     * Mengembalikan pelanggan ke daftar metode pembayaran (fitur "Kembali").
     */
    public function resetMethod(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);

        if ($order->isPaid()) {
            return redirect()
                ->route('payments.show', $order)
                ->with('error', 'Pesanan ini sudah lunas, metode pembayaran tidak dapat diubah.');
        }

        $order->forceFill([
            'metode_pembayaran' => null,
            'snap_token' => null,
        ])->save();

        return redirect()
            ->route('payments.show', $order)
            ->with('success', 'Silakan pilih metode pembayaran lain.');
    }

    public function snapToken(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return $this->issueSnapToken($order);
    }

    public function snapTokenShared(Order $order): JsonResponse
    {
        return $this->issueSnapToken($order);
    }

    private function issueSnapToken(Order $order): JsonResponse
    {
        if ($order->isPaid()) {
            return response()->json(['message' => 'Pesanan sudah lunas.'], 422);
        }

        $token = $this->midtrans->createSnapToken($order, null, $order->metode_pembayaran);

        if ($token === null) {
            return response()->json(['message' => 'Gagal membuat sesi pembayaran Midtrans.'], 502);
        }

        return response()->json(['token' => $token]);
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return $this->paymentStatus($order);
    }

    public function statusShared(Order $order): JsonResponse
    {
        return $this->paymentStatus($order);
    }

    private function paymentStatus(Order $order): JsonResponse
    {
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
        $this->authorizeOwner($order);

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

    public function finish(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        return $this->finishPayment($order, shared: false);
    }

    public function finishShared(Order $order): RedirectResponse
    {
        return $this->finishPayment($order, shared: true);
    }

    private function finishPayment(Order $order, bool $shared): RedirectResponse
    {
        if (! $order->isPaid() && $this->midtrans->isReady()) {
            $this->midtrans->syncStatus($order);
        }

        $redirectUrl = $shared
            ? URL::temporarySignedRoute('payments.shared', now()->addDay(), ['order' => $order])
            : $order->paymentUrl();

        return redirect()
            ->to($redirectUrl)
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

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();

        $isOwnerOrStaff = $user !== null
            && ((int) $order->id_user === (int) $user->id_user || $user->isStaff());

        $key = $request->query('key');
        $hasPaymentKey = is_string($key) && $order->hasValidPaymentKey($key);

        abort_unless(
            $isOwnerOrStaff || $hasPaymentKey || $request->hasValidSignature(),
            403,
            'Pesanan ini bukan milik akun Anda.'
        );
    }

    private function authorizeOwner(Order $order): void
    {
        abort_unless(
            auth()->check() && (int) $order->id_user === (int) auth()->id(),
            403,
            'Pesanan ini bukan milik akun Anda.'
        );
    }
}
