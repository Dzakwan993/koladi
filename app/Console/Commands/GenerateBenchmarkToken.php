<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * GenerateBenchmarkToken Command
 *
 * Membuat token autentikasi sementara untuk benchmark script.
 * Token disimpan di tabel personal_access_tokens (jika pakai Sanctum)
 * atau di file sementara.
 *
 * Cara pakai:
 *   docker exec koladi_app_local php artisan benchmark:token \
 *       --email=ardhaniishere@gmail.com
 *
 * Output: cookie string siap pakai untuk script benchmark.
 */
class GenerateBenchmarkToken extends Command
{
    protected $signature = 'benchmark:token {--email= : Email user yang akan digunakan untuk benchmark}';
    protected $description = 'Generate session cookie untuk benchmark DSS endpoint (local only)';

    public function handle(): int
    {
        if (!app()->environment('local')) {
            $this->error('Command ini hanya bisa dijalankan di environment local!');
            return self::FAILURE;
        }

        $email = $this->option('email') ?? 'ardhaniishere@gmail.com';

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan.");
            return self::FAILURE;
        }

        // Login user secara programatik dan buat session
        Auth::login($user);
        $cookieName = config('session.cookie', 'laravel_session');

        // Regenerate untuk keamanan
        session()->regenerate();
        $newSessionId = session()->getId();

        // Cari company Bismillah (company yang memiliki workspace penelitian)
        // Priority: cari company yang memiliki workspace Div. Desain / HR Keuangan / MOU PT Xyz
        $targetWorkspace = \App\Models\Workspace::whereIn('name', [
            'Div. Desain', 'HR Keuangan', 'MOU PT Xyz'
        ])->first();

        $company = null;
        if ($targetWorkspace) {
            // Ambil company dari workspace tersebut
            $company = $user->companies()
                ->where('companies.id', $targetWorkspace->company_id)
                ->first();
        }

        // Fallback: ambil company pertama
        if (!$company) {
            $company = $user->companies()->first();
        }

        if ($company) {
            session(['active_company_id' => $company->id]);
        }
        session()->save();

        $encryptedSession = \Illuminate\Support\Facades\Crypt::encrypt(
            \Illuminate\Cookie\CookieValuePrefix::create($cookieName, \Illuminate\Support\Facades\Crypt::getKey()) . $newSessionId,
            false
        );

        $this->info("=== Benchmark Token Generated ===");
        $this->info("User    : {$user->name} ({$user->email})");
        $this->info("User ID : {$user->id}");
        $this->info("Company : " . ($company->name ?? 'N/A') . " ({$company->id})");
        $this->info("Session : {$newSessionId}");
        $this->line('');
        $this->info("Simpan output berikut sebagai SESSION_ID di benchmark script:");
        $this->comment($encryptedSession);
        $this->line('');
        $this->info("Cookie name  : {$cookieName}");

        // Simpan ke file config benchmark
        $configPath = storage_path('app/benchmark/auth.json');
        @mkdir(dirname($configPath), 0755, true);
        file_put_contents($configPath, json_encode([
            'session_id'  => $encryptedSession, // Use ENCRYPTED session string for cookie
            'raw_session' => $newSessionId,
            'cookie_name' => $cookieName,
            'user_id'     => $user->id,
            'user_email'  => $user->email,
            'company_id'  => $company->id ?? null,
            'generated_at'=> now()->toISOString(),
        ], JSON_PRETTY_PRINT));

        $this->info("Auth config saved to: storage/app/benchmark/auth.json");

        return self::SUCCESS;
    }
}
