<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk tabel roles.
     */
    public function run(): void
    {
        $roles = [
            ['nama_role' => 'SuperAdmin', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'AdminSistem', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Manager', 'created_at' => now(), 'updated_at' => now()],
            ['nama_role' => 'Member', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('roles')->insert($roles);
    }
}
