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

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            Log::info('=== GOOGLE LOGIN DEBUG ===', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
            ]);

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            }

            Auth::login($user);

            Log::info('User logged in via Google:', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Handle pending invitation
            $pendingToken = session('pending_invitation_token');
            if ($pendingToken) {
                Log::info('User has pending invitation, redirecting to accept');
                return redirect()->route('invite.accept', $pendingToken);
            }

            // 🔥 Check user company dengan validasi status_active
            $userCompany = UserCompany::where('user_id', $user->id)
                ->where('status_active', true) // 🔥 VALIDASI STATUS AKTIF
                ->whereNull('deleted_at')
                ->first();

            Log::info('User Company Check (Google):', [
                'has_company' => $userCompany ? 'YES' : 'NO',
                'company_id' => $userCompany?->company_id,
                'role_id' => $userCompany?->roles_id,
                'status_active' => $userCompany?->status_active,
            ]);

            // ❌ Jika user tidak punya company aktif
            if (!$userCompany) {
                // Cek apakah user ada tapi nonaktif
                $inactiveCompany = UserCompany::where('user_id', $user->id)
                    ->where('status_active', false)
                    ->whereNull('deleted_at')
                    ->first();

                if ($inactiveCompany) {
                    Auth::logout();
                    return redirect()->route('masuk')
                        ->with('error', 'Akun Anda telah dinonaktifkan oleh Administrator. Hubungi admin untuk informasi lebih lanjut.');
                }

                Log::warning('User has no active company (Google login), redirecting to create', [
                    'user_id' => $user->id,
                ]);

                return redirect()->route('buat-perusahaan.create')
                    ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
            }

            // ✅ SET SESSION active_company_id
            session(['active_company_id' => $userCompany->company_id]);

            Log::info('Session Set After Google Login:', [
                'active_company_id' => session('active_company_id'),
                'company_id_from_db' => $userCompany->company_id,
            ]);

            // 🎯 CEK FIRST LOGIN
            $isFirstLogin = !session()->has('has_logged_in_before');
            if ($isFirstLogin) {
                session(['has_logged_in_before' => true]);
                session()->flash('first_login', true);
            }

            return redirect()->intended('/dashboard')
                ->with('success', 'Berhasil masuk dengan Google!');

        } catch (\Exception $e) {
            Log::error('Google Login Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('masuk')
                ->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }
}
