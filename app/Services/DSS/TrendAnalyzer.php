<?php

namespace App\Services\DSS;

use Illuminate\Support\Facades\Log;

class TrendAnalyzer
{
    /**
     * Compare current metrics with previous period
     */
    public function compare($currentMetrics, $previousMetrics)
    {
        Log::info('TrendAnalyzer: Comparing metrics');

        if (empty($previousMetrics)) {
            return $this->getNoTrendData($currentMetrics);
        }

        $trends = [];

        foreach ($currentMetrics as $key => $current) {
            $previous = $previousMetrics[$key] ?? 0;
            $trends[$key] = $this->calculateTrend($current, $previous, $key);
        }

        return $trends;
    }

    /**
     * Calculate trend for single metric
     */
    private function calculateTrend($current, $previous, $metricName)
    {
        // ✅ TAMBAHKAN INI DI AWAL
        if ($previous < 10 && $metricName !== 'totalTasks') {
            return [
                'current' => $current,
                'previous' => $previous,
                'change_percent' => null,
                'change_absolute' => round($current - $previous, 1),
                'direction' => $current > $previous ? 'up' : ($current < $previous ? 'down' : 'stable'),
                'icon' => $current > $previous ? '↑' : ($current < $previous ? '↓' : '→'),
                'status' => 'neutral',
                'sample_size_warning' => true,  // ✅ Flag untuk UI
            ];
        }
        if ($previous == 0) {
            return [
                'current' => $current,
                'previous' => $previous,
                'change_percent' => null,
                'change_absolute' => $current,
                'direction' => $current > 0 ? 'up' : 'stable',
                'icon' => $current > 0 ? '↑' : '→',
                'status' => 'neutral',
            ];
        }

        $changePercent = (($current - $previous) / $previous) * 100;
        $changeAbsolute = $current - $previous;

        // Determine direction
        $direction = $changePercent > 0 ? 'up' : ($changePercent < 0 ? 'down' : 'stable');
        $icon = $changePercent > 0 ? '↑' : ($changePercent < 0 ? '↓' : '→');

        // Determine status (good/bad based on metric type)
        $status = $this->getStatusForMetric($metricName, $changePercent);

        return [
            'current' => $current,
            'previous' => $previous,
            'change_percent' => round($changePercent, 1),
            'change_absolute' => round($changeAbsolute, 1),
            'direction' => $direction,
            'icon' => $icon,
            'status' => $status, // 'good', 'bad', 'neutral'
        ];
    }

    /**
     * Determine if trend is good or bad for specific metric
     */
    private function getStatusForMetric($metricName, $changePercent)
    {
        // Metrics where INCREASE is GOOD
        $positiveMetrics = [
            'onTimeRate',
            'completionRate',
            'taskVelocity',
            'qualityScore',
            'performanceScore',
            'deadlineAdherence',
            'avgProgress',
        ];

        // Metrics where DECREASE is GOOD
        $negativeMetrics = [
            'overdueRate',
            'avgDelay',
            'medianDelay',
            'maxDelay',
            'lateCompletionRate',
            'idleRate',
            'urgentTaskRatio',
            'criticalTaskRatio',
            'riskScore',
            'gini',
            'maxLoadRatio',
        ];

        if (abs($changePercent) < 5) {
            return 'neutral'; // Small changes are neutral
        }

        if (in_array($metricName, $positiveMetrics)) {
            return $changePercent > 0 ? 'good' : 'bad';
        }

        if (in_array($metricName, $negativeMetrics)) {
            return $changePercent < 0 ? 'good' : 'bad';
        }

        return 'neutral';
    }

    /**
     * No previous data available
     */
    private function getNoTrendData($currentMetrics)
    {
        $trends = [];

        foreach ($currentMetrics as $key => $value) {
            $trends[$key] = [
                'current' => $value,
                'previous' => null,
                'change_percent' => null,
                'change_absolute' => null,
                'direction' => 'stable',
                'icon' => '→',
                'status' => 'neutral',
            ];
        }

        return $trends;
    }

    /**
     * Get summary of significant trends
     */
    public function getSummary($trends)
    {
        $significant = [];

        foreach ($trends as $key => $trend) {
            // Only include trends with change > 10%
            if ($trend['change_percent'] !== null && abs($trend['change_percent']) >= 10) {
                $significant[$key] = $trend;
            }
        }

        // Sort by absolute change (biggest first)
        uasort($significant, function ($a, $b) {
            return abs($b['change_percent']) <=> abs($a['change_percent']);
        });

        return array_slice($significant, 0, 5); // Top 5 most significant
    }
}