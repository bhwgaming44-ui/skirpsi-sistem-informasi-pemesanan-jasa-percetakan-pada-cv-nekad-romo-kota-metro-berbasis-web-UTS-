<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        PaymentSetting::query()->updateOrCreate(
            ['is_production' => false],
            [
                'nama_gateway' => 'Midtrans Snap',
                'merchant_name' => 'Nekad Romo',
                'server_key' => 'SB-Mid-server-hvNOh0lS.Jxzi1Ra6t6pqXag',
                'client_key' => 'SB-Mid-client-z7N1Scbho75iJ2Ef',
                'signature_key' => 'dummy-signature-key-sandbox',
                'enabled_payments' => PaymentSetting::defaultChannels(),
                'expiry_hours' => 24,
                'merchant_id' => null,
                'is_active' => true,
            ]
        );

        PaymentSetting::query()->updateOrCreate(
            ['is_production' => true],
            [
                'nama_gateway' => 'Midtrans Snap',
                'merchant_name' => 'Nekad Romo',
                'server_key' => 'dummy-server-key-production',
                'client_key' => 'Mid-client-__6yqWJYQrTpX_Ph',
                'signature_key' => 'dummy-signature-key-production',
                'enabled_payments' => PaymentSetting::defaultChannels(),
                'expiry_hours' => 24,
                'merchant_id' => null,
                'is_active' => false,
            ]
        );

        PaymentSetting::query()
            ->where('is_production', false)
            ->update(['is_active' => true]);

        PaymentSetting::query()
            ->where('is_production', true)
            ->update(['is_active' => false]);

        StoreSetting::query()->updateOrCreate(
            ['id_pengaturan' => 1],
            [
                'nama_perusahaan' => 'CV NEKAD ROMO KOTA METRO',
                'alamat' => 'Jl. Jendral Sudirman No. 45, Kota Metro, Lampung',
                'no_telepon' => '0812-7890-1234',
                'email_kontak' => 'info@nekadromo.com',
                'logo' => 'logo.svg',
                'jam_operasional' => 'Senin - Sabtu, 08.00 - 20.00 WIB',
                'instagram' => '@nekadromo.print',
                'deskripsi' => 'Percetakan digital terpercaya di Kota Metro sejak 2014. Melayani cetak banner, brosur, merchandise, hingga kebutuhan branding usaha Anda.',
                'biaya_kirim' => 15000,
            ]
        );
    }
}
