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
    // Kirim undangan
    public function send(Request $request)
    {
        $request->validate([
            'email_target' => 'required|email'
        ]);

        $user = Auth::user();
        $companyId = session('active_company_id');

        // 🔍 Debug
        Log::info('Sending invitation:', [
            'email' => $request->email_target,
            'company_id' => $companyId,
            'invited_by' => $user->id
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

    // Batalkan undangan
    public function cancel($id)
    {
        $companyId = session('active_company_id');

        $invitation = Invitation::where('id', $id)
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json(['error' => 'Undangan tidak ditemukan'], 404);
        }

        // Update status menjadi cancelled
        $invitation->update(['status' => 'cancelled']);

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        $companyId = session('active_company_id');

        $invitation = Invitation::where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$invitation) {
            return redirect()->back()->with('error', 'Undangan tidak ditemukan.');
        }

        $invitation->delete();

        return redirect()->back()->with('success', 'Undangan berhasil dihapus.');
    }


    // Terima undangan
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

        // 🔥 Ambil role "Member"
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

        return redirect()->route('dashboard')
            ->with('success', 'Selamat! Anda berhasil bergabung ke perusahaan ' . $invitation->company->name);
    }
}
