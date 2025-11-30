<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSistemSeeder extends Seeder
{
    public function run(): void
    {
        // Cari role AdminSistem
        $adminSistemRole = Role::where('name', 'AdminSistem')->first();

        if (!$adminSistemRole) {
            $this->command->error('Role AdminSistem tidak ditemukan!');
            return;
        }

        // Cek apakah sudah ada admin sistem
        $existingAdmin = User::where('system_role_id', $adminSistemRole->id)->first();

        if ($existingAdmin) {
            $this->command->warn('Admin Sistem sudah ada: ' . $existingAdmin->email);
            return;
        }

        // Buat user Admin Sistem
        $adminUser = User::create([
            'id' => Str::uuid(),
            'full_name' => 'Admin Sistem Koladi',
            'email' => 'admin@koladi.com',
            'password' => Hash::make('adminkoladi123$%^'),
            'status_active' => true,
            'system_role_id' => $adminSistemRole->id,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Admin Sistem berhasil dibuat!');
        $this->command->info('📧 Email: ' . $adminUser->email);
        $this->command->info('🔑 Password: admin123');
        $this->command->warn('⚠️ JANGAN LUPA GANTI PASSWORD!');
    }
}