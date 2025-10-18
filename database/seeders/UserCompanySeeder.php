<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserCompanySeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Buat user
        $user = User::create([
            'full_name' => 'Muhammad Sahrosdfsdf',
            'email' => 'admin@exaaalesa.com',
            'password' => Hash::make('12345678'),
            'status_active' => true,
        ]);

        // 2️⃣ Buat perusahaan dengan UUID eksplisit
        $company = new Company([
            'name' => 'PT Contoh',
            'email' => 'info@ptcontoh.com',
            'address' => 'Jakarta',
            'phone' => '08123456789',
        ]);
        $company->id = $company->id ?: Str::uuid()->toString(); // pastikan ada UUID
        $company->save();

        // 3️⃣ Buat pivot UserCompany manual supaya company_id pasti terisi
        UserCompany::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'roles_id' => null,
        ]);

        $this->command->info("Seeder berhasil: User '{$user->full_name}' diattach ke Company '{$company->name}'");
    }
}
