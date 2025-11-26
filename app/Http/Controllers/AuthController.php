<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpVerification;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // 🔥 Cek apakah ini dari undangan (email sudah di-set dari query string)
        $isInvited = $request->query('email') && $request->email === $request->query('email');

        if ($isInvited) {
            // 🔥 Jika dari undangan, langsung buat user tanpa OTP
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(), // Langsung verified karena diundang
            ]);

            $pendingToken = session('pending_invitation_token');
            session()->forget('pending_invitation_token');

            if ($pendingToken) {
                return redirect()->route('masuk')->with([
                    'info' => 'Akun berhasil dibuat! Silakan masuk untuk menerima undangan perusahaan Anda.'
                ]);
            }

            return redirect()->route('masuk')->with([
                'success' => 'Akun berhasil dibuat! Silakan masuk untuk melanjutkan.'
            ]);
        }

        // 🔥 Untuk pendaftaran biasa (bukan undangan), tetap pakai OTP
        session([
            'register_data' => [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ],
            'pending_invitation_token' => session('pending_invitation_token')
        ]);

        try {
            // 🔥 Generate dan kirim OTP
            $otp = OtpVerification::generateOtp($request->email, 'register');
            Mail::to($request->email)->send(new OtpMail($otp, 'register'));

            return redirect()->route('verify-otp.show')
                ->with('success', 'Kode OTP telah dikirim ke email Anda. Silakan cek inbox atau folder spam.');
        } catch (\Exception $e) {
            // Jika email gagal terkirim, tampilkan error yang lebih user-friendly
            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP. Silakan coba lagi atau hubungi admin.'
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

        // 🔥 Verifikasi OTP
        if (!OtpVerification::verifyOtp($registerData['email'], $request->otp, 'register')) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);
        }

        // 🔥 Buat user setelah OTP valid dengan email_verified_at sudah terisi
        $user = User::create([
            'full_name' => $registerData['full_name'],
            'email' => $registerData['email'],
            'password' => $registerData['password'],
            'email_verified_at' => now(), // ⬅️ PENTING: Set email sudah terverifikasi
        ]);

        // 🔥 Hapus session pendaftaran
        $pendingToken = session('pending_invitation_token');
        session()->forget(['register_data', 'pending_invitation_token']);

        // 🔥 Redirect sesuai kondisi
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
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            $pendingToken = session('pending_invitation_token');
            if ($pendingToken) {
                return redirect()->route('invite.accept', $pendingToken);
            }

            $hasCompany = DB::table('user_companies')
                ->where('user_id', $user->id)
                ->exists();

            if (!$hasCompany) {
                return redirect()->route('buat-perusahaan.create')
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
