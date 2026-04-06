# Koladi — Development & Production Setup (Docker)

Dokumen ini menjelaskan cara menjalankan Koladi di **laptop (development)** maupun **server (production)** menggunakan Docker.

> Laptop tidak perlu install PHP, Composer, atau PostgreSQL — cukup **Docker Desktop** dan **Node.js**.

---

## Peta File Konfigurasi

| File | Digunakan untuk |
|------|-----------------|
| `docker-compose.local.yml` | **Local** — docker compose untuk laptop/development |
| `docker-compose.yml` | **Production** — docker compose untuk server |
| `docker/entrypoint.local.sh` | Script booting container **local** |
| `docker/entrypoint.sh` | Script booting container **production** |
| `.env` | File environment aktif (wajib dibuat manual dari contoh di bawah) |
| `.env.docker.prod.example` | Contoh environment untuk **production** |
| `Koladi.sql` | Skema + data awal database (otomatis diimport saat DB kosong) |



---

## Prasyarat

- **Docker Desktop** → [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop/)
- **Node.js ≥ 18** (hanya untuk frontend/Vite, dijalankan di host bukan container)

---

## A. Local Development (Laptop)

### 1. Clone & Setup Environment

```bash
git clone https://github.com/Dzakwan993/koladi.git
cd koladi
```

Buat file `.env` dari template yang sudah ada (atau copy dari teman tim):
```bash
# Cukup buat/edit .env dengan konfigurasi local milikmu
# Pastikan DB_HOST=db, APP_URL=http://localhost:8000 atau URL hasil Ngrok
```

### 2. Jalankan Docker

```bash
docker compose -f docker-compose.local.yml up -d
```

> **Pengguna Windows (WSL2/Git Bash):** Jika muncul error `Permission Denied` atau `php-fpm: not found`, jalankan dulu:
> ```bash
> chmod +x docker/entrypoint.local.sh
> sed -i 's/\r$//' docker/entrypoint.local.sh  # fix Windows line endings
> ```

### 3. Jalankan Frontend (Vite)

Di terminal laptop (bukan di dalam Docker):
```bash
npm install
npm run dev
```

### 4. Akses Aplikasi

Buka browser: **[http://localhost:8000](http://localhost:8000)**

---

## B. Production (Server)

### 1. Clone di Server

```bash
git clone https://github.com/Dzakwan993/koladi.git
cd koladi
```

### 2. Setup Environment

```bash
cp .env.docker.prod.example .env
```

Buka file `.env` dan sesuaikan nilai berikut:
- `APP_URL`: Domain production (misal: `https://koladi.terpal23b.cloud`)
- `GOOGLE_REDIRECT_URI`: Ubah `localhost` ke domain production
- `DB_PASSWORD`: Masukkan password database baru yang aman

### 3. Jalankan Docker

```bash
docker compose up -d --build
```

Saat perintah ini dijalankan, Docker akan otomatis mendownload dependencies (`npm ci`) dan mengompilasi asset (`npm run build`) di dalam container. Kamu tidak perlu menjalankan perintah npm manual di server.

### 4. Cek Status

```bash
docker compose logs -f app
```
Tunggu hingga muncul log `✅ Laravel production ready`.

---

## Update `Koladi.sql` (Saat Ada Perubahan Database)

Setiap kali ada perubahan struktur database (migration baru dari anggota tim), perbarui `Koladi.sql` dari Docker local yang sudah up-to-date, lalu commit:

```bash
# Export seluruh database (schema + data)
docker exec -it koladi_db_local pg_dump -U postgres koladi > Koladi.sql

git add Koladi.sql
git commit -m "chore: update Koladi.sql with latest database schema"
git push
```

---

## Command Berguna

### Lihat Log Real-time

```bash
# Local
docker compose -f docker-compose.local.yml logs -f app

# Production
docker compose logs -f app
```

### Masuk ke Container

```bash
# Local
docker compose -f docker-compose.local.yml exec app bash

# Production
docker compose exec app bash
```

### Jalankan Artisan Manual

```bash
# Local
docker exec -it koladi_app_local php artisan <perintah>

# Production
docker exec -it koladi_app php artisan <perintah>
```

### Reset Database (Hapus Semua Data & Reimport `Koladi.sql`)

```bash
# Local — hati-hati, ini akan menghapus semua data lokal!
docker compose -f docker-compose.local.yml down -v
docker compose -f docker-compose.local.yml up -d
```

### Fix Permission Error (403/500)

```bash
docker exec -it koladi_app_local chmod -R 777 storage bootstrap/cache
```

### Cek Struktur Database Saat Ini (tanpa data)

```bash
docker exec -it koladi_db_local pg_dump -U postgres -s koladi > schema_sekarang.sql
```

---

## Login Google (OAuth)

Pastikan di `.env` sudah terisi:
```env
GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxxxxxx
# Local:
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
# Production:
GOOGLE_REDIRECT_URI=https://koladi.terpal23b.cloud/auth/google/callback
```

Daftarkan URI tersebut juga di [Google Cloud Console](https://console.cloud.google.com) → Credentials → OAuth 2.0.

---

## Testing Webhook Pembayaran (Xendit) di Local

Xendit membutuhkan URL publik untuk kirim callback. Gunakan **ngrok** saat local:

```bash
ngrok http 8000
```

Lalu update `.env`:
```env
APP_URL=https://xxxx.ngrok-free.app
```

Dan jalankan:
```bash
docker exec -it koladi_app_local php artisan config:clear
```