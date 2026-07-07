Tolong implementasikan konfigurasi optimized penelitian DSS Koladi, jalankan benchmark optimized secara lengkap, validasi hasilnya, dan hasilkan file results_optimized.csv.

Sebelum melakukan perubahan, baca dan gunakan dokumen berikut sebagai konteks utama penelitian:


1. Konteks penelitian terbaru (14 Juni 2026).md
2. Dokumentasi_Benchmark_DSS.md

Ketiga dokumen tersebut sudah tersedia di dalam project. Gunakan dokumen-dokumen tersebut untuk memahami ruang lingkup penelitian, endpoint yang diuji, implementasi baseline, rancangan optimized, skenario benchmark, instrumentasi, serta format CSV. Jika terdapat ketidaksesuaian antara dokumentasi dan implementasi aktual, laporkan terlebih dahulu dan gunakan rancangan penelitian terbaru sebagai acuan.

Konteks penting:
- Baseline fixed telah berhasil diukur dan disimpan sebagai results_baseline_fixed.csv.
- Jangan mengubah atau menimpa file baseline fixed.
- Perbaikan propagasi flag cached pada DSSService::getModalData() dan perbaikan flush cache database pada benchmark-dss.js harus dipertahankan.
- Konfigurasi optimized harus dibandingkan dengan baseline menggunakan dataset, endpoint, parameter request, kondisi cache, urutan repetisi, dan lingkungan Docker yang sama.
- Penelitian ini adalah controlled sequential benchmarking, bukan load testing atau concurrency testing.

Endpoint yang diuji:
1. GET /statistik/suggestions
2. GET /statistik/modal-data

Implementasi optimized yang diperlukan:

A. Redis Cache-Aside
1. Ganti jalur database-backed snapshot pada konfigurasi optimized dengan Redis sebagai in-memory cache store.
2. Terapkan pola Cache-Aside:
   - Periksa key Redis pada awal request.
   - Jika hit, kembalikan data dari Redis.
   - Jika miss, jalankan pipeline DSS, simpan hasil ke Redis, lalu kembalikan respons.
3. Untuk endpoint modal-data, cache harus mencakup full response:
   - metrik utama DSS,
   - suggestions,
   - trends,
   - urgent tasks,
   - workload distribution.
4. Pastikan cache hit pada modal-data tidak lagi menghitung ulang komponen tersebut.
5. Gunakan key yang deterministik berdasarkan workspace, endpoint, period start, dan period end.
6. Pertahankan TTL sebagai safety net untuk metrik berbasis waktu seperti urgentTaskRatio, overdueRate, dan criticalTaskRatio.

B. Event-Driven Invalidation
1. Pastikan TaskObserver menginvalidasi cache DSS pada event:
   - created,
   - updated,
   - deleted.
2. Pastikan perubahan assignment pada task_assignments juga menginvalidasi cache melalui TaskAssignmentObserver atau mekanisme yang setara.
3. Invalidasi harus dibatasi pada workspace/key yang relevan.
4. Tambahkan atau jalankan pengujian untuk membuktikan bahwa:
   - cache tersedia sebelum mutasi,
   - perubahan task menghapus cache,
   - assign/unassign anggota menghapus cache,
   - request berikutnya menjadi cache miss dan menghasilkan data terbaru.

C. Optimasi Query ORM
1. Refactor getTasksInPeriod() dari:
   ->with(['assignedUsers'])

   menjadi selective eager loading yang hanya mengambil data yang diperlukan:
   ->with([
       'assignedUsers:id',
       'checklists:id,task_id,is_done'
   ])

2. Pastikan foreign key yang diperlukan Eloquent tetap dimuat agar relasi tidak rusak.
3. Hilangkan lazy loading checklists yang menyebabkan N+1 query pada getProgressPercentage().
4. Refactor getWorkloadDistribution() agar tidak menjalankan Task::whereHas() di dalam loop anggota.
5. Gunakan batch retrieval dan eager loading assignedUsers, kemudian hitung distribusi beban kerja melalui in-memory aggregation.
6. Pastikan payload dan hasil perhitungan DSS tetap ekuivalen dengan baseline. Optimasi tidak boleh mengubah makna metrik, suggestions, trends, urgent tasks, atau workload distribution.

D. Instrumentasi Optimized
1. Pertahankan pencatatan:
   - response_time_ms,
   - query_count,
   - memory_mb,
   - cache_hit_ratio.
2. Response time tetap diukur end-to-end dari Node.js benchmark runner.
3. Query count tetap dicatat menggunakan Laravel query listener pada middleware instrumentasi.
4. Memory tetap dicatat menggunakan memory_get_peak_usage(true).
5. Untuk optimized, cache_hit_ratio harus dihitung sesuai dokumentasi menggunakan delta Redis keyspace_hits dan keyspace_misses per request, bukan nilai Redis global mentah.
6. Pastikan aktivitas Redis lain tidak mencemari delta pengukuran.
7. Pastikan response header/debug instrumentation tetap diterima runner script.
8. Jangan menggunakan Laravel Telescope sebagai sumber data utama benchmark. Telescope hanya boleh digunakan untuk profiling dan validasi pola query.

E. Pelaksanaan Benchmark
Jalankan benchmark optimized dengan rancangan berikut:

3 volume data:
- Small: 50 task, 5 anggota
- Medium: 200 task, 15 anggota
- Large: 500 task, 30 anggota

2 endpoint:
- suggestions
- modal-data

2 kondisi cache:
- cache_miss
- cache_hit

10 repetisi valid per kombinasi
3 request warm-up per kombinasi, tidak dimasukkan ke CSV

Total:
3 x 2 x 1 konfigurasi optimized x 2 kondisi cache x 10 repetisi
= 120 baris valid di luar header.

Ketentuan kondisi cache:
- Cache miss: hapus hanya key/tag Redis yang relevan sebelum request valid.
- Cache hit: populasi cache terlebih dahulu menggunakan request yang tidak masuk data analisis, kemudian jalankan request valid dengan parameter yang sama.
- Jangan menghapus atau mengubah dataset task, user, workspace, checklist, atau assignment.
- Jalankan request secara sekuensial, bukan paralel.
- Gunakan seed dan dataset yang sama dengan baseline fixed.
- Jangan mengubah hardware, Docker resource allocation, atau konfigurasi lingkungan selama perbandingan.

F. Output CSV
Simpan hasil sebagai:

storage/app/benchmark/results_optimized.csv

Gunakan kolom yang sama dengan baseline:

volume_data,
endpoint,
configuration,
cache_condition,
repetition,
response_time_ms,
query_count,
memory_mb,
cache_hit_ratio

Nilai configuration harus optimized pada seluruh baris.

G. Validasi Wajib
Setelah benchmark selesai:

1. Jalankan CSV validator.
2. Pastikan tepat 120 baris valid di luar header.
3. Pastikan terdapat 12 kombinasi:
   3 volume x 2 endpoint x 2 kondisi cache.
4. Pastikan setiap kombinasi memiliki repetisi 1–10 tanpa duplikasi atau baris hilang.
5. Pastikan cache miss tercatat sebagai miss dan cache hit tercatat sebagai hit sesuai mekanisme Redis.
6. Pastikan query count, memory, dan response time tidak kosong.
7. Bandingkan struktur results_optimized.csv dengan results_baseline_fixed.csv agar pasangan observasinya identik berdasarkan:
   - volume_data,
   - endpoint,
   - cache_condition,
   - repetition.
8. Lakukan sanity check:
   - response payload optimized ekuivalen secara fungsional dengan baseline,
   - cache hit modal-data tidak lagi menjalankan full pipeline DSS,
   - query count cache hit optimized tidak bertambah mengikuti volume data,
   - invalidasi task dan assignment benar-benar bekerja.
9. Jangan menghapus atau menimpa results_baseline_fixed.csv.

H. Laporan Akhir
Setelah selesai, berikan:

1. Daftar file yang diubah.
2. Ringkasan implementasi Redis Cache-Aside.
3. Ringkasan implementasi event-driven invalidation.
4. Ringkasan optimasi ORM.
5. Hasil pengujian payload equivalence dan cache invalidation.
6. Lokasi results_optimized.csv.
7. Hasil validasi jumlah baris dan kombinasi CSV.
8. Ringkasan mean dan sample standard deviation untuk empat metrik pada masing-masing dari 12 kombinasi.
9. Perbandingan awal terhadap results_baseline_fixed.csv.
10. Semua bug, anomali, atau penyimpangan yang ditemukan selama benchmark.

Jangan membuat atau mengarang angka hasil sebelum benchmark selesai. Jika benchmark gagal, perbaiki penyebabnya dan jalankan ulang dari awal agar seluruh baris CSV berasal dari satu eksperimen yang konsisten.