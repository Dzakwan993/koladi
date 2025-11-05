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
        $user = Auth::user();
        $companies = $user->companies()->get();

        if ($companies->isEmpty()) {
            return redirect()->route('buat-perusahaan')
                ->with('info', 'Anda belum memiliki perusahaan. Silakan buat perusahaan baru.');
        }

        $activeCompany = null;
        $companyIdSession = session('active_company_id');

        if ($companyIdSession) {
            $hasAccess = UserCompany::where('user_id', $user->id)
                ->where('company_id', $companyIdSession)
                ->exists();

            if ($hasAccess) {
                $activeCompany = Company::find($companyIdSession);
            } else {
                session()->forget('active_company_id');
                $removedCompany = Company::find($companyIdSession);
                session(['removed_from_company' => $removedCompany->name ?? 'Perusahaan']);
                return redirect()->route('member.removed');
            }
        } else {
            $activeCompany = $companies->first();
        }

        if ($activeCompany) {
            session(['active_company_id' => $activeCompany->id]);
        }

        return view('dashboard', compact('companies', 'activeCompany'));
    }

    // Halaman notifikasi member dihapus
    public function memberRemoved()
    {
        $removedCompanyName = session('removed_from_company', 'Perusahaan');
        $user = Auth::user();
        $companies = $user->companies()->get();

        return view('member-removed', compact('removedCompanyName', 'companies'));
    }

    // Tampilkan anggota perusahaan
    public function showMembers()
    {
        $companyId = session('active_company_id');
        if (!$companyId) return redirect()->route('dashboard');

        $hasAccess = UserCompany::where('user_id', Auth::id())
            ->where('company_id', $companyId)
            ->exists();

        if (!$hasAccess) {
            session()->forget('active_company_id');
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke perusahaan ini.');
        }

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

        $invites = Invitation::where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->with('inviter')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tambah-anggota', compact('members', 'invites'));
    }

    // Hapus anggota perusahaan
    public function deleteMember($id)
    {
        $companyId = session('active_company_id');
        $user = User::findOrFail($id);
        $company = Company::findOrFail($companyId);
        $removedBy = Auth::user();

        $userCompanies = UserCompany::where('user_id', $id)
            ->where('company_id', $companyId)
            ->get();

        if ($userCompanies->isEmpty()) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan di perusahaan ini.');
        }

        UserCompany::where('user_id', $id)
            ->where('company_id', $companyId)
            ->forceDelete();

        cache()->forget("user_companies_{$id}");

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

        if ($user->id === Auth::id()) {
            session()->forget('active_company_id');
            session(['removed_from_company' => $company->name]);
            return redirect()->route('member.removed')->with('success', 'Anda telah dihapus dari perusahaan ini.');
        }

        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari perusahaan.');
    }

    // Switch perusahaan
    public function switchCompany($companyId)
    {
        $user = Auth::user();
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

    // Simpan perusahaan baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::create([
            'id' => Str::uuid()->toString(),
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        $superAdminRole = Role::where('name', 'SuperAdmin')->first();

        UserCompany::create([
            'user_id' => Auth::id(),
            'company_id' => $company->id,
            'roles_id' => $superAdminRole->id ?? null,
        ]);

        session(['active_company_id' => $company->id]);
        return redirect()->route('dashboard-awal')
            ->with('success', 'Perusahaan berhasil dibuat!');
    }

    // Update perusahaan
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::findOrFail($id);

        $hasAccess = UserCompany::where('user_id', Auth::id())
            ->where('company_id', $id)
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengedit perusahaan ini');
        }

        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        return redirect()->route('dashboard')->with('success', 'Data perusahaan berhasil diperbarui!');
    }

    // Hapus perusahaan
    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        $hasAccess = UserCompany::where('user_id', Auth::id())
            ->where('company_id', $id)
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk menghapus perusahaan ini');
        }

        UserCompany::where('company_id', $id)->delete();
        $company->delete();

        if (session('active_company_id') === $id) {
            session()->forget('active_company_id');
        }

        return redirect()->route('dashboard')->with('success', 'Perusahaan berhasil dihapus!');
    }

    // ======================
    // 🔥 Method Baru untuk Hak Akses Member Company
    // ======================

    // Tampilkan modal hak akses (AJAX)
    public function hakAkses()
    {
        $companyId = session('active_company_id');
        if (!$companyId) return response()->json(['error' => 'No active company'], 400);

        $members = User::whereHas('userCompanies', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
        ->with(['userCompanies' => function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->with('role');
        }])->get();

        $data = $members->map(function($user) use ($companyId) {
            $userCompany = $user->userCompanies->where('company_id', $companyId)->first();
            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'role_id' => $userCompany->roles_id ?? null,
                'role_name' => optional($userCompany->role)->name
            ];
        });

        return response()->json($data);
    }

    // Update role anggota via AJAX
    public function updateUserRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles_id' => 'required|exists:roles,id',
        ]);

        $userCompany = UserCompany::where('company_id', session('active_company_id'))
            ->where('user_id', $request->user_id)
            ->firstOrFail();

        $currentUserRole = auth()->user()->userCompanies()
            ->where('company_id', session('active_company_id'))
            ->first()?->role->name ?? null;

        if (!in_array($currentUserRole, ['SuperAdmin','Admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userCompany->roles_id = $request->roles_id;
        $userCompany->save();

        return response()->json([
            'message' => 'Role updated successfully',
            'user_id' => $userCompany->user_id,
            'roles_id' => $userCompany->roles_id
        ]);
    }

    // JSON endpoint daftar role company
    public function getRolesForCompany()
    {
        $roles = Role::forCompany()->get(['id','name']); // pastikan scope forCompany ada
        return response()->json($roles);
    }
}
