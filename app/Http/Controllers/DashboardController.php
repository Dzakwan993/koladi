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
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return redirect()->route('buat-perusahaan.create')
                ->with('info', 'Silakan buat perusahaan terlebih dahulu.');
        }

        $company = Company::findOrFail($activeCompanyId);

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

        return view('dashboard', [
            'company' => $company,
            'pengumumans' => $pengumumans,
            'todaySchedules' => $todaySchedules,
        ]);
    }

    /**
     * ✅ Get ALL events (Company + All Workspace) untuk calendar
     */
    public function getAllEvents(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $start = $request->get('start');
        $end = $request->get('end');

        $startDate = Carbon::parse($start)->toDateTimeString();
        $endDate = Carbon::parse($end)->toDateTimeString();

        // ✅ IMPROVED: Query dengan relation yang benar
        $events = CalendarEvent::where('company_id', $activeCompanyId)
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
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_datetime', [$startDate, $endDate])
                    ->orWhereBetween('end_datetime', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_datetime', '<=', $startDate)
                            ->where('end_datetime', '>=', $endDate);
                    });
            })
            ->with(['creator', 'participants.user', 'workspace'])
            ->withCount('comments')
            ->orderBy('start_datetime', 'asc')
            ->get();

        // ✅ DEBUG LOG
        Log::info('Dashboard getAllEvents', [
            'user_id' => $user->id,
            'date_range' => [$startDate, $endDate],
            'events_count' => $events->count(),
            'company_events' => $events->where('workspace_id', null)->count(),
            'workspace_events' => $events->whereNotNull('workspace_id')->count(),
            'workspace_details' => $events->whereNotNull('workspace_id')->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'workspace_id' => $e->workspace_id,
                'workspace_name' => $e->workspace->name ?? 'Unknown',
                'is_private' => $e->is_private,
                'has_participant' => $e->participants->where('user_id', $user->id)->isNotEmpty()
            ])
        ]);

        $formattedEvents = $events->map(function ($event) use ($user) {
            $startDate = Carbon::parse($event->start_datetime)->setTimezone('Asia/Jakarta');
            $endDate = Carbon::parse($event->end_datetime)->setTimezone('Asia/Jakarta');

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $startDate->toIso8601String(),
                'end' => $endDate->toIso8601String(),
                'backgroundColor' => $event->is_private ? '#ef4444' : '#2563eb',
                'borderColor' => $event->is_private ? '#ef4444' : '#2563eb',
                'extendedProps' => [
                    'description' => $event->description ?? '',
                    'is_online' => $event->is_online_meeting ?? false,
                    'meeting_link' => $event->meeting_link ?? '',
                    'location' => $event->location ?? '',
                    'is_private' => $event->is_private ?? false,
                    'participants_count' => $event->participants->count(),
                    'creator_name' => $event->creator->full_name ?? 'Unknown',
                    'creator_avatar' => $this->getAvatarUrl($event->creator),
                    'is_creator' => $event->created_by === $user->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'comments_count' => $event->comments_count ?? 0,
                    'schedule_type' => $event->workspace_id ? 'workspace' : 'company',
                    'workspace_id' => $event->workspace_id,
                    'workspace_name' => $event->workspace->name ?? null,
                ]
            ];
        });

        return response()->json($formattedEvents);
    }

    /**
     * ✅ Get schedules by date (untuk AJAX calendar)
     */
    public function getSchedulesByDate($date)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $selectedDate = Carbon::parse($date, 'Asia/Jakarta')->startOfDay();
        $endOfDay = Carbon::parse($date, 'Asia/Jakarta')->endOfDay();

        // ✅ Query yang sama dengan getAllEvents
        $schedules = CalendarEvent::where('company_id', $activeCompanyId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('workspace_id')
                        ->whereHas('participants', function ($q2) use ($user) {
                            $q2->where('user_id', $user->id);
                        });
                })
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
            ->where(function ($q) use ($selectedDate, $endOfDay) {
                $q->whereBetween('start_datetime', [$selectedDate, $endOfDay])
                    ->orWhereBetween('end_datetime', [$selectedDate, $endOfDay])
                    ->orWhere(function ($q2) use ($selectedDate, $endOfDay) {
                        $q2->where('start_datetime', '<=', $selectedDate)
                            ->where('end_datetime', '>=', $endOfDay);
                    });
            })
            ->with(['creator', 'participants.user', 'workspace'])
            ->withCount('comments')
            ->orderBy('start_datetime', 'asc')
            ->get();

        $schedules->each(function ($schedule) {
            $schedule->creator->avatar_url = $this->getAvatarUrl($schedule->creator);
            $schedule->participants->each(function ($participant) {
                $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
            });

            $schedule->schedule_type = $schedule->workspace_id ? 'workspace' : 'company';
            $schedule->schedule_label = $schedule->workspace_id
                ? 'Workspace: ' . ($schedule->workspace->name ?? 'Unknown')
                : 'Jadwal Umum';
        });

        return response()->json([
            'schedules' => $schedules,
            'date' => $selectedDate->format('Y-m-d')
        ]);
    }
}
