## Konteks Lengkap Penelitian (Copy-Paste Ready)

---

**KONTEKS PENELITIAN — TUGAS AKHIR (VERSI FINAL)**

**Judul:**
"The System Knows Your Team's Performance, But Takes Forever to Tell You": A Comparative Study of Caching Strategy and Query Optimization on a Decision Support System in a Collaborative Virtual Office Application

**Aplikasi yang Diteliti:**
Koladi — platform collaborative virtual office SaaS berbasis Laravel 12.x, PostgreSQL 15, Eloquent ORM, Redis 7.x, Docker Compose. Multi-tenant architecture.

**Scope Penelitian:**
Fokus eksklusif pada Lapisan 2 DSS (DSS Modal) — kalkulasi yang dipicu saat user menekan "Lihat Tindak Lanjut". Endpoint: `GET /statistik/modal-data` dan `GET /statistik/suggestions`. Lapisan 1 (summary card halaman utama via `calculateRekapKinerja()`) tidak termasuk scope.

**Pipeline Kalkulasi DSS Modal:**
DSSService → SnapshotManager (cache check) → MetricsCalculator (22 metrik mentah) → TrendAnalyzer (perbandingan periode) → RecommendationEngine (critical/warning/positive) → SnapshotManager (cache save). Ditambah `getUrgentTasks()` dan `getWorkloadDistribution()` yang dihitung terpisah di controller.

---

**DUA MASALAH UTAMA (TERVALIDASI DARI KODE)**

**Masalah A — Inefficient Caching Mechanism**

*A1 — Database-backed cache:*
SnapshotManager menyimpan hasil kalkulasi DSS ke tabel relasional `workspace_performance_snapshots`. Setiap request — termasuk cache hit — tetap mengeksekusi SQL query ke database untuk mengecek validitas cache. Tidak ada jalur kode yang bisa bypass database sama sekali. Minimum 4 query dieksekusi bahkan saat cache hit pada `modal-data`, minimum 3 query pada `suggestions` cache hit.

*A2 — Double-call pada getSuggestions (terkonfirmasi dari kode):*
Endpoint `GET /statistik/suggestions` memanggil `getWorkspaceSuggestions()` dua kali dalam satu request. Panggilan pertama di L282, panggilan kedua via `getTopSuggestion()` di L289-293 yang memanggil ulang `getWorkspaceSuggestions()` secara independen. Ini menyebabkan cache check ke database dieksekusi dua kali. Total query cache hit = 3, total query cache miss dengan 500 tasks = ~508.

*A3 — TaskAssignment Blind Spot (terkonfirmasi dari kode):*
Task Observer tidak menangkap perubahan assign/unassign member karena operasi tersebut menyentuh tabel pivot `task_assignments` via `belongsToMany`, bukan model Task. Lebih kritis: operasi delete di `manageTaskAssignments()` menggunakan query builder (`->delete()`), bukan Eloquent model delete — sehingga bahkan TaskAssignment Observer pun tidak akan menangkap operasi delete ini tanpa perbaikan tambahan. Akibatnya metrik `gini`, `tasksPerMember`, `maxLoadRatio` bisa stale hingga TTL habis meskipun event-driven invalidation sudah diterapkan pada Task.

**Masalah B — ORM Layer Inefficiency**

*B1 — Column over-fetching di MetricsCalculator (terkonfirmasi dari kode):*
`getTasksInPeriod()` menggunakan `->with(['assignedUsers'])` tanpa pembatasan kolom. Seluruh kolom tabel `users` dimuat ke memori PHP, padahal `calculateWorkloadMetrics()` hanya mengakses kolom `id`. Ini adalah Unnecessary Data Retrieval (UD) anti-pattern.

*B2 — N+1 query pada getProgressPercentage() (terkonfirmasi dari kode):*
`getTasksInPeriod()` tidak meng-eager-load relasi `checklists`. `calculateProgressMetrics()` memanggil `$task->getProgressPercentage()` per task, yang mengakses `$this->checklists` secara lazy load. Setiap task memicu satu query `SELECT * FROM checklists WHERE task_id = ?`. Untuk 500 tasks = 500 query tambahan per cache miss. Ini adalah N+1 problem yang terkonfirmasi langsung dari kode.

---

**SOLUSI YANG DIIMPLEMENTASIKAN**

*Solusi A — Redis Cache-Aside + Comprehensive Event-Driven Invalidation:*
- Ganti database-backed cache dengan Redis Cache-Aside — hasil DSS disimpan di RAM Redis
- Tambahkan Laravel Observer pada model `Task` untuk invalidasi saat created/updated/deleted
- Tambahkan Laravel Observer pada model `TaskAssignment` untuk menangkap assign/unassign
- Perbaiki query builder delete di `manageTaskAssignments()` menjadi Eloquent model delete (`.get()->each->delete()`) agar TaskAssignment Observer dapat menangkap operasi delete
- TTL tetap dipertahankan sebagai safety net untuk time-based decay pada metrik yang berubah seiring waktu tanpa write operation (urgentTaskRatio, overdueRate, criticalTaskRatio — karena `days_until_due` berubah setiap hari tanpa ada yang mengubah database)

*Solusi B — Selective Eager Loading + N+1 Elimination:*
- Ubah eager loading di `getTasksInPeriod()` dari `->with(['assignedUsers'])` menjadi `->with(['assignedUsers:id', 'checklists:id,task_id,is_done'])` dalam satu perubahan
- Syntax `assignedUsers:id` valid untuk belongsToMany di Laravel tanpa perlu prefix tabel
- `task_id` wajib disertakan di checklists karena merupakan foreign key untuk mapping relasi hasMany
- Perubahan ini mengeliminasi column over-fetching (B1) dan N+1 checklists (B2) sekaligus

---

**EMPAT METRIK PENGUKURAN**

1. Response Time (ms) — Apache JMeter — mengukur dampak Redis cache hit vs database cache, dan eliminasi N+1
2. Query Count (SQL per request) — Laravel Telescope — mengukur eliminasi double-call, database cache check, dan N+1 checklists
3. Memory Utilization (MB) — `memory_get_peak_usage()` + Redis INFO MEMORY — mengukur dampak column restriction pada MetricsCalculator
4. Cache Hit Ratio (%) — Redis INFO STATS — mengukur efektivitas comprehensive event-driven invalidation

**Catatan penting per metrik:**
- Response time: perbaikan paling dramatis di cache miss skenario Large karena eliminasi 500 N+1 queries
- Query count: cache hit `suggestions` turun dari 3 ke 1, cache miss Large turun dari ~508 ke ~7
- Memory utilization: perbaikan terukur tapi modest karena `getUrgentTasks()` punya eager loading tersendiri yang tidak dioptimasi (di luar scope)
- Cache hit ratio: penambahan TaskAssignment Observer berpotensi menurunkan hit ratio dibanding hanya Task Observer, tapi ini trade-off yang disengaja demi akurasi data

---

**METODE PENELITIAN**

Studi eksperimental komparatif before-after. Dua kondisi: baseline (database-backed cache + unrestricted eager loading tanpa checklists) vs optimized (Redis + Task Observer + TaskAssignment Observer + selective eager loading + eager load checklists).

Load testing: Apache JMeter. Data sintetis via Laravel Seeder.

variasi di bawah masih akan ditentukan lagi

Variasi data volume: Small (50 tasks, 5 members), Medium (200 tasks, 15 members), Large (500 tasks, 30 members).

Variasi concurrency: 1, 5, 10 virtual users. Setiap skenario diulang 10 kali. Analisis: paired t-test α=0.05 + persentase improvement.

---

**KRITERIA KEBERHASILAN**

masih diplanning dulu

---

**LINGKUNGAN EKSPERIMEN**

Hardware: Apple M2 Pro, 16GB RAM, 994GB SSD. OS: macOS. Stack: Laravel 12.x, PHP ^8.2, PostgreSQL 15, Redis 7.x (redis:7-alpine), phpredis client, Nginx + PHP-FPM via Docker Compose dalam isolated network koladi_net.

---

**YANG TIDAK TERMASUK SCOPE**

- `getUrgentTasks()` eager loading (akses `name`, `avatar` dari assignedUsers — dibutuhkan untuk tampilan UI)
- `getWorkloadDistribution()` N+1 per member di controller
- Kepuasan pengguna, UX evaluation
- Perubahan pada logika kalkulasi DSS (formula metrik tidak diubah)
- Perubahan public API contract endpoint

---

**TEMUAN KRITIS DARI ANALISIS KODE (UNTUK REFERENSI)**

- `calculateTimeMetrics()` dipanggil dua kali di dalam MetricsCalculator — redundansi komputasi, bukan query (di luar scope tapi documented)
- `getUrgentTasks()` punya N+1 checklists tersendiri via `$task->getProgressPercentage()` di L602 — di luar scope
- `getWorkloadDistribution()` melakukan 1 query per member dalam loop — di luar scope
- Query builder delete di `manageTaskAssignments()` tidak memicu model observer — harus diperbaiki sebagai bagian dari Solusi A