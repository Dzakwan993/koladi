<?php

namespace App\Services\DSS;

use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class DSSService
{
    protected $metricsCalculator;
    protected $trendAnalyzer;
    protected $recommendationEngine;
    protected $snapshotManager;

    // Redis TTL: 1 jam sebagai safety net untuk metrik berbasis waktu
    const REDIS_TTL_SECONDS = 3600;

    // Prefix key untuk DSS cache
    const DSS_CACHE_PREFIX = 'dss';

    public function __construct(
        MetricsCalculator $metricsCalculator,
        TrendAnalyzer $trendAnalyzer,
        RecommendationEngine $recommendationEngine,
        SnapshotManager $snapshotManager
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->trendAnalyzer = $trendAnalyzer;
        $this->recommendationEngine = $recommendationEngine;
        $this->snapshotManager = $snapshotManager;
    }

    /**
     * Bangun Redis key yang deterministik berdasarkan workspace, endpoint, dan periode.
     */
    public function buildRedisKey(string $type, string $workspaceId, string $periodStart, string $periodEnd): string
    {
        return self::DSS_CACHE_PREFIX . ":{$type}:workspace_{$workspaceId}:start_{$periodStart}:end_{$periodEnd}";
    }

    /**
     * Ambil data dari Redis. Kembalikan null jika miss atau error.
     */
    private function getFromRedis(string $key): ?array
    {
        try {
            $raw = Redis::get($key);
            if ($raw === null || $raw === false) {
                return null;
            }
            $decoded = json_decode($raw, true);
            return (is_array($decoded)) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::warning('[DSSService] Redis GET error', ['key' => $key, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Simpan data ke Redis dengan TTL.
     */
    private function putToRedis(string $key, array $data): void
    {
        try {
            Redis::setex($key, self::REDIS_TTL_SECONDS, json_encode($data));
        } catch (\Throwable $e) {
            Log::warning('[DSSService] Redis SETEX error', ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Invalidasi semua cache DSS untuk workspace tertentu.
     * Dipanggil oleh TaskObserver dan TaskAssignmentObserver.
     */
    public function invalidateCacheForWorkspace(string $workspaceId): void
    {
        try {
            $pattern = self::DSS_CACHE_PREFIX . ":*:workspace_{$workspaceId}:*";
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                foreach ($keys as $key) {
                    Redis::del($key);
                }
                Log::info('[DSSService] Cache invalidated for workspace', [
                    'workspace_id' => $workspaceId,
                    'keys_deleted' => count($keys),
                    'keys'         => $keys,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[DSSService] Cache invalidation error', [
                'workspace_id' => $workspaceId,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * Main method: Get workspace suggestions
     *
     * Implementasi Redis Cache-Aside:
     * 1. Cek Redis → jika hit, kembalikan langsung (zero database query untuk data DSS)
     * 2. Jika miss → jalankan full pipeline DSS, simpan ke Redis, kembalikan respons
     *
     * @param string $workspaceId
     * @param string $periodStart (Y-m-d)
     * @param string $periodEnd (Y-m-d)
     * @param bool $forceRecalculate
     * @return array
     */
    public function getWorkspaceSuggestions($workspaceId, $periodStart, $periodEnd, $forceRecalculate = false)
    {
        Log::info('=== DSSService: getWorkspaceSuggestions START ===', [
            'workspace_id'      => $workspaceId,
            'period_start'      => $periodStart,
            'period_end'        => $periodEnd,
            'force_recalculate' => $forceRecalculate,
        ]);

        $redisKey = $this->buildRedisKey('suggestions', $workspaceId, $periodStart, $periodEnd);

        // ─── Cache-Aside: Check Redis ───
        if (!$forceRecalculate) {
            $cached = $this->getFromRedis($redisKey);
            if ($cached !== null) {
                Log::info('[DSSService] Redis cache HIT (suggestions)', ['key' => $redisKey]);
                $cached['cached'] = true;
                return $cached;
            }
            Log::info('[DSSService] Redis cache MISS (suggestions)', ['key' => $redisKey]);
        }

        // ─── Calculate metrics (real-time) ───
        Log::info('Calculating metrics (real-time)');
        $metrics = $this->metricsCalculator->calculate($workspaceId, $periodStart, $periodEnd);

        // ─── Get previous period for trend analysis ───
        $periodType = $this->determinePeriodType($periodStart, $periodEnd);
        $previousSnapshot = $this->snapshotManager->getPreviousPeriod(
            $workspaceId,
            $periodStart,
            $periodEnd,
            $periodType
        );

        $trends = null;
        if ($previousSnapshot) {
            Log::info('Comparing with previous period');
            $trends = $this->trendAnalyzer->compare($metrics, $previousSnapshot->metrics);
        } else {
            Log::info('No previous period data, skipping trend analysis');
            $trends = $this->trendAnalyzer->compare($metrics, []);
        }

        // ─── Generate suggestions ───
        Log::info('Generating recommendations');
        $suggestions = $this->recommendationEngine->generate($metrics, $trends);

        // ─── Build result ───
        $result = [
            'metrics'           => $metrics,
            'suggestions'       => $suggestions,
            'trends'            => $trends,
            'performance_score' => $metrics['performanceScore'] ?? 0,
            'quality_score'     => $metrics['qualityScore'] ?? 0,
            'risk_score'        => $metrics['riskScore'] ?? 0,
            'cached'            => false,
            'generated_at'      => now()->toISOString(),
        ];

        // ─── Store in Redis (cache population) ───
        $this->putToRedis($redisKey, $result);
        Log::info('[DSSService] Result stored in Redis (suggestions)', ['key' => $redisKey]);

        Log::info('=== DSSService: getWorkspaceSuggestions COMPLETE ===');
        return $result;
    }

    /**
     * Get top 1 suggestion for card
     */
    public function getTopSuggestion($workspaceId, $periodStart, $periodEnd)
    {
        $data = $this->getWorkspaceSuggestions($workspaceId, $periodStart, $periodEnd);

        return $this->recommendationEngine->getTopSuggestion($data['suggestions']);
    }

    /**
     * Determine period type (week or month)
     */
    private function determinePeriodType($periodStart, $periodEnd)
    {
        $start = Carbon::parse($periodStart);
        $end   = Carbon::parse($periodEnd);

        $days = $start->diffInDays($end);

        // If period is 6-8 days, assume it's a week
        if ($days >= 6 && $days <= 8) {
            return 'week';
        }

        // If period is 28-31 days, assume it's a month
        if ($days >= 28 && $days <= 31) {
            return 'month';
        }

        // Default to week
        return 'week';
    }

    /**
     * Get formatted data for modal view.
     *
     * Implementasi Redis Cache-Aside untuk full modal response.
     * Cache mencakup: metrics, suggestions, trends, urgent_tasks, workload_distribution.
     * Sehingga cache hit = full database bypass untuk SEMUA komponen modal.
     *
     * @param string $workspaceId
     * @param string $periodStart
     * @param string $periodEnd
     * @return array
     */
    public function getModalData($workspaceId, $periodStart, $periodEnd)
    {
        $redisKey = $this->buildRedisKey('modal-data', $workspaceId, $periodStart, $periodEnd);

        // ─── Cache-Aside: Check Redis ───
        $cached = $this->getFromRedis($redisKey);
        if ($cached !== null) {
            Log::info('[DSSService] Redis cache HIT (modal-data)', ['key' => $redisKey]);
            $cached['cached'] = true;
            return $cached;
        }
        Log::info('[DSSService] Redis cache MISS (modal-data)', ['key' => $redisKey]);

        // ─── Cache miss: Compute full DSS data ───
        $data = $this->getWorkspaceSuggestions($workspaceId, $periodStart, $periodEnd);

        // ─── Hitung komponen tambahan yang tidak ada di suggestions ───
        $urgentTasks           = $this->computeUrgentTasks($workspaceId, $periodStart, $periodEnd);
        $workloadDistribution  = $this->computeWorkloadDistribution($workspaceId, $periodStart, $periodEnd);
        $workloadRecommendations = $this->recommendationEngine->generateWorkloadRecommendations($workloadDistribution);

        // ─── Build full modal result ───
        $result = [
            'workspace_id' => $workspaceId,
            'period'       => [
                'start'   => $periodStart,
                'end'     => $periodEnd,
                'display' => Carbon::parse($periodStart)->format('d M') . ' - ' . Carbon::parse($periodEnd)->format('d M Y'),
            ],
            'performance' => [
                'score'   => $data['performance_score'],
                'quality' => $data['quality_score'],
                'risk'    => $data['risk_score'],
                'rating'  => $this->getPerformanceRating($data['performance_score']),
            ],
            'metrics'                 => $data['metrics'],
            'suggestions'             => $data['suggestions'],
            'trends'                  => $data['trends'],
            'urgent_tasks'            => $urgentTasks->values()->toArray(),
            'workload'                => $workloadDistribution->values()->toArray(),
            'workload_recommendations' => $workloadRecommendations,
            'generated_at'            => $data['generated_at'],
            'cached'                  => false,
        ];

        // ─── Store in Redis (cache population) ───
        $this->putToRedis($redisKey, $result);
        Log::info('[DSSService] Result stored in Redis (modal-data)', ['key' => $redisKey]);

        return $result;
    }

    /**
     * Hitung urgent tasks (overdue + deadline <= 3 hari) untuk sebuah workspace.
     * Digunakan oleh getModalData agar ikut ter-cache di Redis.
     */
    private function computeUrgentTasks(string $workspaceId, string $periodStart, string $periodEnd)
    {
        $tasks = Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers:id,full_name,avatar'])
            ->where('status', '!=', 'done')
            ->where(function ($q) use ($periodEnd) {
                $q->where('due_datetime', '<=', $periodEnd);
            })
            ->get();

        return $tasks->filter(function ($task) {
            if (!$task->due_datetime) return false;
            $daysUntilDue = now()->diffInDays($task->due_datetime, false);
            return $daysUntilDue <= 3;
        })->map(function ($task) {
            $daysUntilDue = now()->diffInDays($task->due_datetime, false);

            $priority = $task->priority ?? 'medium';
            if ($daysUntilDue < 0) {
                $priority = 'overdue';
            } elseif ($daysUntilDue <= 1) {
                $priority = 'urgent';
            } elseif ($daysUntilDue <= 3) {
                $priority = 'high';
            }

            return [
                'id'             => $task->id,
                'title'          => $task->title,
                'priority'       => $priority,
                'status'         => $task->status,
                'due_datetime'   => $task->due_datetime->toISOString(),
                'days_until_due' => $daysUntilDue,
                'progress'       => $task->getProgressPercentage(),
                'assigned_users' => $task->assignedUsers->map(fn($u) => [
                    'id'     => $u->id,
                    'name'   => $u->full_name,
                    'avatar' => $u->avatar
                        ? (filter_var($u->avatar, FILTER_VALIDATE_URL)
                            ? $u->avatar
                            : asset('storage/' . $u->avatar))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($u->full_name) . '&background=random&color=fff',
                ])->values(),
            ];
        })->sortBy('days_until_due');
    }

    /**
     * Hitung workload distribution via batch retrieval + in-memory aggregation.
     * Digunakan oleh getModalData agar ikut ter-cache di Redis.
     */
    private function computeWorkloadDistribution(string $workspaceId, string $periodStart, string $periodEnd)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $members   = $workspace->activeMembers;

        // Batch retrieval semua task sekaligus dengan eager loading assignedUsers
        $allTasks = Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers:id'])
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                    ->orWhereBetween('due_datetime', [$periodStart, $periodEnd]);
            })
            ->get();

        $totalWorkspaceTasks = $allTasks->count();

        $distribution = [];
        foreach ($members as $member) {
            // In-memory aggregation — tidak ada query tambahan per anggota
            $memberTasks = $allTasks->filter(function ($task) use ($member) {
                return $task->assignedUsers->contains('id', $member->id);
            });

            $total   = $memberTasks->count();
            $done    = $memberTasks->where('status', 'done')->count();
            $overdue = $memberTasks->filter(fn($t) => $t->isOverdue() && $t->status !== 'done')->count();

            $loadPercentage = $totalWorkspaceTasks > 0
                ? round(($total / $totalWorkspaceTasks) * 100, 1)
                : 0;

            $distribution[] = [
                'user_id'         => $member->id,
                'name'            => $member->full_name,
                'avatar'          => $member->avatar
                    ? (filter_var($member->avatar, FILTER_VALIDATE_URL)
                        ? $member->avatar
                        : asset('storage/' . $member->avatar))
                    : 'https://ui-avatars.com/api/?name=' . urlencode($member->full_name) . '&background=random&color=fff',
                'total_tasks'     => $total,
                'completed_tasks' => $done,
                'overdue_tasks'   => $overdue,
                'completion_rate' => $total > 0 ? round(($done / $total) * 100) : 0,
                'load_percentage' => $loadPercentage,
            ];
        }

        return collect($distribution)->sortByDesc('total_tasks');
    }

    /**
     * Get performance rating
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
}