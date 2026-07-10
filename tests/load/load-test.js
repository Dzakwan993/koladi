// ============================================================
// KOLADI — Load Testing Script (k6)
// Skenario : TC-LOAD-01 (50 VU) & TC-LOAD-02 (100 VU / Stress)
// Target   : POST http://localhost:8000/masuk  (halaman login)
// Jalankan : k6 run load-test.js
// ============================================================

import http from "k6/http";
import { check, sleep } from "k6";
import { Trend, Rate, Counter } from "k6/metrics";

// ── Custom Metrics ──────────────────────────────────────────
const loginDuration = new Trend("login_duration", true); // durasi khusus endpoint login
const errorRate     = new Rate("error_rate");             // proporsi request gagal
const successCount  = new Counter("success_count");       // jumlah request sukses

// ── Konfigurasi Skenario ────────────────────────────────────
// Tahap 1-3  → TC-LOAD-01: Uji beban normal 50 VU selama 30 detik
// Tahap 4-6  → TC-LOAD-02: Uji beban stress ramp-up ke 100 VU
export const options = {
  stages: [
    // ---- TC-LOAD-01: Normal Load (50 VU) ----
    { duration: "10s", target: 50 },  // ramp-up ke 50 VU dalam 10 detik
    { duration: "30s", target: 50 },  // pertahankan 50 VU selama 30 detik
    { duration: "10s", target: 0  },  // ramp-down ke 0 VU

    // ---- TC-LOAD-02: Stress Load (100 VU) ----
    { duration: "10s", target: 100 }, // ramp-up ke 100 VU dalam 10 detik
    { duration: "30s", target: 100 }, // pertahankan 100 VU selama 30 detik
    { duration: "10s", target: 0  },  // ramp-down ke 0 VU
  ],

  // ── Thresholds (Kriteria Lulus) ────────────────────────────
  thresholds: {
    // TC-LOAD-01: P95 harus di bawah 2 detik (2000ms)
    "http_req_duration{scenario:normal}": ["p(95)<2000"],

    // Error rate keseluruhan harus di bawah 1%
    "error_rate": ["rate<0.01"],

    // Durasi khusus login P95 < 2000ms
    "login_duration": ["p(95)<2000"],

    // HTTP request duration global
    "http_req_duration": ["p(95)<2000", "p(99)<3000"],
  },
};

// ── Konfigurasi URL & Header ────────────────────────────────
const BASE_URL = "http://localhost:8000";

// ── Fungsi Utama (dijalankan tiap VU setiap iterasi) ────────
export default function () {

  // ── LANGKAH 1: GET halaman login untuk ambil CSRF token ───
  const getRes = http.get(`${BASE_URL}/masuk`, {
    tags: { name: "GET /masuk" },
  });

  check(getRes, {
    "GET /masuk → status 200": (r) => r.status === 200,
    "Halaman login termuat":   (r) => r.body.includes("masuk") || r.body.includes("login"),
  });

  // Ambil CSRF token dari meta tag atau input hidden
  // Laravel menyisipkan token di dalam <meta name="csrf-token"> atau field _token
  let csrfToken = "";
  const metaMatch  = getRes.body.match(/<meta name="csrf-token" content="([^"]+)"/);
  const inputMatch = getRes.body.match(/name="_token"[^>]*value="([^"]+)"/);

  if (metaMatch)       csrfToken = metaMatch[1];
  else if (inputMatch) csrfToken = inputMatch[1];

  // Ambil cookie session yang diberikan server
  const cookies = getRes.cookies;

  sleep(0.5); // jeda singkat sebelum POST (simulasi pengguna mengetik)

  // ── LANGKAH 2: POST kredensial login ──────────────────────
  const payload = {
    _token:   csrfToken,
    email:    "admin@koladi.com",
    password: "adminkoladi123$%^",
  };

  const headers = {
    "Content-Type":     "application/x-www-form-urlencoded",
    "X-CSRF-TOKEN":     csrfToken,
    "Accept":           "text/html,application/xhtml+xml",
  };

  const postRes = http.post(`${BASE_URL}/masuk`, payload, {
    headers:       headers,
    redirects:     5,          // ikuti redirect ke /dashboard
    tags:          { name: "POST /masuk", scenario: "normal" },
  });

  // Catat durasi POST ke custom metric
  loginDuration.add(postRes.timings.duration);

  // ── LANGKAH 3: Validasi Response ──────────────────────────
  const loginSuccess =
    postRes.status === 200 &&
    (postRes.url.includes("/dashboard") || postRes.body.includes("dashboard"));

  const loginChecks = check(postRes, {
    "POST /masuk → status 200 atau 302": (r) =>
      r.status === 200 || r.status === 302,
    "Redirect ke /dashboard setelah login": (r) =>
      r.url.includes("/dashboard") || r.status === 302,
    "Response time < 2000ms": (r) =>
      r.timings.duration < 2000,
  });

  // Update custom metrics
  errorRate.add(!loginChecks);
  if (loginChecks) successCount.add(1);

  sleep(1); // jeda 1 detik antar iterasi (simulasi think time pengguna)
}

// ── Lifecycle: Setup (opsional, dijalankan sekali sebelum tes) ─
export function setup() {
  console.log("=== Koladi Load Test Dimulai ===");
  console.log(`Target URL : ${BASE_URL}/masuk`);
  console.log("Skenario   : TC-LOAD-01 (50 VU) + TC-LOAD-02 (100 VU)");
  console.log("================================================");

  // Periksa apakah server dapat dijangkau
  const healthCheck = http.get(`${BASE_URL}/masuk`);
  if (healthCheck.status !== 200) {
    console.error(`Server tidak dapat dijangkau! Status: ${healthCheck.status}`);
  } else {
    console.log("Server OK — memulai pengujian beban...");
  }
}

// ── Lifecycle: Teardown (opsional, dijalankan sekali setelah tes) ─
export function teardown(data) {
  console.log("=== Koladi Load Test Selesai ===");
  console.log("Periksa ringkasan metrik di atas untuk hasil TC-LOAD-01 & TC-LOAD-02.");
}
