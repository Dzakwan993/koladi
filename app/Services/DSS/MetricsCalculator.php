<?php

namespace App\Services\DSS;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetricsCalculator
{
    /**
     * Calculate all 20 DSS metrics for a workspace in a period
     */
    public function calculate($workspaceId, $periodStart, $periodEnd)
    {
        Log::info('MetricsCalculator: Calculating metrics', [
            'workspace_id' => $workspaceId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);

        // Get all tasks in period
        $tasks = $this->getTasksInPeriod($workspaceId, $periodStart, $periodEnd);

        // ✅ DEBUG: Log overdue tasks
        $overdueTasks = $tasks->filter(function ($t) {
            return $t->status !== 'done' && $t->isOverdue();
        });

        Log::info('🔍 Overdue Tasks Detected', [
            'period' => "$periodStart to $periodEnd",
            'overdue_count' => $overdueTasks->count(),
            'overdue_titles' => $overdueTasks->pluck('title')->toArray(),
            'overdue_due_dates' => $overdueTasks->pluck('due_datetime')->toArray(),
        ]);

        if ($tasks->isEmpty()) {
            $empty = $this->getEmptyMetrics();
            // ✅ TAMBAHKAN INI
            $empty['totalTasks'] = 0;
            return $empty;
        }


        // ✅ TAMBAHKAN FLAGS
        $hasCompletedTasks = $tasks->where('status', 'done')->count() > 0;
        $totalTasks = $tasks->count();

        // Calculate each metric group
        // ✅ NEW: Detect workspace phase
        $workspacePhase = $this->detectWorkspacePhase($tasks, $hasCompletedTasks);

        // Calculate each metric group
        $performance = $this->calculatePerformanceMetrics($tasks, $hasCompletedTasks);
        $progress = $this->calculateProgressMetrics($tasks);
        $time = $this->calculateTimeMetrics($tasks);
        $workload = $this->calculateWorkloadMetrics($tasks);
        $quality = $this->calculateQualityMetrics($tasks, $performance, $progress, $workspacePhase);
        // ✅ MERGE dengan flags
        return array_merge(
            $performance,
            $progress,
            $time,
            $workload,
            $quality,
            [
                'hasCompletedTasks' => $hasCompletedTasks,
                'totalTasks' => $totalTasks,
                'workspacePhase' => $workspacePhase, // ✅ TAMBAH INI
            ]
        );
    }


    /**
     * Get tasks in period
     */
    private function getTasksInPeriod($workspaceId, $periodStart, $periodEnd)
    {
        return Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers'])
            ->where(function ($q) use ($periodStart, $periodEnd) {
                // Task yang start/due di periode ini
                $q->where(function ($sub) use ($periodStart, $periodEnd) {
                    $sub->whereBetween('start_datetime', [$periodStart, $periodEnd])
                        ->orWhereBetween('due_datetime', [$periodStart, $periodEnd])
                        ->orWhere(function ($span) use ($periodStart, $periodEnd) {
                            // Task yang merentang periode
                            $span->where('start_datetime', '<=', $periodEnd)
                                ->where('due_datetime', '>=', $periodStart);
                        });
                })
                    // ✅ INCLUDE: Overdue yang belum selesai (apapun due-nya)
                    ->orWhere(function ($overdue) use ($periodEnd) {
                    $overdue->where('status', '!=', 'done')
                        ->where('due_datetime', '<', now())
                        ->where('due_datetime', '<=', $periodEnd); // ✅ Overdue sebelum akhir periode
                });
            })
            ->get();
    }

    /**
     * GROUP 1: Performance Metrics
     */
    /**
     * GROUP 1: Performance Metrics
     */
    /**
     * GROUP 1: Performance Metrics
     */
    private function calculatePerformanceMetrics($tasks, $hasCompletedTasks)
    {
        $total = $tasks->count();

        if ($total === 0) {
            return [
                'onTimeRate' => 100,
                'overdueRate' => 0,
                'overdueCount' => 0,  // ✅ TAMBAH: Untuk risk calculation
                'avgDelay' => 0,
                'medianDelay' => 0,
                'maxDelay' => 0,
                'lateCompletionRate' => 0,
                'hasCompletedTasks' => false,
            ];
        }

        $completed = $tasks->where('status', 'done');
        $completedCount = $completed->count();

        // On-time dari completed
        $onTime = $completed->filter(fn($task) => !$task->isCompletedLate());
        $onTimeCount = $onTime->count();

        // MENJADI:
        $onTimeRate_completed = $total > 0
            ? round(($onTimeCount / $total) * 100, 1)
            : 0; // Ubah default dari 100 ke 0

        // ✅ PERBAIKAN: OVERDUE HANYA UNTUK TUGAS YANG BELUM SELESAI
        $overdue = $tasks->filter(function ($task) {
            // Hanya tugas yang belum selesai DAN lewat deadline
            return $task->status !== 'done' && $task->isOverdue();
        });

        $overdueCount = $overdue->count();
        $overdueRate = round(($overdueCount / $total) * 100, 1);

        // ✅ DEBUG: Log detail overdue tasks
        Log::info('🔍 Performance Metrics - Overdue Analysis', [
            'total_tasks' => $total,
            'overdue_count' => $overdueCount,
            'overdue_rate' => $overdueRate,
            'overdue_task_ids' => $overdue->pluck('id')->toArray(),
            'overdue_task_titles' => $overdue->pluck('title')->toArray(),
            'overdue_task_status' => $overdue->pluck('status')->toArray(),
        ]);

        // Late completion rate (untuk quality, bukan risk)
        $completedLate = $completed->filter(fn($task) => $task->isCompletedLate());
        $lateCompletionRate = $completedCount > 0
            ? round(($completedLate->count() / $completedCount) * 100, 1)
            : 0;

        // Delays (untuk historical analysis, bukan risk)
        $delays = $completedLate->map(function ($task) {
            if ($task->completed_at && $task->due_datetime) {
                return $task->completed_at->diffInDays($task->due_datetime);
            }
            return 0;
        })->filter()->values();

        $avgDelay = $delays->avg() ?? 0;
        $avgDelayCapped = min($avgDelay, 30);

        return [
            'onTimeRate' => $onTimeRate_completed,
            'overdueRate' => $overdueRate,
            'overdueCount' => $overdueCount,  // ✅ TAMBAH INI
            'avgDelay' => round($delays->avg() ?? 0, 1),
            'avgDelayCapped' => round($avgDelayCapped, 1),
            'medianDelay' => round($delays->median() ?? 0, 1),
            'maxDelay' => $delays->max() ?? 0,
            'lateCompletionRate' => $lateCompletionRate,
            'hasCompletedTasks' => $completedCount > 0,
        ];
    }

    /**
     * GROUP 2: Progress Metrics
     */
    private function calculateProgressMetrics($tasks)
    {
        $total = $tasks->count();
        $done = $tasks->where('status', 'done')->count();
        $inProgress = $tasks->where('status', 'inprogress')->count();
        $todo = $tasks->where('status', 'todo')->count();

        // Average progress
        $avgProgress = $tasks->avg(function ($task) {
            return $task->getProgressPercentage();
        });

        // Task velocity (tasks completed per day)
        $periodDays = now()->diffInDays($tasks->min('start_datetime') ?? now()) ?: 1;
        $velocity = $done / $periodDays;

        return [
            'completionRate' => $total > 0 ? round(($done / $total) * 100, 1) : 0,
            'wipRate' => $total > 0 ? round(($inProgress / $total) * 100, 1) : 0,
            'idleRate' => $total > 0 ? round(($todo / $total) * 100, 1) : 0,
            'avgProgress' => round($avgProgress ?? 0, 1),
            'taskVelocity' => round($velocity, 2),
        ];
    }

    /**
     * ✅ NEW: Detect workspace phase
     * Returns: 'empty', 'new', 'active', 'stagnant', 'recovering'
     */
    private function detectWorkspacePhase($tasks, $hasCompletedTasks)
    {
        $total = $tasks->count();

        if ($total == 0) {
            return 'empty';
        }

        $todoCount = $tasks->where('status', 'todo')->count();
        $wipCount = $tasks->where('status', 'inprogress')->count();
        $doneCount = $tasks->where('status', 'done')->count();

        // Percentages
        $todoRate = ($todoCount / $total) * 100;
        $wipRate = ($wipCount / $total) * 100;
        $doneRate = ($doneCount / $total) * 100;

        // Phase 1: NEW (belum ada completed, mayoritas todo)
        if (!$hasCompletedTasks && $todoRate > 60) {
            return 'new';
        }

        // Phase 2: STAGNANT (belum ada completed, banyak overdue)
        if (!$hasCompletedTasks) {
            $overdueCount = $tasks->filter(fn($t) => $t->status !== 'done' && $t->isOverdue())->count();
            $overdueRate = ($overdueCount / $total) * 100;

            if ($overdueRate > 30) {
                return 'stagnant';
            }
        }

        // Phase 3: RECOVERING (sudah ada completed tapi masih banyak overdue)
        if ($hasCompletedTasks) {
            $overdueCount = $tasks->filter(fn($t) => $t->status !== 'done' && $t->isOverdue())->count();
            $overdueRate = ($overdueCount / $total) * 100;

            if ($overdueRate > 40 && $doneRate < 40) {
                return 'recovering';
            }
        }

        // Phase 4: ACTIVE (normal operation)
        return 'active';
    }

    /**
     * GROUP 3: Time Management Metrics
     */
    private function calculateTimeMetrics($tasks)
    {
        $notDone = $tasks->where('status', '!=', 'done');
        $total = $notDone->count();

        // ✅ FIX: URGENT = Overdue (negatif) ATAU deadline ≤ 3 hari
        $urgent = $notDone->filter(function ($task) {
            $days = $task->days_until_due ?? null;

            // ✅ PERBAIKAN: Termasuk yang overdue (negatif) atau deadline ≤ 3 hari
            return $days !== null && $days <= 3;  // ✅ HAPUS && $days >= 0
        });
        $urgentCount = $urgent->count();

        // ✅ FIX: CRITICAL = Overdue (negatif) ATAU deadline ≤ 1 hari
        $critical = $notDone->filter(function ($task) {
            $days = $task->days_until_due ?? null;

            // ✅ PERBAIKAN: Termasuk yang overdue atau deadline ≤ 1 hari
            return $days !== null && $days <= 1;  // ✅ HAPUS && $days >= 0
        });
        $criticalCount = $critical->count();

        // ✅ DEBUG
        Log::info('🔍 Time Metrics - Days Until Due Check', [
            'total_not_done' => $total,
            'sample_tasks' => $notDone->take(3)->map(fn($t) => [
                'title' => $t->title,
                'due_datetime' => $t->due_datetime?->toISOString(),
                'days_until_due' => $t->days_until_due,
            ])->toArray()
        ]);

        // Average time to deadline
        $avgTimeToDeadline = $notDone->map(function ($task) {
            return $task->days_until_due;
        })->filter(fn($d) => $d !== null)->avg() ?? 0;

        // Deadline adherence
        $completed = $tasks->where('status', 'done');
        $completedEarly = $completed->filter(function ($task) {
            if ($task->completed_at && $task->due_datetime) {
                $daysBeforeDeadline = $task->due_datetime->diffInDays($task->completed_at, false);
                return $daysBeforeDeadline >= 2;
            }
            return false;
        })->count();

        // ✅ DEBUG
        Log::info('🔍 Time Metrics Analysis', [
            'total_not_done' => $total,
            'urgent_count' => $urgentCount,
            'urgent_ratio' => $total > 0 ? round(($urgentCount / $total) * 100, 1) : 0,
            'critical_count' => $criticalCount,
            'critical_ratio' => $total > 0 ? round(($criticalCount / $total) * 100, 1) : 0,
            'urgent_task_titles' => $urgent->pluck('title')->toArray(),
            'urgent_days_until' => $urgent->pluck('days_until_due')->toArray(),
            'critical_task_titles' => $critical->pluck('title')->toArray(),
        ]);

        return [
            'urgentTaskRatio' => $total > 0 ? round(($urgentCount / $total) * 100, 1) : 0,
            'criticalTaskRatio' => $total > 0 ? round(($criticalCount / $total) * 100, 1) : 0,
            'avgTimeToDeadline' => round($avgTimeToDeadline, 1),
            'deadlineAdherence' => $completed->count() > 0
                ? round(($completedEarly / $completed->count()) * 100, 1)
                : 0,
        ];
    }

    /**
     * GROUP 4: Workload Distribution Metrics
     */
    private function calculateWorkloadMetrics($tasks)
    {
        // Count tasks per member
        $memberTaskCounts = [];

        foreach ($tasks as $task) {
            foreach ($task->assignedUsers as $user) {
                if (!isset($memberTaskCounts[$user->id])) {
                    $memberTaskCounts[$user->id] = 0;
                }
                $memberTaskCounts[$user->id]++;
            }
        }

        if (empty($memberTaskCounts)) {
            return [
                'tasksPerMember' => 0,
                'gini' => 0,
                'maxLoadRatio' => 0,
                'memberCount' => 0,
            ];
        }

        $counts = array_values($memberTaskCounts);
        $memberCount = count($counts);
        $avgTasks = array_sum($counts) / $memberCount;

        // Gini coefficient
        $gini = $this->calculateGini($counts);

        // ✅ ADJUSTMENT: Gini lebih sensitif untuk tim kecil
        // ✅ GANTI JADI (return raw gini):


        // Max load ratio
        $max = max($counts);
        $min = min($counts);
        $maxLoadRatio = $min > 0 ? $max / $min : $max;

        return [
            'tasksPerMember' => round($avgTasks, 1),
            'gini' => round($gini, 2),  // ✅ Raw value aja
            'maxLoadRatio' => round($maxLoadRatio, 1),
            'memberCount' => $memberCount,
        ];

    }

    /**
     * ✅ NEW: Calculate risk score berdasarkan phase
     */
    private function calculateRiskScore($performance, $progress, $phase, $time)
    {
        // ✅ DEBUG: Log input data
        Log::info('🔍 Risk Score Calculation - Input', [
            'phase' => $phase,
            'overdueRate' => $performance['overdueRate'] ?? 0,
            'overdueCount' => $performance['overdueCount'] ?? 0,
            'urgentTaskRatio' => $time['urgentTaskRatio'] ?? 0,
            'criticalTaskRatio' => $time['criticalTaskRatio'] ?? 0,
            'idleRate' => $progress['idleRate'] ?? 0,
            'wipRate' => $progress['wipRate'] ?? 0,
            'completionRate' => $progress['completionRate'] ?? 0,
        ]);

        $overdueRate = $performance['overdueRate'] ?? 0;
        $urgentTaskRatio = $time['urgentTaskRatio'] ?? 0;
        $criticalTaskRatio = $time['criticalTaskRatio'] ?? 0;
        $idleRate = $progress['idleRate'] ?? 0;
        $wipRate = $progress['wipRate'] ?? 0;
        $completionRate = $progress['completionRate'] ?? 0;

        // WORKSPACE BARU/NEW
        if ($phase === 'new') {
            $baseRisk = 15;
            $idlePenalty = ($idleRate > 80) ? ($idleRate * 0.25) : 0;
            $overduePenalty = $overdueRate * 0.6;
            $urgentPenalty = ($urgentTaskRatio > 30) ? ($urgentTaskRatio * 0.3) : 0;

            $risk = $baseRisk + $idlePenalty + $overduePenalty + $urgentPenalty;

            // ✅ DEBUG
            Log::info('🔍 Risk Score - NEW Phase', [
                'baseRisk' => $baseRisk,
                'idlePenalty' => round($idlePenalty, 2),
                'overduePenalty' => round($overduePenalty, 2),
                'urgentPenalty' => round($urgentPenalty, 2),
                'finalRisk' => round($risk),
            ]);

            return max(0, min(100, round($risk)));
        }

        // WORKSPACE STAGNANT
        if ($phase === 'stagnant') {
            $baseRisk = 70;
            $overdueBoost = $overdueRate * 0.6;
            $idleBoost = ($idleRate > 70) ? 20 : 0;
            $risk = $baseRisk + $overdueBoost + $idleBoost;

            // ✅ DEBUG
            Log::info('🔍 Risk Score - STAGNANT Phase', [
                'baseRisk' => $baseRisk,
                'overdueBoost' => round($overdueBoost, 2),
                'idleBoost' => $idleBoost,
                'finalRisk' => round($risk),
            ]);

            return max(0, min(100, round($risk)));
        }

        // WORKSPACE RECOVERING
        if ($phase === 'recovering') {
            $baseRisk = 50;
            $overdueWeight = $overdueRate * 0.7;
            $urgentWeight = ($urgentTaskRatio > 40) ? 15 : 0;
            $risk = $baseRisk + $overdueWeight + $urgentWeight;

            // ✅ DEBUG
            Log::info('🔍 Risk Score - RECOVERING Phase', [
                'baseRisk' => $baseRisk,
                'overdueWeight' => round($overdueWeight, 2),
                'urgentWeight' => $urgentWeight,
                'finalRisk' => round($risk),
            ]);

            return max(0, min(100, round($risk)));
        }

        // ✅ WORKSPACE ACTIVE
        $overdueComponent = $overdueRate * 0.8;
        $urgentComponent = $urgentTaskRatio * 0.5;
        $criticalComponent = $criticalTaskRatio * 0.7;

        $idleComponent = 0;
        if ($idleRate > 50 && $completionRate < 30) {
            $idleComponent = ($idleRate * 0.2);
        }

        $wipStuckComponent = 0;
        if ($wipRate > 40 && $completionRate < 30) {
            $wipStuckComponent = 10;
        }

        $risk = $overdueComponent + $urgentComponent + $criticalComponent
            + $idleComponent + $wipStuckComponent;

        // ✅ DEBUG: Breakdown komponen
        Log::info('🔍 Risk Score - ACTIVE Phase', [
            'overdueComponent' => round($overdueComponent, 2),
            'urgentComponent' => round($urgentComponent, 2),
            'criticalComponent' => round($criticalComponent, 2),
            'idleComponent' => round($idleComponent, 2),
            'wipStuckComponent' => $wipStuckComponent,
            'totalRisk' => round($risk),
        ]);

        return max(0, min(100, round($risk)));
    }

    /**
     * GROUP 5: Quality & Risk Scores
     */
    private function calculateQualityMetrics($tasks, $performance, $progress, $workspacePhase)
    {
        // ✅ HAPUS duplikat calculation, langsung pakai method
        // ✅ Hitung time metrics dulu
        $time = $this->calculateTimeMetrics($tasks);

        $riskScore = $this->calculateRiskScore($performance, $progress, $workspacePhase, $time);


        // Quality Score (tetap sama)
        $qualityScore = (
            $performance['onTimeRate'] * 0.5 +
            $progress['completionRate'] * 0.3 +
            (100 - $performance['overdueRate']) * 0.2
        );
        $qualityScore = max(0, min(100, round($qualityScore)));

        // Performance Score
        $performanceScore = $this->calculatePerformanceScore($performance, $progress, $workspacePhase);

        return [
            'qualityScore' => $qualityScore,
            'riskScore' => $riskScore,
            'performanceScore' => $performanceScore,
        ];
    }

    /**
     * Calculate Gini coefficient
     */
    private function calculateGini(array $values)
    {
        if (empty($values))
            return 0;

        sort($values);
        $n = count($values);
        $sum = array_sum($values);

        if ($sum == 0)
            return 0;

        $gini = 0;
        for ($i = 0; $i < $n; $i++) {
            $gini += ($i + 1) * $values[$i];
        }

        $gini = (2 * $gini) / ($n * $sum) - ($n + 1) / $n;

        return max(0, min(1, $gini));
    }

    /**
     * Calculate performance score (0-100)
     */
    // Formula alternatif: fokus pada tepat waktu
    /**
     * Calculate performance score (0-100)
     * ✅ PERBAIKAN: Formula yang lebih realistis
     */
    /**
     * Calculate performance score (0-100)
     * ✅ PERBAIKAN: Formula yang lebih realistis
     */
    private function calculatePerformanceScore($performance, $progress, $phase)
    {
        // WORKSPACE NEW (DIPERBAIKI)
        if ($phase === 'new') {
            $baseScore = 60;

            // Bonus WIP (sedang produktif)
            $progressBonus = $progress['wipRate'] > 0 ? min(15, $progress['wipRate'] * 0.3) : 0;

            // Bonus completion (ada yang selesai meski baru)
            $completionBonus = ($progress['completionRate'] > 0) ? 10 : 0;

            // Penalty overdue
            $overduePenalty = $performance['overdueRate'] * 0.6;

            // Penalty idle tinggi (>80%)
            $idlePenalty = ($progress['idleRate'] > 80) ? 15 : 0;

            // Penalty stagnant (idle >90% + no WIP)
            $stagnantPenalty = ($progress['idleRate'] > 90 && $progress['wipRate'] == 0) ? 20 : 0;

            $score = $baseScore + $progressBonus + $completionBonus
                - $overduePenalty - $idlePenalty - $stagnantPenalty;

            return max(40, min(85, round($score)));
        }

        // WORKSPACE STAGNANT
        if ($phase === 'stagnant') {
            $baseScore = 40;
            $progressBonus = ($progress['wipRate'] > 0) ? 5 : 0;
            $overduePenalty = $performance['overdueRate'] * 0.8;
            $score = $baseScore + $progressBonus - $overduePenalty;
            return max(20, min(100, round($score)));
        }

        // WORKSPACE RECOVERING
        if ($phase === 'recovering') {
            $baseScore = 55;
            $completionWeight = $progress['completionRate'] * 0.3;
            $onTimeWeight = $performance['onTimeRate'] * 0.3;
            $overduePenalty = $performance['overdueRate'] * 0.4;
            $score = $baseScore + $completionWeight + $onTimeWeight - $overduePenalty;
            return max(30, min(100, round($score)));
        }

        // ✅ WORKSPACE ACTIVE (FORMULA DIPERBAIKI)
        // Kurangi bobot onTimeRate karena sekarang dari completed only
        $onTimeWeight = $performance['onTimeRate'] * 0.3;  // ✅ turun dari 0.4

        // Naikkan bobot completion karena volume penting
        $completionWeight = $progress['completionRate'] * 0.35;  // ✅ naik dari 0.25

        // Bonus WIP (produktivitas)
        $wipBonus = min(15, $progress['wipRate'] * 0.15);

        // Penalty overdue (risiko)
        $overduePenalty = $performance['overdueRate'] * 0.3;

        // Penalty delay (pakai capped)
        $avgDelayPenalty = min(10, $performance['avgDelayCapped']);  // ✅ FIX

        // Penalty late completion
        $lateCompletionPenalty = ($performance['lateCompletionRate'] ?? 0) * 0.1;

        $score = $onTimeWeight + $completionWeight + $wipBonus
            - $overduePenalty - $avgDelayPenalty - $lateCompletionPenalty;

        return max(0, min(100, round($score)));
    }

    /**
     * Empty metrics (when no tasks)
     */
    private function getEmptyMetrics()
    {
        return [
            'onTimeRate' => 0,
            'overdueRate' => 0,
            'avgDelay' => 0,
            'medianDelay' => 0,
            'maxDelay' => 0,
            'lateCompletionRate' => 0,
            'completionRate' => 0,
            'wipRate' => 0,
            'idleRate' => 0,
            'avgProgress' => 0,
            'taskVelocity' => 0,
            'urgentTaskRatio' => 0,
            'criticalTaskRatio' => 0,
            'avgTimeToDeadline' => 0,
            'deadlineAdherence' => 0,
            'tasksPerMember' => 0,
            'gini' => 0,
            'maxLoadRatio' => 0,
            'qualityScore' => 0,
            'riskScore' => 0,
            'performanceScore' => 0,
            'totalTasks' => 0, // ✅ TAMBAHKAN INI   
            'workspacePhase' => 'empty', // ✅ TAMBAH INI
        ];
    }
}