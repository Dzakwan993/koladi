<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\Role;
use App\Models\UserCompany;

class CompanyController extends Controller
{
    // Method untuk dashboard
    public function dashboard()
    {
        $user = Auth::user();

        // Ambil semua perusahaan yang terhubung dengan user
        $companies = $user->companies;

        // Ambil perusahaan yang sedang aktif (dari session atau yang pertama)
        $activeCompany = session('active_company_id')
            ? Company::find(session('active_company_id'))
            : $companies->first();

        // Jika ada perusahaan aktif, simpan di session
        if ($activeCompany) {
            session(['active_company_id' => $activeCompany->id]);
        }

        return view('dashboard', compact('companies', 'activeCompany'));
    }

    // Method untuk switch perusahaan
    public function switchCompany($companyId)
    {
        $user = Auth::user();

        // Ambil ID semua perusahaan user
        $userCompanyIds = $user->companies->pluck('id')->toArray();

        // Cek apakah companyId ada dalam daftar perusahaan user
        if (in_array($companyId, $userCompanyIds)) {
            session(['active_company_id' => $companyId]);
            return redirect()->route('dashboard')->with('success', 'Berhasil beralih perusahaan');
        }

        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke perusahaan ini');
    }

    // Halaman form buat perusahaan
    public function create()
    {
        return view('buat-perusahaan');
    }

    // Proses simpan perusahaan baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Simpan data perusahaan baru dengan UUID
        $company = Company::create([
            'id' => Str::uuid()->toString(),
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        // Ambil role SuperAdmin
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();

        // Hubungkan user login dengan perusahaan baru
        UserCompany::create([
            'user_id' => Auth::id(),
            'company_id' => $company->id,
            'roles_id' => $superAdminRole->id ?? null,
        ]);

        // Set perusahaan baru sebagai active
        session(['active_company_id' => $company->id]);

        return redirect()->route('dashboard')->with('success', 'Perusahaan berhasil dibuat!');
    }
}
