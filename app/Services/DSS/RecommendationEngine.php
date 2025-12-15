<?php

namespace App\Services\DSS;

use Illuminate\Support\Facades\Log;

class RecommendationEngine
{
    private $metrics;
    private $trends;

    /**
     * Generate suggestions based on metrics and trends
     */
    public function generate($metrics, $trends = null)
    {
        Log::info('RecommendationEngine: Generating suggestions');
        Log::info('Metrics received:', $metrics); // ✅ Debug

        $this->metrics = $metrics;
        $this->trends = $trends;

        // ✅ FIX: Pakai totalTasks, BUKAN rate!
        if (isset($metrics['totalTasks']) && $metrics['totalTasks'] == 0) {

            Log::info('✅ Empty state detected: totalTasks = 0');

            return [
                'critical' => [],
                'warning' => [],
                'positive' => [],
                'actions' => [],
                'empty_state' => true,
            ];
        }

        Log::info('Has tasks, generating normal suggestions', [
            'total_tasks' => $metrics['totalTasks'] ?? 'MISSING'
        ]);

        $critical = $this->generateCritical();
        $warning = $this->generateWarning();
        $positive = $this->generatePositive();
        $actions = $this->generateActions($critical, $warning);

        return [
            'critical' => $critical,
            'warning' => $warning,
            'positive' => $positive,
            'actions' => $actions,
            'empty_state' => false,
        ];
    }

    /**
     * Generate CRITICAL suggestions (max 5)
     */
    private function generateCritical()
    {
        $critical = [];

        // 1. OnTimeRate < 50%
        if ($this->metrics['onTimeRate'] < 50) {
            $critical[] = [
                'title' => 'Banyak tugas terlambat',
                'description' => "Hanya {$this->metrics['onTimeRate']}% tugas selesai tepat waktu",
                'metric' => 'onTimeRate',
                'value' => $this->metrics['onTimeRate'] . '%',
                'priority' => 1,
            ];
        }

        // 2. OverdueRate > 40%
        if ($this->metrics['overdueRate'] > 40) {
            $critical[] = [
                'title' => 'Terlalu banyak tugas lewat deadline',
                'description' => "{$this->metrics['overdueRate']}% tugas sudah melewati deadline",
                'metric' => 'overdueRate',
                'value' => $this->metrics['overdueRate'] . '%',
                'priority' => 1,
            ];
        }

        // 3. AvgDelay > 7 days
        if ($this->metrics['avgDelay'] > 7) {
            $critical[] = [
                'title' => 'Keterlambatan sangat tinggi',
                'description' => "Rata-rata tugas terlambat {$this->metrics['avgDelay']} hari",
                'metric' => 'avgDelay',
                'value' => $this->metrics['avgDelay'] . ' hari',
                'priority' => 1,
            ];
        }

        // 4. UrgentTaskRatio > 50%
        if ($this->metrics['urgentTaskRatio'] > 50) {
            $critical[] = [
                'title' => 'Banyak deadline mendesak',
                'description' => "{$this->metrics['urgentTaskRatio']}% tugas harus selesai dalam 3 hari",
                'metric' => 'urgentTaskRatio',
                'value' => $this->metrics['urgentTaskRatio'] . '%',
                'priority' => 1,
            ];
        }

        // 5. RiskScore > 80
        if ($this->metrics['riskScore'] > 80) {
            $critical[] = [
                'title' => 'Tingkat risiko sangat tinggi',
                'description' => "Workspace berada dalam kondisi risiko tinggi (skor: {$this->metrics['riskScore']})",
                'metric' => 'riskScore',
                'value' => $this->metrics['riskScore'],
                'priority' => 1,
            ];
        }

        // 6. Trend performance drop > 20%
        if ($this->trends && isset($this->trends['performanceScore'])) {
            $trend = $this->trends['performanceScore'];
            if ($trend['change_percent'] !== null && $trend['change_percent'] < -20) {
                $critical[] = [
                    'title' => 'Performa turun drastis',
                    'description' => "Performa menurun {$trend['change_percent']}% dibanding periode lalu",
                    'metric' => 'performanceScore',
                    'value' => $trend['change_percent'] . '%',
                    'priority' => 1,
                ];
            }
        }

        return array_slice($critical, 0, 5); // Max 5
    }

    /**
     * Generate WARNING suggestions (max 5)
     */
    private function generateWarning()
    {
        $warning = [];

        // 1. OnTimeRate 50-70%
        if ($this->metrics['onTimeRate'] >= 50 && $this->metrics['onTimeRate'] < 70) {
            $warning[] = [
                'title' => 'Performa kurang optimal',
                'description' => "Hanya {$this->metrics['onTimeRate']}% tugas selesai tepat waktu",
                'metric' => 'onTimeRate',
                'value' => $this->metrics['onTimeRate'] . '%',
            ];
        }

        // 2. AvgDelay 3-7 days
        if ($this->metrics['avgDelay'] >= 3 && $this->metrics['avgDelay'] <= 7) {
            $warning[] = [
                'title' => 'Keterlambatan cukup tinggi',
                'description' => "Rata-rata tugas terlambat {$this->metrics['avgDelay']} hari",
                'metric' => 'avgDelay',
                'value' => $this->metrics['avgDelay'] . ' hari',
            ];
        }

        // 3. IdleRate > 40%
        if ($this->metrics['idleRate'] > 40) {
            $warning[] = [
                'title' => 'Banyak tugas belum dimulai',
                'description' => "{$this->metrics['idleRate']}% tugas masih belum dikerjakan",
                'metric' => 'idleRate',
                'value' => $this->metrics['idleRate'] . '%',
            ];
        }

        // 4. WIPRate > 50%
        if ($this->metrics['wipRate'] > 50) {
            $warning[] = [
                'title' => 'Terlalu banyak tugas sedang dikerjakan',
                'description' => "{$this->metrics['wipRate']}% tugas dalam status dikerjakan, fokus selesaikan dulu",
                'metric' => 'wipRate',
                'value' => $this->metrics['wipRate'] . '%',
            ];
        }

        // 5. Gini > 0.4
        if ($this->metrics['gini'] > 0.4) {
            $warning[] = [
                'title' => 'Beban kerja tidak merata',
                'description' => "Ada ketimpangan pembagian tugas antar anggota tim",
                'metric' => 'gini',
                'value' => $this->metrics['gini'],
            ];
        }

        return array_slice($warning, 0, 5); // Max 5
    }

    /**
     * Generate POSITIVE feedback (max 3)
     */
    private function generatePositive()
    {
        $positive = [];

        // 1. Performa BENAR-BENAR bagus (banyak selesai DAN tepat waktu)
        if ($this->metrics['onTimeRate'] > 85 && $this->metrics['completionRate'] > 80) {
            $positive[] = [
                'title' => 'Performa sangat baik',
                'description' => "{$this->metrics['onTimeRate']}% tugas selesai tepat waktu, pertahankan!",
                'metric' => 'onTimeRate',
                'value' => $this->metrics['onTimeRate'] . '%',
            ];
        }

        // 2. Workload merata
        if ($this->metrics['gini'] < 0.3 && $this->metrics['tasksPerMember'] > 0) {
            $positive[] = [
                'title' => 'Pembagian tugas merata',
                'description' => "Beban kerja terdistribusi dengan baik ke semua anggota",
                'metric' => 'gini',
                'value' => $this->metrics['gini'],
            ];
        }

        // 3. Velocity tinggi
        if ($this->metrics['taskVelocity'] > 2) {
            $positive[] = [
                'title' => 'Produktivitas tinggi',
                'description' => "Tim menyelesaikan {$this->metrics['taskVelocity']} tugas per hari",
                'metric' => 'taskVelocity',
                'value' => $this->metrics['taskVelocity'] . ' tugas/hari',
            ];
        }

        // 4. Positive trend
        if ($this->trends && isset($this->trends['performanceScore'])) {
            $trend = $this->trends['performanceScore'];
            if ($trend['change_percent'] !== null && $trend['change_percent'] > 10) {
                $positive[] = [
                    'title' => 'Performa meningkat signifikan',
                    'description' => "Performa naik {$trend['change_percent']}% dibanding periode lalu",
                    'metric' => 'performanceScore',
                    'value' => '+' . $trend['change_percent'] . '%',
                ];
            }
        }

        return array_slice($positive, 0, 3);
    }

    /**
     * Generate ACTION items (max 5)
     */
    private function generateActions($critical, $warning)
    {
        $actions = [];

        // Actions based on critical issues
        if (!empty($critical)) {
            foreach ($critical as $issue) {
                if ($issue['metric'] === 'urgentTaskRatio') {
                    $actions[] = "Cek tugas yang deadline-nya dekat, prioritaskan yang penting";
                }
                if ($issue['metric'] === 'onTimeRate') {
                    $actions[] = "Evaluasi kenapa banyak tugas terlambat, review beban kerja tim";
                }
                if ($issue['metric'] === 'avgDelay') {
                    $actions[] = "Review estimasi waktu tugas, mungkin deadline terlalu ketat";
                }
            }
        }

        // Actions based on warnings
        if (!empty($warning)) {
            foreach ($warning as $issue) {
                if ($issue['metric'] === 'idleRate') {
                    $actions[] = "Dorong tim untuk mulai tugas yang belum dikerjakan";
                }
                if ($issue['metric'] === 'wipRate') {
                    $actions[] = "Fokus selesaikan tugas yang sedang dikerjakan dulu";
                }
                if ($issue['metric'] === 'gini') {
                    $actions[] = "Atur ulang pembagian tugas agar lebih merata";
                }
            }
        }

        // Default actions if everything is fine
        if (empty($actions)) {
            $actions[] = "Pertahankan cara kerja tim yang sekarang";
            $actions[] = "Pantau terus performa agar tetap stabil";
        }

        // Remove duplicates and limit
        $actions = array_unique($actions);
        return array_slice(array_values($actions), 0, 5); // Max 5
    }

    /**
     * Get top 1 most urgent suggestion
     */
    public function getTopSuggestion($suggestions)
    {
        // ✅ FIX: Check empty state first
        if (!empty($suggestions['empty_state']) && $suggestions['empty_state'] === true) {
            return [
                'type' => 'empty',
                'data' => [
                    'title' => 'Belum Ada Tugas',
                    'description' => 'Workspace ini belum memiliki tugas. Silakan buat tugas terlebih dahulu untuk melihat analisis kinerja.',
                ],
            ];
        }

        // Priority: critical > warning > positive
        if (!empty($suggestions['critical'])) {
            return [
                'type' => 'critical',
                'data' => $suggestions['critical'][0],
            ];
        }

        if (!empty($suggestions['warning'])) {
            return [
                'type' => 'warning',
                'data' => $suggestions['warning'][0],
            ];
        }

        if (!empty($suggestions['positive'])) {
            return [
                'type' => 'positive',
                'data' => $suggestions['positive'][0],
            ];
        }

        return [
            'type' => 'neutral',
            'data' => [
                'title' => 'Tidak ada data cukup',
                'description' => 'Belum ada analisis untuk periode ini',
            ],
        ];
    }
}