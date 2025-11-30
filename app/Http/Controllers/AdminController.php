<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Tampilkan dashboard admin sistem
     */
    public function dashboard()
    {
        // Ambil semua perusahaan dengan jumlah member
        $companies = Company::withCount(['userCompanies as member_count'])
            ->with(['userCompanies.user', 'userCompanies.role'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($company) {
                // Hitung jumlah member aktif
                $activeMemberCount = DB::table('user_companies')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
                    ->count();

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'member_count' => $activeMemberCount,
                    'created_at' => $company->created_at,
                    // Untuk sementara hardcode, nanti bisa dikembangkan
                    'package_type' => 'Basic', 
                    'addons' => '-',
                    'payment_status' => 'Trial',
                ];
            });

        return view('dashboard_admin', compact('companies'));
    }

    /**
     * Tampilkan detail perusahaan
     */
    public function showCompany($id)
    {
        $company = Company::with(['userCompanies.user', 'userCompanies.role'])
            ->findOrFail($id);

        return view('admin.company_detail', compact('company'));
    }

    /**
     * Suspend/aktifkan perusahaan
     */
    public function toggleCompanyStatus($id)
    {
        $company = Company::findOrFail($id);
        
        // Toggle status (bisa tambahkan kolom status di tabel companies)
        // Untuk sementara kita bisa soft delete
        
        return back()->with('success', 'Status perusahaan berhasil diubah');
    }
}