<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Invitation;
use App\Models\UserCompany;
use Illuminate\Support\Str;
use App\Mail\InvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
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

    // ✅ Kirim undangan dengan permission check
    public function send(Request $request)
    {
        $request->validate([
            'email_target' => 'required|email'
        ]);

        $user = Auth::user();
        $companyId = session('active_company_id');

        if (!$companyId) {
            return response()->json(['error' => 'Company tidak ditemukan.'], 400);
        }

        // ✅ Check permission - apakah boleh undang member?
        $currentUserRole = $this->getUserRole($companyId);

        if (!$this->canInviteMember($currentUserRole)) {
            return response()->json([
                'error' => 'Anda tidak memiliki izin untuk mengundang anggota! Hanya SuperAdmin, Admin, dan Manager yang dapat mengundang.'
            ], 403);
        }

        // 🔍 Debug
        Log::info('Sending invitation:', [
            'email' => $request->email_target,
            'company_id' => $companyId,
            'invited_by' => $user->id,
            'inviter_role' => $currentUserRole
        ]);

        // Cek apakah email sudah ada di perusahaan
        $existing = User::where('email', $request->email_target)->first();
        if ($existing && UserCompany::where('user_id', $existing->id)->where('company_id', $companyId)->exists()) {
            return response()->json(['error' => 'User sudah terdaftar di perusahaan ini.'], 400);
        }

        // Cek apakah sudah ada undangan pending
        $pendingInvite = Invitation::where('email_target', $request->email_target)
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->first();

        if ($pendingInvite) {
            return response()->json(['error' => 'Undangan masih aktif untuk email ini.'], 400);
        }

        // ✅ Generate token SEBELUM digunakan
        $token = Str::random(64);

        // Buat invitation baru
        $invitation = Invitation::create([
            'email_target' => $request->email_target,
            'token' => $token,
            'invited_by' => $user->id,
            'company_id' => $companyId,
            'expired_at' => now()->addDays(3),
        ]);

        // 🔍 Debug
        Log::info('Invitation created:', $invitation->toArray());

        // Kirim email
        try {
            Mail::to($request->email_target)->send(new InvitationMail($invitation, $user));
            Log::info('Email sent successfully to: ' . $request->email_target);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            // Tetap return success karena invitation sudah dibuat
        }

        return response()->json(['success' => true]);
    }

    // ✅ Batalkan undangan dengan permission check
    public function cancel($id)
    {
        $companyId = session('active_company_id');

        if (!$companyId) {
            return response()->json(['error' => 'Company tidak ditemukan'], 400);
        }

        // ✅ Check permission
        $currentUserRole = $this->getUserRole($companyId);

        if (!$this->canInviteMember($currentUserRole)) {
            return response()->json([
                'error' => 'Anda tidak memiliki izin untuk membatalkan undangan!'
            ], 403);
        }

        $invitation = Invitation::where('id', $id)
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json(['error' => 'Undangan tidak ditemukan'], 404);
        }

        // Update status menjadi cancelled
        $invitation->update(['status' => 'cancelled']);

        Log::info("Invitation cancelled by {$currentUserRole}: {$invitation->email_target}");

        return response()->json(['success' => true]);
    }

    // ✅ Hapus undangan dengan permission check
    public function delete($id)
    {
        $companyId = session('active_company_id');

        if (!$companyId) {
            return redirect()->back()->with('error', 'Company tidak ditemukan.');
        }

        // ✅ Check permission
        $currentUserRole = $this->getUserRole($companyId);

        if (!$this->canInviteMember($currentUserRole)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus undangan! Hanya SuperAdmin, Admin, dan Manager yang dapat menghapus undangan.');
        }

        $invitation = Invitation::where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$invitation) {
            return redirect()->back()->with('error', 'Undangan tidak ditemukan.');
        }

        $invitation->delete();

        Log::info("Invitation deleted by {$currentUserRole}: {$invitation->email_target}");

        return redirect()->back()->with('success', 'Undangan berhasil dihapus.');
    }

    // Terima undangan (tidak perlu permission check karena ini untuk yang diundang)
    public function accept($token)
    {
        // Cari invitation berdasarkan token
        $invitation = Invitation::where('token', $token)->firstOrFail();

        // Cek apakah undangan sudah expired atau sudah digunakan
        if ($invitation->status !== 'pending') {
            return redirect()->route('masuk')->with('error', 'Undangan sudah pernah digunakan.');
        }

        if ($invitation->expired_at->isPast()) {
            return redirect()->route('masuk')->with('error', 'Undangan sudah kadaluarsa.');
        }

        // 🔥 Cek apakah user sudah login
        $user = Auth::user();

        // 🔥 JIKA BELUM LOGIN
        if (!$user) {
            // Simpan token di session
            session(['pending_invitation_token' => $token]);

            // Cek apakah email sudah terdaftar di sistem
            $existingUser = User::where('email', $invitation->email_target)->first();

            if ($existingUser) {
                // ✅ User SUDAH PUNYA AKUN → arahkan ke login
                return redirect()->route('masuk')
                    ->with('info', 'Silakan masuk dengan akun Anda untuk menerima undangan.');
            } else {
                // ✅ User BELUM PUNYA AKUN → arahkan ke registrasi
                return redirect()->route('daftar', ['email' => $invitation->email_target])
                    ->with('info', 'Silakan daftar terlebih dahulu untuk menerima undangan.');
            }
        }

        // 🔥 JIKA SUDAH LOGIN
        // Validasi email yang login harus sama dengan email yang diundang
        if ($user->email !== $invitation->email_target) {
            Auth::logout();
            session(['pending_invitation_token' => $token]);
            return redirect()->route('masuk')
                ->with('error', 'Email Anda (' . $user->email . ') tidak sesuai dengan undangan (' . $invitation->email_target . '). Silakan login dengan email yang benar.');
        }

        // Cek apakah user sudah menjadi member perusahaan ini
        $alreadyMember = UserCompany::where('user_id', $user->id)
            ->where('company_id', $invitation->company_id)
            ->exists();

        if ($alreadyMember) {
            // Update status jadi accepted
            $invitation->update(['status' => 'accepted']);
            session()->forget('pending_invitation_token');

            return redirect()->route('dashboard')
                ->with('info', 'Anda sudah menjadi anggota perusahaan ini.');
        }

        // 🔥 Ambil role "Member" (role default untuk yang diundang)
        $memberRole = Role::where('name', 'Member')->first();

        if (!$memberRole) {
            return redirect()->route('dashboard')
                ->with('error', 'Role Member tidak ditemukan di sistem. Silakan hubungi administrator.');
        }

        // 🔥 Tambahkan user ke perusahaan dengan role Member
        UserCompany::create([
            'user_id' => $user->id,
            'company_id' => $invitation->company_id,
            'roles_id' => $memberRole->id,
        ]);

        // 🔥 Update status undangan menjadi ACCEPTED
        $invitation->update(['status' => 'accepted']);

        // 🔥 Hapus pending invitation token dari session
        session()->forget('pending_invitation_token');

        // Set perusahaan yang baru dimasuki sebagai active
        session(['active_company_id' => $invitation->company_id]);

        Log::info("User {$user->email} accepted invitation and joined company {$invitation->company_id}");

        return redirect()->route('dashboard')
            ->with('success', 'Selamat! Anda berhasil bergabung ke perusahaan ' . $invitation->company->name);
    }
}
