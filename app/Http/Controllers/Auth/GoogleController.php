<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Redirect user ke halaman login Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle callback dari Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // 🔍 DEBUG LOG
            Log::info('=== GOOGLE LOGIN DEBUG ===', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
            ]);

            // ✅ Cari atau buat user
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(), // ⬅️ Google user sudah terverifikasi
                ]);
            } else {
                // Update Google ID jika user sudah ada tapi belum punya google_id
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            }

            // ✅ Login user
            Auth::login($user);

            // 🔍 DEBUG LOG
            Log::info('User logged in via Google:', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // ===================================
            // 🔥 PERBAIKAN UTAMA: SET SESSION
            // ===================================

            // ✅ Handle pending invitation (jika ada)
            $pendingToken = session('pending_invitation_token');
            if ($pendingToken) {
                Log::info('User has pending invitation, redirecting to accept');
                return redirect()->route('invite.accept', $pendingToken);
            }

            // ✅ PERBAIKAN: Ambil data company (bukan cuma cek exists)
            $userCompany = UserCompany::where('user_id', $user->id)
                ->whereNull('deleted_at') // ⬅️ Pastikan tidak soft deleted
                ->first(); // ⬅️ AMBIL DATA, bukan exists()

            // 🔍 DEBUG LOG
            Log::info('User Company Check (Google):', [
                'has_company' => $userCompany ? 'YES' : 'NO',
                'company_id' => $userCompany?->company_id,
                'role_id' => $userCompany?->roles_id,
            ]);

            // ❌ Jika user BENAR-BENAR tidak punya company
            if (!$userCompany) {
                Log::warning('User has no company (Google login), redirecting to create', [
                    'user_id' => $user->id,
                ]);

                return redirect()->route('buat-perusahaan.create')
                    ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
            }

            // ✅ SET SESSION active_company_id (INI YANG PENTING!)
            session(['active_company_id' => $userCompany->company_id]);

            // 🔍 DEBUG LOG
            Log::info('Session Set After Google Login:', [
                'active_company_id' => session('active_company_id'),
                'company_id_from_db' => $userCompany->company_id,
            ]);

            // ✅ Redirect ke dashboard
            return redirect('/dashboard')
                ->with('success', 'Berhasil login dengan Google!');
        } catch (\Exception $e) {
            // ❌ Error handling
            Log::error('Google Login Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('masuk')
                ->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }
}
