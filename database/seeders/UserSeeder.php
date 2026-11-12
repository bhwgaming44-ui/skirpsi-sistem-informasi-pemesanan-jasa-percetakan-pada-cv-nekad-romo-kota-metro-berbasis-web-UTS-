<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nama' => 'Administrator Nekad Romo',
                'email' => 'admin@nekadromo.com',
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Jendral Sudirman No. 45, Kota Metro, Lampung',
                'username' => 'admin',
                'password' => 'admin123',
                'role' => 'admin',
            ],
            [
                'nama' => 'Pimpinan CV Nekad Romo',
                'email' => 'pimpinan@nekadromo.com',
                'no_hp' => '081234567891',
                'alamat' => 'Jl. Jendral Sudirman No. 45, Kota Metro, Lampung',
                'username' => 'pimpinan',
                'password' => 'pimpinan123',
                'role' => 'pimpinan',
            ],
            [
                'nama' => 'Muhammad Raihan',
                'email' => 'raihan@gmail.com',
                'no_hp' => '085712345678',
                'alamat' => 'Jl. Ki Hajar Dewantara No. 12, Kota Metro',
                'username' => 'raihan',
                'password' => 'pelanggan123',
                'role' => 'pelanggan',
            ],
            [
                'nama' => 'Siti Aminah',
                'email' => 'siti.aminah@gmail.com',
                'no_hp' => '081271112223',
                'alamat' => 'Jl. A. Yani No. 8, Kota Metro',
                'username' => 'siti',
                'password' => 'pelanggan123',
                'role' => 'pelanggan',
            ],
            [
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'no_hp' => '081372223334',
                'alamat' => 'Jl. Soekarno Hatta No. 21, Kota Metro',
                'username' => 'budi',
                'password' => 'pelanggan123',
                'role' => 'pelanggan',
            ],
            [
                'nama' => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'no_hp' => '081943334445',
                'alamat' => 'Jl. Pemuda No. 5, Metro Timur',
                'username' => 'dewi',
                'password' => 'pelanggan123',
                'role' => 'pelanggan',
            ],
            [
                'nama' => 'Agus Pratama',
                'email' => 'agus.pratama@gmail.com',
                'no_hp' => '082155556667',
                'alamat' => 'Jl. Cut Mutia No. 33, Metro Utara',
                'username' => 'agus',
                'password' => 'pelanggan123',
                'role' => 'pelanggan',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
