<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => '11111111-1111-1111-1111-111111111111', 'name' => 'SuperAdmin'],
            ['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager'],
            ['id' => '33333333-3333-3333-3333-333333333333', 'name' => 'AdminSistem'],
            ['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member'],
            ['id' => '55555555-5555-5555-5555-555555555555', 'name' => 'Administrator'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                ['name' => $role['name']]
            );
        }
    }
}

