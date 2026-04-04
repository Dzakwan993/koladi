<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCompany;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{
    // ✅ Halaman daftar
    public function showRegister()
    {
        $email = request()->query('email', '');
        return view('auth.daftar', compact('email'));
    }

    // ✅ Proses daftar - KIRIM OTP
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $isInvited = $request->query('email') && $request->email === $request->query('email');
        $pendingToken = session('pending_invitation_token');

        // 🔥 SKENARIO 1: User baru dari undangan (skip OTP)
        if ($isInvited && $pendingToken) {
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);

            // ✅ JANGAN hapus token, biarkan sampai login
            // Token akan dihapus setelah berhasil join company di InvitationController@accept

            return redirect()->route('masuk')->with([
                'success' => 'Akun berhasil dibuat! Silakan masuk untuk menerima undangan perusahaan Anda.'
            ]);
        }

        // 🔥 SKENARIO 2: Pendaftaran biasa (pakai OTP)
        session([
            'register_data' => [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ],
            'pending_invitation_token' => $pendingToken // Simpan token kalau ada
        ]);

        try {
            $otp = OtpVerification::generateOtp($request->email, 'register');
            Mail::to($request->email)->send(new OtpMail($otp, 'register'));

            return redirect()->route('verify-otp.show')
                ->with('success', 'Kode OTP telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP. Silakan coba lagi.'
            ])->withInput();
        }
    }

    // ✅ Halaman verifikasi OTP
    public function showVerifyOtp()
    {
        if (!session()->has('register_data')) {
            return redirect()->route('daftar')->with('error', 'Sesi pendaftaran tidak valid.');
        }

        return view('auth.verify-otp');
    }

    // ✅ Proses verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $registerData = session('register_data');
        if (!$registerData) {
            return back()->withErrors(['otp' => 'Sesi pendaftaran tidak valid.']);
        }

        if (!OtpVerification::verifyOtp($registerData['email'], $request->otp, 'register')) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);
        }

        $user = User::create([
            'full_name' => $registerData['full_name'],
            'email' => $registerData['email'],
            'password' => $registerData['password'],
            'email_verified_at' => now(),
        ]);

        // ✅ Ambil token tapi JANGAN hapus
        $pendingToken = session('pending_invitation_token');

        // ✅ Hapus HANYA register_data
        session()->forget('register_data');
        // JANGAN forget pending_invitation_token di sini!

        if ($pendingToken) {
            return redirect()->route('masuk')->with([
                'info' => 'Akun berhasil dibuat! Silakan masuk untuk menerima undangan perusahaan Anda.'
            ]);
        }

        return redirect()->route('masuk')->with([
            'success' => 'Akun berhasil dibuat dan email terverifikasi! Silakan masuk.'
        ]);
    }

    // ✅ Kirim ulang OTP
    public function resendOtp(Request $request)
    {
        $registerData = session('register_data');
        if (!$registerData) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid.'], 400);
        }

        $otp = OtpVerification::generateOtp($registerData['email'], 'register');
        Mail::to($registerData['email'])->send(new OtpMail($otp, 'register'));

        return response()->json(['success' => true, 'message' => 'Kode OTP baru telah dikirim.']);
    }

    // ✅ Halaman lupa password
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // ✅ Kirim OTP untuk reset password
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $otp = OtpVerification::generateOtp($request->email, 'reset_password');
        Mail::to($request->email)->send(new OtpMail($otp, 'reset_password'));

        session(['reset_email' => $request->email]);

        return redirect()->route('reset-password.verify-otp')
            ->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    // ✅ Halaman verifikasi OTP reset password
    public function showResetPasswordVerifyOtp()
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('forgot-password')->with('error', 'Sesi tidak valid.');
        }

        return view('auth.verify-reset-otp');
    }

    // ✅ Verifikasi OTP reset password
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $email = session('reset_email');
        if (!$email) {
            return back()->withErrors(['otp' => 'Sesi tidak valid.']);
        }

        if (!OtpVerification::verifyOtp($email, $request->otp, 'reset_password')) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);
        }

        session(['otp_verified' => true]);
        return redirect()->route('reset-password.form');
    }

    // ✅ Halaman form reset password baru
    public function showResetPasswordForm()
    {
        if (!session()->has('otp_verified')) {
            return redirect()->route('forgot-password')->with('error', 'Verifikasi OTP diperlukan.');
        }

        return view('auth.reset-password');
    }

    // ✅ Proses reset password
    public function resetPassword(Request $request)
    {
        if (!session()->has('otp_verified')) {
            return redirect()->route('forgot-password')->with('error', 'Verifikasi OTP diperlukan.');
        }

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $email = session('reset_email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        session()->forget(['reset_email', 'otp_verified']);

        return redirect()->route('masuk')->with('success', 'Password berhasil direset. Silakan masuk dengan password baru.');
    }

    // ✅ Halaman login
    public function showLogin()
    {
        return view('auth.masuk');
    }

    // ✅ Proses login
     // ✅ Proses login - DENGAN CEK STATUS AKTIF
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        $user = Auth::user();

        // 🔥 CEK SYSTEM ADMIN
        if ($user->isSystemAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Selamat datang, Admin Sistem!');
        }

        // ✅ Handle pending invitation
        $pendingToken = session('pending_invitation_token');
        if ($pendingToken) {
            $invitation = \App\Models\Invitation::where('token', $pendingToken)->first();
            // Hanya redirect ke accept jika email user yang login SAMA dengan target undangan
            if ($invitation && $user->email === $invitation->email_target) {
                return redirect()->route('invite.accept', $pendingToken);
            } else {
                // Hapus token dari session agar tidak mengganggu login akun lain
                session()->forget('pending_invitation_token');
            }
        }

        // 🔥 PRIORITAS 2: Check apakah user punya company AKTIF
        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('status_active', true) // 🔥 VALIDASI STATUS AKTIF
            ->whereNull('deleted_at')
            ->first();

        if (!$userCompany) {
            // Cek apakah user ada tapi nonaktif
            $inactiveCompany = UserCompany::where('user_id', $user->id)
                ->where('status_active', false)
                ->whereNull('deleted_at')
                ->first();

            if ($inactiveCompany) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan oleh Administrator. Hubungi admin untuk informasi lebih lanjut.',
                ])->onlyInput('email');
            }

            Log::warning('User has no active company, redirecting to create company', [
                'user_id' => $user->id,
            ]);

            return redirect()->route('buat-perusahaan.create')
                ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
        }

        // ✅ SET SESSION active_company_id
        session(['active_company_id' => $userCompany->company_id]);



        return redirect()->intended('/dashboard')
            ->with('success', 'Berhasil masuk. Selamat datang!');
    }

    return back()->withErrors([
        'email' => 'Email atau kata sandi salah.',
    ])->onlyInput('email');
}

    // ========================================
    // ✅ METHOD LOGOUT - Bersihkan session
    // ========================================
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk')->with('success', 'Anda telah keluar.');
    }
}
