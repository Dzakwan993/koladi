<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * BenchmarkInstrumentation Middleware
 *
 * Aktif HANYA saat APP_ENV=local dan header X-Benchmark-Mode: 1 ada.
 * Menambahkan debug headers ke response:
 *   - X-Debug-Query-Count  : jumlah SQL query yang dieksekusi
 *   - X-Debug-Memory-MB    : peak memory usage PHP dalam MB
 *   - X-Debug-Cache-Hit    : true/false apakah cache digunakan
 *   - X-Debug-Cache-Hit-Ratio : rasio hit Redis (%) atau snapshot baseline
 *   - X-Debug-Config       : baseline | optimized
 */
class BenchmarkInstrumentation
{
    private static int $queryCount = 0;
    private static bool $listening = false;

    public function handle(Request $request, Closure $next): Response
    {
        // Hanya aktif di local environment & saat header benchmark hadir
        if (
            app()->environment('local') &&
            $request->header('X-Benchmark-Mode') === '1'
        ) {
            return $this->handleWithInstrumentation($request, $next);
        }

        return $next($request);
    }

    private function handleWithInstrumentation(Request $request, Closure $next): Response
    {
        // Reset query counter
        self::$queryCount = 0;

        // Catat Redis stats SEBELUM request (untuk optimized config)
        $redisStatsBefore = $this->getRedisStats();

        // Listen semua query DB
        DB::listen(function ($query) {
            self::$queryCount++;
        });

        // Reset peak memory (tidak bisa direset secara native, tapi kita record sebelum)
        $memBefore = memory_get_usage(true);

        // Eksekusi request utama
        $response = $next($request);

        // Ukur memory SETELAH request
        $memPeakBytes = memory_get_peak_usage(true);
        $memMB = round($memPeakBytes / 1024 / 1024, 4);

        // Catat Redis stats SETELAH request (untuk hitung delta)
        $redisStatsAfter = $this->getRedisStats();

        // Hitung cache hit ratio berdasarkan konfigurasi
        $config = $request->header('X-Benchmark-Config', 'baseline');
        [$cacheHit, $cacheHitRatio] = $this->calculateCacheMetrics(
            $config,
            $request,
            $response,
            $redisStatsBefore,
            $redisStatsAfter
        );

        // Tambahkan debug headers ke response
        $response->headers->set('X-Debug-Query-Count', (string) self::$queryCount);
        $response->headers->set('X-Debug-Memory-MB', (string) $memMB);
        $response->headers->set('X-Debug-Cache-Hit', $cacheHit ? 'true' : 'false');
        $response->headers->set('X-Debug-Cache-Hit-Ratio', (string) $cacheHitRatio);
        $response->headers->set('X-Debug-Config', $config);

        return $response;
    }

    /**
     * Ambil keyspace stats dari Redis untuk konfigurasi optimized.
     */
    private function getRedisStats(): array
    {
        try {
            $info = Redis::connection('default')->client()->info('stats');
            return [
                'keyspace_hits'   => (int) ($info['keyspace_hits']   ?? 0),
                'keyspace_misses' => (int) ($info['keyspace_misses'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return ['keyspace_hits' => 0, 'keyspace_misses' => 0];
        }
    }

    /**
     * Hitung cache metrics sesuai konfigurasi.
     *
     * - optimized: pakai Redis keyspace_hits delta
     * - baseline : pakai deteksi apakah snapshot ditemukan di workspace_performance_snapshots
     *
     * @return array [bool $cacheHit, float $cacheHitRatio]
     */
    private function calculateCacheMetrics(
        string $config,
        Request $request,
        Response $response,
        array $before,
        array $after
    ): array {
        if ($config === 'optimized') {
            // Delta keyspace hits/misses selama 1 request ini
            $deltaHits   = max(0, $after['keyspace_hits']   - $before['keyspace_hits']);
            $deltaMisses = max(0, $after['keyspace_misses'] - $before['keyspace_misses']);
            $total = $deltaHits + $deltaMisses;

            $ratio    = $total > 0 ? round(($deltaHits / $total) * 100, 2) : 0.0;
            $cacheHit = $deltaHits > 0;

            return [$cacheHit, $ratio];
        }

        // baseline: deteksi cache hit dari response body (field 'cached')
        // atau cek langsung tabel workspace_performance_snapshots
        try {
            $content = json_decode($response->getContent(), true);
            $cacheHit = (bool) ($content['data']['cached'] ?? false);

            // Cache hit ratio untuk baseline: proporsi sederhana (1.0 atau 0.0)
            // karena baseline hanya bisa hit/miss per request
            $ratio = $cacheHit ? 100.0 : 0.0;

            return [$cacheHit, $ratio];
        } catch (\Throwable $e) {
            return [false, 0.0];
        }
    }
}
