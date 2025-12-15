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

        if ($tasks->isEmpty()) {
            $empty = $this->getEmptyMetrics();
            // ✅ TAMBAHKAN INI
            $empty['totalTasks'] = 0;
            return $empty;
        }

        // Calculate each metric group
        $performance = $this->calculatePerformanceMetrics($tasks);
        $progress = $this->calculateProgressMetrics($tasks);
        $time = $this->calculateTimeMetrics($tasks);
        $workload = $this->calculateWorkloadMetrics($tasks);
        $quality = $this->calculateQualityMetrics($tasks, $performance, $progress);

        // Merge all metrics
        $metrics = array_merge($performance, $progress, $time, $workload, $quality);

        // ✅ TAMBAHKAN INI
        $metrics['totalTasks'] = $tasks->count();

        return $metrics;
    }

    /**
     * Get tasks in period
     */
    private function getTasksInPeriod($workspaceId, $periodStart, $periodEnd)
    {
        return Task::where('workspace_id', $workspaceId)
            ->with(['assignedUsers'])
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('start_datetime', [$periodStart, $periodEnd])
                    ->orWhereBetween('due_datetime', [$periodStart, $periodEnd])
                    ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                        $sub->where('start_datetime', '<=', $periodEnd)
                            ->where('due_datetime', '>=', $periodStart);
                    });
            })
            ->get();
    }

    /**
     * GROUP 1: Performance Metrics
     */
    private function calculatePerformanceMetrics($tasks)
    {
        $total = $tasks->count();

        if ($total === 0) {
            return [
                'onTimeRate' => 0,
                'overdueRate' => 0,
                'avgDelay' => 0,
                'medianDelay' => 0,
                'maxDelay' => 0,
                'lateCompletionRate' => 0,
            ];
        }

        $completed = $tasks->where('status', 'done');
        $completedCount = $completed->count();

        // On-time: selesai DAN tidak telat
        $onTime = $completed->filter(fn($task) => !$task->isCompletedLate());
        $onTimeCount = $onTime->count();

        // Late completion: selesai tapi telat
        $completedLate = $completed->filter(fn($task) => $task->isCompletedLate());

        // Overdue: belum selesai DAN sudah lewat deadline
        $overdue = $tasks->filter(function ($task) {
            return $task->status !== 'done' && $task->isOverdue();
        });

        // Calculate delays
        $delays = $completedLate->map(function ($task) {
            if ($task->completed_at && $task->due_datetime) {
                return $task->completed_at->diffInDays($task->due_datetime);
            }
            return 0;
        })->filter()->values();

        return [
            // ✅ Dari TOTAL tasks
            'onTimeRate' => round(($onTimeCount / $total) * 100, 1),
            'overdueRate' => round(($overdue->count() / $total) * 100, 1),

            // Delay stats
            'avgDelay' => round($delays->avg() ?? 0, 1),
            'medianDelay' => round($delays->median() ?? 0, 1),
            'maxDelay' => $delays->max() ?? 0,

            // ✅ Dari completed tasks
            'lateCompletionRate' => $completedCount > 0
                ? round(($completedLate->count() / $completedCount) * 100, 1)
                : 0,
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
     * GROUP 3: Time Management Metrics
     */
    private function calculateTimeMetrics($tasks)
    {
        $notDone = $tasks->where('status', '!=', 'done');
        $total = $notDone->count();

        // Urgent (deadline < 3 days)
        $urgent = $notDone->filter(function ($task) {
            $days = $task->days_until_due ?? null;
            return $days !== null && $days <= 3 && $days >= 0;
        })->count();

        // Critical (deadline < 1 day)
        $critical = $notDone->filter(function ($task) {
            $days = $task->days_until_due ?? null;
            return $days !== null && $days <= 1 && $days >= 0;
        })->count();

        // Average time to deadline
        $avgTimeToDeadline = $notDone->map(function ($task) {
            return $task->days_until_due;
        })->filter(fn($d) => $d !== null)->avg() ?? 0;

        // Deadline adherence (completed before H-2)
        $completed = $tasks->where('status', 'done');
        $completedEarly = $completed->filter(function ($task) {
            if ($task->completed_at && $task->due_datetime) {
                $daysBeforeDeadline = $task->due_datetime->diffInDays($task->completed_at, false);
                return $daysBeforeDeadline >= 2;
            }
            return false;
        })->count();

        return [
            'urgentTaskRatio' => $total > 0 ? round(($urgent / $total) * 100, 1) : 0,
            'criticalTaskRatio' => $total > 0 ? round(($critical / $total) * 100, 1) : 0,
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
            ];
        }

        $counts = array_values($memberTaskCounts);
        $avgTasks = array_sum($counts) / count($counts);

        // Gini coefficient
        $gini = $this->calculateGini($counts);

        // Max load ratio
        $max = max($counts);
        $min = min($counts);
        $maxLoadRatio = $min > 0 ? $max / $min : $max;

        return [
            'tasksPerMember' => round($avgTasks, 1),
            'gini' => round($gini, 2),
            'maxLoadRatio' => round($maxLoadRatio, 1),
        ];
    }

    /**
     * GROUP 5: Quality & Risk Scores
     */
    private function calculateQualityMetrics($tasks, $performance, $progress)
    {
        // Quality Score (0-100, higher is better)
        $qualityScore = (
            $performance['onTimeRate'] * 0.5 +
            $progress['completionRate'] * 0.3 +
            (100 - $performance['overdueRate']) * 0.2
        );

        // Risk Score (0-100, higher is worse)
        $riskScore = (
            $performance['overdueRate'] * 0.4 +
            (100 - $performance['onTimeRate']) * 0.3 +
            $progress['idleRate'] * 0.2 +
            ($performance['avgDelay'] * 3) // 3 points per day delay
        );
        $riskScore = min(100, $riskScore); // Cap at 100

        // Performance Score (0-100, from your existing calculation)
        $performanceScore = $this->calculatePerformanceScore($performance, $progress);

        return [
            'qualityScore' => round($qualityScore, 0),
            'riskScore' => round($riskScore, 0),
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

        return max(0, min(1, $gini)); // Clamp between 0 and 1
    }

    /**
     * Calculate performance score (0-100)
     */
    private function calculatePerformanceScore($performance, $progress)
    {
        $score = (
            $performance['onTimeRate'] * 0.4 +
            $progress['completionRate'] * 0.3 +
            (100 - $performance['overdueRate']) * 0.3
        );

        return round($score, 0);
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
        ];
    }
}