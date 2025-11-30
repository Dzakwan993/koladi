<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan semua seeder penting
        $this->call([
            RolesSeeder::class,
            ColorSeeder::class,
            SubscriptionSeeder::class,
        ]);
    }
}
