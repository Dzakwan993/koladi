<?php

namespace App\Services\DSS;

use App\Models\WorkspacePerformanceSnapshot;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SnapshotManager
{
    /**
     * Save snapshot to database
     */
    public function save($workspaceId, $periodStart, $periodEnd, $periodType, $metrics, $suggestions)
    {
        Log::info('SnapshotManager: Saving snapshot', [
            'workspace_id' => $workspaceId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_type' => $periodType
        ]);

        try {
            $snapshot = WorkspacePerformanceSnapshot::updateOrCreate(
                [
                    'workspace_id' => $workspaceId,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                ],
                [
                    'period_type' => $periodType,
                    'metrics' => $metrics,
                    'performance_score' => $metrics['performanceScore'] ?? 0,
                    'quality_score' => $metrics['qualityScore'] ?? 0,
                    'risk_score' => $metrics['riskScore'] ?? 0,
                    'suggestions' => $suggestions,
                ]
            );

            Log::info('Snapshot saved successfully', ['snapshot_id' => $snapshot->id]);

            return $snapshot;
        } catch (\Exception $e) {
            Log::error('Failed to save snapshot: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get snapshot from database
     */
    public function get($workspaceId, $periodStart, $periodEnd)
    {
        Log::info('SnapshotManager: Getting snapshot', [
            'workspace_id' => $workspaceId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);

        return WorkspacePerformanceSnapshot::where('workspace_id', $workspaceId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->latest()
            ->first();
    }

    /**
     * Check if snapshot exists
     */
    public function exists($workspaceId, $periodStart, $periodEnd)
    {
        return WorkspacePerformanceSnapshot::where('workspace_id', $workspaceId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->exists();
    }

    /**
     * Get previous period snapshot for comparison
     */
    public function getPreviousPeriod($workspaceId, $periodStart, $periodEnd, $periodType = 'week')
    {
        $start = Carbon::parse($periodStart);
        $end = Carbon::parse($periodEnd);

        if ($periodType === 'week') {
            // Previous week
            $prevStart = $start->copy()->subWeek()->startOfWeek();
            $prevEnd = $end->copy()->subWeek()->endOfWeek();
        } else {
            // Previous month
            $prevStart = $start->copy()->subMonth()->startOfMonth();
            $prevEnd = $end->copy()->subMonth()->endOfMonth();
        }

        Log::info('Getting previous period', [
            'current_start' => $periodStart,
            'current_end' => $periodEnd,
            'prev_start' => $prevStart->toDateString(),
            'prev_end' => $prevEnd->toDateString()
        ]);

        return $this->get(
            $workspaceId,
            $prevStart->toDateString(),
            $prevEnd->toDateString()
        );
    }

    /**
     * Delete old snapshots (cleanup)
     */
    public function cleanup($olderThan = 90)
    {
        $date = Carbon::now()->subDays($olderThan);

        $deleted = WorkspacePerformanceSnapshot::where('created_at', '<', $date)->delete();

        Log::info("Cleaned up old snapshots: $deleted records deleted");

        return $deleted;
    }

    /**
     * Get all snapshots for a workspace (for trend analysis)
     */
    public function getHistory($workspaceId, $limit = 12)
    {
        return WorkspacePerformanceSnapshot::where('workspace_id', $workspaceId)
            ->orderBy('period_start', 'desc')
            ->limit($limit)
            ->get();
    }
}