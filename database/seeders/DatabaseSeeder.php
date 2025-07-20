<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $tahunDuaDigit = Carbon::now()->format('y');

        User::firstOrCreate(
            [
                'email' => 'wahyusyipul@gmail.com' // Kunci untuk pengecekan agar tidak duplikat
            ],
            [
                'id_anggota' => $tahunDuaDigit . '-0001',
                'name' => 'Wahyu Syaiful',
                'bin_binti' => 'bin Bapak Wahyu',
                'jenis_kelamin' => 'laki-laki',
                'email' => 'wahyusyipul@gmail.com',
                'password' => Hash::make('Wafy2001'),
                'role' => 'admin',
                'status' => 'Aktif',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'alamat' => 'Jl. Kebon Jeruk No. 1',
                'no_hp' => '081234567890',
                'tanggal_wafat' => null,
                'email_verified_at' => now(),
            ]
        );
        User::firstOrCreate(
            [
                'email' => 'wafyputrapangestu@gmail.com' // Kunci untuk pengecekan agar tidak duplikat
            ],
            [
                'id_anggota' => $tahunDuaDigit . '-0002',
                'name' => 'Wafy Putra Pangestu',
                'bin_binti' => 'bin Bapak Wafy',
                'jenis_kelamin' => 'laki-laki',
                'email' => 'wafyputrapangestu@gmail.com',
                'password' => Hash::make('Wafy2001'),
                'role' => 'user',
                'status' => 'Pending',
                'tempat_lahir' => 'Tangerang',
                'tanggal_lahir' => '2001-08-17',
                'alamat' => 'Perumahan Indah Blok C No. 5',
                'no_hp' => '08976543210',
                'tanggal_wafat' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
