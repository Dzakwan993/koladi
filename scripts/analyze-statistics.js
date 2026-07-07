const fs = require('fs');
const path = require('path');

// File paths
const baselinePath = path.join(__dirname, '../storage/app/benchmark/results_baseline_fixed.csv');
const optimizedPath = path.join(__dirname, '../storage/app/benchmark/results_optimized.csv');
const outputPath = path.join(__dirname, '../storage/app/benchmark/results_statistical_tests.csv');

/**
 * Gamma function approximation or exact values for Student's t PDF calculation.
 * For df = 9, the constant factor C = Gamma((df+1)/2) / (sqrt(df*pi) * Gamma(df/2))
 * C = Gamma(5) / (3 * sqrt(pi) * Gamma(4.5))
 * Gamma(5) = 24
 * Gamma(4.5) = 105 * sqrt(pi) / 16
 * C = 24 / (3 * sqrt(pi) * 105 * sqrt(pi) / 16) = 128 / (105 * pi)
 */
const DF = 9;
const CONST_FACTOR = 128.0 / (105.0 * Math.PI);

/**
 * Probability Density Function (PDF) of Student's t-distribution for df = 9
 */
function tPDF(x) {
    return CONST_FACTOR * Math.pow(1 + (x * x) / DF, -(DF + 1) / 2);
}

/**
 * Cumulative Distribution Function (CDF) of Student's t-distribution for df = 9
 * Calculated using numerical integration (Trapezoidal rule)
 */
function tCDF(t) {
    if (t < 0) return 1 - tCDF(-t);
    if (t === 0) return 0.5;
    if (t > 40) return 1.0; // Avoid unnecessary integration for highly significant t-values

    let sum = 0;
    const steps = 10000;
    const dt = t / steps;
    
    for (let i = 0; i < steps; i++) {
        const x1 = i * dt;
        const x2 = (i + 1) * dt;
        sum += 0.5 * (tPDF(x1) + tPDF(x2)) * dt;
    }
    
    return 0.5 + sum;
}

/**
 * Performs a paired-sample t-test on two arrays of size 10.
 * Returns the two-tailed p-value.
 */
function calculatePairedPValue(baselineVal, optimizedVal) {
    const n = baselineVal.length;
    if (n !== 10) {
        throw new Error(`Invalid sample size: expected 10, got ${n}`);
    }

    const diffs = [];
    let sumDiff = 0;
    let allZero = true;
    let allConstant = true;

    for (let i = 0; i < n; i++) {
        const d = baselineVal[i] - optimizedVal[i];
        diffs.push(d);
        sumDiff += d;
        if (d !== 0) {
            allZero = false;
        }
        if (i > 0 && d !== diffs[0]) {
            allConstant = false;
        }
    }

    // Rule 7: If all differences are exactly 0, p-value is 1.000000
    if (allZero) {
        return 1.0;
    }

    // Rule 8: If all differences are constant but non-zero, sd is 0.
    // This represents a deterministic shift, meaning the difference is 100% certain (p-value = 0.000000).
    if (allConstant) {
        return 0.0;
    }

    const meanDiff = sumDiff / n;

    let sumSqDiff = 0;
    for (let i = 0; i < n; i++) {
        sumSqDiff += Math.pow(diffs[i] - meanDiff, 2);
    }
    const variance = sumSqDiff / (n - 1);
    const sd = Math.sqrt(variance);

    const tStatistic = meanDiff / (sd / Math.sqrt(n));
    const twoTailedPValue = 2 * (1 - tCDF(Math.abs(tStatistic)));

    return Math.max(0.0, Math.min(1.0, twoTailedPValue));
}

/**
 * Parses a CSV file into structured rows
 */
function parseCSV(filePath) {
    if (!fs.existsSync(filePath)) {
        throw new Error(`File not found: ${filePath}`);
    }
    const content = fs.readFileSync(filePath, 'utf-8').trim();
    const lines = content.split('\n');
    const header = lines[0].split(',').map(h => h.trim());
    
    return lines.slice(1).map((line, idx) => {
        const values = line.split(',').map(v => v.trim());
        const row = {};
        header.forEach((col, i) => {
            row[col] = values[i];
        });
        row._lineNum = idx + 2;
        return row;
    });
}

function main() {
    console.log('📊 Starting Statistical Analysis on DSS Benchmark Data...');
    
    const baselineRows = parseCSV(baselinePath);
    const optimizedRows = parseCSV(optimizedPath);

    // Group rows by combination key
    const makeGroupKey = (row) => `${row.volume_data}|${row.endpoint}|${row.cache_condition}`;
    
    const groupMap = {};
    
    // Sort logic helper
    const volOrder = { 'small': 1, 'medium': 2, 'large': 3 };
    const epOrder = { 'suggestions': 1, 'modal-data': 2 };
    const cacheOrder = { 'cache_miss': 1, 'cache_hit': 2 };

    const combinations = [];
    
    // Populate all combinations in sorted order
    for (const vol of ['small', 'medium', 'large']) {
        for (const ep of ['suggestions', 'modal-data']) {
            for (const cache of ['cache_miss', 'cache_hit']) {
                const key = `${vol}|${ep}|${cache}`;
                groupMap[key] = {
                    volume_data: vol,
                    endpoint: ep,
                    cache_condition: cache,
                    baseline: {},
                    optimized: {}
                };
                combinations.push(key);
            }
        }
    }

    // Populate baseline data
    baselineRows.forEach(row => {
        const key = makeGroupKey(row);
        if (groupMap[key]) {
            const rep = parseInt(row.repetition);
            groupMap[key].baseline[rep] = {
                rt: parseFloat(row.response_time_ms),
                qc: parseFloat(row.query_count),
                mem: parseFloat(row.memory_mb)
            };
        }
    });

    // Populate optimized data
    optimizedRows.forEach(row => {
        const key = makeGroupKey(row);
        if (groupMap[key]) {
            const rep = parseInt(row.repetition);
            groupMap[key].optimized[rep] = {
                rt: parseFloat(row.response_time_ms),
                qc: parseFloat(row.query_count),
                mem: parseFloat(row.memory_mb)
            };
        }
    });

    // Write header
    const outputLines = ['volume_data,endpoint,cache_condition,n,p_response_time,p_query_count,p_memory'];

    combinations.forEach(key => {
        const group = groupMap[key];
        const rtBaseline = [];
        const rtOptimized = [];
        const qcBaseline = [];
        const qcOptimized = [];
        const memBaseline = [];
        const memOptimized = [];

        // Validate that repetitions 1-10 exist for both baseline and optimized
        for (let rep = 1; rep <= 10; rep++) {
            const b = group.baseline[rep];
            const o = group.optimized[rep];

            if (!b) {
                throw new Error(`Missing repetition ${rep} in baseline for combination ${key}`);
            }
            if (!o) {
                throw new Error(`Missing repetition ${rep} in optimized for combination ${key}`);
            }

            rtBaseline.push(b.rt);
            rtOptimized.push(o.rt);
            qcBaseline.push(b.qc);
            qcOptimized.push(o.qc);
            memBaseline.push(b.mem);
            memOptimized.push(o.mem);
        }

        // Perform t-tests
        const pResponseTime = calculatePairedPValue(rtBaseline, rtOptimized).toFixed(6);
        const pQueryCount = calculatePairedPValue(qcBaseline, qcOptimized).toFixed(6);
        const pMemory = calculatePairedPValue(memBaseline, memOptimized).toFixed(6);

        outputLines.push(`${group.volume_data},${group.endpoint},${group.cache_condition},10,${pResponseTime},${pQueryCount},${pMemory}`);
    });

    // Write output CSV
    fs.writeFileSync(outputPath, outputLines.join('\n') + '\n');
    console.log(`✅ Success! Statistical test results saved to: ${outputPath}`);
}

try {
    main();
} catch (err) {
    console.error(`❌ Analysis Failed: ${err.message}`);
    process.exit(1);
}
