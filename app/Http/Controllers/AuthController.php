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
        return view('auth.daftar');
    }

    // Proses daftar
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('masuk')->with('success', 'Akun berhasil dibuat! Silakan masuk.');
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
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // ✅ Cek apakah user sudah terhubung ke perusahaan
            $hasCompany = DB::table('user_companies')
                ->where('user_id', $user->id)
                ->exists();

            if (!$hasCompany) {
                // Kalau belum punya perusahaan, arahkan ke halaman buat perusahaan
                return redirect()->route('buat-perusahaan')
                    ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
            }

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
