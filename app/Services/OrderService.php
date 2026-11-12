<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly CartService $cart) {}

    /**
     * @param  array{metode_pengambilan: string, alamat_pengiriman?: ?string, catatan?: ?string}  $checkout
     */
    public function createFromCart(User $user, array $checkout): Order
    {
        $items = $this->cart->items();

        if ($items === []) {
            throw new \RuntimeException('Keranjang pesanan masih kosong.');
        }

        $store = StoreSetting::current();
        $totalProduk = (int) collect($items)->sum(fn (array $item) => $item['quote']['subtotal']);
        $ongkir = $checkout['metode_pengambilan'] === 'Kirim Kurir/Ekspedisi'
            ? (int) round((float) $store->biaya_kirim)
            : 0;

        $estimasi = collect($items)->max(fn (array $item) => $item['product']->estimasi_hari) ?? 1;

        $order = DB::transaction(function () use ($user, $checkout, $items, $totalProduk, $ongkir, $estimasi): Order {
            $order = Order::query()->create([
                'kode_pesanan' => $this->generateCode(),
                'id_user' => $user->id_user,
                'tgl_pesan' => now(),
                'total_produk' => $totalProduk,
                'ongkir' => $ongkir,
                'grand_total' => $totalProduk + $ongkir,
                'metode_pengambilan' => $checkout['metode_pengambilan'],
                'alamat_pengiriman' => $checkout['alamat_pengiriman'] ?? null,
                'status_pembayaran' => 'Menunggu Pembayaran',
                'status_pesanan' => Order::STATUS_MENUNGGU,
                'estimasi_selesai' => now()->addDays((int) $estimasi),
                'catatan' => $checkout['catatan'] ?? null,
            ]);

            foreach ($items as $item) {
                /** @var Product $product */
                $product = $item['product'];
                /** @var Material $material */
                $material = $item['material'];
                $quote = $item['quote'];

                $detail = $order->detail()->create([
                    'id_bahan' => $material->id_bahan,
                    'nama_produk_snapshot' => $product->nama_produk,
                    'nama_bahan_snapshot' => $material->nama_bahan,
                    'satuan_snapshot' => $product->satuan,
                    'panjang' => $quote['panjang'],
                    'lebar' => $quote['lebar'],
                    'jumlah' => $quote['jumlah'],
                    'harga_bahan_saat_beli' => $material->harga_satuan,
                    'tipe_desain' => $item['tipe_desain'],
                    'biaya_desain' => $quote['biaya_desain'],
                    'file_desain' => $item['file_desain'],
                    'nama_file_desain' => $item['nama_file_desain'],
                    'catatan_desain' => $item['catatan_desain'],
                    'subtotal' => $quote['subtotal'],
                ]);

                foreach ($quote['finishings'] as $finishing) {
                    $detail->finishing()->create([
                        'id_finishing' => $finishing->id_finishing,
                        'nama_finishing_snapshot' => $finishing->nama_finishing,
                        'biaya_saat_beli' => $finishing->biaya_default,
                    ]);
                }
            }

            OrderHistory::query()->create([
                'id_pesanan' => $order->id_pesanan,
                'id_user' => $user->id_user,
                'status_tahapan' => 'Pesanan Dibuat',
                'keterangan' => 'Pesanan berhasil dibuat dan menunggu pembayaran melalui payment gateway.',
            ]);

            return $order;
        });

        $this->cart->clear();

        return $order;
    }

    public function generateCode(): string
    {
        do {
            $code = 'NR-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Order::query()->where('kode_pesanan', $code)->exists());

        return $code;
    }
}
