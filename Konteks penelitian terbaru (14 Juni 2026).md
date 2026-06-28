Berikut rangkuman lengkap konteks penelitian kamu.

**Judul Penelitian**

Penelitian kamu berfokus pada optimasi performa jalur DSS pada platform Koladi melalui penerapan Redis Cache-Aside berbasis event-driven invalidation dan optimasi query ORM.

Inti judulnya dapat dipahami sebagai:

> Studi komparatif optimasi caching dan query ORM pada jalur Decision Support System di aplikasi collaborative virtual office berbasis Laravel.

**Objek Penelitian**

Objek penelitian adalah platform **Koladi**, yaitu aplikasi SaaS collaborative virtual office berbasis:
- Laravel 12.x,
- PostgreSQL 15,
- Redis 7.x,
- Docker Compose,
- Eloquent ORM.

Koladi memiliki fitur statistik kinerja yang menjalankan pipeline DSS untuk menghitung metrik produktivitas workspace, seperti progres tugas, distribusi beban kerja, tugas mendesak, dan rekomendasi tindak lanjut.

**Ruang Lingkup Penelitian**

Penelitian tidak mengukur performa seluruh aplikasi Koladi.

Ruang lingkup dibatasi pada **jalur DSS halaman statistik kinerja**, khususnya dua endpoint:

```text
GET /statistik/suggestions
GET /statistik/modal-data
```

Endpoint `suggestions` merepresentasikan jalur pengambilan rekomendasi DSS melalui `SnapshotManager`.

Endpoint `modal-data` merepresentasikan respons DSS yang lebih lengkap karena mencakup:
- metrik utama DSS,
- daftar tugas mendesak,
- distribusi beban kerja.

**Masalah Utama yang Ditemukan**

Ada dua kelompok masalah utama.

Pertama, masalah caching.

Pada baseline, hasil DSS disimpan menggunakan database-backed snapshot melalui tabel:

```text
workspace_performance_snapshots
```

Masalahnya:
- cache baseline tetap membutuhkan query SQL ke PostgreSQL;
- cache hit belum benar-benar menjadi database bypass;
- endpoint `modal-data` mengalami partial cache hit karena hanya metrik utama yang memakai snapshot, sedangkan urgent tasks dan workload distribution tetap dihitung ulang;
- perubahan task dan assignment belum sepenuhnya menginvalidasi cache DSS;
- perubahan pada tabel pivot `task_assignments` belum memicu invalidasi cache, sehingga data workload bisa tidak fresh sampai TTL habis.

Kedua, masalah ORM.

Masalah ORM yang ditemukan:
- `getTasksInPeriod()` memakai `with(['assignedUsers'])` tanpa pembatasan kolom, padahal hanya `id` yang dibutuhkan;
- relasi `checklists` tidak di-eager-load, sehingga `getProgressPercentage()` memicu N+1 query;
- `getWorkloadDistribution()` menjalankan `Task::whereHas()` di dalam loop anggota workspace, sehingga query bertambah mengikuti jumlah anggota aktif.

**Intervensi Optimasi**

Penelitian menerapkan dua intervensi utama secara simultan.

Pertama, optimasi caching:
- mengganti database-backed snapshot menjadi Redis Cache-Aside;
- menggunakan Redis sebagai in-memory cache store;
- menerapkan event-driven invalidation melalui:
  - `TaskObserver`,
  - `TaskAssignmentObserver`;
- mempertahankan TTL sebagai safety net untuk metrik berbasis waktu seperti:
  - `urgentTaskRatio`,
  - `overdueRate`,
  - `criticalTaskRatio`.

Kedua, optimasi ORM:
- mengubah eager loading dari:

```php
->with(['assignedUsers'])
```

menjadi:

```php
->with(['assignedUsers:id', 'checklists:id,task_id,is_done'])
```

- membatasi kolom `assignedUsers` hanya pada `id`;
- menambahkan eager loading `checklists`;
- mengubah `getWorkloadDistribution()` dari query loop per anggota menjadi batch retrieval dan in-memory aggregation.

**Metode Penelitian**

Metode penelitian adalah:

> eksperimen kuantitatif dengan desain before-after controlled benchmarking.

Artinya, performa sistem dibandingkan antara:
- konfigurasi baseline,
- konfigurasi optimized.

Keduanya diuji pada kondisi yang sama:
- dataset sama,
- endpoint sama,
- parameter request sama,
- volume data sama,
- kondisi cache sama,
- lingkungan Docker yang sama.

Penelitian ini bukan:
- load testing,
- stress testing,
- concurrency testing,
- throughput testing,
- P95/tail latency testing,
- simulasi virtual users.

**Metode Pengujian**

Metode pengujian adalah **controlled performance measurement** melalui request HTTP terkontrol.

Pengujian dilakukan pada:

```text
3 volume data x 2 endpoint x 2 konfigurasi sistem x 2 kondisi cache x 10 repetisi
```

Total data valid:

```text
3 x 2 x 2 x 2 x 10 = 240 pengukuran
```

Sebelum 10 repetisi valid, setiap kombinasi didahului oleh 3 request warm-up. Data warm-up tidak dimasukkan ke analisis.

**Dataset Eksperimen**

Dataset yang digunakan adalah dataset sintetis dari Laravel Seeder dan Factory, bukan data produksi.

Tiga skenario volume data:
- Small: 50 task, 5 anggota;
- Medium: 200 task, 15 anggota;
- Large: 500 task, 30 anggota.

Distribusi status task:
- 40% `done`,
- 35% `in progress`,
- 25% `todo`.

Sebagian task memiliki due date yang sudah lewat untuk menguji metrik berbasis waktu seperti `overdueRate` dan `urgentTaskRatio`.

**Kondisi Cache**

Setiap endpoint diuji dalam dua kondisi cache.

Cache miss:
- cache relevan dikosongkan sebelum request pengukuran;
- digunakan untuk mengukur biaya eksekusi penuh pipeline DSS.

Cache hit:
- cache dipopulasi lebih dulu menggunakan request awal yang tidak masuk data analisis;
- request berikutnya dengan parameter sama dicatat sebagai data valid;
- digunakan untuk mengukur efisiensi retrieval cache.

**Metrik yang Diukur**

Ada empat metrik utama.

1. Response time  
Mengukur durasi dari request dikirim sampai respons diterima, dalam milidetik.

2. Query count  
Mengukur jumlah query SQL ke PostgreSQL per request, dikumpulkan menggunakan Laravel Telescope.

3. Memory utilization  
Mengukur puncak penggunaan memori PHP per request, misalnya melalui `memory_get_peak_usage()`.

4. Cache hit ratio  
Mengukur proporsi request cache yang berhasil dilayani dari mekanisme cache.

Untuk optimized, cache hit ratio dihitung dari Redis:

```text
keyspace_hits / (keyspace_hits + keyspace_misses) x 100
```

Untuk baseline, cache hit perlu dibaca hati-hati karena database-backed snapshot tetap dapat melibatkan query SQL ke PostgreSQL. Karena itu, cache hit ratio dianalisis bersama query count.

**Metode Analisis Data**

Analisis data terdiri dari:
- statistik deskriptif;
- persentase perubahan;
- paired-sample t-test.

Statistik deskriptif meliputi:
- mean,
- minimum,
- maksimum,
- standard deviation.

Persentase perubahan digunakan untuk menunjukkan dampak praktis optimasi.

Contoh:

```text
((mean baseline - mean optimized) / mean baseline) x 100
```

Paired-sample t-test digunakan karena setiap hasil baseline memiliki pasangan langsung pada optimized dengan:
- volume data sama,
- endpoint sama,
- kondisi cache sama,
- parameter request sama,
- urutan repetisi sama.

Tingkat signifikansi:

```text
α = 0,05
```

Hasil signifikan jika:

```text
p < 0,05
```

**Cara Membaca Hasil**

Hasil penelitian nantinya tidak hanya dilihat dari satu metrik.

Kesimpulan diambil dari kombinasi:
- apakah response time turun;
- apakah query count turun;
- apakah memory utilization membaik;
- bagaimana cache hit ratio berubah;
- apakah perubahan signifikan secara statistik;
- apakah persentase perubahan cukup bermakna secara praktis.

Untuk cache hit ratio, interpretasinya tidak boleh mentah. Jika cache hit ratio optimized tidak selalu meningkat, itu belum tentu gagal, karena event-driven invalidation bisa lebih sering membersihkan cache demi menjaga freshness data. Jadi cache hit ratio dibaca bersama response time dan query count.

**Kontribusi Penelitian**

Kontribusi utama penelitian kamu adalah:

1. Menguji Redis Cache-Aside dan optimasi ORM secara simultan pada jalur DSS, bukan secara terpisah.

2. Mengevaluasi performa DSS berbasis pipeline agregasi data relasional, bukan hanya API CRUD atau product listing biasa.

3. Memasukkan masalah pivot assignment melalui `TaskAssignmentObserver`, yang penting karena perubahan assignment memengaruhi workload distribution.

4. Mengukur empat metrik secara bersamaan:
   - response time,
   - query count,
   - memory utilization,
   - cache hit ratio.

5. Menggunakan desain before-after terkontrol tanpa load testing, sehingga fokus penelitian adalah efisiensi eksekusi sistem, bukan kapasitas menghadapi trafik paralel.

**Batasan Penelitian**

Penelitian ini tidak mengukur:
- pengalaman pengguna;
- kepuasan pengguna;
- akurasi keputusan manajerial;
- usability;
- throughput;
- concurrent users;
- stress limit;
- P95 latency;
- performa seluruh aplikasi Koladi.

Penelitian hanya mengukur performa instrumental pada dua endpoint DSS halaman statistik kinerja.