<?php

namespace Database\Seeders;

use App\Models\GatewayTransaction;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemFinishing;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\PricingService;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        $customers = User::query()->where('role', 'pelanggan')->orderBy('id_user')->get();
        $products = Product::query()->with(['bahan', 'finishing'])->aktif()->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $store = StoreSetting::current();
        $pricing = app(PricingService::class);

        for ($i = 0; $i < 46; $i++) {
            $orderedAt = now()->subDays(random_int(0, 75))->setTime(random_int(8, 19), random_int(0, 59));
            $customer = $customers->random();
            /** @var Product $product */
            $product = $products->random();
            /** @var Material $material */
            $material = $product->bahan->random();

            $isMeter = $product->satuan === 'meter';
            $panjang = $isMeter ? random_int(2, 5) : 1;
            $lebar = $isMeter ? random_int(1, 3) : 1;
            $jumlah = match ($product->satuan) {
                'meter' => random_int(1, 3),
                'lembar' => max($product->min_order, random_int(1, 6) * 25),
                'pcs' => max($product->min_order, random_int(1, 6) * 12),
                'rim' => random_int(1, 3),
            };

            $finishingIds = $product->finishing->random(random_int(0, min(2, $product->finishing->count())))
                ->pluck('id_finishing')->all();

            $tipeDesain = random_int(1, 100) <= 30 ? 'Jasa Desain Nekad Romo' : 'Punya Desain Sendiri';
            $quote = $pricing->quote($product, $material, [
                'panjang' => $panjang,
                'lebar' => $lebar,
                'jumlah' => $jumlah,
                'finishing_ids' => $finishingIds,
                'tipe_desain' => $tipeDesain,
            ]);

            $metode = random_int(1, 100) <= 25 ? 'Kirim Kurir/Ekspedisi' : 'Ambil di Toko';
            $ongkir = $metode === 'Kirim Kurir/Ekspedisi' ? (int) $store->biaya_kirim : 0;
            $total = $quote['subtotal'];

            $roll = random_int(1, 100);
            $status = match (true) {
                $roll <= 52 => Order::STATUS_SELESAI,
                $roll <= 64 => Order::STATUS_SIAP,
                $roll <= 74 => Order::STATUS_SELESAI_CETAK,
                $roll <= 83 => Order::STATUS_CETAK,
                $roll <= 92 => Order::STATUS_ANTREAN,
                default => Order::STATUS_MENUNGGU,
            };

            $paid = $status !== Order::STATUS_MENUNGGU;
            $kodePesanan = 'NR-'.$orderedAt->format('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));

            $order = Order::query()->create([
                'kode_pesanan' => $kodePesanan,
                'id_user' => $customer->id_user,
                'tgl_pesan' => $orderedAt,
                'total_produk' => $total,
                'ongkir' => $ongkir,
                'grand_total' => $total + $ongkir,
                'metode_pengambilan' => $metode,
                'alamat_pengiriman' => $metode === 'Kirim Kurir/Ekspedisi' ? $customer->alamat : null,
                'status_pembayaran' => $paid ? 'Lunas' : 'Menunggu Pembayaran',
                'status_pesanan' => $status,
                'estimasi_selesai' => $orderedAt->copy()->addDays($product->estimasi_hari),
                'catatan' => $tipeDesain === 'Jasa Desain Nekad Romo' ? 'Mohon buatkan desain sesuai contoh yang dikirim via WhatsApp.' : null,
            ]);

            $detail = OrderItem::query()->create([
                'id_pesanan' => $order->id_pesanan,
                'id_bahan' => $material->id_bahan,
                'nama_produk_snapshot' => $product->nama_produk,
                'nama_bahan_snapshot' => $material->nama_bahan,
                'satuan_snapshot' => $product->satuan,
                'panjang' => $panjang,
                'lebar' => $lebar,
                'jumlah' => $jumlah,
                'harga_bahan_saat_beli' => $material->harga_satuan,
                'tipe_desain' => $tipeDesain,
                'biaya_desain' => $quote['biaya_desain'],
                'catatan_desain' => $tipeDesain === 'Jasa Desain Nekad Romo' ? 'Desain dibuat oleh tim kreatif Nekad Romo.' : null,
                'subtotal' => $total,
            ]);

            foreach ($quote['finishings'] as $finishing) {
                OrderItemFinishing::query()->create([
                    'id_detail' => $detail->id_detail,
                    'id_finishing' => $finishing->id_finishing,
                    'nama_finishing_snapshot' => $finishing->nama_finishing,
                    'biaya_saat_beli' => $finishing->biaya_default,
                ]);
            }

            $this->createHistory($order, $orderedAt, $status, $paid);

            GatewayTransaction::query()->create([
                'id_pesanan' => $order->id_pesanan,
                'order_id_gateway' => $kodePesanan,
                'transaction_id_gateway' => 'DEMO-'.strtoupper(bin2hex(random_bytes(5))),
                'payment_type' => ['qris', 'bank_transfer', 'gopay'][random_int(0, 2)],
                'bank_va' => $paid ? ['bca', 'bri', 'mandiri'][random_int(0, 2)] : null,
                'va_number' => $paid ? '88'.random_int(1000000000, 9999999999) : null,
                'gross_amount' => $order->grand_total,
                'transaction_status' => $paid ? 'settlement' : 'pending',
                'fraud_status' => 'accept',
                'payment_time' => $paid ? $orderedAt->copy()->addMinutes(7) : null,
                'settlement_time' => $paid ? $orderedAt->copy()->addMinutes(8) : null,
                'raw_response' => json_encode(['mode' => 'demo', 'status' => $paid ? 'settlement' : 'pending']),
            ]);
        }
    }

    private function createHistory(Order $order, CarbonInterface $orderedAt, string $finalStatus, bool $paid): void
    {
        $rows = [[
            'status_tahapan' => 'Pesanan Dibuat',
            'keterangan' => 'Pesanan berhasil dibuat melalui website.',
            'created_at' => $orderedAt,
        ]];

        if ($paid) {
            $rows[] = [
                'status_tahapan' => 'Pembayaran Lunas',
                'keterangan' => 'Pembayaran berhasil diverifikasi otomatis oleh payment gateway.',
                'created_at' => $orderedAt->copy()->addMinutes(8),
            ];
        }

        foreach (Order::TAHAPAN as $index => $tahapan) {
            if ($index === 0) {
                continue;
            }

            $rows[] = [
                'status_tahapan' => $tahapan,
                'keterangan' => 'Status pesanan diperbarui oleh operator workshop.',
                'created_at' => $orderedAt->copy()->addHours($index * 6),
            ];

            if ($tahapan === $finalStatus) {
                break;
            }
        }

        foreach ($rows as $row) {
            DB::table('riwayat_pesanan')->insert([
                'id_pesanan' => $order->id_pesanan,
                'id_user' => null,
                'status_tahapan' => $row['status_tahapan'],
                'keterangan' => $row['keterangan'],
                'created_at' => $row['created_at'],
            ]);
        }
    }
}
