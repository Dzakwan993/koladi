<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\Workspace;
use App\Models\Pengumuman;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\UserWorkspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $user->refresh(); // ⬅️ TAMBAHKAN INI untuk fresh data dari DB
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return redirect()->route('buat-perusahaan.create')
                ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
        }

        $company = Company::findOrFail($activeCompanyId);

        // ✅ PERBAIKAN: Cek has_seen_onboarding DAN onboarding_step
        $showOnboarding = false;
        $onboardingType = null;

        if (!$user->has_seen_onboarding && is_null($user->onboarding_step)) {
            $showOnboarding = true;
            $onboardingType = $user->onboarding_type ?? 'member'; // default to member
        }

        // ========================================
        // 🔥 AMBIL DATA WORKSPACE UNTUK DASHBOARD
        // ========================================
        $workspaces = $this->getWorkspacesForDashboard($user, $activeCompanyId);

        // ========================================
        // 🔥 DEBUG: CEK WORKSPACE USER
        // ========================================
        $userWorkspaces = UserWorkspace::where('user_id', $user->id)
            ->where('status_active', true)
            ->with('workspace')
            ->get();

        Log::info('=== DEBUG DASHBOARD ===');
        Log::info('User Workspaces:', [
            'count' => $userWorkspaces->count(),
            'workspaces' => $userWorkspaces->map(fn($uw) => [
                'id' => $uw->workspace_id,
                'name' => $uw->workspace->name ?? 'Unknown',
                'company_id' => $uw->workspace->company_id ?? null
            ])
        ]);

        // ========================================
        // 🔥 AMBIL PENGUMUMAN PERUSAHAAN (KIRI)
        // ========================================
        $pengumumans = Pengumuman::where('company_id', $activeCompanyId)
            ->whereNull('workspace_id')
            ->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhere('created_by', $user->id);
            })
            ->withCount('comments')
            ->with(['creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pengumumans->each(function ($pengumuman) {
            $pengumuman->creator->avatar_url = $this->getAvatarUrl($pengumuman->creator);
        });

        // ========================================
        // 🔥 AMBIL JADWAL HARI INI (COMPANY + WORKSPACE)
        // ========================================
        $today = Carbon::today('Asia/Jakarta');
        $todayEnd = Carbon::today('Asia/Jakarta')->endOfDay();

        // ✅ IMPROVED QUERY dengan detailed logging
        $todaySchedules = CalendarEvent::where('company_id', $activeCompanyId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($user) {
                // 1️⃣ Company events: user HARUS jadi participant
                $query->where(function ($q) use ($user) {
                    $q->whereNull('workspace_id')
                        ->whereHas('participants', function ($q2) use ($user) {
                            $q2->where('user_id', $user->id);
                        });
                })
                    // 2️⃣ Workspace events: user adalah member workspace DAN (public ATAU participant)
                    ->orWhere(function ($q) use ($user) {
                        $q->whereNotNull('workspace_id')
                            ->whereHas('workspace.userWorkspaces', function ($q2) use ($user) {
                                $q2->where('user_id', $user->id)
                                    ->where('status_active', true);
                            })
                            ->where(function ($q3) use ($user) {
                                $q3->where('is_private', false)
                                    ->orWhereHas('participants', function ($q4) use ($user) {
                                        $q4->where('user_id', $user->id);
                                    });
                            });
                    });
            })
            ->where(function ($q) use ($today, $todayEnd) {
                $q->whereBetween('start_datetime', [$today, $todayEnd])
                    ->orWhereBetween('end_datetime', [$today, $todayEnd])
                    ->orWhere(function ($q2) use ($today, $todayEnd) {
                        $q2->where('start_datetime', '<=', $today)
                            ->where('end_datetime', '>=', $todayEnd);
                    });
            })
            ->with(['creator', 'participants.user', 'workspace'])
            ->withCount('comments')
            ->orderBy('start_datetime', 'asc')
            ->get();

        // ✅ DEBUG LOG
        Log::info('Today Schedules:', [
            'total' => $todaySchedules->count(),
            'company' => $todaySchedules->where('workspace_id', null)->count(),
            'workspace' => $todaySchedules->whereNotNull('workspace_id')->count(),
            'details' => $todaySchedules->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'type' => $s->workspace_id ? 'workspace' : 'company',
                'workspace_id' => $s->workspace_id,
                'workspace_name' => $s->workspace->name ?? null,
                'is_private' => $s->is_private
            ])
        ]);

        $todaySchedules->each(function ($schedule) {
            $schedule->creator->avatar_url = $this->getAvatarUrl($schedule->creator);
            $schedule->participants->each(function ($participant) {
                $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
            });

            $schedule->schedule_type = $schedule->workspace_id ? 'workspace' : 'company';
            $schedule->schedule_label = $schedule->workspace_id
                ? 'Workspace: ' . ($schedule->workspace->name ?? 'Unknown')
                : 'Jadwal Umum';
        });

        // ✅ CEK ROLE USER
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();
        $userRole = $userCompany?->role?->name ?? 'Member';

        return view('dashboard', [
            'company' => $company,
            'workspaces' => $workspaces,           // ✅ TAMBAHKAN INI
            'userRole' => $userRole,               // ✅ TAMBAHKAN INI
            'pengumumans' => $pengumumans,
            'todaySchedules' => $todaySchedules,
            'showOnboarding' => $showOnboarding,
            'onboardingType' => $onboardingType,
        ]);
    }

    /**
     * ✅ AMBIL WORKSPACES UNTUK DASHBOARD
     */
    private function getWorkspacesForDashboard($user, $activeCompanyId)
    {
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->where('status_active', true)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        // Admin/Manager bisa lihat semua workspace
        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            $workspaces = Workspace::with(['creator', 'userWorkspaces.user', 'userWorkspaces.role'])
                ->where('company_id', $activeCompanyId)
                ->active()
                ->get()
                ->map(function ($workspace) {
                    // Filter user workspace untuk hanya tampilkan yang aktif
                    $workspace->userWorkspaces = $workspace->userWorkspaces->filter(function ($uw) use ($workspace) {
                        $userCompany = $uw->user->userCompanies
                            ->where('company_id', $workspace->company_id)
                            ->first();
                        return $userCompany && $userCompany->status_active === true;
                    });
                    return $workspace;
                });
        } else {
            // Member hanya lihat workspace yang dia ikuti DAN masih aktif
            $workspaces = Workspace::with(['creator', 'userWorkspaces.user', 'userWorkspaces.role'])
                ->where('company_id', $activeCompanyId)
                ->active()
                ->whereHas('userWorkspaces', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('status_active', true);
                })
                ->get()
                ->map(function ($workspace) {
                    // Filter user workspace untuk hanya tampilkan yang aktif
                    $workspace->userWorkspaces = $workspace->userWorkspaces->filter(function ($uw) use ($workspace) {
                        $userCompany = $uw->user->userCompanies
                            ->where('company_id', $workspace->company_id)
                            ->first();
                        return $userCompany && $userCompany->status_active === true;
                    });
                    return $workspace;
                });
        }

        return $workspaces;
    }



    public function markOnboardingSeen()
    {
        $user = Auth::user();
        $user->has_seen_onboarding = true; // ✅ Set flag
        $user->onboarding_step = null;     // ✅ Reset step
        $user->save();

        return response()->json(['success' => true]);
    }

    public function updateOnboardingStep(Request $request)
    {
        $user = Auth::user();
        $user->onboarding_step = $request->step;
        $user->save();

        return response()->json(['success' => true]);
    }

    /**
     * ✅ Get ALL events (Company + All Workspace) untuk calendar
     */
    public function getAllEvents(Request $request)
    {
        // ... (kode existing tetap sama)
    }

    /**
     * ✅ Get schedules by date (untuk AJAX calendar)
     */
    public function getSchedulesByDate($date)
    {
        // ... (kode existing tetap sama)
    }

    public function completeOnboarding()
    {
        $user = Auth::user();
        $user->has_seen_onboarding = true;
        $user->onboarding_step = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Onboarding selesai! Selamat bekerja! 🚀'
        ]);
    }
}
