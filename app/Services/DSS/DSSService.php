<?php

namespace App\Services\DSS;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DSSService
{
    protected $metricsCalculator;
    protected $trendAnalyzer;
    protected $recommendationEngine;
    protected $snapshotManager;

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
     * Main method: Get workspace suggestions
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
            'workspace_id' => $workspaceId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'force_recalculate' => $forceRecalculate
        ]);

        // 1. Check if snapshot exists and is fresh
        if (!$forceRecalculate) {
            $snapshot = $this->snapshotManager->get($workspaceId, $periodStart, $periodEnd);

            if ($snapshot) {
                Log::info('Using cached snapshot', ['snapshot_id' => $snapshot->id]);
                
                return [
                    'metrics' => $snapshot->metrics,
                    'suggestions' => $snapshot->suggestions,
                    'performance_score' => $snapshot->performance_score,
                    'quality_score' => $snapshot->quality_score,
                    'risk_score' => $snapshot->risk_score,
                    'trends' => null, // Trends tidak disimpan di snapshot (optional)
                    'cached' => true,
                   'generated_at' => $snapshot->updated_at->toISOString() // ✅ PERBAIKAN!
                ];
            }
        }

        // 2. Calculate metrics (real-time)
        Log::info('Calculating metrics (real-time)');
        $metrics = $this->metricsCalculator->calculate($workspaceId, $periodStart, $periodEnd);

        // 3. Get previous period for trend analysis
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

        // 4. Generate suggestions
        Log::info('Generating recommendations');
        $suggestions = $this->recommendationEngine->generate($metrics, $trends);

        // 5. Save snapshot to database
        Log::info('Saving snapshot to database');
        $this->snapshotManager->save(
            $workspaceId,
            $periodStart,
            $periodEnd,
            $periodType,
            $metrics,
            $suggestions
        );

        Log::info('=== DSSService: getWorkspaceSuggestions COMPLETE ===');

        return [
            'metrics' => $metrics,
            'suggestions' => $suggestions,
            'trends' => $trends,
            'performance_score' => $metrics['performanceScore'] ?? 0,
            'quality_score' => $metrics['qualityScore'] ?? 0,
            'risk_score' => $metrics['riskScore'] ?? 0,
            'cached' => false,
            'generated_at' => now()->toISOString()
        ];
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
        $end = Carbon::parse($periodEnd);
        
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
     * Get formatted data for modal view
     */
    public function getModalData($workspaceId, $periodStart, $periodEnd)
    {
        $data = $this->getWorkspaceSuggestions($workspaceId, $periodStart, $periodEnd);
        
        return [
            'workspace_id' => $workspaceId,
            'period' => [
                'start' => $periodStart,
                'end' => $periodEnd,
                'display' => Carbon::parse($periodStart)->format('d M') . ' - ' . Carbon::parse($periodEnd)->format('d M Y')
            ],
            'performance' => [
                'score' => $data['performance_score'],
                'quality' => $data['quality_score'],
                'risk' => $data['risk_score'],
                'rating' => $this->getPerformanceRating($data['performance_score'])
            ],
            'metrics' => $data['metrics'],
            'suggestions' => $data['suggestions'],
            'trends' => $data['trends'],
            'generated_at' => $data['generated_at']
        ];
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