<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\UserWorkspace;
use App\Services\DSS\DSSService;

class StatistikController extends Controller
{
    // Role IDs (sesuai database kamu)
    const ROLE_SUPERADMIN = '11111111-1111-1111-1111-111111111111';
    const ROLE_ADMINISTRATOR = '55555555-5555-5555-5555-555555555555';
    const ROLE_MANAGER = 'a688ef38-3030-45cb-9a4d-0407605bc322';
    const ROLE_MEMBER = 'ed81bd39-9041-43b8-a504-bf743b5c2919';

    public function index()
    {
        Log::info('=== STATISTIK CONTROLLER START ===');

        $user = Auth::user();
        Log::info('User ID: ' . $user->id);
        Log::info('User Name: ' . $user->name);

        // ✅ Ambil dari SESSION
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            Log::warning('No active company in session, redirecting to dashboard');
            return redirect()->route('dashboard')->with('error', 'Tidak ada company aktif');
        }

        // ✅ Get user role in company
        $companyRole = $this->getUserRoleInCompany($user, $activeCompanyId);

        Log::info('Company Role:', [
            'company_id' => $activeCompanyId,
            'role' => $companyRole
        ]);

        if (!$companyRole) {
            Log::warning('User has no role in active company');
            return redirect()->route('dashboard')->with('error', 'Tidak ada akses ke company ini');
        }

        // ✅ Ambil workspaces berdasarkan role di COMPANY
        if (in_array($companyRole, ['superadmin', 'admin'])) {
            // SuperAdmin/Admin: Lihat SEMUA workspace di company aktif
            $workspaces = Workspace::where('company_id', $activeCompanyId)
                ->with('activeMembers')
                ->get();

            Log::info('Company Admin - All Workspaces:', [
                'count' => $workspaces->count(),
                'workspace_ids' => $workspaces->pluck('id')->toArray()
            ]);
        } else {
            // Manager/Member: Lihat workspace yang dia ikuti SAJA
            $workspaces = Workspace::where('company_id', $activeCompanyId)
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhereHas('activeMembers', function ($subQ) use ($user) {
                            $subQ->where('users.id', $user->id);
                        });
                })
                ->with('activeMembers')
                ->get();

            Log::info('User Workspaces:', [
                'company_id' => $activeCompanyId,
                'count' => $workspaces->count(),
                'workspace_ids' => $workspaces->pluck('id')->toArray()
            ]);
        }

        if ($workspaces->isEmpty()) {
            Log::warning('No workspaces found, redirecting to dashboard');

            return redirect()->route('dashboard')->with([
                'alert_type' => 'warning',
                'alert_title' => 'Belum Ada Workspace',
                'alert_message' => 'Anda belum memiliki workspace di company ini.',
                'alert_button' => 'Kelola Workspace',
                'alert_url' => route('kelola-workspace')
            ]);
        }

        // ✅ Set workspace default = workspace pertama
        $defaultWorkspace = $workspaces->first();

        Log::info('Default Workspace:', [
            'id' => $defaultWorkspace->id,
            'name' => $defaultWorkspace->name
        ]);

        // ✅ Ambil members berdasarkan role
        $members = $this->getAccessibleMembers($defaultWorkspace, $user, $activeCompanyId);

        Log::info('Accessible Members:', [
            'count' => $members->count(),
            'user_ids' => $members->pluck('id')->toArray()
        ]);

        // ✅ Set periode default
        $defaultPeriod = $this->getCurrentWeekPeriod();

        // ✅ Get tasks
        $tasks = $this->getTasksByPeriodAndStatus(
            $defaultWorkspace->id,
            $defaultPeriod['start'],
            $defaultPeriod['end'],
            'todo'
        );

        // ✅ Calculate rekap kinerja
        $rekapKinerja = $this->calculateRekapKinerja(
            $defaultWorkspace->id,
            $defaultPeriod['start'],
            $defaultPeriod['end']
        );

        // ✅ Generate dropdown periode
        $periodOptions = $this->generatePeriodOptions();

        Log::info('=== RENDERING VIEW: statistik ===');

        return view('statistik', [
            'user' => $user,
            'workspaces' => $workspaces,
            'defaultWorkspace' => $defaultWorkspace,
            'members' => $members,
            'tasks' => $tasks,
            'rekapKinerja' => $rekapKinerja,
            'periodOptions' => $periodOptions,
            'defaultPeriod' => $defaultPeriod,
            'companyRole' => $companyRole, // ✅ Pass ke view
        ]);
    }

    public function getWorkspaceData(Request $request, $workspaceId)
    {
        try {
            $user = Auth::user();
            $filter = $request->get('filter', 'todo');

            Log::info('API: getWorkspaceData', [
                'workspace_id' => $workspaceId,
                'filter' => $filter,
                'user_id' => $user->id
            ]);

            // 1. Validasi akses workspace
            $workspace = Workspace::findOrFail($workspaceId);

            if (!$this->userCanAccessWorkspace($workspace, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada akses ke workspace ini'
                ], 403);
            }

            // 2. Get company ID dari WORKSPACE, bukan session
            $companyId = $workspace->company_id;

            // 3. Get user role in company
            $companyRole = $this->getUserRoleInCompany($user, $companyId);


            // 3. Get members
            $members = $this->getAccessibleMembers($workspace, $user, $companyId);

            // ✅ JADI INI:
            // 4. Get periode (dari request atau default)
            $periodStart = $request->get('start');
            $periodEnd = $request->get('end');

            // ✅ SIMPAN JUGA UNTUK DISPLAY
            $periodDisplay = null;

            if (!$periodStart || !$periodEnd) {
                $period = $this->getCurrentWeekPeriod();
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
                $periodDisplay = $period['display'];
            }

            // 5. Get tasks
            $status = $this->mapFilterToStatus($filter);
            $tasks = $this->getTasksByPeriodAndStatus(
                $workspaceId,
                $periodStart,
                $periodEnd,
                $status
            );

            // 6. Calculate rekap kinerja
            $rekapKinerja = $this->calculateRekapKinerja($workspaceId, $periodStart, $periodEnd);

            return response()->json([
                'success' => true,
                'data' => [
                    'workspace' => [
                        'id' => $workspace->id,
                        'name' => $workspace->name,
                        'description' => $workspace->description,
                        'type' => $workspace->type
                    ],
                    'members' => $members->map(fn($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'avatar' => $m->avatar
                            ? (filter_var($m->avatar, FILTER_VALIDATE_URL)
                                ? $m->avatar
                                : asset('storage/' . $m->avatar))
                            : 'https://ui-avatars.com/api/?name=' . urlencode($m->name) . '&background=random&color=fff'
                    ]),
                    'tasks' => $tasks,
                    'rekap_kinerja' => $rekapKinerja,
                    // ✅ FIX: Return periode sebagai object
                    'period' => [
                        'start' => $periodStart,
                        'end' => $periodEnd,
                        'display' => $periodDisplay
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getWorkspaceData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * GET /api/statistik/suggestions
     * Get DSS suggestions for current workspace & period
     */
    public function getSuggestions(Request $request, DSSService $dssService)
    {
        try {
            $user = Auth::user();
            $workspaceId = $request->get('workspace_id');
            $periodStart = $request->get('start');
            $periodEnd = $request->get('end');

            Log::info('API: getSuggestions', [
                'workspace_id' => $workspaceId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd
            ]);

            // 1. Validasi akses workspace
            $workspace = Workspace::findOrFail($workspaceId);

            if (!$this->userCanAccessWorkspace($workspace, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada akses ke workspace ini'
                ], 403);
            }

            // 2. Jika tidak ada periode, pakai default
            if (!$periodStart || !$periodEnd) {
                $period = $this->getCurrentWeekPeriod();
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
            }

            // 3. Get DSS data
            $dssData = $dssService->getWorkspaceSuggestions(
                $workspaceId,
                $periodStart,
                $periodEnd
            );

            // 4. Get top suggestion for card
            $topSuggestion = $dssService->getTopSuggestion(
                $workspaceId,
                $periodStart,
                $periodEnd
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'top_suggestion' => $topSuggestion,
                    'all_suggestions' => $dssData['suggestions'],
                    'metrics' => $dssData['metrics'],
                    'trends' => $dssData['trends'],
                    'performance' => [
                        'score' => $dssData['performance_score'],
                        'quality' => $dssData['quality_score'],
                        'risk' => $dssData['risk_score'],
                    ],
                    'cached' => $dssData['cached'],
                    'generated_at' => $dssData['generated_at'] ?? now(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getSuggestions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/statistik/modal-data
     * Get full data for DSS modal
     */
    public function getModalData(Request $request, DSSService $dssService)
    {
        try {
            $user = Auth::user();
            $workspaceId = $request->get('workspace_id');
            $periodStart = $request->get('start');
            $periodEnd = $request->get('end');

            // 1. Validasi
            $workspace = Workspace::findOrFail($workspaceId);

            if (!$this->userCanAccessWorkspace($workspace, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada akses'
                ], 403);
            }

            // 2. Default period
            if (!$periodStart || !$periodEnd) {
                $period = $this->getCurrentWeekPeriod();
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
            }

            // 3. Get modal data
            $modalData = $dssService->getModalData(
                $workspaceId,
                $periodStart,
                $periodEnd
            );

            // 4. Get urgent tasks (deadline < 3 days)
            $urgentTasks = $this->getUrgentTasks($workspaceId, $periodStart, $periodEnd);

            // 5. Get workload distribution
            $workloadData = $this->getWorkloadDistribution($workspaceId, $periodStart, $periodEnd);

            // ✅ TAMBAHKAN INI: Generate workload recommendations
            $workloadRecommendations = app(\App\Services\DSS\RecommendationEngine::class)
                ->generateWorkloadRecommendations($workloadData);

            $modalData['urgent_tasks'] = $urgentTasks;
            $modalData['workload'] = $workloadData;
            $modalData['workload_recommendations'] = $workloadRecommendations; // ✅ Sudah ada

            return response()->json([
                'success' => true,
                'data' => $modalData
            ]);

        } catch (\Exception $e) {
            Log::error('Error getModalData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/statistik/refresh-snapshot
     * Force recalculate snapshot
     */
    public function refreshSnapshot(Request $request, DSSService $dssService)
    {
        try {
            $user = Auth::user();
            $workspaceId = $request->get('workspace_id');
            $periodStart = $request->get('start');
            $periodEnd = $request->get('end');

            Log::info('Refresh Snapshot Request:', [
                'workspace_id' => $workspaceId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd
            ]);

            // 1. Validasi
            $workspace = Workspace::findOrFail($workspaceId);

            if (!$this->userCanAccessWorkspace($workspace, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada akses'
                ], 403);
            }

            // 2. Default period
            if (!$periodStart || !$periodEnd) {
                $period = $this->getCurrentWeekPeriod();
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
            }

            // 3. Force recalculate DSS data
            $dssData = $dssService->getWorkspaceSuggestions(
                $workspaceId,
                $periodStart,
                $periodEnd,
                true // ✅ Force recalculate
            );

            // 4. Get top suggestion (PENTING!)
            $topSuggestion = $dssService->getTopSuggestion(
                $workspaceId,
                $periodStart,
                $periodEnd
            );

            Log::info('Refresh Snapshot Response:', [
                'top_suggestion' => $topSuggestion,
                'suggestions_count' => count($dssData['suggestions'] ?? []),
                'generated_at' => $dssData['generated_at']
            ]);

            // ✅ FIX: Return struktur yang SAMA dengan getSuggestions()
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => [
                    'top_suggestion' => $topSuggestion,           // ✅ TAMBAH INI
                    'all_suggestions' => $dssData['suggestions'], // ✅ RENAME dari 'suggestions'
                    'metrics' => $dssData['metrics'],
                    'trends' => $dssData['trends'],               // ✅ TAMBAH INI
                    'performance' => [                            // ✅ TAMBAH INI
                        'score' => $dssData['performance_score'],
                        'quality' => $dssData['quality_score'],
                        'risk' => $dssData['risk_score'],
                    ],
                    'generated_at' => $dssData['generated_at'],
                    'cached' => false,                            // ✅ Selalu false karena fresh
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error refreshSnapshot:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate performance score (0-100)
     */
    private function calculatePerformanceScore($rekapKinerja)
    {
        $total = $rekapKinerja['total'] ?? 0;

        // ✅ Empty state
        if ($total == 0) {
            return 100; // Perfect score untuk empty workspace
        }

        $selesai = $rekapKinerja['selesai'] ?? 0;
        $terlambat = $rekapKinerja['terlambat'] ?? 0;
        $dikerjakan = $rekapKinerja['dikerjakan'] ?? 0;
        $belum = $rekapKinerja['belum'] ?? 0;

        // ✅ CHECK: Apakah ada task selesai?
        $hasCompleted = ($selesai > 0);

        // ============================================
        // CASE 1: WORKSPACE BARU (Belum ada completed)
        // ============================================
        if (!$hasCompleted) {
            $baseScore = 70; // Netral

            // Bonus: Ada progress
            $progressBonus = ($dikerjakan > 0) ? 10 : 0;

            // Penalty: Overdue
            $overduePenalty = $terlambat * 0.5;

            // Penalty: Idle terlalu tinggi
            $idlePenalty = ($belum > 80) ? 10 : 0;

            // Penalty: Stagnant (idle tinggi + no progress)
            $stagnantPenalty = ($belum > 90 && $dikerjakan == 0) ? 15 : 0;

            $finalScore = $baseScore + $progressBonus - $overduePenalty - $idlePenalty - $stagnantPenalty;

            return max(50, min(100, round($finalScore)));
        }

        // ============================================
        // CASE 2: WORKSPACE AKTIF (Sudah ada completed)
        // ============================================

        // Simplified formula (mirip DSS tapi lebih ringan)

        // Komponen positif
        $completionWeight = $selesai * 0.8;      // Max 80 points
        $progressWeight = $dikerjakan * 0.3;      // Max 30 points

        // Komponen negatif
        $overduePenalty = $terlambat * 0.6;       // Penalty overdue
        $idlePenalty = ($belum > 40) ? ($belum * 0.2) : 0; // Penalty idle tinggi

        $finalScore = $completionWeight + $progressWeight - $overduePenalty - $idlePenalty;

        return max(0, min(100, round($finalScore)));
    }

    /**
     * Get rating berdasarkan score
     */
    private function getPerformanceRating($score)
    {
        if ($score >= 80) {
            return ['label' => 'Sangat Bagus', 'stars' => 5, 'color' => '#10b981'];
        } elseif ($score >= 65) {
            return ['label' => 'Bagus', 'stars' => 4, 'color' => '#3b82f6'];
        } elseif ($score >= 50) {
            return ['label' => 'Cukup', 'stars' => 3, 'color' => '#fbbf24'];
        } elseif ($score >= 35) {
            return ['label' => 'Kurang', 'stars' => 2, 'color' => '#f97316'];
        } else {
            return ['label' => 'Buruk', 'stars' => 1, 'color' => '#ef4444'];
        }
    }

    /**
     * Get urgent tasks (deadline < 3 days)
     */
    /**
     * Get urgent tasks (overdue + deadline < 3 days)
     * ✅ PERBAIKAN: Include tugas yang SUDAH TERLAMBAT
     */
    private function getUrgentTasks($workspaceId, $periodStart, $periodEnd)
    {
        $tasks = Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers'])
            // ✅ FIX: Hanya tugas yang BELUM SELESAI
            ->where('status', '!=', 'done')
            ->where(function ($q) use ($periodStart, $periodEnd) {
                // Tugas yang due-nya di periode ini atau sebelumnya
                $q->where('due_datetime', '<=', $periodEnd);
            })
            ->get();

        return $tasks->filter(function ($task) {
            if (!$task->due_datetime)
                return false;

            $daysUntilDue = now()->diffInDays($task->due_datetime, false);

            // Include tugas yang sudah lewat (negatif) atau deadline < 3 hari
            return $daysUntilDue <= 3;
        })
            ->map(function ($task) {
                $daysUntilDue = now()->diffInDays($task->due_datetime, false);

                // Auto-promote priority jika sudah telat
                $priority = $task->priority ?? 'medium';
                if ($daysUntilDue < 0) {
                    $priority = 'overdue';
                } elseif ($daysUntilDue <= 1) {
                    $priority = 'urgent';
                } elseif ($daysUntilDue <= 3) {
                    $priority = 'high';
                }

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'priority' => $priority,
                    'status' => $task->status,
                    'due_datetime' => $task->due_datetime->toISOString(),
                    'days_until_due' => $daysUntilDue,
                    'progress' => $task->getProgressPercentage(),
                    'assigned_users' => $task->assignedUsers->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'avatar' => $u->avatar
                            ? (filter_var($u->avatar, FILTER_VALIDATE_URL)
                                ? $u->avatar
                                : asset('storage/' . $u->avatar))
                            : 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=random&color=fff'
                    ])->values() // ✅ TAMBAH values() untuk reset array key
                ];
            })
            ->sortBy('days_until_due') // Sort by urgency
            ->values();
    }

    /**
     * Get workload distribution
     */
    private function getWorkloadDistribution($workspaceId, $periodStart, $periodEnd)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $members = $workspace->activeMembers;

        // ✅ TAMBAHKAN: Hitung total task di workspace
        $totalWorkspaceTasks = Task::where('workspace_id', $workspaceId)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                    ->orWhereBetween('due_datetime', [$periodStart, $periodEnd]);
            })
            ->count();

        $distribution = [];
        foreach ($members as $member) {
            $tasks = Task::where('workspace_id', $workspaceId)
                ->whereHas('assignedUsers', function ($q) use ($member) {
                    $q->where('users.id', $member->id);
                })
                ->where(function ($q) use ($periodStart, $periodEnd) {
                    $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                        ->orWhereBetween('due_datetime', [$periodStart, $periodEnd]);
                })
                ->get();

            $total = $tasks->count();
            $done = $tasks->where('status', 'done')->count();
            $overdue = $tasks->filter(fn($t) => $t->isOverdue() && $t->status !== 'done')->count();

            // ✅ PERBAIKAN: Hitung berdasarkan share dari total workspace
            $loadPercentage = $totalWorkspaceTasks > 0
                ? round(($total / $totalWorkspaceTasks) * 100, 1)
                : 0;

            $distribution[] = [
                'user_id' => $member->id,
                'name' => $member->name,
                'avatar' => $member->avatar
                    ? (filter_var($member->avatar, FILTER_VALIDATE_URL)
                        ? $member->avatar
                        : asset('storage/' . $member->avatar))
                    : 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=random&color=fff',
                'total_tasks' => $total,
                'completed_tasks' => $done,
                'overdue_tasks' => $overdue,
                'completion_rate' => $total > 0 ? round(($done / $total) * 100) : 0,
                'load_percentage' => $loadPercentage, // ✅ Sekarang relatif terhadap total workspace
            ];
        }

        return collect($distribution)->sortByDesc('total_tasks')->values();
    }


    /**
     * GET /api/statistik/member/{memberId}
     * Dipanggil ketika user klik member tertentu
     */
    public function getMemberData(Request $request, $memberId)
    {
        try {
            $user = Auth::user();
            $workspaceId = $request->get('workspace_id');
            $filter = $request->get('filter', 'todo');

            // ✅ TAMBAHKAN: Ambil periode dari request
            $periodStart = $request->get('start');
            $periodEnd = $request->get('end');

            // ✅ Jika tidak ada, pakai default
            if (!$periodStart || !$periodEnd) {
                $period = $this->getCurrentWeekPeriod();
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
            }

            // 1. Validasi akses
            $workspace = Workspace::findOrFail($workspaceId);

            if (!$this->userCanAccessWorkspace($workspace, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada akses'
                ], 403);
            }

            // 2. Get periode
            $period = $this->getCurrentWeekPeriod();

            // 3. Get tasks khusus member ini
            $status = $this->mapFilterToStatus($filter);

            // ✅ Get tasks dengan periode yang benar
            $tasks = $this->getTasksByMember(
                $workspaceId,
                $memberId,
                $periodStart,  // ✅ Pakai periode
                $periodEnd,    // ✅ Pakai periode
                $status
            );

            // 4. Calculate rekap untuk member ini
            $rekapKinerja = $this->calculateMemberRekap(
                $workspaceId,
                $memberId,
                $periodStart,  // ✅ Pakai periode
                $periodEnd     // ✅ Pakai periode
            );

            // ✅ TAMBAHKAN INI sebelum return response
            $attendance = $this->getMeetingAttendance(
                $memberId,
                $workspaceId,
                $periodStart,
                $periodEnd
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'tasks' => $tasks,
                    'rekap_kinerja' => $rekapKinerja,
                    'attendance' => $attendance  // ✅ TAMBAH INI
                ]
            ]);


        } catch (\Exception $e) {
            Log::error('Error getMemberData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/statistik/tasks
     * Dipanggil ketika user ganti filter status
     */
    public function getTasksByFilter(Request $request)
    {
        try {
            $user = Auth::user();
            $workspaceId = $request->get('workspace_id');
            $filter = $request->get('filter', 'todo');
            $memberId = $request->get('member_id'); // optional

            // ✅ TAMBAHKAN: Ambil periode dari request (bukan hardcode!)
            $periodStart = $request->get('start');
            $periodEnd = $request->get('end');

            Log::info('API: getTasksByFilter', [
                'workspace_id' => $workspaceId,
                'filter' => $filter,
                'member_id' => $memberId,
                'period_start' => $periodStart, // ✅ Log periode
                'period_end' => $periodEnd
            ]);

            // 1. Validasi
            $workspace = Workspace::findOrFail($workspaceId);

            if (!$this->userCanAccessWorkspace($workspace, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada akses'
                ], 403);
            }

            // ✅ JIKA TIDAK ADA PERIODE, PAKAI DEFAULT
            if (!$periodStart || !$periodEnd) {
                $period = $this->getCurrentWeekPeriod();
                $periodStart = $period['start'];
                $periodEnd = $period['end'];
            }

            $status = $this->mapFilterToStatus($filter);

            // 3. Get tasks
            if ($memberId) {
                $tasks = $this->getTasksByMember($workspaceId, $memberId, $periodStart, $periodEnd, $status);
            } else {
                $tasks = $this->getTasksByPeriodAndStatus($workspaceId, $periodStart, $periodEnd, $status);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tasks' => $tasks
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getTasksByFilter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/statistik/periode
     * Dipanggil ketika user ganti periode
     */
    public function getPeriodeData(Request $request)
    {
        try {
            $user = Auth::user();
            $workspaceId = $request->get('workspace_id');
            $periodStart = $request->get('start');
            $periodEnd = $request->get('end');
            $filter = $request->get('filter', 'todo');
            $memberId = $request->get('member_id'); // optional

            Log::info('API: getPeriodeData', [
                'workspace_id' => $workspaceId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'filter' => $filter,
                'member_id' => $memberId
            ]);

            // 1. Validasi
            $workspace = Workspace::findOrFail($workspaceId);

            if (!$this->userCanAccessWorkspace($workspace, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada akses'
                ], 403);
            }

            $status = $this->mapFilterToStatus($filter);

            // 2. Get tasks
            if ($memberId) {
                $tasks = $this->getTasksByMember($workspaceId, $memberId, $periodStart, $periodEnd, $status);
                $rekapKinerja = $this->calculateMemberRekap($workspaceId, $memberId, $periodStart, $periodEnd);
            } else {
                $tasks = $this->getTasksByPeriodAndStatus($workspaceId, $periodStart, $periodEnd, $status);
                $rekapKinerja = $this->calculateRekapKinerja($workspaceId, $periodStart, $periodEnd);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tasks' => $tasks,
                    'rekap_kinerja' => $rekapKinerja
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getPeriodeData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // HELPER METHODS (TAMBAHAN)
    // ============================================

    /**
     * Check apakah user bisa akses workspace
     */
    /**
     * Check apakah user bisa akses workspace
     */
    private function userCanAccessWorkspace($workspace, $user)
    {
        // ✅ PERBAIKAN: Ambil dari workspace, bukan session
        $companyId = $workspace->company_id;

        // ✅ Check company role
        $companyRole = $this->getUserRoleInCompany($user, $companyId);

        if (!$companyRole)
            return false;

        // ✅ SuperAdmin/Admin company → Akses semua
        if (in_array($companyRole, ['superadmin', 'admin'])) {
            return true;
        }

        // ✅ Check workspace role
        $workspaceRole = $this->getUserRoleInWorkspace($workspace->id, $user->id);

        // ✅ Ada role di workspace (creator, manager, member)
        return $workspaceRole !== null;
    }

    /**
     * Check apakah user adalah company admin
     */


    /**
     * Map filter name ke status database
     */
    private function mapFilterToStatus($filter)
    {
        $map = [
            'Todo List' => 'todo',
            'Dikerjakan' => 'inprogress',
            'Selesai' => 'done',
            'Terlambat' => 'terlambat'
        ];

        return $map[$filter] ?? 'todo';
    }

    /**
     * Get tasks by member
     */
    private function getTasksByMember($workspaceId, $memberId, $periodStart, $periodEnd, $status)
    {
        $query = Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers', 'boardColumn'])
            ->whereHas('assignedUsers', function ($q) use ($memberId) {
                $q->where('users.id', $memberId);
            });

        // ✅ FILTER PERIODE
        $query->where(function ($q) use ($periodStart, $periodEnd) {
            $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                ->orWhereBetween('due_datetime', [$periodStart, $periodEnd])
                ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                    // Task yang merentang periode
                    $sub->where('start_datetime', '<=', $periodEnd)
                        ->where('due_datetime', '>=', $periodStart);
                });
        });

        // ✅ PERBAIKAN: Samakan dengan getTasksByPeriodAndStatus()
        if ($status === 'terlambat') {
            // Filter "Terlambat": Tampilkan semua task yang telat
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('due_datetime', '<', now())
                        ->where('status', '!=', 'done');
                })
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'done')
                            ->whereNotNull('completed_at')
                            ->whereColumn('completed_at', '>', 'due_datetime');
                    });
            });
        } else {
            // ✅ PERBAIKAN: Status normal TETAP tampilkan task telat juga
            $query->where('status', $status);
            // TIDAK perlu filter tambahan
        }

        return $query->get()->map(function ($task) {
            $isOverdue = $task->isOverdue();
            $isCompletedLate = $task->isCompletedLate();

            // ✅ TAMBAH: Hitung sisa hari ke deadline
            $daysUntilDue = null;
            if ($task->due_datetime) {
                $daysUntilDue = now()->diffInDays($task->due_datetime, false);
            }

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => strip_tags($task->description ?? ''), // ✅ TAMBAH INI
                'status' => $task->status,
                'start_datetime' => $task->start_datetime?->toISOString(),
                'due_datetime' => $task->due_datetime?->toISOString(),
                'completed_at' => $task->completed_at?->toISOString(),
                'is_overdue' => $isOverdue,
                'is_completed_late' => $isCompletedLate,
                'days_until_due' => $daysUntilDue,
                'progress' => $task->getProgressPercentage(),
                'board_column' => $task->boardColumn?->name,
                'assigned_users' => $task->assignedUsers->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'avatar' => $u->avatar
                        ? (filter_var($u->avatar, FILTER_VALIDATE_URL)
                            ? $u->avatar
                            : asset('storage/' . $u->avatar))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=random&color=fff'
                ])->values() // ✅ TAMBAH values() untuk reset array key
            ];
        });
    }

    /**
     * Calculate rekap kinerja workspace
     */
    /**
     * Calculate rekap kinerja workspace
     */
    private function calculateRekapKinerja($workspaceId, $periodStart, $periodEnd)
    {
        $allTasks = Task::where('workspace_id', $workspaceId)
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                    ->orWhereBetween('due_datetime', [$periodStart, $periodEnd])
                    ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                        $sub->where('start_datetime', '<=', $periodEnd)
                            ->where('due_datetime', '>=', $periodStart);
                    });
            })
            ->get();

        $total = $allTasks->count();

        // ✅ PERBAIKAN: TERLAMBAT = Belum selesai ATAU Selesai telat
        $terlambat = $allTasks->filter(function ($t) {
            // Belum selesai DAN lewat deadline
            if ($t->status !== 'done' && $t->isOverdue()) {
                return true;
            }
            // ATAU Selesai tapi telat
            if ($t->status === 'done' && $t->isCompletedLate()) {
                return true;
            }
            return false;
        });
        $terlambatCount = $terlambat->count();

        // ✅ Hitung sisanya (yang TIDAK termasuk terlambat)
        $notLate = $allTasks->filter(function ($t) {
            // Exclude yang belum selesai DAN lewat deadline
            if ($t->status !== 'done' && $t->isOverdue()) {
                return false;
            }
            // Exclude yang selesai tapi telat
            if ($t->status === 'done' && $t->isCompletedLate()) {
                return false;
            }
            return true;
        });

        $belum = $notLate->where('status', 'todo')->count();
        $dikerjakan = $notLate->where('status', 'inprogress')->count();

        // ✅ SELESAI = Hanya yang selesai TEPAT WAKTU
        $selesai = $allTasks->filter(function ($t) {
            return $t->status === 'done' && !$t->isCompletedLate();
        })->count();

        $selesaiTepatWaktu = $selesai; // Sudah sama

        // ✅ BUAT ARRAY REKAP
        $rekap = [
            'belum' => round($total > 0 ? ($belum / $total) * 100 : 0),
            'dikerjakan' => round($total > 0 ? ($dikerjakan / $total) * 100 : 0),
            'selesai' => round($total > 0 ? ($selesai / $total) * 100 : 0),
            'terlambat' => round($total > 0 ? ($terlambatCount / $total) * 100 : 0), // ✅ GANTI INI
            'total' => $total,
            'completed_on_time' => $selesaiTepatWaktu . ' dari ' . $total
        ];

        // ✅ TAMBAHKAN PERFORMANCE
        $score = $this->calculatePerformanceScore($rekap);
        $rating = $this->getPerformanceRating($score);

        $rekap['performance'] = [
            'score' => $score,
            'label' => $rating['label'],
            'stars' => $rating['stars'],
            'color' => $rating['color']
        ];

        return $rekap;
    }

    /**
     * Calculate rekap untuk 1 member
     */
    private function calculateMemberRekap($workspaceId, $memberId, $periodStart, $periodEnd)
    {
        $allTasks = Task::where('workspace_id', $workspaceId)
            ->whereHas('assignedUsers', function ($q) use ($memberId) {
                $q->where('users.id', $memberId);
            })
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->where(function ($sub) use ($periodStart, $periodEnd) {
                    $sub->whereBetween('start_datetime', [$periodStart, $periodEnd])
                        ->orWhereBetween('due_datetime', [$periodStart, $periodEnd])
                        ->orWhere(function ($span) use ($periodStart, $periodEnd) {
                            $span->where('start_datetime', '<=', $periodEnd)
                                ->where('due_datetime', '>=', $periodStart);
                        });
                })
                    ->orWhere(function ($overdue) use ($periodEnd) {
                        $overdue->where('status', '!=', 'done')
                            ->where('due_datetime', '<', now())
                            ->where('due_datetime', '<=', $periodEnd);
                    });
            })
            ->get();

        $total = $allTasks->count();

        $terlambat = $allTasks->filter(function ($t) {
            if ($t->status !== 'done' && $t->isOverdue()) {
                return true;
            }
            if ($t->status === 'done' && $t->isCompletedLate()) {
                return true;
            }
            return false;
        });
        $terlambatCount = $terlambat->count();

        $notLate = $allTasks->filter(function ($t) {
            if ($t->status !== 'done' && $t->isOverdue()) {
                return false;
            }
            if ($t->status === 'done' && $t->isCompletedLate()) {
                return false;
            }
            return true;
        });

        $belum = $notLate->where('status', 'todo')->count();
        $dikerjakan = $notLate->where('status', 'inprogress')->count();
        $selesai = $allTasks->filter(function ($t) {
            return $t->status === 'done' && !$t->isCompletedLate();
        })->count();

        $selesaiTepatWaktu = $selesai;

        // ✅ GET ATTENDANCE DATA
        $attendance = $this->getMeetingAttendance(
            $memberId,
            $workspaceId,
            $periodStart,
            $periodEnd
        );

        $rekap = [
            'belum' => round($total > 0 ? ($belum / $total) * 100 : 0),
            'dikerjakan' => round($total > 0 ? ($dikerjakan / $total) * 100 : 0),
            'selesai' => round($total > 0 ? ($selesai / $total) * 100 : 0),
            'terlambat' => round($total > 0 ? ($terlambatCount / $total) * 100 : 0),
            'total' => $total,
            'completed_on_time' => $selesaiTepatWaktu . ' dari ' . $total,
            'attendance' => $attendance // ✅ TAMBAH INI
        ];

        // ✅ HITUNG PERFORMANCE DENGAN ATTENDANCE
        $score = $this->calculateMemberPerformanceScore($rekap); // ✅ Method baru
        $rating = $this->getPerformanceRating($score);

        $rekap['performance'] = [
            'score' => $score,
            'label' => $rating['label'],
            'stars' => $rating['stars'],
            'color' => $rating['color']
        ];

        return $rekap;
    }

    /**
     * ✅ FIXED: Calculate performance untuk MEMBER (include attendance)
     */
    private function calculateMemberPerformanceScore($rekapKinerja)
    {
        $total = $rekapKinerja['total'] ?? 0;

        // ✅ GET ATTENDANCE DATA
        $attendance = $rekapKinerja['attendance'] ?? null;
        $attendancePercentage = $attendance['percentage'] ?? 0;
        $hasAttendanceData = $attendance && $attendance['total'] > 0;

        // ✅ FIX: Empty state DENGAN attendance check
        if ($total == 0) {
            // Jika tidak ada tugas TAPI tidak hadir rapat = BURUK
            if ($hasAttendanceData && $attendancePercentage < 50) {
                return 30; // Buruk (1 bintang)
            }
            // Jika tidak ada tugas DAN hadir rapat = Netral
            if ($hasAttendanceData && $attendancePercentage >= 50) {
                return 60; // Cukup (3 bintang)
            }
            // Jika tidak ada tugas DAN tidak ada rapat = Netral
            return 100; // Bagus (4 bintang) - karena belum ada data untuk dinilai
        }

        $selesai = $rekapKinerja['selesai'] ?? 0;
        $terlambat = $rekapKinerja['terlambat'] ?? 0;
        $dikerjakan = $rekapKinerja['dikerjakan'] ?? 0;
        $belum = $rekapKinerja['belum'] ?? 0;

        $hasCompleted = ($selesai > 0);

        // WORKSPACE BARU (Belum ada completed)
        if (!$hasCompleted) {
            $baseScore = 70;
            $progressBonus = ($dikerjakan > 0) ? 10 : 0;
            $overduePenalty = $terlambat * 0.5;
            $idlePenalty = ($belum > 80) ? 10 : 0;
            $stagnantPenalty = ($belum > 90 && $dikerjakan == 0) ? 15 : 0;

            // ✅ ATTENDANCE IMPACT (lebih besar penalty)
            $attendanceImpact = 0;
            if ($hasAttendanceData) {
                if ($attendancePercentage >= 80) {
                    $attendanceImpact = 10; // Bonus naik dari 5 ke 10
                } elseif ($attendancePercentage >= 50) {
                    $attendanceImpact = 0; // Netral
                } else {
                    $attendanceImpact = -15; // Penalty keras jika bolos
                }
            }

            $finalScore = $baseScore + $progressBonus + $attendanceImpact - $overduePenalty - $idlePenalty - $stagnantPenalty;

            return max(30, min(100, round($finalScore))); // Min 30 bukan 50
        }

        // WORKSPACE AKTIF (Sudah ada completed)
        $completionWeight = $selesai * 0.7;
        $progressWeight = $dikerjakan * 0.3;
        $overduePenalty = $terlambat * 0.6;
        $idlePenalty = ($belum > 40) ? ($belum * 0.2) : 0;

        // ✅ ATTENDANCE IMPACT (diperkuat)
        $attendanceBonus = 0;
        if ($hasAttendanceData) {
            // Formula: attendance % dibagi 10 = max 10 points
            $attendanceBonus = ($attendancePercentage / 10);

            // Extra penalty jika attendance sangat rendah
            if ($attendancePercentage < 50) {
                $attendanceBonus -= 10; // Naik dari -5 ke -10
            }
        }

        $finalScore = $completionWeight + $progressWeight + $attendanceBonus - $overduePenalty - $idlePenalty;

        return max(30, min(100, round($finalScore))); // Min 30 bukan 0
    }

    /**
     * Get members yang bisa diakses user berdasarkan role
     */
    /**
     * Get members yang bisa diakses user berdasarkan role
     */
    private function getAccessibleMembers($workspace, $user, $companyId)
    {
        $companyRole = $this->getUserRoleInCompany($user, $companyId);

        Log::info('getAccessibleMembers DEBUG:', [
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'company_role' => $companyRole
        ]);

        // ✅ SuperAdmin/Admin company → Lihat semua member
        if (in_array($companyRole, ['superadmin', 'admin'])) {
            return $workspace->activeMembers->map(function ($member) {
                return (object) [
                    'id' => $member->id,
                    'name' => $member->name,
                    'avatar' => $member->avatar
                        ? (filter_var($member->avatar, FILTER_VALIDATE_URL)
                            ? $member->avatar
                            : asset('storage/' . $member->avatar))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=random&color=fff'
                ];
            });
        }

        // ✅ Check role di workspace
        $workspaceRole = $this->getUserRoleInWorkspace($workspace->id, $user->id);

        Log::info('Workspace role:', ['role' => $workspaceRole]);

        // ✅ Creator atau Manager workspace → Lihat semua member
        if (in_array($workspaceRole, ['creator', 'manager'])) {
            return $workspace->activeMembers->map(function ($member) {
                return (object) [
                    'id' => $member->id,
                    'name' => $member->name,
                    'avatar' => $member->avatar
                        ? asset('storage/' . $member->avatar)
                        : 'https://i.pravatar.cc/40?u=' . $member->id
                ];
            });
        }

        Log::info('Return: Self only (member)');
        // ✅ Manager/Member company DAN bukan manager/creator workspace → Cuma lihat diri sendiri
        return collect([
            (object) [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar
                    ? asset('storage/' . $user->avatar)
                    : 'https://i.pravatar.cc/40?u=' . $user->id
            ]
        ]);
    }

    /**
     * Helper: Get periode minggu ini
     */
    private function getCurrentWeekPeriod()
    {
        $now = Carbon::now();
        return [
            'label' => 'Minggu ini',
            'value' => 'current_week',
            'start' => $now->copy()->startOfWeek()->toDateString(),
            'end' => $now->copy()->endOfWeek()->toDateString(),
            'display' => $now->copy()->startOfWeek()->format('d M') . ' - ' . $now->copy()->endOfWeek()->format('d M Y')
        ];
    }


    /**
     * Helper: Generate dropdown options (6 bulan terakhir)
     */
    private function generatePeriodOptions()
    {
        $options = [];

        // Minggu ini
        $now = Carbon::now();
        $options[] = [
            'label' => 'Minggu ini',
            'value' => 'current_week',
            'start' => $now->copy()->startOfWeek()->toDateString(),
            'end' => $now->copy()->endOfWeek()->toDateString(),
            'display' => $now->copy()->startOfWeek()->format('d M') . ' - ' . $now->copy()->endOfWeek()->format('d M Y')
        ];

        // 6 bulan terakhir
        for ($i = 0; $i < 6; $i++) {
            $month = Carbon::now()->subMonths($i);
            $options[] = [
                'label' => $month->translatedFormat('F Y'),
                'value' => $month->format('Y-m'),
                'start' => $month->copy()->startOfMonth()->toDateString(),
                'end' => $month->copy()->endOfMonth()->toDateString(),
                'display' => $month->translatedFormat('F Y')
            ];
        }

        return $options;
    }

    /**
     * Get user role in company
     */
    private function getUserRoleInCompany($user, $companyId)
    {
        $userCompany = $user->userCompanies()
            ->where('company_id', $companyId)
            ->first();

        if (!$userCompany)
            return null;

        $roleId = $userCompany->roles_id;

        if ($roleId === self::ROLE_SUPERADMIN)
            return 'superadmin';
        if ($roleId === self::ROLE_ADMINISTRATOR)
            return 'admin';
        if ($roleId === self::ROLE_MANAGER)
            return 'manager';
        if ($roleId === self::ROLE_MEMBER)
            return 'member';

        return null;
    }

    /**
     * Get user role in workspace
     */
    private function getUserRoleInWorkspace($workspaceId, $userId)
    {
        $workspace = Workspace::find($workspaceId);

        if (!$workspace)
            return null;

        // Check if creator
        if ($workspace->created_by === $userId) {
            return 'creator';
        }

        // Check role in workspace
        $userWorkspace = UserWorkspace::where('workspace_id', $workspaceId)
            ->where('user_id', $userId)
            ->where('status_active', true)
            ->first();

        if (!$userWorkspace)
            return null;

        if ($userWorkspace->roles_id === self::ROLE_MANAGER)
            return 'manager';

        return 'member';
    }

    /**
     * Check if user can see all workspaces in company
     */
    private function canSeeAllWorkspaces($user, $companyId)
    {
        $role = $this->getUserRoleInCompany($user, $companyId);
        return in_array($role, ['superadmin', 'admin']);
    }

    /**
     * Check if user can see all members in workspace
     */
    private function canSeeAllMembersInWorkspace($workspaceId, $userId)
    {
        $workspaceRole = $this->getUserRoleInWorkspace($workspaceId, $userId);
        return in_array($workspaceRole, ['creator', 'manager']);
    }

    /**
     * Get meeting attendance untuk member dalam periode tertentu
     */
    private function getMeetingAttendance($memberId, $workspaceId, $periodStart, $periodEnd)
    {
        Log::info('=== getMeetingAttendance DEBUG ===', [
            'member_id' => $memberId,
            'workspace_id' => $workspaceId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);

        // ✅ PERBAIKAN: Pastikan end date sampai akhir hari (23:59:59)
        $periodEndFull = $periodEnd . ' 23:59:59';
        $periodStartFull = $periodStart . ' 00:00:00';

        // Ambil semua meeting di workspace yang masuk periode
        $meetings = \App\Models\CalendarEvent::where('workspace_id', $workspaceId)
            ->where('is_online_meeting', true)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($periodStartFull, $periodEndFull) {
                $q->whereBetween('start_datetime', [$periodStartFull, $periodEndFull])
                    ->orWhereBetween('end_datetime', [$periodStartFull, $periodEndFull])
                    ->orWhere(function ($sub) use ($periodStartFull, $periodEndFull) {
                        $sub->where('start_datetime', '<=', $periodEndFull)
                            ->where('end_datetime', '>=', $periodStartFull);
                    });
            })
            ->get();

        // ✅ TAMBAHKAN: Log query SQL untuk debug
        Log::info('SQL Query:', [
            'query' => \App\Models\CalendarEvent::where('workspace_id', $workspaceId)
                ->where('is_online_meeting', true)
                ->whereNull('deleted_at')
                ->where(function ($q) use ($periodStartFull, $periodEndFull) {
                    $q->whereBetween('start_datetime', [$periodStartFull, $periodEndFull])
                        ->orWhereBetween('end_datetime', [$periodStartFull, $periodEndFull]);
                })
                ->toSql()
        ]);

        Log::info('Meetings Found:', [
            'count' => $meetings->count(),
            'meetings' => $meetings->map(fn($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'workspace_id' => $m->workspace_id,
                'start' => $m->start_datetime,
                'end' => $m->end_datetime,
                'is_online' => $m->is_online_meeting,
                'deleted_at' => $m->deleted_at
            ])
        ]);

        $meetingIds = $meetings->pluck('id');

        if ($meetingIds->isEmpty()) {
            Log::warning('No meetings found in period');
            return [
                'attended' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }

        // Hitung berapa meeting yang dihadiri
        $participants = \App\Models\CalendarParticipant::where('user_id', $memberId)
            ->whereIn('event_id', $meetingIds)
            ->get(); // ✅ get() dulu untuk debug

        // ✅ DEBUG: Log participants
        Log::info('Participants Found:', [
            'member_id' => $memberId,
            'count' => $participants->count(),
            'participants' => $participants->map(fn($p) => [
                'id' => $p->id,
                'event_id' => $p->event_id,
                'user_id' => $p->user_id,
                'status' => $p->status,
                'attendance' => $p->attendance
            ])
        ]);

        $attended = $participants->where('attendance', true)->count();
        $total = $meetingIds->count();

        // ✅ DEBUG: Log hasil akhir
        Log::info('Attendance Result:', [
            'attended' => $attended,
            'total' => $total,
            'percentage' => $total > 0 ? round(($attended / $total) * 100) : 0
        ]);

        return [
            'attended' => $attended,
            'total' => $total,
            'percentage' => $total > 0 ? round(($attended / $total) * 100) : 0
        ];
    }


    /**
     * Helper: Get tasks berdasarkan periode dan status
     */
    private function getTasksByPeriodAndStatus($workspaceId, $periodStart, $periodEnd, $status)
    {
        Log::info('Getting Tasks:', [
            'workspace_id' => $workspaceId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => $status
        ]);

        $query = Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers', 'boardColumn']);

        // ✅ FILTER PERIODE: Task yang start/due di periode ini ATAU merentang periode
        $query->where(function ($q) use ($periodStart, $periodEnd) {
            $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                ->orWhereBetween('due_datetime', [$periodStart, $periodEnd])
                ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                    // Task yang merentang periode
                    $sub->where('start_datetime', '<=', $periodEnd)
                        ->where('due_datetime', '>=', $periodStart);
                });
        });

        if ($status === 'terlambat') {
            // Filter "Terlambat": Tampilkan semua task yang telat, apapun statusnya
            $query->where(function ($q) {
                // Belum selesai & telat
                $q->where(function ($sub) {
                    $sub->where('due_datetime', '<', now())
                        ->where('status', '!=', 'done');
                })
                    // Atau sudah selesai tapi telat
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'done')
                            ->whereNotNull('completed_at')
                            ->whereColumn('completed_at', '>', 'due_datetime');
                    });
            });
        } else {
            // ✅ PERBAIKAN: Status normal TETAP tampilkan task telat juga
            // Jadi task yang telat akan muncul di 2 tempat:
            // 1. Di filter "Terlambat"
            // 2. Di filter status aslinya (todo/in_progress/done)
            $query->where('status', $status);
            // TIDAK perlu filter tambahan, biarkan task telat tetap muncul
        }

        $tasks = $query->get();

        Log::info('Tasks Retrieved:', [
            'count' => $tasks->count(),
            'sample' => $tasks->first() ? [
                'title' => $tasks->first()->title,
                'status' => $tasks->first()->status,
                'start' => $tasks->first()->start_datetime,
                'due' => $tasks->first()->due_datetime
            ] : null
        ]);

        return $tasks->map(function ($task) {
            $isOverdue = $task->isOverdue();
            $isCompletedLate = $task->isCompletedLate();

            // ✅ TAMBAH: Hitung sisa hari ke deadline
            $daysUntilDue = null;
            if ($task->due_datetime) {
                $daysUntilDue = now()->diffInDays($task->due_datetime, false); // false = bisa negatif
            }

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => strip_tags($task->description ?? ''), // ✅ TAMBAH INI
                'status' => $task->status,
                'start_datetime' => $task->start_datetime?->toISOString(),
                'due_datetime' => $task->due_datetime?->toISOString(),
                'completed_at' => $task->completed_at?->toISOString(),
                'is_overdue' => $isOverdue,
                'is_completed_late' => $isCompletedLate,
                'days_until_due' => $daysUntilDue, // ✅ TAMBAH INI
                'progress' => $task->getProgressPercentage(),
                'board_column' => $task->boardColumn?->name,
                'assigned_users' => $task->assignedUsers->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'avatar' => $u->avatar
                        ? (filter_var($u->avatar, FILTER_VALIDATE_URL)
                            ? $u->avatar
                            : asset('storage/' . $u->avatar))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=random&color=fff'
                ])->values() // ✅ TAMBAH values() untuk reset array key
            ];
        });
    }
}