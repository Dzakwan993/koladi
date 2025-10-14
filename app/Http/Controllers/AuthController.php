<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    // Tampilkan halaman register
    public function showRegister()
    {
        return view('daftar'); // sesuaikan nama blade
    }

    public function register(Request $request)
    {
        // dd($request->all()); // lihat semua data yang dikirim

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'id_role' => 5, // misal default role user biasa
            'status_aktif' => true,
        ]);

        return redirect()->route('masuk')->with('success', 'Akun berhasil dibuat!');
    }

    // Tampilkan halaman login
    public function showLogin()
    {
        return view('masuk'); // sesuaikan dengan file blade
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
            return redirect('/')->with('success', 'Selamat datang, ' . Auth::user()->nama_lengkap);
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
        return redirect('/masuk');
    }
}
