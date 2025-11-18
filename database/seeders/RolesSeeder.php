<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ UUID TETAP sesuai dengan yang di blade template
        $roles = [
            ['id' => '11111111-1111-1111-1111-111111111111', 'name' => 'SuperAdmin'],
            ['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager'],      // ✅ Sesuaikan!
            ['id' => '33333333-3333-3333-3333-333333333333', 'name' => 'AdminSistem'],
            ['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member'],       // ✅ Sesuaikan!
            ['id' => '55555555-5555-5555-5555-555555555555', 'name' => 'Administrator'],
        ];

        // Hapus data lama
        DB::table('roles')->truncate();

        // Insert data baru
        DB::table('roles')->insert($roles);
    }
}
