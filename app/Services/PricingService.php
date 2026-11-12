<?php

namespace App\Services;

use App\Models\Finishing;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Support\Collection;

class PricingService
{
    /**
     * Hitung rincian harga satu item cetak berdasarkan spesifikasi pelanggan.
     *
     * @param  array{panjang?: float|int|string, lebar?: float|int|string, jumlah?: int, finishing_ids?: array<int, int|string>, tipe_desain?: string}  $data
     * @return array<string, mixed>
     */
    public function quote(Product $product, Material $material, array $data): array
    {
        $panjang = round((float) ($data['panjang'] ?? 1), 2);
        $lebar = round((float) ($data['lebar'] ?? 1), 2);
        $jumlah = max((int) ($data['jumlah'] ?? $product->min_order), 1);
        $isMeter = $product->satuan === 'meter';
        $luas = $isMeter ? round($panjang * $lebar, 2) : 0.0;
        $hargaSatuan = (float) $material->harga_satuan;

        $hargaDasar = $isMeter
            ? round($hargaSatuan * max($luas, 0.25) * $jumlah)
            : round($hargaSatuan * $jumlah);

        /** @var Collection<int, Finishing> $finishings */
        $finishings = $product->finishing
            ->whereIn('id_finishing', collect($data['finishing_ids'] ?? [])->map(fn ($id) => (int) $id))
            ->values();

        $biayaFinishing = (int) round($finishings->sum(fn (Finishing $finishing) => (float) $finishing->biaya_default) * $jumlah);

        $jasaDesain = ($data['tipe_desain'] ?? 'Punya Desain Sendiri') === 'Jasa Desain Nekad Romo';
        $biayaDesain = $jasaDesain ? (int) config('percetakan.biaya_desain', 25000) : 0;

        $subtotal = $hargaDasar + $biayaFinishing + $biayaDesain;

        return [
            'panjang' => $panjang,
            'lebar' => $lebar,
            'jumlah' => $jumlah,
            'luas' => $luas,
            'harga_satuan' => $hargaSatuan,
            'harga_dasar' => $hargaDasar,
            'biaya_finishing' => $biayaFinishing,
            'biaya_desain' => $biayaDesain,
            'finishings' => $finishings,
            'subtotal' => $subtotal,
        ];
    }

    public function satuanLabel(string $satuan): string
    {
        return match ($satuan) {
            'meter' => 'meter persegi',
            'lembar' => 'lembar',
            'pcs' => 'pcs',
            'rim' => 'rim',
            default => $satuan,
        };
    }
}
