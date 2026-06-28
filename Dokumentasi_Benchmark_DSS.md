# Dokumentasi Benchmark Performa DSS Koladi

Dokumen ini menjelaskan spesifikasi teknis dan panduan penggunaan **Controlled Before-After Benchmarking Suite** yang dirancang khusus untuk penelitian optimasi *Decision Support System* (DSS) pada aplikasi Koladi. 

Benchmarking ini **bukanlah** alat *load testing* atau *stress testing*, melainkan alat ukur HTTP sekuensial terkontrol untuk mengukur efisiensi eksekusi algoritma dan efisiensi *query* database.

---

## 1. Jaminan Spesifikasi Penelitian

Pengujian ini menggunakan *middleware* di level Laravel dan *runner script* di level Node.js yang mematuhi 7 prinsip desain penelitian Anda:

1.  **Isolasi Penghapusan Cache (Cache Miss)**
    Script **tidak pernah** melakukan *truncate/drop database* PostgreSQL atau menghapus data eksperimen tugas. Proses *flush cache* dijamin aman:
    *   **Baseline:** Hanya menghapus spesifik baris pada tabel `workspace_performance_snapshots` untuk *workspace* dan periode yang sedang diuji.
    *   **Optimized:** Hanya menghapus *key/tag* Redis yang berelasi spesifik dengan DSS, *workspace*, dan periode yang diuji.

2.  **Presisi Redis Cache Hit Ratio**
    Untuk pengukuran Redis, *script* **tidak** menggunakan angka `INFO` Redis kumulatif mentah. Middleware mencatat `keyspace_hits` dan `keyspace_misses` **sebelum** dan **sesudah** setiap *request* tunggal, mencari selisih (*delta*), dan menghitung secara presisi: `(delta_hits / (delta_hits + delta_misses)) x 100`.

3.  **Presisi Baseline Cache Hit**
    Untuk *baseline*, status *cache hit* dibaca langsung dari laporan objek `SnapshotManager` di dalam kode (melalui flag `cached: true/false`), dan bukan menebak-nebak dari angka *query count*.

4.  **Akurasi Pemetaan Skenario Volume Data**
    Kombinasi *looping* terikat langsung pada 3 *workspace* pengujian Anda:
    *   **Small:** `Div. Desain` (50 tugas, 5 anggota)
    *   **Medium:** `HR Keuangan` (200 tugas, 15 anggota)
    *   **Large:** `MOU PT Xyz` (500 tugas, 30 anggota)

5.  **Konsistensi Parameter *Paired-Sample***
    Baik *runner* untuk *baseline* maupun *optimized* menggunakan matriks *looping* yang **100% identik** (parameter, URL, *workspace*, periode waktu, urutan eksekusi). Hal ini menjamin bahwa output CSV pada baris ke-$N$ di *baseline* adalah pasangan sempurna (dependen) untuk baris ke-$N$ di *optimized* guna keperluan Uji-T Berpasangan (*Paired-Sample t-test*).

6.  **Pengukuran Response Time End-to-End**
    Nilai `response_time_ms` diukur langsung dari perspektif klien (Node.js) sebagai total durasi HTTP Request *end-to-end*, menggunakan fungsi High-Resolution Time (`process.hrtime`), menghasilkan akurasi tinggi tanpa *overhead load testing*.

7.  **Pemisahan Data Warm-up**
    *Runner* secara sadar akan melakukan 3 *request warm-up* di awal untuk menstabilkan OPcache dan *network stack*. Ketiga *request* ini **dibuang** dan tidak akan masuk ke dalam output CSV. File CSV murni hanya mencatat 10 repetisi valid per kombinasi.

---

## 2. Struktur File

Berikut adalah daftar *file* yang ditambahkan/dimodifikasi ke dalam aplikasi Koladi:

*   **`bootstrap/app.php`** (Modifikasi) — Mendaftarkan alias `benchmark` untuk middleware *instrumentation*.
*   **`app/Http/Middleware/BenchmarkInstrumentation.php`** (Baru) — Middleware pengukur metrik, delta Redis, dan listener SQL.
*   **`app/Console/Commands/GenerateBenchmarkToken.php`** (Baru) — *Command line* otentikasi simulasi *session*.
*   **`scripts/package.json`** (Baru) — Kumpulan dependensi lokal untuk skrip benchmark (*node-fetch*, *commander*).
*   **`scripts/benchmark-dss.js`** (Baru) — *Engine* eksekusi HTTP sekuensial.
*   **`storage/app/benchmark/`** (Folder Baru) — Lokasi penyimpanan *session* JSON dan output file CSV.

---

## 3. Prasyarat & Persiapan

Sebelum menjalankan tes, pastikan hal-hal berikut:
1.  **Docker Berjalan**: Lingkungan `koladi_app_local`, `koladi_db_local`, dan `koladi_redis_local` harus aktif.
2.  **Instalasi Dependensi Node.js**:
    Pindah ke folder `scripts` dan *install module* yang dibutuhkan (hanya perlu dilakukan sekali).
    ```bash
    cd scripts
    npm install
    cd ..
    ```

---

## 4. Cara Penggunaan

### Langkah 1: Generate Session Token
Anda wajib membuat tiket otentikasi (`auth.json`) agar *script* penguji mendapatkan izin masuk (Login simulasi ke *workspace*).
Jalankan perintah ini dari *root* direktori `koladi-laravel`:

```bash
docker exec koladi_app_local php artisan benchmark:token --email=ardhaniishere@gmail.com
```

### Langkah 2: Eksekusi Benchmark Baseline
Pastikan *source code* Koladi Anda saat ini masih berada pada *logic* lama (menggunakan tabel `workspace_performance_snapshots` dan belum menggunakan *Eager Loading* `checklists`).

Jalankan pengujian:
```bash
npm run benchmark:baseline --prefix scripts
```
*Tunggu hingga proses selesai. Anda dapat memantau bar progres di terminal.*

### Langkah 3: Eksekusi Benchmark Optimized
Setelah *source code* DSS Anda ubah ke versi perbaikan yang baru (implementasi Redis Observer & perbaikan ORM *MetricsCalculator*), jalankan pengujian versi *Optimized*:

```bash
npm run benchmark:optimized --prefix scripts
```

---

## 5. Memahami Hasil Output CSV

Setelah selesai, dua file eksperimen siap digunakan:
1.  `storage/app/benchmark/results_baseline.csv`
2.  `storage/app/benchmark/results_optimized.csv`

**Struktur Kolom CSV:**

| Nama Kolom | Penjelasan |
| :--- | :--- |
| `volume_data` | `small` (50 task), `medium` (200 task), `large` (500 task). |
| `endpoint` | `suggestions` atau `modal-data`. |
| `configuration` | `baseline` atau `optimized`. |
| `cache_condition` | `cache_miss` atau `cache_hit`. |
| `repetition` | Nomor repetisi valid (1 - 10). |
| `response_time_ms` | Durasi respons HTTP *end-to-end* dalam milidetik. |
| `query_count` | Total eksekusi *query* PostgreSQL pada 1 siklus *request* API. |
| `memory_mb` | Puncak pemakaian RAM PHP per *request*. |
| `cache_hit_ratio` | Persentase *cache hit*. Pada *Optimized* (Redis), ini adalah rasio delta `keyspace`. Pada *Baseline*, ini adalah konversi boolean dari `SnapshotManager`. |

> [!WARNING]
> **Catatan Penting untuk Baseline Endpoint `modal-data`**
> Nilai `cache_hit_ratio = 100` pada konfigurasi *baseline* untuk *endpoint* `modal-data` hanya berarti bahwa data utama DSS berhasil dibaca dari `SnapshotManager`. Ini **bukanlah full database bypass**. Komponen lain di `modal-data` (seperti *urgent tasks* dan distribusi beban kerja) masih tetap melakukan instruksi `Task::where(...)` ke PostgreSQL. Hal inilah yang menyebabkan mengapa angka `query_count` pada *baseline* tetap tinggi meskipun terjadi *cache hit*, dan membuktikan mengapa penelitian ini diperlukan.
