<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['id' => '2a854d03-557a-4457-b395-136d0baafcf5', 'rgb' => '#795548'], // Brown
            ['id' => '2cf4a4f4-06a0-47c7-876b-2cf788e16351', 'rgb' => '#FF9800'], // Orange
            ['id' => '34baa825-01f9-4b26-8fc4-519dfca5af6b', 'rgb' => '#9E9E9E'], // Grey
            ['id' => '3f9fedb9-d632-4a8f-860a-05e0781fb70c', 'rgb' => '#000000'], // Black
            ['id' => '4712cec0-289c-44bd-9592-7bdaa5dbe883', 'rgb' => '#00796B'], // Teal Dark
            ['id' => '4ce6f29c-fbf7-4ade-b6a3-4e49ac28dec3', 'rgb' => '#8BC34A'], // Light Green
            ['id' => '5c0a1142-25ef-4373-9164-6137718a5b5f', 'rgb' => '#FFFFFF'], // White
            ['id' => '62f25f1b-506e-4f9e-a0f5-bac0bc6d543d', 'rgb' => '#FF5722'], // Deep Orange
            ['id' => '69bed3f4-1659-49c1-8bbe-820e135f781f', 'rgb' => '#FF4C4C'], // Red Light
            ['id' => '6c8f270d-fc3d-45fa-a35a-64be13a797e9', 'rgb' => '#303F9F'], // Indigo Dark
            ['id' => '6f736fa7-7b45-4b05-a3d4-cdad4851c02b', 'rgb' => '#607D8B'], // Blue Grey
            ['id' => '75442d05-1b79-446f-ae39-d3698531caa9', 'rgb' => '#FFC107'], // Amber
            ['id' => '780ac390-64ae-4e4c-b599-cd07c0bfe105', 'rgb' => '#0288D1'], // Light Blue Dark
            ['id' => '792985e0-1d2f-4830-b296-4c06a97a0c64', 'rgb' => '#7B1FA2'], // Purple Dark
            ['id' => '7f07267f-c7cb-430a-b840-4bf68072454b', 'rgb' => '#3F51B5'], // Indigo
            ['id' => '7f0e2934-5c76-41b6-b1bd-eb57c5509d53', 'rgb' => '#00BCD4'], // Cyan
            ['id' => '828fd126-71c8-4692-b990-a067faa78b2b', 'rgb' => '#512DA8'], // Deep Purple
            ['id' => '97176aa4-a936-42f4-9a67-b07fa555ad87', 'rgb' => '#C2185B'], // Pink Dark
            ['id' => '986e4c53-cf2a-4b8a-89ed-aa4fca0a00fb', 'rgb' => '#2E7D32'], // Green Dark
            ['id' => '98d8ecc3-3b2c-4e2a-9f54-7138c43534e1', 'rgb' => '#CDDC39'], // Lime
            ['id' => '9ca431e2-97f8-4eba-9656-0286baf96ee9', 'rgb' => '#4CAF50'], // Green
            ['id' => 'a222164c-e36e-43ad-b9bb-6867f94c3f43', 'rgb' => '#009688'], // Teal
            ['id' => 'b2ed830a-1187-477c-813f-dbba8cf3114a', 'rgb' => '#FFEB3B'], // Yellow
            ['id' => 'b7265dd1-078e-4365-a718-4a9736c43698', 'rgb' => '#E91E63'], // Pink
            ['id' => 'cee15dee-c5b0-477b-a9ea-d511e64f3b31', 'rgb' => '#FFCDD2'], // Red Light 100
            ['id' => 'cfe9ecde-3169-4db8-b535-67ac6d728ff9', 'rgb' => '#03A9F4'], // Light Blue
            ['id' => 'd6d3bbb2-c943-433c-ac71-c451925134f7', 'rgb' => '#2196F3'], // Blue
            ['id' => 'f4f8cdec-f51b-439d-a4ca-38a232480fa3', 'rgb' => '#F44336'], // Red
            ['id' => 'fa32a7da-4d6b-4149-ac51-588bb48f6555', 'rgb' => '#673AB7'], // Deep Purple Light
            ['id' => 'fe4955e8-9aad-4677-b0d8-3665c5c84b53', 'rgb' => '#9C27B0'], // Purple
        ];

        DB::table('colors')->insert($colors);
    }
}
