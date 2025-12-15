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

        // ✅ FIX: Ambil dari SESSION seperti Dashboard
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            Log::warning('No active company in session, redirecting to dashboard');
            return redirect()->route('dashboard')->with('error', 'Tidak ada company aktif');
        }

        // ✅ Ambil company berdasarkan session
        $activeCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('company')
            ->first();

        Log::info('Active Company Query Result:', [
            'session_company_id' => $activeCompanyId,
            'found' => $activeCompany ? 'yes' : 'no',
            'company_id' => $activeCompany?->company_id,
            'company_name' => $activeCompany?->company?->name ?? 'N/A',
            'role_id' => $activeCompany?->roles_id ?? 'N/A'
        ]);

        if (!$activeCompany) {
            Log::warning('Active company not found in user companies');
            return redirect()->route('dashboard')->with('error', 'Tidak ada akses ke company ini');
        }

        // 2. Cek role di company
        $isCompanyAdmin = in_array($activeCompany->roles_id, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMINISTRATOR
        ]);

        Log::info('Is Company Admin: ' . ($isCompanyAdmin ? 'YES' : 'NO'));

        // 3. Ambil workspaces berdasarkan role
        if ($isCompanyAdmin) {
            // SuperAdmin/Administrator: Lihat SEMUA workspace di company aktif
            $workspaces = Workspace::where('company_id', $activeCompanyId) // ✅ Pakai session ID
                ->with('activeMembers') // ✅ Pakai activeMembers (ga pakai ->using())
                ->get();

            Log::info('Company Admin - All Workspaces:', [
                'count' => $workspaces->count(),
                'workspace_ids' => $workspaces->pluck('id')->toArray(),
                'workspace_names' => $workspaces->pluck('name')->toArray()
            ]);
        } else {
            $workspaces = Workspace::where('company_id', $activeCompanyId)
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhereHas('activeMembers', function ($subQ) use ($user) {
                            $subQ->where('users.id', $user->id);
                        });
                })
                ->with('activeMembers') // ✅ Pakai activeMembers
                ->get();

            Log::info('User Workspaces:', [
                'company_id_filter' => $activeCompanyId,
                'count' => $workspaces->count(),
                'workspace_ids' => $workspaces->pluck('id')->toArray(),
                'workspace_names' => $workspaces->pluck('name')->toArray(),
                'workspace_companies' => $workspaces->pluck('company_id')->toArray(),
                'created_by_user' => $workspaces->where('created_by', $user->id)->pluck('name')->toArray()
            ]);
        }

        if ($workspaces->isEmpty()) {
            Log::warning('No workspaces found, redirecting to dashboard');

            return redirect()->route('dashboard')->with([
                'alert_type' => 'warning',
                'alert_title' => 'Belum Ada Workspace',
                'alert_message' => 'Anda belum memiliki workspace di company ini. Silakan buat workspace terlebih dahulu untuk mengakses Laporan Kinerja.',
                'alert_button' => 'Kelola Workspace',
                'alert_url' => route('kelola-workspace')
            ]);
        }

        // 4. Set workspace default = workspace pertama
        $defaultWorkspace = $workspaces->first();
        Log::info('Default Workspace:', [
            'id' => $defaultWorkspace->id,
            'name' => $defaultWorkspace->name,
            'company_id' => $defaultWorkspace->company_id, // ✅ Debug
            'created_by' => $defaultWorkspace->created_by,
            'is_user_creator' => $defaultWorkspace->created_by === $user->id ? 'YES' : 'NO'
        ]);

        // 5. Ambil members berdasarkan hak akses
        $members = $this->getAccessibleMembers($defaultWorkspace, $user, $isCompanyAdmin);

        Log::info('Accessible Members:', [
            'count' => $members->count(),
            'user_ids' => $members->pluck('id')->toArray(),
            'user_names' => $members->pluck('name')->toArray()
        ]);

        // 6. Set periode default = "Minggu ini"
        $defaultPeriod = $this->getCurrentWeekPeriod();
        Log::info('Default Period:', $defaultPeriod);

        // 7. Ambil tasks
        $tasks = $this->getTasksByPeriodAndStatus(
            $defaultWorkspace->id,
            $defaultPeriod['start'],
            $defaultPeriod['end'],
            'todo'
        );
        Log::info('Tasks Found: ' . $tasks->count());

        // ✅ TAMBAHKAN INI: Calculate rekap kinerja
        $rekapKinerja = $this->calculateRekapKinerja(
            $defaultWorkspace->id,
            $defaultPeriod['start'],
            $defaultPeriod['end']
        );

        Log::info('Rekap Kinerja Calculated:', [
            'total' => $rekapKinerja['total'],
            'selesai' => $rekapKinerja['selesai'],
            'performance_score' => $rekapKinerja['performance']['score']
        ]);

        // 8. Generate dropdown periode
        $periodOptions = $this->generatePeriodOptions();

        Log::info('=== RENDERING VIEW: statistik ===');

        return view('statistik', [
            'user' => $user,
            'workspaces' => $workspaces,
            'defaultWorkspace' => $defaultWorkspace,
            'members' => $members,
            'tasks' => $tasks,
            'rekapKinerja' => $rekapKinerja, // ✅ TAMBAHKAN INI
            'periodOptions' => $periodOptions,
            'defaultPeriod' => $defaultPeriod,
            'isCompanyAdmin' => $isCompanyAdmin,
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

            // 2. Check role
            $activeCompanyId = session('active_company_id');
            $activeCompany = $user->userCompanies()
                ->where('company_id', $activeCompanyId)
                ->first();

            $isCompanyAdmin = in_array($activeCompany->roles_id, [
                self::ROLE_SUPERADMIN,
                self::ROLE_ADMINISTRATOR
            ]);

            // 3. Get members
            $members = $this->getAccessibleMembers($workspace, $user, $isCompanyAdmin);

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
                        'avatar' => $m->avatar ?? 'https://i.pravatar.cc/40?u=' . $m->id
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

            $modalData['urgent_tasks'] = $urgentTasks;
            $modalData['workload'] = $workloadData;

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
        // ✅ FIX: Kalau tidak ada tugas sama sekali, return score 100 (perfect)
        if ($rekapKinerja['total'] == 0) {
            return 100;
        }
        
        $selesai = $rekapKinerja['selesai'];
        $terlambat = $rekapKinerja['terlambat'];
        $dikerjakan = $rekapKinerja['dikerjakan'];
        $belum = $rekapKinerja['belum'];

        // Scoring dengan bobot
        $score = ($selesai * 2) + ($dikerjakan * 0.5) - ($terlambat * 3) - ($belum * 1);

        // Normalisasi ke 0-100
        $normalizedScore = (($score + 300) / 500) * 100;
        return max(0, min(100, round($normalizedScore)));
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
    private function getUrgentTasks($workspaceId, $periodStart, $periodEnd)
    {
        $tasks = Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers'])
            ->where('status', '!=', 'done')
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('due_datetime', [$periodStart, $periodEnd]);
            })
            ->get();

        return $tasks->filter(function ($task) {
            if (!$task->due_datetime)
                return false;
            $daysUntilDue = now()->diffInDays($task->due_datetime, false);
            return $daysUntilDue >= 0 && $daysUntilDue <= 3;
        })->map(function ($task) {
            $daysUntilDue = now()->diffInDays($task->due_datetime, false);
            return [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority ?? 'medium',
                'status' => $task->status,
                'due_datetime' => $task->due_datetime->toISOString(),
                'days_until_due' => $daysUntilDue,
                'progress' => $task->getProgressPercentage(),
                'assigned_users' => $task->assignedUsers->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'avatar' => $u->avatar ?? 'https://i.pravatar.cc/40?u=' . $u->id
                ])
            ];
        })->sortBy('days_until_due')->values()->take(5);
    }

    /**
     * Get workload distribution
     */
    private function getWorkloadDistribution($workspaceId, $periodStart, $periodEnd)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $members = $workspace->activeMembers;

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

            $distribution[] = [
                'user_id' => $member->id,
                'name' => $member->name,
                'avatar' => $member->avatar ?? 'https://i.pravatar.cc/40?u=' . $member->id,
                'total_tasks' => $total,
                'completed_tasks' => $done,
                'overdue_tasks' => $overdue,
                'completion_rate' => $total > 0 ? round(($done / $total) * 100) : 0,
                'load_percentage' => min(150, $total * 10), // Assume 10 tasks = 100%
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

            return response()->json([
                'success' => true,
                'data' => [
                    'tasks' => $tasks,
                    'rekap_kinerja' => $rekapKinerja
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
    private function userCanAccessWorkspace($workspace, $user)
    {
        $activeCompanyId = session('active_company_id');

        $activeCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->first();

        if (!$activeCompany)
            return false;

        $isCompanyAdmin = in_array($activeCompany->roles_id, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMINISTRATOR
        ]);

        if ($isCompanyAdmin)
            return true;

        if ($workspace->created_by === $user->id)
            return true;

        // ✅ Query langsung
        return UserWorkspace::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('status_active', true)
            ->exists();
    }

    /**
     * Check apakah user adalah company admin
     */
    private function isCompanyAdmin($user, $companyId)
    {
        $userCompany = $user->userCompanies()
            ->where('company_id', $companyId)
            ->first();

        return $userCompany && in_array($userCompany->roles_id, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMINISTRATOR
        ]);
    }

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
                'description' => $task->description,
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
                    'avatar' => $u->avatar ?? 'https://i.pravatar.cc/40?u=' . $u->id
                ])
            ];
        });
    }

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
        $belum = $allTasks->where('status', 'todo')->count();
        $dikerjakan = $allTasks->where('status', 'inprogress')->count();
        $selesai = $allTasks->where('status', 'done')->count();
        $terlambat = $allTasks->filter(fn($t) => $t->isOverdue() && $t->status !== 'done')->count();

        $selesaiTepatWaktu = $allTasks->filter(function ($t) {
            return $t->status === 'done' && !$t->isCompletedLate();
        })->count();

        // ✅ BUAT ARRAY REKAP
        $rekap = [
            'belum' => round($total > 0 ? ($belum / $total) * 100 : 0),
            'dikerjakan' => round($total > 0 ? ($dikerjakan / $total) * 100 : 0),
            'selesai' => round($total > 0 ? ($selesai / $total) * 100 : 0),
            'terlambat' => round($total > 0 ? ($terlambat / $total) * 100 : 0),
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
                $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                    ->orWhereBetween('due_datetime', [$periodStart, $periodEnd])
                    ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                        $sub->where('start_datetime', '<=', $periodEnd)
                            ->where('due_datetime', '>=', $periodStart);
                    });
            })
            ->get();

        $total = $allTasks->count();
        $belum = $allTasks->where('status', 'todo')->count();
        $dikerjakan = $allTasks->where('status', 'inprogress')->count();
        $selesai = $allTasks->where('status', 'done')->count();
        $terlambat = $allTasks->filter(fn($t) => $t->isOverdue() && $t->status !== 'done')->count();

        $selesaiTepatWaktu = $allTasks->filter(function ($t) {
            return $t->status === 'done' && !$t->isCompletedLate();
        })->count();

        // ✅ BUAT ARRAY REKAP
        $rekap = [
            'belum' => round($total > 0 ? ($belum / $total) * 100 : 0),
            'dikerjakan' => round($total > 0 ? ($dikerjakan / $total) * 100 : 0),
            'selesai' => round($total > 0 ? ($selesai / $total) * 100 : 0),
            'terlambat' => round($total > 0 ? ($terlambat / $total) * 100 : 0),
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
     * Get members yang bisa diakses user berdasarkan role
     */
    private function getAccessibleMembers($workspace, $user, $isCompanyAdmin)
    {
        if ($isCompanyAdmin) {
            return $workspace->activeMembers; // ✅ Langsung akses property
        }

        if ($workspace->created_by === $user->id) {
            return $workspace->activeMembers;
        }

        // ✅ Query langsung ke UserWorkspace model
        $userWorkspace = UserWorkspace::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('status_active', true)
            ->first();

        if (!$userWorkspace) {
            return collect([$user]);
        }

        $isManager = $userWorkspace->roles_id === self::ROLE_MANAGER;

        if ($isManager) {
            return $workspace->activeMembers;
        }

        return collect([$user]);
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
                'description' => $task->description,
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
                    'avatar' => $u->avatar ?? 'https://i.pravatar.cc/40?u=' . $u->id
                ])
            ];
        });
    }
}