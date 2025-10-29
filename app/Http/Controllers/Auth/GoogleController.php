<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)),
                ]);
            }

            Auth::login($user);

            // Cek apakah user sudah punya perusahaan
            if (!DB::table('user_companies')->where('user_id', $user->id)->exists()) {
                return redirect()->route('buat-perusahaan.create')
                    ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
            }

            return redirect('/dashboard')->with('success', 'Berhasil login dengan Google!');
        } catch (\Exception $e) {
            return redirect()->route('masuk')->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }
}
