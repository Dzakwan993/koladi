#!/usr/bin/env node
/**
 * benchmark-dss.js
 *
 * Controlled Before-After Benchmarking Script untuk Penelitian Koladi
 * ===================================================================
 * Metode: HTTP sequential terkontrol (bukan load testing / concurrent)
 * Output: CSV dengan kolom sesuai desain penelitian
 *
 * Cara pakai:
 *   node scripts/benchmark-dss.js --configuration=baseline
 *   node scripts/benchmark-dss.js --configuration=optimized
 *
 * Prerequisites:
 *   1. Jalankan: docker exec koladi_app_local php artisan benchmark:token --email=ardhaniishere@gmail.com
 *   2. Pastikan file storage/app/benchmark/auth.json sudah ada
 *   3. npm install (hanya butuh node-fetch dan commander, lihat bawah)
 */

'use strict';

// ─────────────────────────────────────────────────────────
// DEPENDENCIES CHECK
// ─────────────────────────────────────────────────────────
let fetch, commander, path, fs;
try {
    path = require('path');
    fs   = require('fs');
} catch (e) {
    console.error('Node.js built-in module missing:', e.message);
    process.exit(1);
}

// Try to load dependencies, install jika belum ada
async function loadDeps() {
    try {
        const { default: _fetch } = await import('node-fetch');
        fetch = _fetch;
    } catch {
        console.error('❌ node-fetch tidak ditemukan. Jalankan: npm install node-fetch');
        process.exit(1);
    }
    try {
        const { Command } = require('commander');
        commander = new Command();
    } catch {
        console.error('❌ commander tidak ditemukan. Jalankan: npm install commander');
        process.exit(1);
    }
    return { fetch, commander };
}

// ─────────────────────────────────────────────────────────
// KONFIGURASI
// ─────────────────────────────────────────────────────────

/** Workspace IDs (sesuai database Koladi) */
const WORKSPACES = {
    small: {
        id:    '3b61e2cf-dd4b-4732-ae89-8042450187b2',
        label: 'Div. Desain',
        tasks: 50,
        members: 5,
    },
    medium: {
        id:    '173a617f-c955-42f6-9042-9815a0553ae6',
        label: 'HR Keuangan',
        tasks: 200,
        members: 15,
    },
    large: {
        id:    '8e26fb16-12bc-4768-b8b6-72f7d28efcc8',
        label: 'MOU PT Xyz',
        tasks: 500,
        members: 30,
    },
};

/** Endpoint yang diuji */
const ENDPOINTS = [
    { key: 'suggestions', path: '/statistik/suggestions',  label: 'suggestions' },
    { key: 'modal-data',  path: '/statistik/modal-data',   label: 'modal-data'  },
];

const BASE_URL     = 'http://localhost:8000';
const WARM_UP_REPS = 3;   // Tidak masuk CSV
const VALID_REPS   = 10;  // Masuk CSV

/** Periode pengujian: 1 bulan penuh agar mencakup semua task (due_datetime dalam 1 tahun) */
const PERIOD_START = '2025-01-01';
const PERIOD_END   = '2027-12-31';

// ─────────────────────────────────────────────────────────
// HELPER FUNCTIONS
// ─────────────────────────────────────────────────────────

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function getAuthConfig(configDir) {
    const authPath = path.join(configDir, '..', '..', '..', '..', 'storage', 'app', 'benchmark', 'auth.json');
    // Coba beberapa path
    const candidates = [
        authPath,
        path.join(__dirname, '..', 'storage', 'app', 'benchmark', 'auth.json'),
        '/var/www/storage/app/benchmark/auth.json', // Docker path
    ];

    for (const p of candidates) {
        if (fs.existsSync(p)) {
            return JSON.parse(fs.readFileSync(p, 'utf-8'));
        }
    }

    throw new Error(
        'auth.json tidak ditemukan!\n' +
        'Jalankan dulu:\n' +
        '  docker exec koladi_app_local php artisan benchmark:token --email=ardhaniishere@gmail.com'
    );
}

/**
 * Kirim 1 HTTP request ke endpoint DSS dan kembalikan metrics.
 */
async function sendRequest(url, cookieHeader, config) {
    const startTime = process.hrtime.bigint();

    let response;
    try {
        response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept':              'application/json',
                'Cookie':              cookieHeader,
                'X-Benchmark-Mode':   '1',
                'X-Benchmark-Config': config,
                'X-Requested-With':   'XMLHttpRequest',
            },
            redirect: 'follow',
        });
    } catch (err) {
        throw new Error(`HTTP request failed: ${err.message}`);
    }

    const endTime  = process.hrtime.bigint();
    const elapsedMs = Number(endTime - startTime) / 1_000_000;

    // Baca debug headers dari middleware
    const queryCount     = parseInt(response.headers.get('x-debug-query-count')     ?? '-1', 10);
    const memoryMb       = parseFloat(response.headers.get('x-debug-memory-mb')     ?? '0');
    const cacheHit       = response.headers.get('x-debug-cache-hit')                ?? 'false';
    const cacheHitRatio  = parseFloat(response.headers.get('x-debug-cache-hit-ratio') ?? '0');

    // Validasi response sukses
    const body = await response.json().catch(() => null);
    if (!body || !body.success) {
        const errMsg = body?.message ?? `HTTP ${response.status}`;
        throw new Error(`Response tidak sukses: ${errMsg}`);
    }

    return {
        response_time_ms: Math.round(elapsedMs * 100) / 100,
        query_count:      queryCount,
        memory_mb:        memoryMb,
        cache_hit:        cacheHit === 'true',
        cache_hit_ratio:  cacheHitRatio,
    };
}

/**
 * Flush cache relevan untuk 1 workspace + endpoint via Artisan command.
 * Dipanggil sebelum setiap repetisi valid pada kondisi cache_miss.
 */
async function flushDssCache(workspaceId, config, dockerContainer) {
    const { execSync } = require('child_process');

    const phpCode = `
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Cache;

$workspaceId = '${workspaceId}';
$periodStart = '${PERIOD_START}';
$periodEnd   = '${PERIOD_END}';

if ('${config}' === 'optimized') {
    $keys = Cache::getRedis()->keys("*{$workspaceId}*");
    $count = 0;
    foreach ($keys as $k) {
        Cache::getRedis()->del($k);
        $count++;
    }
    DB::table('workspace_performance_snapshots')
        ->where('workspace_id', $workspaceId)
        ->where('period_start', $periodStart)
        ->where('period_end', $periodEnd)
        ->delete();
    echo "Flushed: " . $count . " Redis keys + snapshot";
} else {
    $deleted = DB::table('workspace_performance_snapshots')
        ->where('workspace_id', $workspaceId)
        ->delete();
    echo "Flushed: " . $deleted . " snapshots from DB";
}
    `.replace(/\n/g, ' ').replace(/"/g, '\\"').replace(/\$/g, '\\$').trim();

    try {
        const cmd = `docker exec ${dockerContainer} php artisan tinker --execute="${phpCode}"`;
        execSync(cmd, { stdio: 'pipe', timeout: 30000 });
    } catch (err) {
        console.warn(`    ⚠️  Cache flush warning: ${err.message.substring(0, 100)}`);
    }
}

/**
 * Populate cache (warm-up awal untuk kondisi cache_hit).
 */
async function warmUpCache(url, cookieHeader, config) {
    try {
        await sendRequest(url, cookieHeader, config);
    } catch (err) {
        console.warn(`    ⚠️  Warm-up request failed: ${err.message}`);
    }
}

/**
 * Escape CSV field.
 */
function csvField(val) {
    const s = String(val ?? '');
    if (s.includes(',') || s.includes('"') || s.includes('\n')) {
        return `"${s.replace(/"/g, '""')}"`;
    }
    return s;
}

/**
 * Tulis satu baris ke CSV.
 */
function appendCsvRow(csvPath, row) {
    const line = [
        csvField(row.volume_data),
        csvField(row.endpoint),
        csvField(row.configuration),
        csvField(row.cache_condition),
        csvField(row.repetition),
        csvField(row.response_time_ms),
        csvField(row.query_count),
        csvField(row.memory_mb),
        csvField(row.cache_hit_ratio),
    ].join(',') + '\n';

    fs.appendFileSync(csvPath, line, 'utf-8');
}

/**
 * Progress bar sederhana.
 */
function progress(label, current, total) {
    const pct = Math.round((current / total) * 100);
    const bar = '█'.repeat(Math.floor(pct / 5)) + '░'.repeat(20 - Math.floor(pct / 5));
    process.stdout.write(`\r    [${bar}] ${pct}% ${label}`);
    if (current === total) process.stdout.write('\n');
}

// ─────────────────────────────────────────────────────────
// MAIN BENCHMARK RUNNER
// ─────────────────────────────────────────────────────────

async function runBenchmark(config) {
    console.log(`\n${'═'.repeat(60)}`);
    console.log(` 🔬 DSS Benchmark — konfigurasi: ${config.toUpperCase()}`);
    console.log(` Metode : Controlled sequential HTTP measurement`);
    console.log(` Dataset: Small(50) | Medium(200) | Large(500)`);
    console.log(`${'═'.repeat(60)}\n`);

    // ─── Load auth config ───
    let authConfig;
    try {
        authConfig = getAuthConfig(__dirname);
    } catch (err) {
        console.error('❌', err.message);
        process.exit(1);
    }

    const cookieName   = authConfig.cookie_name   ?? 'laravel_session';
    const sessionId    = authConfig.session_id;
    const cookieHeader = `${cookieName}=${sessionId}`;
    const dockerContainer = 'koladi_app_local';

    console.log(`✅ Auth loaded  : ${authConfig.user_email}`);
    console.log(`✅ Cookie       : ${cookieName}`);
    console.log(`✅ Base URL     : ${BASE_URL}`);

    // ─── Persiapkan output CSV ───
    const benchmarkDir = path.join(__dirname, '..', 'storage', 'app', 'benchmark');
    fs.mkdirSync(benchmarkDir, { recursive: true });

    const csvPath = path.join(benchmarkDir, `results_${config}.csv`);
    const csvHeader = 'volume_data,endpoint,configuration,cache_condition,repetition,response_time_ms,query_count,memory_mb,cache_hit_ratio\n';

    // Reset CSV (tulis ulang dari awal)
    fs.writeFileSync(csvPath, csvHeader, 'utf-8');
    console.log(`📄 Output CSV   : ${csvPath}\n`);

    // ─── Loop kombinasi ───
    const cacheConditions = ['cache_miss', 'cache_hit'];
    const totalCombinations = Object.keys(WORKSPACES).length * ENDPOINTS.length * cacheConditions.length;
    let combinationsDone = 0;

    for (const [volumeKey, workspace] of Object.entries(WORKSPACES)) {
        for (const endpoint of ENDPOINTS) {
            for (const cacheCondition of cacheConditions) {

                combinationsDone++;
                const combinationLabel = `[${combinationsDone}/${totalCombinations}] ` +
                    `${volumeKey.toUpperCase()} | ${endpoint.label} | ${cacheCondition}`;

                console.log(`\n${'─'.repeat(60)}`);
                console.log(` ${combinationLabel}`);
                console.log(`${'─'.repeat(60)}`);

                // Bangun URL dengan parameter workspace dan periode
                const url = `${BASE_URL}${endpoint.path}?workspace_id=${workspace.id}&start=${PERIOD_START}&end=${PERIOD_END}`;

                // ─── 1. Flush cache sebelum mulai kombinasi ini ───
                process.stdout.write('  🧹 Flushing cache...');
                await flushDssCache(workspace.id, config, dockerContainer);
                console.log(' ✓');
                await sleep(300);

                // ─── 2. Untuk cache_hit: populasi cache dulu ───
                if (cacheCondition === 'cache_hit') {
                    process.stdout.write('  🔥 Populating cache (pre-warm)...');
                    await warmUpCache(url, cookieHeader, config);
                    console.log(' ✓');
                    await sleep(200);
                }

                // ─── 3. Warm-up requests (tidak masuk CSV) ───
                console.log(`  ⏩ Warm-up (${WARM_UP_REPS} requests, tidak dicatat)...`);
                for (let w = 1; w <= WARM_UP_REPS; w++) {
                    try {
                        await sendRequest(url, cookieHeader, config);
                    } catch (err) {
                        console.warn(`     warm-up #${w} gagal: ${err.message}`);
                    }
                    await sleep(100);
                }
                console.log('  ✓ Warm-up selesai');

                // ─── 4. Valid repetitions (masuk CSV) ───
                console.log(`  📊 Pengukuran valid (${VALID_REPS} repetisi)...`);

                for (let rep = 1; rep <= VALID_REPS; rep++) {
                    // Untuk cache_miss: flush sebelum SETIAP request valid
                    if (cacheCondition === 'cache_miss') {
                        await flushDssCache(workspace.id, config, dockerContainer);
                        await sleep(100);
                    }

                    let metrics;
                    try {
                        metrics = await sendRequest(url, cookieHeader, config);
                    } catch (err) {
                        console.error(`\n  ❌ Repetisi ${rep} gagal: ${err.message}`);
                        // Tulis baris dengan nilai -1 sebagai marker error
                        appendCsvRow(csvPath, {
                            volume_data:     volumeKey,
                            endpoint:        endpoint.label,
                            configuration:   config,
                            cache_condition: cacheCondition,
                            repetition:      rep,
                            response_time_ms: -1,
                            query_count:     -1,
                            memory_mb:       -1,
                            cache_hit_ratio: -1,
                        });
                        await sleep(500);
                        continue;
                    }

                    // Tulis ke CSV
                    appendCsvRow(csvPath, {
                        volume_data:     volumeKey,
                        endpoint:        endpoint.label,
                        configuration:   config,
                        cache_condition: cacheCondition,
                        repetition:      rep,
                        response_time_ms: metrics.response_time_ms,
                        query_count:     metrics.query_count,
                        memory_mb:       metrics.memory_mb,
                        cache_hit_ratio: metrics.cache_hit_ratio,
                    });

                    // Log singkat per repetisi
                    console.log(
                        `    rep ${String(rep).padStart(2, ' ')}/${VALID_REPS}` +
                        ` | ${String(metrics.response_time_ms).padStart(8, ' ')} ms` +
                        ` | ${String(metrics.query_count).padStart(3, ' ')} queries` +
                        ` | ${String(metrics.memory_mb).padStart(6, ' ')} MB` +
                        ` | hit_ratio: ${metrics.cache_hit_ratio}%` +
                        ` | cache: ${metrics.cache_hit ? '✓' : '✗'}`
                    );

                    // Delay antar repetisi (jaga konsistensi kondisi sistem)
                    await sleep(200);
                }

                console.log(`  ✅ Kombinasi selesai`);
            }
        }
    }

    console.log(`\n${'═'.repeat(60)}`);
    console.log(` ✅ Benchmark SELESAI`);
    console.log(` 📄 Hasil tersimpan di: ${csvPath}`);
    console.log(` 📊 Total pengukuran  : ${Object.keys(WORKSPACES).length} vol × ${ENDPOINTS.length} endpoint × ${cacheConditions.length} cache × ${VALID_REPS} reps = ${Object.keys(WORKSPACES).length * ENDPOINTS.length * cacheConditions.length * VALID_REPS} baris`);
    console.log(`${'═'.repeat(60)}\n`);
}

// ─────────────────────────────────────────────────────────
// ENTRY POINT
// ─────────────────────────────────────────────────────────

async function main() {
    await loadDeps();

    const { Command } = require('commander');
    const program = new Command();

    program
        .name('benchmark-dss')
        .description('Controlled DSS benchmark untuk penelitian Koladi')
        .requiredOption(
            '--configuration <type>',
            'Konfigurasi sistem: baseline | optimized',
            (val) => {
                if (!['baseline', 'optimized'].includes(val)) {
                    console.error('❌ --configuration harus: baseline atau optimized');
                    process.exit(1);
                }
                return val;
            }
        )
        .option('--dry-run', 'Hanya tampilkan konfigurasi, tidak eksekusi benchmark')
        .parse(process.argv);

    const opts   = program.opts();
    const config = opts.configuration;

    if (opts.dryRun) {
        console.log('=== DRY RUN ===');
        console.log('Config      :', config);
        console.log('Workspaces  :', Object.keys(WORKSPACES).join(', '));
        console.log('Endpoints   :', ENDPOINTS.map(e => e.label).join(', '));
        console.log('Total reps  :', Object.keys(WORKSPACES).length * ENDPOINTS.length * 2 * VALID_REPS);
        return;
    }

    await runBenchmark(config);
}

main().catch(err => {
    console.error('❌ Fatal error:', err.message);
    process.exit(1);
});
