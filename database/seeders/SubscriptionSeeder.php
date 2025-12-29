<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['plan_name' => 'Paket Basic', 'price_monthly' => 15000, 'base_user_limit' => 5],
            ['plan_name' => 'Paket Standard', 'price_monthly' => 45000, 'base_user_limit' => 20],
            ['plan_name' => 'Paket Business', 'price_monthly' => 100000, 'base_user_limit' => 50],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['plan_name' => $plan['plan_name']],
                array_merge($plan, [
                    'is_active' => true,
                    'description' => 'Auto seeded',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        DB::table('addons')->updateOrInsert(
            ['addon_name' => 'Tambahan User'],
            [
                'price_per_user' => 4000,
                'description' => 'Tambah 1 user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

