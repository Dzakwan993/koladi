<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Tampilkan halaman register
    public function showRegister()
    {
        return view('daftar');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'id_role' => 5, // Role default (sesuaikan dengan ID role di tabel roles)
            'status_aktif' => true,
        ]);

        return redirect()->route('masuk')->with('success', 'Akun berhasil dibuat! Silakan login.');
    }

    // Tampilkan halaman login
    public function showLogin()
    {
        return view('masuk');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // ✅ Cek apakah user sudah punya perusahaan
            if ($user->companies()->count() === 0) {
                return redirect()->route('companies.create')
                    ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
            }

            // ✅ Jika sudah punya perusahaan, redirect ke dashboard
            return redirect()->route('dashboard')
                ->with('success', 'Selamat datang, ' . $user->nama_lengkap);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah',
        ])->onlyInput('email');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('masuk');
    }
}
