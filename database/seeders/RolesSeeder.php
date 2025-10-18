<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => \Illuminate\Support\Str::uuid(), 'name' => 'SuperAdmin'],
            ['id' => \Illuminate\Support\Str::uuid(), 'name' => 'Administrator'],
            ['id' => \Illuminate\Support\Str::uuid(), 'name' => 'AdminSistem'],
            ['id' => \Illuminate\Support\Str::uuid(), 'name' => 'Manager'],
            ['id' => \Illuminate\Support\Str::uuid(), 'name' => 'Member'],
        ]);
    }
}
