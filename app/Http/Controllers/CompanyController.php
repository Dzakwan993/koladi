<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
    // ✅ Helper: Ambil role user di company
    private function getUserRole($companyId, $userId = null)
    {
        $userId = $userId ?? Auth::id();

        $userCompany = UserCompany::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->with('role')
            ->first();

        return $userCompany?->role?->name ?? null;
    }

    // ✅ Helper: Check apakah user adalah Super Admin
    private function isSuperAdmin($companyId)
    {
        $role = $this->getUserRole($companyId);
        return in_array($role, ['SuperAdmin', 'Super Admin']);
    }

    // ✅ Helper: Check permission untuk hapus member
    private function canDeleteMember($currentUserRole, $targetUserRole)
    {
        // ❌ AdminSistem tidak termasuk dalam hierarki permission
        if ($currentUserRole === 'AdminSistem' || $targetUserRole === 'AdminSistem') {
            return false;
        }

        $hierarchy = [
            'SuperAdmin' => 4,
            'Super Admin' => 4,
            'Admin' => 3,
            'Administrator' => 3, // Support untuk nama alternatif
            'Manager' => 2,
            'Member' => 1
        ];

        $currentLevel = $hierarchy[$currentUserRole] ?? 0;
        $targetLevel = $hierarchy[$targetUserRole] ?? 0;

        // SuperAdmin bisa hapus Admin, Manager, Member
        // Admin bisa hapus Manager, Member
        // Manager bisa hapus Member
        // Member tidak bisa hapus siapa-siapa
        return $currentLevel > $targetLevel;
    }

    // ✅ Helper: Check permission untuk undang member
    private function canInviteMember($role)
    {
        // ❌ AdminSistem tidak bisa undang member
        if ($role === 'AdminSistem') {
            return false;
        }

        // Hanya SuperAdmin, Admin, dan Manager yang bisa undang
        return in_array($role, ['SuperAdmin', 'Super Admin', 'Admin', 'Administrator', 'Manager']);
    }

    // Halaman notifikasi member dihapus
    public function memberRemoved()
    {
        $removedCompanyName = session('removed_from_company', 'Perusahaan');
        $user = Auth::user();
        $companies = $user->companies()->get();

        return view('member-removed', compact('removedCompanyName', 'companies'));
    }

    // ✅ Tampilkan anggota perusahaan dengan permission check
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

    // ✅ Ambil role user yang sedang login
    $currentUserRole = $this->getUserRole($companyId);

    $members = User::whereHas('userCompanies', function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
    })
        ->with(['userCompanies' => function ($query) use ($companyId) {
            $query->where('company_id', $companyId)->with('role');
        }])
        ->get()
        ->map(function ($user) use ($companyId, $currentUserRole) {
            $userCompany = $user->userCompanies->where('company_id', $companyId)->first();
            $user->role_name = ($userCompany && $userCompany->role)
                ? ($userCompany->role->nama_role ?? $userCompany->role->name ?? null)
                : null;

            // ✅ Check apakah current user bisa hapus member ini
            $user->can_delete = $this->canDeleteMember($currentUserRole, $user->role_name);

            return $user;
        })
        // ❌ Filter out AdminSistem dari list
        ->filter(function ($user) {
            return $user->role_name !== 'AdminSistem';
        });

    $invites = Invitation::where('company_id', $companyId)
        ->where('status', 'pending')
        ->where('expired_at', '>', now())
        ->with('inviter')
        ->orderBy('created_at', 'desc')
        ->get();

    // ✅ Check permission untuk undang member
    $canInvite = $this->canInviteMember($currentUserRole);

    // 🔥 CEK STATUS TRIAL DAN LIMIT USER
    $company = Company::findOrFail($companyId);
    
    // 🔥 TAMBAHAN: Cek apakah perusahaan masih dalam masa trial
    $isTrial = $company->status === 'trial' && 
               $company->trial_end && 
               now()->lessThan($company->trial_end);
    
    $activeUserCount = $company->active_users_count;
    $userLimit = $company->subscription->total_user_limit ?? 0;
    
    // 🔥 PERUBAHAN LOGIKA: Limit tidak berlaku jika masih trial
    $isLimitReached = !$isTrial && ($activeUserCount >= $userLimit);

    // 🔥 Jika limit tercapai DAN bukan trial, disable tombol undang
    if ($isLimitReached) {
        $canInvite = false;
    }

    return view('tambah-anggota', compact(
        'members', 
        'invites', 
        'canInvite',
        'activeUserCount',
        'userLimit',
        'isLimitReached',
        'currentUserRole',
        'company',        // 🔥 TAMBAHAN: Kirim data company ke view
        'isTrial'         // 🔥 TAMBAHAN PENTING: Kirim status trial
    ));
}

    // ✅ Hapus anggota perusahaan dengan permission check
    public function deleteMember($id)
    {
        $companyId = session('active_company_id');
        if (!$companyId) {
            return redirect()->back()->with('error', 'Company tidak ditemukan.');
        }

        // ✅ Ambil role user yang sedang login
        $currentUserRole = $this->getUserRole($companyId);
        if (!$currentUserRole) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses.');
        }

        // ❌ AdminSistem tidak bisa hapus member
        if ($currentUserRole === 'AdminSistem') {
            return redirect()->back()->with('error', 'AdminSistem tidak memiliki izin untuk menghapus anggota!');
        }

        // ✅ Ambil data member yang akan dihapus
        $targetUser = User::findOrFail($id);
        $targetUserRole = $this->getUserRole($companyId, $id);

        // ❌ Tidak bisa hapus AdminSistem
        if ($targetUserRole === 'AdminSistem') {
            return redirect()->back()->with('error', 'AdminSistem tidak dapat dihapus!');
        }

        // ✅ Check permission - apakah boleh hapus member ini?
        if (!$this->canDeleteMember($currentUserRole, $targetUserRole)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus anggota ini!');
        }

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
            Mail::to($targetUser->email)->send(new MemberRemovedNotification(
                $targetUser,
                $company,
                $removedBy
            ));
        } catch (\Exception $e) {
            Log::error('Gagal kirim email penghapusan anggota: ' . $e->getMessage());
        }

        Log::info("Member {$targetUser->email} berhasil dihapus dari perusahaan {$company->name} oleh {$removedBy->email}");

        if ($targetUser->id === Auth::id()) {
            session()->forget('active_company_id');
            session(['removed_from_company' => $company->name]);
            return redirect()->route('member.removed')->with('success', 'Anda telah dihapus dari perusahaan ini.');
        }

        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari perusahaan.');
    }

    // Switch perusahaan
   // Switch perusahaan - DENGAN CEK STATUS AKTIF
public function switchCompany($companyId)
{
    $user = Auth::user();
    
    // 🔥 CEK STATUS AKTIF
    $userCompany = UserCompany::where('user_id', $user->id)
        ->where('company_id', $companyId)
        ->where('status_active', true) // 🔥 HANYA YANG AKTIF
        ->first();

    if ($userCompany) {
        session(['active_company_id' => $companyId]);
        return redirect()->route('dashboard')->with('success', 'Berhasil beralih perusahaan');
    }

    // Cek apakah user ada di company tapi nonaktif
    $inactiveCompany = UserCompany::where('user_id', $user->id)
        ->where('company_id', $companyId)
        ->where('status_active', false)
        ->first();

    if ($inactiveCompany) {
        return redirect()->route('dashboard')->with('error', 'Akun Anda di perusahaan ini telah dinonaktifkan oleh Administrator.');
    }

    return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke perusahaan ini');
}

    // Halaman form buat perusahaan
    public function create()
    {
        return view('buat-perusahaan');
    }

    // ✅ Update perusahaan - HANYA SUPER ADMIN
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::findOrFail($id);

        // ✅ Check apakah user adalah Super Admin
        if (!$this->isSuperAdmin($id)) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat mengedit perusahaan!');
        }

        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        return redirect()->route('dashboard')->with('alert', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Data perusahaan berhasil diperbarui!',
        ]);
    }

    // ✅ Hapus perusahaan - HANYA SUPER ADMIN
    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        // ✅ Check apakah user adalah Super Admin
        if (!$this->isSuperAdmin($id)) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus perusahaan!');
        }

        $user = Auth::user();
        $wasActive = session('active_company_id') === $id;

        UserCompany::where('company_id', $id)->delete();
        $company->delete();

        cache()->forget("user_companies_{$user->id}");

        if ($wasActive) {
            session()->forget('active_company_id');

            // Find another company the user is member of
            $nextCompany = UserCompany::where('user_id', $user->id)
                ->where('status_active', true)
                ->first();

            if ($nextCompany) {
                session(['active_company_id' => $nextCompany->company_id]);
                return redirect()->route('dashboard')->with('alert', [
                    'icon' => 'success',
                    'title' => 'Terhapus!',
                    'text' => 'Perusahaan berhasil dihapus. Anda dialihkan ke perusahaan lain.',
                ]);
            }
        }

        return redirect()->route('dashboard')->with('alert', [
            'icon' => 'success',
            'title' => 'Terhapus!',
            'text' => 'Perusahaan berhasil dihapus!',
        ]);
    }

    // ======================
    // 🔥 Method untuk Hak Akses Member Company
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

        $data = $members->map(function ($user) use ($companyId) {
            $userCompany = $user->userCompanies->where('company_id', $companyId)->first();
            $roleName = optional($userCompany->role)->name;

            // ❌ Skip AdminSistem
            if ($roleName === 'AdminSistem') {
                return null;
            }

            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'role_id' => $userCompany->roles_id ?? null,
                'role_name' => $roleName
            ];
        })->filter(); // Remove null values

        return response()->json($data);
    }

    // Update role anggota via AJAX
    public function updateUserRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles_id' => 'required|exists:roles,id',
        ]);

        $companyId = session('active_company_id');
        $currentUserRole = $this->getUserRole($companyId);

        // ❌ AdminSistem tidak bisa update role
        if ($currentUserRole === 'AdminSistem') {
            return response()->json(['message' => 'AdminSistem tidak memiliki izin'], 403);
        }

        // Hanya SuperAdmin dan Admin yang bisa update role
        if (!in_array($currentUserRole, ['SuperAdmin', 'Super Admin', 'Admin', 'Administrator'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userCompany = UserCompany::where('company_id', $companyId)
            ->where('user_id', $request->user_id)
            ->firstOrFail();

        // ❌ Tidak bisa mengubah role menjadi AdminSistem
        $newRole = Role::find($request->roles_id);
        if ($newRole && $newRole->name === 'AdminSistem') {
            return response()->json(['message' => 'Tidak dapat mengubah role menjadi AdminSistem'], 403);
        }

        $userCompany->roles_id = $request->roles_id;
        $userCompany->save();

        return response()->json([
            'message' => 'Role updated successfully',
            'user_id' => $userCompany->user_id,
            'roles_id' => $userCompany->roles_id
        ]);
    }

    // JSON endpoint daftar role company (exclude AdminSistem)
    public function getRolesForCompany()
    {
        $roles = Role::whereIn('name', ['SuperAdmin', 'Admin', 'Administrator', 'Manager', 'Member'])
            ->get(['id', 'name']);

        return response()->json($roles);
    }
    // app/Http/Controllers/CompanyController.php

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $company = Company::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => 'trial',
                'trial_start' => Carbon::now(),
                'trial_end' => Carbon::now()->addDays(7),
            ]);

            Log::info('Company Created:', [
                'id' => $company->id,
                'name' => $company->name,
                'status' => $company->status,
                'trial_start' => $company->trial_start,
                'trial_end' => $company->trial_end,
                'trial_is_future' => Carbon::parse($company->trial_end)->isFuture(),
            ]);

            $superAdminRole = Role::where('name', 'SuperAdmin')
                ->orWhere('name', 'Super Admin')
                ->first();

            if (!$superAdminRole) {
                throw new \Exception('Role SuperAdmin tidak ditemukan di database');
            }

            $company->users()->attach(Auth::id(), [
                'roles_id' => $superAdminRole->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            session(['active_company_id' => $company->id]);

            Log::info('Session Set:', [
                'active_company_id' => session('active_company_id'),
                'company_found' => Company::find(session('active_company_id')) ? 'YES' : 'NO'
            ]);

            // ✅ SET ONBOARDING TYPE UNTUK FOUNDER (FULL TUTORIAL)
            $user = Auth::user();
            if (!$user->onboarding_type) {
                $user->onboarding_type = 'full';  // ⬅️ TAMBAHAN INI
                $user->has_seen_onboarding = false;
                $user->onboarding_step = null;
                $user->save();

                Log::info('✅ Onboarding type set for founder:', [
                    'user_id' => $user->id,
                    'type' => 'full'
                ]);
            }

            DB::commit();

            return redirect()->route('dashboard')
                ->with('success', 'Perusahaan berhasil dibuat dengan 7 hari trial gratis!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat perusahaan: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat perusahaan: ' . $e->getMessage());
        }
    }

    public function checkTrialExpiration()
    {
        $companyId = session('active_company_id');

        if (!$companyId) {
            return redirect()->route('dashboard')
                ->with('error', 'Silakan pilih perusahaan terlebih dahulu.');
        }

        $company = Company::findOrFail($companyId);

        if ($company->status === 'trial' && $company->trial_end) {
            $trialEnds = Carbon::parse($company->trial_end);

            // Jika trial sudah lewat dan tidak ada subscription aktif
            if ($trialEnds->isPast()) {
                $hasActiveSubscription = $company->subscription &&
                    $company->subscription->status === 'active' &&
                    Carbon::parse($company->subscription->end_date)->isFuture();

                if (!$hasActiveSubscription) {
                    // Matikan akses company
                    $company->update([
                        'status' => 'expired'
                    ]);

                    return redirect()->route('pembayaran')
                        ->with('warning', 'Trial Anda telah berakhir. Silakan pilih paket untuk melanjutkan.');
                }
            }
        }

        return null;
    }
}
