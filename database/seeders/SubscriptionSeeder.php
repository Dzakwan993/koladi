<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Hapus data lama jika ada
        DB::table('addons')->truncate();
        DB::table('plans')->truncate();

        // 1. Insert Plans (sesuai gambar ke-2)
        $plans = [
            [
                'id' => Str::uuid()->toString(),
                'plan_name' => 'Paket Basic', // ✅ Ganti nama
                'price_monthly' => 15000, // ✅ Ganti harga
                'base_user_limit' => 5,
                'description' => 'Cocok untuk tim kecil',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'plan_name' => 'Paket Standard', // ✅ Ganti nama
                'price_monthly' => 45000, // ✅ Ganti harga
                'base_user_limit' => 20,
                'description' => 'Untuk tim yang berkembang',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'plan_name' => 'Paket Business', // ✅ Ganti nama
                'price_monthly' => 100000, // ✅ Ganti harga
                'base_user_limit' => 50,
                'description' => 'Untuk organisasi besar',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('plans')->insert($plans);

        // 2. Insert Addons
        $addon = [
            'id' => Str::uuid()->toString(),
            'addon_name' => 'Tambahan User',
            'price_per_user' => 4000, // ✅ Sesuai gambar (Rp4.000)
            'description' => 'Tambah 1 user ke paket yang kamu pilih',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('addons')->insert($addon);

        $this->command->info('✅ Plans & Addons berhasil di-seed!');
    }
}
