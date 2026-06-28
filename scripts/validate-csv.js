const fs = require('fs');

const file = process.argv[2];
if (!file) {
    console.error("Usage: node validate-csv.js <path-to-csv>");
    process.exit(1);
}

if (!fs.existsSync(file)) {
    console.error(`File not found: ${file}`);
    process.exit(1);
}

const content = fs.readFileSync(file, 'utf-8').trim().split('\n');
const header = content[0];
const rows = content.slice(1);

console.log(`\n=== VALIDASI CSV: ${file} ===`);
console.log(`Total baris (tanpa header): ${rows.length}`);

if (rows.length !== 120) {
    console.error(`❌ GAGAL: Jumlah baris harus 120, tapi ditemukan ${rows.length}.`);
    process.exit(1);
} else {
    console.log(`✅ SUCCESS: Jumlah baris valid (120).`);
}

const combinations = {};
let hasWarmUp = false;

rows.forEach((row, idx) => {
    const cols = row.split(',');
    // volume_data,endpoint,configuration,cache_condition,repetition,response_time_ms,query_count,memory_mb,cache_hit_ratio
    const volume = cols[0];
    const endpoint = cols[1];
    const cacheCond = cols[3];
    const rep = parseInt(cols[4]);
    
    if (rep <= 0) {
        hasWarmUp = true;
    }

    const key = `${volume}|${endpoint}|${cacheCond}`;
    if (!combinations[key]) {
        combinations[key] = [];
    }
    combinations[key].push(rep);
});

if (hasWarmUp) {
    console.error(`❌ GAGAL: Ditemukan repetition <= 0 (warm-up masuk ke CSV!).`);
    process.exit(1);
} else {
    console.log(`✅ SUCCESS: Tidak ada data warm-up di dalam CSV.`);
}

const expectedCombinations = 3 * 2 * 2; // 12
const keys = Object.keys(combinations);
console.log(`Total kombinasi ditemukan: ${keys.length}`);

if (keys.length !== expectedCombinations) {
    console.error(`❌ GAGAL: Jumlah kombinasi harus ${expectedCombinations}, tapi ditemukan ${keys.length}.`);
    process.exit(1);
} else {
    console.log(`✅ SUCCESS: Terdapat 12 kombinasi unik (3 Volume x 2 Endpoint x 2 Cache).`);
}

let repsValid = true;
keys.forEach(k => {
    const reps = combinations[k].sort((a,b) => a-b);
    const expected = [1,2,3,4,5,6,7,8,9,10];
    if (reps.join(',') !== expected.join(',')) {
        repsValid = false;
        console.error(`❌ GAGAL: Kombinasi ${k} memiliki repetisi yang tidak lengkap/berantakan: ${reps.join(',')}`);
    }
});

if (repsValid) {
    console.log(`✅ SUCCESS: Setiap kombinasi memiliki repetisi 1 sampai 10 secara lengkap.`);
} else {
    process.exit(1);
}

console.log(`\n🎉 SEMUA VALIDASI BERHASIL! Format instrumen benchmark sudah 100% tepat.`);
