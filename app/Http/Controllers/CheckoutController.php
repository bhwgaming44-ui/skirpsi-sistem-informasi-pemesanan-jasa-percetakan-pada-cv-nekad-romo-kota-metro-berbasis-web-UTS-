<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
        private readonly MidtransService $midtrans,
    ) {}

    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('catalog.index')->with('error', 'Keranjang masih kosong.');
        }

        return view('checkout.index', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'store' => StoreSetting::current(),
            'paymentReady' => $this->midtrans->isReady(),
            'draft' => $this->cart->checkoutDraft(),
        ]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metode_pengambilan' => ['nullable', Rule::in(['Ambil di Toko', 'Kirim Kurir/Ekspedisi'])],
            'alamat_pengiriman' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->cart->saveCheckoutDraft([
            'metode_pengambilan' => $validated['metode_pengambilan'] ?? 'Ambil di Toko',
            'alamat_pengiriman' => $validated['alamat_pengiriman'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return response()->json([
            'saved' => true,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'metode_pengambilan' => ['required', Rule::in(['Ambil di Toko', 'Kirim Kurir/Ekspedisi'])],
            'alamat_pengiriman' => ['nullable', 'required_if:metode_pengambilan,Kirim Kurir/Ekspedisi', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'alamat_pengiriman.required_if' => 'Alamat pengiriman wajib diisi jika memilih kirim kurir.',
        ]);

        try {
            /** @var Order $order */
            $order = $this->orders->createFromCart($request->user(), $validated);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($this->midtrans->isReady()) {
            $token = $this->midtrans->createSnapToken($order);

            if ($token === null) {
                return redirect()
                    ->route('payments.show', $order)
                    ->with('error', 'Pesanan dibuat, tetapi koneksi ke Midtrans gagal. Silakan coba lagi dari halaman pembayaran.');
            }
        }

        return redirect()
            ->route('payments.show', $order)
            ->with('success', 'Pesanan '.$order->kode_pesanan.' berhasil dibuat. Selesaikan pembayaran untuk masuk antrean cetak.');
    }
}
