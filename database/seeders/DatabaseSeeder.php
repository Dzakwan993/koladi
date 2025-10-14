<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jalankan seeder roles
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Buat user default untuk setiap role
        User::factory()->create([
            'nama_lengkap' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'status_aktif' => true,
            'id_role' => 1, // Super Admin
            'password' => bcrypt('password123'), // jangan lupa password
        ]);

        User::factory()->create([
            'nama_lengkap' => 'Admin User',
            'email' => 'admin@example.com',
            'status_aktif' => true,
            'id_role' => 2, // Admin
            'password' => bcrypt('password123'),
        ]);
    }
}
