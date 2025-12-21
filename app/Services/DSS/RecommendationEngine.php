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
        Log::info('RecommendationEngine: Generating suggestions', [
            'total_tasks' => $metrics['totalTasks'] ?? 0,
            'has_completed' => $metrics['hasCompletedTasks'] ?? false,
            'performance_score' => $metrics['performanceScore'] ?? 0
        ]);

        $this->metrics = $metrics;
        $this->trends = $trends;

        // ✅ EMPTY STATE
        if (($metrics['totalTasks'] ?? 0) == 0) {
            return [
                'critical' => [],
                'warning' => [],
                'positive' => [],
                'actions' => ['Buat tugas pertama untuk memulai workspace ini'],
                'empty_state' => true,
            ];
        }

        // ✅ GENERATE berdasarkan performance score & context
        // ✅ GENERATE berdasarkan workspace phase
        $phase = $metrics['workspacePhase'] ?? 'active';
        $performanceScore = $metrics['performanceScore'] ?? 0;

        $critical = [];
        $warning = [];
        $positive = [];

        // ✅ WORKSPACE BARU (phase = 'new')
        if ($phase === 'new') {
            $critical = $this->generateCritical(); // ✅ TAMBAH INI!
            $positive = $this->generatePositiveForNew();
            $warning = $this->generateWarningForNew();
        }
        // ✅ WORKSPACE STAGNANT
        elseif ($phase === 'stagnant') {
            $critical = $this->generateCritical();
            $warning = $this->generateWarning();
            // Tambah pesan khusus stagnant
            array_unshift($critical, [
                'title' => 'Workspace tidak aktif',
                'description' => 'Banyak tugas terlambat tapi belum ada yang selesai',
                'metric' => 'workspacePhase',
                'value' => 'stagnant',
                'priority' => 1,
            ]);
        }
        // ✅ WORKSPACE AKTIF (sudah ada task selesai)
        else {
            if ($performanceScore < 50) {
                // Buruk/Kurang (1-2 bintang)
                $critical = $this->generateCritical();
                $warning = $this->generateWarning();
            } elseif ($performanceScore < 70) {
                // Cukup (3 bintang)
                $warning = $this->generateWarning();
                $positive = $this->generatePositive();
            } else {
                // Bagus/Sangat Bagus (4-5 bintang)
                $positive = $this->generatePositive();
                $warning = $this->generateWarning();
            }
        }

        $actions = $this->generateActions($critical, $warning, $positive);

        return [
            'critical' => $critical,
            'warning' => $warning,
            'positive' => $positive,
            'actions' => $actions,
            'empty_state' => false,
        ];
    }

    /**
     * ✅ POSITIVE untuk workspace baru (belum ada completed)
     */
    private function generatePositiveForNew()
    {
        $positive = [];
        $idleRate = $this->metrics['idleRate'] ?? 0;
        $wipRate = $this->metrics['wipRate'] ?? 0;
        $totalTasks = $this->metrics['totalTasks'] ?? 0;

        // ✅ PERBAIKAN: Case 1 - Idle rate moderate (30-70%)
        if ($idleRate >= 30 && $idleRate <= 70 && $totalTasks >= 3) {
            $positive[] = [
                'title' => 'Workspace siap dimulai',
                'description' => "Sudah ada {$totalTasks} tugas terdaftar dan mulai dikerjakan. Momentum bagus!",
                'metric' => 'totalTasks',
                'value' => $totalTasks,
            ];
        }

        // ✅ PERBAIKAN: Case 2 - Ada WIP yang signifikan (20-50%)
        if ($wipRate >= 20 && $wipRate <= 50) {
            $wipCount = round($totalTasks * ($wipRate / 100));
            $positive[] = [
                'title' => 'Tim mulai produktif',
                'description' => "{$wipCount} tugas sudah dikerjakan. Teruskan sampai selesai!",
                'metric' => 'wipRate',
                'value' => $wipRate . '%',
            ];
        }

        return $positive;
    }

    /**
     * ✅ WARNING untuk workspace baru
     */
    private function generateWarningForNew()
    {
        $warning = [];
        $idleRate = $this->metrics['idleRate'] ?? 0;
        $overdueRate = $this->metrics['overdueRate'] ?? 0;
        $totalTasks = $this->metrics['totalTasks'] ?? 0;

        // Warning 1: Hampir semua idle
        if ($idleRate > 80 && $totalTasks > 0) {
            $idleCount = round($totalTasks * ($idleRate / 100));
            $warning[] = [
                'title' => 'Banyak tugas belum dimulai',
                'description' => "{$idleCount} dari {$totalTasks} tugas belum dikerjakan",
                'metric' => 'idleRate',
                'value' => $idleRate . '%',
                'suggestions' => [
                    'Assign tugas ke anggota tim',
                    'Mulai 2-3 tugas prioritas tinggi',
                    'Set target harian untuk tim'
                ]
            ];
        }

        // Warning 2: Ada overdue padahal belum ada completed
        if ($overdueRate > 0) {
            $overdueCount = round($totalTasks * ($overdueRate / 100));
            $warning[] = [
                'title' => 'Ada tugas melewati deadline',
                'description' => "{$overdueCount} tugas sudah lewat deadline tapi belum selesai",
                'metric' => 'overdueRate',
                'value' => $overdueRate . '%',
                'suggestions' => [
                    'Prioritaskan tugas yang sudah overdue',
                    'Review apakah deadline realistis',
                    'Cek hambatan yang menghambat tim'
                ]
            ];
        }

        return $warning;
    }

    /**
     * ✅ CRITICAL - Universal (tidak ada istilah teknis)
     */
    private function generateCritical()
    {
        $critical = [];
        $totalTasks = $this->metrics['totalTasks'] ?? 0;
        $hasCompleted = $this->metrics['hasCompletedTasks'] ?? false;
        $phase = $this->metrics['workspacePhase'] ?? 'active';

        $overdueRate = $this->metrics['overdueRate'] ?? 0;
        $overdueCount = $this->metrics['overdueCount'] ?? 0; // ✅ TAMBAH INI
        $completionRate = $this->metrics['completionRate'] ?? 0;
        $onTimeRate = $this->metrics['onTimeRate'] ?? 0;
        $onTimeTrend = $this->trends['onTimeRate'] ?? null;

        // ✅ FIX: Skip critical untuk workspace baru TANPA overdue
        if ($phase === 'new' && $overdueRate == 0) {
            return []; // Tidak ada critical issue
        }

        // ✅ DYNAMIC: Adjust threshold berdasarkan phase dan total tasks
        $overdueThreshold = 30;
        if ($phase === 'new') {
            $overdueThreshold = 20; // Lebih strict untuk workspace baru
        } elseif ($totalTasks < 5) {
            $overdueThreshold = 40; // Lebih toleran untuk workspace kecil
        }

        // Critical 1: Overdue + Low completion
        if ($overdueRate > $overdueThreshold && $completionRate < 40) {
            $overdueCount = round($totalTasks * ($overdueRate / 100));

            $critical[] = [
                'title' => 'Banyak tugas terlambat dan sedikit yang selesai',
                'description' => "{$overdueCount} tugas melewati deadline, hanya {$completionRate}% yang selesai",
                'metric' => 'overdueRate',
                'value' => $overdueRate . '%',
                'priority' => 1,
                'actions' => [
                    'Review semua tugas overdue, prioritaskan yang paling kritis',
                    'Identifikasi hambatan utama yang memperlambat tim',
                    'Reschedule deadline jika memang tidak realistis'
                ]
            ];
        }
        // Critical 2: Low on-time (DYNAMIC - dengan trend)
        elseif ($hasCompleted && $onTimeRate < 40) {
            $trendContext = '';
            $suggestions = [
                'Evaluasi estimasi waktu, mungkin terlalu optimis',
                'Tingkatkan komunikasi untuk deteksi hambatan lebih awal'
            ];

            // Cek trend: turun drastis = lebih serius
            if ($onTimeTrend && isset($onTimeTrend['change_percent'])) {
                $trendPercent = $onTimeTrend['change_percent'];

                if ($trendPercent < -15) {
                    $trendContext = " dan turun " . abs(round($trendPercent)) . "% dari periode lalu";
                    array_unshift($suggestions, 'URGENT: Ada degradasi performa, identify penyebab segera');
                } elseif ($trendPercent > 10) {
                    $trendContext = " tapi membaik +" . round($trendPercent) . "% dari periode lalu";
                    $suggestions = ['Teruskan perbaikan yang sudah dilakukan', 'Monitor agar trend positif berlanjut'];
                }
            }

            $critical[] = [
                'title' => 'Mayoritas tugas selesai terlambat',
                'description' => "Hanya {$onTimeRate}% tugas selesai tepat waktu{$trendContext}",
                'metric' => 'onTimeRate',
                'value' => $onTimeRate . '%' . ($trendContext ? ' (trending)' : ''),
                'priority' => 2,
                'actions' => $suggestions
            ];
        }

        // Critical 3: Banyak deadline mendesak (DYNAMIC)
        $urgentTaskRatio = $this->metrics['urgentTaskRatio'] ?? 0;
        $criticalTaskRatio = $this->metrics['criticalTaskRatio'] ?? 0;
        // Di method generateCritical(), bagian Critical 3
        $overdueCount = $this->metrics['overdueCount'] ?? 0;
        $completionRate = $this->metrics['completionRate'] ?? 0;
        $totalNotDone = $totalTasks * ((100 - $completionRate) / 100);

        // ✅ Hitung overdue percentage
        $overduePercentage = $totalNotDone > 0
            ? round(($overdueCount / $totalNotDone) * 100, 1)
            : 0;

        // ✅ TAMBAH DEBUG LOG INI
        Log::info('🔍 Critical Check #3', [
            'overdueCount' => $overdueCount,
            'totalNotDone' => $totalNotDone,
            'overduePercentage' => $overduePercentage,
            'urgentTaskRatio' => $urgentTaskRatio,
            'will_trigger' => ($overduePercentage > 30 || $urgentTaskRatio > 40) ? 'YES' : 'NO'
        ]);


        // ✅ TRIGGER: Overdue >30% ATAU urgent >40%
        if ($overduePercentage > 30 || $urgentTaskRatio > 40) {

            // ✅ FIX: Hitung TANPA DUPLIKASI
            $urgentCount = round($totalNotDone * ($urgentTaskRatio / 100));
            $criticalCount = max(0, round($totalNotDone * ($criticalTaskRatio / 100)) - $overdueCount);
            $urgentOnly = max(0, $urgentCount - $criticalCount - $overdueCount);

            // ✅ BUILD MESSAGE
            $urgentMsg = [];

            if ($overdueCount > 0) {
                $urgentMsg[] = "{$overdueCount} tugas sudah terlambat";
            }

            if ($criticalCount > 0) {
                $urgentMsg[] = "{$criticalCount} tugas deadline dalam 24 jam";
            }

            if ($urgentOnly > 0) {
                $urgentMsg[] = "{$urgentOnly} tugas deadline 2-3 hari";
            }

            if (empty($urgentMsg)) {
                $urgentMsg[] = "Ada deadline yang perlu diperhatikan";
            }

            $description = implode(', ', $urgentMsg);

            // ✅ Deteksi severity
            $causeContext = '';
            $suggestions = [];
            $priority = 1;

            if ($overduePercentage > 50) {
                $causeContext = ' — Tim kesulitan mengejar deadline';
                $priority = 1;
                $suggestions = [
                    'URGENT: Review semua tugas overdue, prioritaskan yang paling kritis',
                    'Identifikasi hambatan yang membuat tugas terlambat',
                    'Pertimbangkan reschedule jika deadline tidak realistis'
                ];
            } elseif ($overduePercentage > 30) {
                $causeContext = ' — Ada masalah eksekusi yang perlu segera diatasi';
                $priority = 1;
                $suggestions = [
                    'PRIORITY: Selesaikan tugas overdue terlebih dahulu',
                    'Daily check-in untuk monitor blocker',
                    'Escalate jika ada dependency issue'
                ];
            } elseif (($this->metrics['taskVelocity'] ?? 0) < 0.3 && ($this->metrics['avgTimeToDeadline'] ?? 0) < 2) {
                $causeContext = ' — Kemungkinan tugas terlambat didaftarkan';
                $priority = 2;
                $suggestions = [
                    'Register tugas lebih awal di periode berikutnya',
                    'Buat buffer waktu untuk task baru (min 5-7 hari)',
                    'Fokus selesaikan tugas paling urgent'
                ];
            } else {
                $causeContext = ' — Periode deadline memang padat';
                $priority = 2;
                $suggestions = [
                    'All hands on deck: fokus selesaikan urgent tasks',
                    'Delegate tugas non-urgent ke periode berikutnya',
                    'Komunikasi intensif untuk blocker resolution'
                ];
            }

            $critical[] = [
                'title' => 'Banyak deadline mendesak',
                'description' => $description . $causeContext,
                'metric' => 'urgentTaskRatio',
                'value' => $urgentTaskRatio . '%',
                'priority' => $priority,
                'actions' => $suggestions
            ];
        }

        // Critical 4: Avg delay tinggi (tetap)
        if ($hasCompleted && $this->metrics['avgDelay'] > 5) {
            $critical[] = [
                'title' => 'Pola keterlambatan berulang',
                'description' => "Rata-rata tugas terlambat {$this->metrics['avgDelay']} hari",
                'metric' => 'avgDelay',
                'value' => round($this->metrics['avgDelay'], 1) . ' hari',
                'priority' => 1,
            ];
        }

        // Critical 5: Risk score tinggi (tetap)
        if ($this->metrics['riskScore'] > 70) {
            $critical[] = [
                'title' => 'Tingkat risiko tinggi',
                'description' => "Workspace berisiko gagal mencapai target (skor risiko: {$this->metrics['riskScore']}/100)",
                'metric' => 'riskScore',
                'value' => $this->metrics['riskScore'] . '/100',
                'priority' => 1,
            ];
        }

        usort($critical, function ($a, $b) {
            return ($a['priority'] ?? 5) <=> ($b['priority'] ?? 5);
        });

        return array_slice($critical, 0, 3);
    }

    /**
     * ✅ WARNING - Universal
     */
    /**
     * ✅ WARNING - Universal
     */
    private function generateWarning()
    {
        $warning = [];
        $performanceScore = $this->metrics['performanceScore'] ?? 0;
        $totalTasks = $this->metrics['totalTasks'] ?? 0;
        $overdueCount = $this->metrics['overdueCount'] ?? 0; // ✅ TAMBAH INI
        $hasCompleted = $this->metrics['hasCompletedTasks'] ?? false;
        $phase = $this->metrics['workspacePhase'] ?? 'active';

        // Warning 1: Performance perlu ditingkatkan
        if ($performanceScore >= 50 && $performanceScore < 70) {
            $warning[] = [
                'title' => 'Performa perlu ditingkatkan',
                'description' => "Skor performa workspace: {$performanceScore}/100",
                'metric' => 'performanceScore',
                'value' => $performanceScore . '/100',
                'suggestions' => $this->getGenericSuggestions()
            ];
        }

        // Warning 2: Banyak idle (DYNAMIC - dengan trend)
        $idleRate = $this->metrics['idleRate'] ?? 0;
        $idleTrend = $this->trends['idleRate'] ?? null;

        if ($idleRate > 40) {
            $idleCount = round($totalTasks * ($idleRate / 100));

            if ($idleTrend && isset($idleTrend['change_percent'])) {
                $trendPercent = $idleTrend['change_percent'];

                // Idle naik = lebih serius
                if ($trendPercent > 10) {
                    $warning[] = [
                        'title' => 'Banyak tugas belum dimulai dan terus meningkat',
                        'description' => "{$idleCount} tugas belum dikerjakan (naik {$trendPercent}% dari periode lalu)",
                        'metric' => 'idleRate',
                        'value' => $idleRate . '% (↑' . abs(round($trendPercent)) . '%)',
                        'suggestions' => [
                            'URGENT: Identify kenapa tim tidak mulai tugas',
                            'Cek apakah ada blocker atau ketergantungan',
                            'Assign tugas secara eksplisit ke anggota'
                        ]
                    ];
                }
                // Idle stabil atau turun sedikit = warning normal
                elseif ($trendPercent >= -10) {
                    $warning[] = [
                        'title' => 'Banyak tugas belum dimulai',
                        'description' => "{$idleCount} tugas masih menunggu dikerjakan",
                        'metric' => 'idleRate',
                        'value' => $idleRate . '%',
                        'suggestions' => [
                            'Assign tugas ke anggota',
                            'Mulai tugas prioritas tinggi',
                            'Cek apakah ada hambatan untuk mulai'
                        ]
                    ];
                }
                // Idle turun drastis (>10%) = skip warning, kondisi membaik
            }
            // Tidak ada trend data = warning normal
            else {
                $warning[] = [
                    'title' => 'Banyak tugas belum dimulai',
                    'description' => "{$idleCount} tugas masih menunggu dikerjakan",
                    'metric' => 'idleRate',
                    'value' => $idleRate . '%',
                    'suggestions' => [
                        'Assign tugas ke anggota',
                        'Mulai tugas prioritas tinggi',
                        'Cek apakah ada hambatan untuk mulai'
                    ]
                ];
            }
        }

        // Warning 3: Terlalu banyak WIP (DYNAMIC - dengan velocity)
        $wipRate = $this->metrics['wipRate'] ?? 0;
        $completionRate = $this->metrics['completionRate'] ?? 0;
        $taskVelocity = $this->metrics['taskVelocity'] ?? 0;
        $avgProgress = $this->metrics['avgProgress'] ?? 0;

        if ($wipRate > 50 && $completionRate < 40) {
            $wipCount = round($totalTasks * ($wipRate / 100));

            // Velocity rendah DAN avg progress rendah = stuck (serious)
            if ($taskVelocity < 0.5 && $avgProgress < 30) {
                $warning[] = [
                    'title' => 'Banyak tugas stuck',
                    'description' => "{$wipCount} tugas sedang dikerjakan tapi progress lambat (rata-rata {$avgProgress}%, velocity {$taskVelocity} tugas/hari)",
                    'metric' => 'wipRate',
                    'value' => $wipRate . '% (velocity: ' . round($taskVelocity, 1) . ')',
                    'suggestions' => [
                        'Identifikasi hambatan pada tugas yang stuck',
                        'Fokus selesaikan 1-2 tugas prioritas dulu',
                        'Review apakah task terlalu besar, perlu dipecah'
                    ]
                ];
            }
            // Velocity cukup ATAU avg progress cukup = productive (warning ringan)
            elseif ($taskVelocity >= 0.5 || $avgProgress >= 30) {
                $warning[] = [
                    'title' => 'Banyak tugas sedang dikerjakan',
                    'description' => "{$wipCount} tugas in-progress tapi masih produktif (velocity {$taskVelocity} tugas/hari)",
                    'metric' => 'wipRate',
                    'value' => $wipRate . '% (productive)',
                    'suggestions' => [
                        'Batasi WIP per orang (max 2-3 tugas)',
                        'Prioritaskan tugas yang hampir selesai'
                    ]
                ];
            }
        }

        // Warning 4: Beban kerja tidak merata (sudah dynamic)
        $memberCount = $this->metrics['memberCount'] ?? 999;
        $gini = $this->metrics['gini'] ?? 0;

        // Dynamic threshold berdasarkan team size
        if ($memberCount == 1) {
            return $warning; // Skip gini warning untuk solo
        } elseif ($memberCount == 2) {
            $giniThreshold = 0.25; // Sangat strict untuk 2 orang
        } elseif ($memberCount <= 3) {
            $giniThreshold = 0.35;
        } elseif ($memberCount <= 5) {
            $giniThreshold = 0.45;
        } else {
            $giniThreshold = 0.50; // Lebih toleran untuk tim besar
        }

        if ($this->metrics['gini'] > $giniThreshold || $this->metrics['maxLoadRatio'] > 3) {
            $warning[] = [
                'title' => 'Beban kerja tidak merata',
                'description' => "Ada anggota dengan tugas terlalu banyak, ada yang terlalu sedikit",
                'metric' => 'gini',
                'value' => round($this->metrics['gini'], 2),
                'suggestions' => [
                    'Redistribusi tugas agar lebih seimbang',
                    'Bantu anggota yang kelebihan beban',
                    'Review kapasitas masing-masing anggota'
                ]
            ];
        }

        return array_slice($warning, 0, 4);
    }

    /**
     * ✅ POSITIVE - Universal
     */
    private function generatePositive()
    {
        $positive = [];
        $performanceScore = $this->metrics['performanceScore'] ?? 0;

        // Positive 1: Performance bagus
        if ($performanceScore >= 70) {
            $label = $performanceScore >= 85 ? 'sangat baik' : 'baik';
            $positive[] = [
                'title' => "Performa workspace {$label}",
                'description' => "Skor performa: {$performanceScore}/100. Pertahankan!",
                'metric' => 'performanceScore',
                'value' => $performanceScore . '/100',
            ];
        }

        // Positive 2: Workload merata
        if ($this->metrics['gini'] < 0.3 && $this->metrics['tasksPerMember'] > 0) {
            $positive[] = [
                'title' => 'Pembagian tugas merata',
                'description' => "Beban kerja terdistribusi dengan baik",
                'metric' => 'gini',
                'value' => round($this->metrics['gini'], 2),
            ];
        }

        // Positive 3: Trend positif
        if ($this->trends && isset($this->trends['performanceScore'])) {
            $trend = $this->trends['performanceScore'];
            if ($trend['change_percent'] !== null && $trend['change_percent'] > 10) {
                $positive[] = [
                    'title' => 'Performa meningkat',
                    'description' => "Naik {$trend['change_percent']}% dibanding periode lalu",
                    'metric' => 'performanceScore',
                    'value' => '+' . $trend['change_percent'] . '%',
                ];
            }
        }

        // Positive 4: On-time rate tinggi
        if ($this->metrics['hasCompletedTasks'] && $this->metrics['onTimeRate'] > 80) {
            $positive[] = [
                'title' => 'Mayoritas tugas selesai tepat waktu',
                'description' => "{$this->metrics['onTimeRate']}% tugas selesai on-time",
                'metric' => 'onTimeRate',
                'value' => $this->metrics['onTimeRate'] . '%',
            ];
        }

        return array_slice($positive, 0, 3);
    }

    /**
     * ✅ ACTIONS - Universal & Generic
     */
    private function generateActions($critical, $warning, $positive)
    {
        $actions = [];

        // Actions dari critical
        if (!empty($critical)) {
            foreach ($critical as $issue) {
                if (stripos($issue['title'], 'terlambat') !== false) {
                    $actions[] = "Review tugas yang terlambat dan cari solusi";
                }
                if (stripos($issue['title'], 'deadline mendesak') !== false) {
                    $actions[] = "Prioritaskan tugas dengan deadline terdekat";
                }
                if (stripos($issue['title'], 'risiko') !== false) {
                    $actions[] = "Evaluasi dan mitigasi risiko workspace";
                }
            }
        }

        // Actions dari warning
        if (!empty($warning)) {
            foreach ($warning as $issue) {
                if (isset($issue['suggestions']) && !empty($issue['suggestions'])) {
                    // Ambil max 2 suggestions per warning
                    $actions = array_merge($actions, array_slice($issue['suggestions'], 0, 2));
                }
            }
        }

        // Default actions
        if (empty($actions)) {
            if (!empty($positive)) {
                $actions[] = "Pertahankan cara kerja yang sudah baik";
                $actions[] = "Dokumentasi proses yang berhasil";
            } else {
                $actions[] = "Pantau progress tugas secara berkala";
                $actions[] = "Komunikasikan hambatan dengan tim";
            }
        }

        // Remove duplicates dan limit
        $actions = array_unique($actions);
        return array_slice(array_values($actions), 0, 5);
    }

    /**
     * ✅ Generic suggestions berdasarkan context
     */
    private function getGenericSuggestions()
    {
        $onTimeRate = $this->metrics['onTimeRate'] ?? 0;
        $completionRate = $this->metrics['completionRate'] ?? 0;
        $hasCompleted = $this->metrics['hasCompletedTasks'] ?? false;

        if (!$hasCompleted) {
            return [
                'Mulai eksekusi tugas prioritas tinggi',
                'Set target mingguan yang realistis',
                'Track progress secara konsisten'
            ];
        }

        if ($onTimeRate < 60) {
            return [
                'Review estimasi waktu tugas',
                'Identifikasi penyebab keterlambatan',
                'Tingkatkan koordinasi tim'
            ];
        } elseif ($completionRate < 50) {
            return [
                'Fokus selesaikan tugas yang sudah dimulai',
                'Kurangi multitasking',
                'Pecah tugas besar menjadi lebih kecil'
            ];
        } else {
            return [
                'Pertahankan momentum kerja',
                'Optimalkan proses yang sudah berjalan',
                'Tingkatkan komunikasi tim'
            ];
        }
    }

    /**
     * ✅ Workload recommendations (Universal)
     */
    public function generateWorkloadRecommendations($workloadData)
    {
        if (empty($workloadData) || count($workloadData) == 0) {
            return ["Belum ada data distribusi tugas"];
        }

        $collection = collect($workloadData);
        $maxLoad = $collection->max('load_percentage');
        $minLoad = $collection->min('load_percentage');
        $loadGap = $maxLoad - $minLoad;
        $totalWorkspaceTasks = $collection->sum('total_tasks');

        $recommendations = [];

        // ✅ PERBAIKAN: Gap tinggi (≥40%)
        if ($loadGap >= 40) {
            $avgLoad = $collection->avg('load_percentage');

            $overloaded = $collection->filter(
                fn($m) =>
                $m['load_percentage'] > ($avgLoad * 1.3)
            )->sortByDesc('load_percentage');

            $underloaded = $collection->filter(
                fn($m) =>
                $m['load_percentage'] < ($avgLoad * 0.7)
            )->sortBy('load_percentage');

            if ($overloaded->isNotEmpty() && $underloaded->isNotEmpty()) {
                $over = $overloaded->first();
                $under = $underloaded->first();

                // ✅ DYNAMIC CAP: Berdasarkan real data + workspace size
                $taskGap = $over['total_tasks'] - $under['total_tasks'];
                $idealMove = ceil($taskGap / 2);

                // Cap dinamis: minimal 5, maksimal 15% dari total workspace atau 20 tugas
                $dynamicCap = min(
                    max(5, ceil($totalWorkspaceTasks * 0.15)),
                    20
                );

                $suggestedMove = min($idealMove, $dynamicCap);

                // Hitung hasil setelah pindah
                $overAfter = $over['total_tasks'] - $suggestedMove;
                $underAfter = $under['total_tasks'] + $suggestedMove;

                $recommendations[] = "⚠️ Gap beban kerja sangat besar (" . round($loadGap, 1) . "%)";
                $recommendations[] = "Pindahkan {$suggestedMove} tugas dari {$over['name']} ({$over['total_tasks']} tugas) ke {$under['name']} ({$under['total_tasks']} tugas)";
                $recommendations[] = "Hasil: {$over['name']} akan punya {$overAfter} tugas, {$under['name']} akan punya {$underAfter} tugas";
            } else {
                $over = $collection->sortByDesc('total_tasks')->first();
                $under = $collection->sortBy('total_tasks')->first();

                if ($over['total_tasks'] - $under['total_tasks'] >= 3) {
                    $taskGap = $over['total_tasks'] - $under['total_tasks'];
                    $idealMove = ceil($taskGap / 2);

                    $dynamicCap = min(
                        max(5, ceil($totalWorkspaceTasks * 0.15)),
                        20
                    );

                    $suggestedMove = min($idealMove, $dynamicCap);

                    $overAfter = $over['total_tasks'] - $suggestedMove;
                    $underAfter = $under['total_tasks'] + $suggestedMove;

                    $recommendations[] = "⚠️ Beban {$over['name']} ({$over['total_tasks']} tugas) jauh lebih besar dari {$under['name']} ({$under['total_tasks']} tugas)";
                    $recommendations[] = "Pindahkan {$suggestedMove} tugas untuk menyeimbangkan";
                    $recommendations[] = "Hasil: {$over['name']} → {$overAfter} tugas, {$under['name']} → {$underAfter} tugas";
                } else {
                    $recommendations[] = "Gap beban kerja cukup besar (" . round($loadGap, 1) . "%), tapi distribusi tugas masih dalam batas wajar";
                    $recommendations[] = "Pantau untuk memastikan tidak ada anggota yang overload";
                }
            }
        } elseif ($loadGap >= 20) {
            $over = $collection->sortByDesc('total_tasks')->first();
            $under = $collection->sortBy('total_tasks')->first();

            $recommendations[] = "Distribusi cukup merata tapi masih bisa lebih baik";

            if ($over['total_tasks'] - $under['total_tasks'] >= 2) {
                $taskGap = $over['total_tasks'] - $under['total_tasks'];
                $suggestedMove = min(ceil($taskGap / 2), 3);

                $recommendations[] = "Pertimbangkan pindahkan {$suggestedMove} tugas dari {$over['name']} ({$over['total_tasks']} tugas) ke {$under['name']} ({$under['total_tasks']} tugas)";
            } else {
                $recommendations[] = "Monitor beban kerja agar tetap seimbang";
            }
        } else {
            $recommendations[] = "Distribusi tugas sudah sangat merata, pertahankan!";

            $avgTasks = round($collection->avg('total_tasks'), 1);
            $recommendations[] = "Rata-rata beban: {$avgTasks} tugas per orang";
        }

        return array_slice($recommendations, 0, 3);
    }
    /**
     * ✅ Get top suggestion
     */
    public function getTopSuggestion($suggestions)
    {
        // ✅ TAMBAH: Guard untuk empty state
        if (empty($suggestions)) {
            return [
                'type' => 'neutral',
                'data' => [
                    'title' => 'Belum ada data',
                    'description' => 'Refresh untuk melihat analisis',
                ],
            ];
        }

        // Empty state check
        if (!empty($suggestions['empty_state']) && $suggestions['empty_state'] === true) {
            return [
                'type' => 'empty',
                'data' => [
                    'title' => 'Belum ada tugas',
                    'description' => 'Buat tugas pertama untuk memulai workspace ini',
                ],
            ];
        }

        // ✅ FIX: PRIORITAS BERDASARKAN ARRAY ORDER, BUKAN LOGIC DINAMIS
        // Priority 1: Critical (paling urgent)
        if (!empty($suggestions['critical'])) {
            return ['type' => 'critical', 'data' => $suggestions['critical'][0]];
        }

        // Priority 2: Warning
        if (!empty($suggestions['warning'])) {
            return ['type' => 'warning', 'data' => $suggestions['warning'][0]];
        }

        // Priority 3: Positive
        if (!empty($suggestions['positive'])) {
            return ['type' => 'positive', 'data' => $suggestions['positive'][0]];
        }

        // Default
        return [
            'type' => 'neutral',
            'data' => [
                'title' => 'Workspace berjalan normal',
                'description' => 'Pantau terus perkembangan tim',
            ],
        ];
    }
}
