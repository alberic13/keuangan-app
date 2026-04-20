<?php

namespace Database\Seeders;

use App\Models\KategoriTransaksi;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitialFinanceSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@man2.test'],
            [
                'name' => 'Admin Keuangan',
                'password' => 'password123',
            ]
        );

        collect([
            ['nama_kategori' => 'SPP', 'tipe' => 'pemasukan'],
            ['nama_kategori' => 'Dana Bantuan', 'tipe' => 'pemasukan'],
            ['nama_kategori' => 'Operasional', 'tipe' => 'pengeluaran'],
            ['nama_kategori' => 'Sarana Prasarana', 'tipe' => 'pengeluaran'],
            ['nama_kategori' => 'Kesiswaan', 'tipe' => 'pengeluaran'],
            ['nama_kategori' => 'Kegiatan Akademik', 'tipe' => 'pengeluaran'],
        ])->each(function (array $kategori) {
            KategoriTransaksi::query()->updateOrCreate(
                ['nama_kategori' => $kategori['nama_kategori']],
                ['tipe' => $kategori['tipe']]
            );
        });
    }
}
