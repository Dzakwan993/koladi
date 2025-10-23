<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Halaman daftar
    public function showRegister()
    {
        // Ambil email dari query string jika ada (dari undangan)
        $email = request()->query('email', '');
        return view('auth.daftar', compact('email'));
    }

    // Proses daftar
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Buat user baru
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 🔥 Simpan token undangan (jika ada)
        $pendingToken = session('pending_invitation_token');

        // 🔥 Hapus sesi login otomatis, biar user login manual dulu
        Auth::logout();

        // 🔥 Redirect ke halaman login, dengan info sesuai kondisinya
        if ($pendingToken) {
            return redirect()->route('masuk')->with([
                'info' => 'Akun berhasil dibuat! Silakan masuk untuk menerima undangan perusahaan Anda.'
            ]);
        }

        return redirect()->route('masuk')->with([
            'success' => 'Akun berhasil dibuat! Silakan masuk untuk melanjutkan.'
        ]);
    }


    // ✅ Halaman login
    public function showLogin()
    {
        return view('auth.masuk');
    }


    // ✅ Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // 🔥 PRIORITAS 1: Cek apakah ada pending invitation
            $pendingToken = session('pending_invitation_token');
            if ($pendingToken) {
                return redirect()->route('invite.accept', $pendingToken);
            }

            // 🔥 PRIORITAS 2: Cek apakah user sudah terhubung ke perusahaan
            $hasCompany = DB::table('user_companies')
                ->where('user_id', $user->id)
                ->exists();

            if (!$hasCompany) {
                return redirect()->route('buat-perusahaan.create')
                    ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
            }

            // 🔥 PRIORITAS 3: Jika sudah punya perusahaan, langsung ke dashboard
            return redirect()->intended('/dashboard')
                ->with('success', 'Berhasil masuk. Selamat datang!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi salah.',
        ])->onlyInput('email');
    }


    // ✅ Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk')->with('success', 'Anda telah keluar.');
    }
}
