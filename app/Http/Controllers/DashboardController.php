<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\Pengumuman;
use App\Models\CalendarEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Helper: Get Avatar URL
     */
    private function getAvatarUrl($user)
    {
        if (!$user) {
            return 'https://ui-avatars.com/api/?name=User&background=3B82F6&color=fff&bold=true&size=128';
        }

        if ($user->avatar) {
            if (Str::startsWith($user->avatar, ['http://', 'https://'])) {
                return $user->avatar;
            }
            return asset('storage/' . $user->avatar);
        }

        $name = $user->full_name ?? $user->name ?? 'User';
        $cleanName = preg_replace('/[^a-zA-Z\s]/', '', $name);
        $cleanName = trim($cleanName);

        if (empty($cleanName)) {
            $cleanName = 'User';
        }

        if (str_word_count($cleanName) > 1) {
            $words = explode(' ', $cleanName);
            $cleanName = $words[0][0] . (isset($words[1]) ? $words[1][0] : '');
        } else {
            $cleanName = substr($cleanName, 0, 2);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($cleanName) . '&background=3B82F6&color=fff&bold=true&size=128';
    }

    /**
     * Tampilkan dashboard perusahaan
     */
    public function index()
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return redirect()->route('buat-perusahaan.create')
                ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
        }

        $company = Company::findOrFail($activeCompanyId);

        // ========================================
        // 🔥 AMBIL PENGUMUMAN PERUSAHAAN (KIRI)
        // ========================================
        $pengumumans = Pengumuman::where('company_id', $activeCompanyId)
            ->whereNull('workspace_id') // Hanya pengumuman company
            ->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhere('created_by', $user->id);
            })
            ->withCount('comments')
            ->with(['creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5) // Batasi 5 pengumuman terbaru
            ->get();

        // Set avatar untuk creator
        $pengumumans->each(function ($pengumuman) {
            $pengumuman->creator->avatar_url = $this->getAvatarUrl($pengumuman->creator);
        });

        // ========================================
        // 🔥 AMBIL JADWAL COMPANY HARI INI (KANAN)
        // ========================================
        $today = Carbon::today('Asia/Jakarta');
        $todayEnd = Carbon::today('Asia/Jakarta')->endOfDay();

        $todaySchedules = CalendarEvent::whereNull('workspace_id') // Jadwal company
            ->where('company_id', $activeCompanyId)
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where(function ($q) use ($today, $todayEnd) {
                $q->whereBetween('start_datetime', [$today, $todayEnd])
                    ->orWhereBetween('end_datetime', [$today, $todayEnd])
                    ->orWhere(function ($q2) use ($today, $todayEnd) {
                        $q2->where('start_datetime', '<=', $today)
                            ->where('end_datetime', '>=', $todayEnd);
                    });
            })
            ->with(['creator', 'participants.user'])
            ->withCount('comments')
            ->orderBy('start_datetime', 'asc')
            ->get();

        // Set avatar untuk participants
        $todaySchedules->each(function ($schedule) {
            $schedule->creator->avatar_url = $this->getAvatarUrl($schedule->creator);
            $schedule->participants->each(function ($participant) {
                $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
            });
        });

        return view('dashboard', [
            'company' => $company,
            'pengumumans' => $pengumumans,
            'todaySchedules' => $todaySchedules,
        ]);
    }
}
