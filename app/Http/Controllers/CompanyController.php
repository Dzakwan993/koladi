<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\UserCompany;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberRemovedNotification;

class CompanyController extends Controller
{
    // Method untuk dashboard
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 🔥 PENTING: Fresh query untuk memastikan data ter-update
        $companies = $user->companies()->get();

        // 🔥 Jika user tidak punya perusahaan, redirect ke halaman buat perusahaan
        if ($companies->isEmpty()) {
            return redirect()->route('buat-perusahaan')
                ->with('info', 'Anda belum memiliki perusahaan. Silakan buat perusahaan baru.');
        }

        // Pastikan session active_company_id memang milik user
        $activeCompany = null;
        $companyIdSession = session('active_company_id');

        if ($companyIdSession) {
            // 🔥 Validasi ulang dengan fresh query
            $hasAccess = UserCompany::where('user_id', $user->id)
                ->where('company_id', $companyIdSession)
                ->exists();

            if ($hasAccess) {
                $activeCompany = Company::find($companyIdSession);
            } else {
                // 🔥 User tidak punya akses lagi - REDIRECT KE HALAMAN REMOVED
                session()->forget('active_company_id');

                // Simpan info company yang dihapus untuk ditampilkan
                $removedCompany = Company::find($companyIdSession);
                session(['removed_from_company' => $removedCompany->name ?? 'Perusahaan']);

                // Redirect ke halaman notifikasi penghapusan
                return redirect()->route('member.removed');
            }
        } else {
            // kalau tidak ada session, ambil first company user
            $activeCompany = $companies->first();
        }

        if ($activeCompany) {
            session(['active_company_id' => $activeCompany->id]);
        }

        return view('dashboard', compact('companies', 'activeCompany'));
    }

    // 🆕 Halaman notifikasi member dihapus
    public function memberRemoved()
    {

        $removedCompanyName = session('removed_from_company', 'Perusahaan');
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $companies = $user->companies()->get();

        return view('member-removed', compact('removedCompanyName', 'companies'));
    }

    public function showMembers()
    {
        $companyId = session('active_company_id');

        if (!$companyId) {
            return redirect()->route('dashboard');
        }

        // 🔥 Validasi akses user ke company sebelum menampilkan member
        $hasAccess = UserCompany::where('user_id', Auth::id())
            ->where('company_id', $companyId)
            ->exists();

        if (!$hasAccess) {
            session()->forget('active_company_id');
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke perusahaan ini.');
        }

        // Ambil semua members perusahaan (termasuk user yang sedang login)
        $members = User::whereHas('userCompanies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['userCompanies' => function ($query) use ($companyId) {
                $query->where('company_id', $companyId)->with('role');
            }])
            ->get()
            ->map(function ($user) use ($companyId) {
                $userCompany = $user->userCompanies->where('company_id', $companyId)->first();
                $user->role_name = ($userCompany && $userCompany->role)
                    ? ($userCompany->role->nama_role ?? $userCompany->role->name ?? null)
                    : null;
                return $user;
            });

        // Ambil undangan yang masih pending
        $invites = Invitation::where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tambah-anggota', compact('members', 'invites'));
    }

    public function deleteMember($id)
    {
        $companyId = session('active_company_id');
        $user = User::findOrFail($id);
        $company = Company::findOrFail($companyId);
        $removedBy = Auth::user();

        // Cari semua relasi user–company
        $userCompanies = UserCompany::where('user_id', $id)
            ->where('company_id', $companyId)
            ->get();

        if ($userCompanies->isEmpty()) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan di perusahaan ini.');
        }

        // 🔥 Hapus secara permanen
        UserCompany::where('user_id', $id)
            ->where('company_id', $companyId)
            ->forceDelete();

        cache()->forget("user_companies_{$id}");

        // 🔥 Kirim email notifikasi (jika ada)
        try {
            Mail::to($user->email)->send(new MemberRemovedNotification(
                $user,
                $company,
                $removedBy
            ));
        } catch (\Exception $e) {
            Log::error('Gagal kirim email penghapusan anggota: ' . $e->getMessage());
        }

        Log::info("Member {$user->email} berhasil dihapus dari perusahaan {$company->name}");

        // 🔥 Kalau user yang dihapus sedang login, arahkan ke halaman member-removed
        if ($user->id === Auth::id()) {
            // Jika user yang dihapus adalah dirinya sendiri
            session()->forget('active_company_id');
            session(['removed_from_company' => $company->name]);

            return redirect()->route('member.removed')->with('success', 'Anda telah dihapus dari perusahaan ini.');
        }

        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari perusahaan.');
    }


    // Method untuk switch perusahaan
    public function switchCompany($companyId)
    {
        $user = Auth::user();

        // 🔥 Fresh query untuk validasi akses
        $hasAccess = UserCompany::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->exists();

        if ($hasAccess) {
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
        return redirect()->route('dashboard-awal')
            ->with('success', 'Perusahaan berhasil dibuat!');
    }

    // 🆕 Update data perusahaan
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::findOrFail($id);

        // 🔥 Fresh query validasi akses
        $hasAccess = UserCompany::where('user_id', Auth::id())
            ->where('company_id', $id)
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengedit perusahaan ini');
        }

        // Update data perusahaan
        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        return redirect()->route('dashboard')->with('success', 'Data perusahaan berhasil diperbarui!');
    }

    // 🆕 Hapus perusahaan
    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        // 🔥 Fresh query validasi akses
        $hasAccess = UserCompany::where('user_id', Auth::id())
            ->where('company_id', $id)
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk menghapus perusahaan ini');
        }

        // Hapus relasi dari tabel pivot
        UserCompany::where('company_id', $id)->delete();

        // Hapus perusahaan
        $company->delete();

        // Hapus dari session kalau sedang aktif
        if (session('active_company_id') === $id) {
            session()->forget('active_company_id');
        }

        return redirect()->route('dashboard')->with('success', 'Perusahaan berhasil dihapus!');
    }
}
