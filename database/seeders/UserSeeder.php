<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk tabel users.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => Str::uuid(),
            'full_name' => 'Super Admin Koladi',
            'email' => 'superadmin@koladi.com',
            'password' => Hash::make('password123'), // ubah kalau mau password lain
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
