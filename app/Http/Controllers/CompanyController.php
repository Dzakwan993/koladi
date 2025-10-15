<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Role;

class CompanyController extends Controller
{
    public function create()
    {
        return view('buat-perusahaan');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Cari role SuperAdmin (tanpa spasi)
        $superAdminRole = Role::where('nama_role', 'SuperAdmin')->first();

        // ✅ PENTING: Cek apakah role ditemukan
        if (!$superAdminRole) {
            return back()->withErrors([
                'error' => 'Role SuperAdmin tidak ditemukan di database. Silakan jalankan seeder terlebih dahulu.'
            ])->withInput();
        }

        // Simpan data perusahaan
        $company = Company::create([
            'name' => $request->name,
            'email' => null,
            'address' => null,
            'phone' => null,
        ]);

        // Hubungkan user login dengan perusahaan + role SuperAdmin
        $company->users()->attach(auth()->id(),[
            'role_id' => $superAdminRole->id
        ]);

        // Redirect ke dashboard
        return redirect()->route('dashboard')->with('success', 'Perusahaan berhasil dibuat!');
    }
}
