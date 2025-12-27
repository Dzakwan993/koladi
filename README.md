# Koladi — Development Setup (Docker)

Dokumen ini menjelaskan **cara menjalankan Koladi di laptop mana pun (Mac/Linux/Windows)** menggunakan **Docker** untuk fase **development**.

Target utama:
* Laptop **tidak perlu** install PHP, Composer, PostgreSQL
* Cukup **Docker + Node.js** (Node hanya untuk Vite / frontend dev)

---

## 1. Prasyarat

### Wajib
* **Docker Desktop** (termasuk Docker Compose)
  * [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/)

### Untuk Frontend (Vite)
* **Node.js ≥ 18**
  * Digunakan **di host**, bukan di container
  * Cek:
    ```bash
    node -v
    npm -v
    ```

> ❗ Tanpa Node.js, halaman yang pakai `@vite(...)` akan error.

---

## 2. Clone Repository

```bash
git clone -b docker <repo-koladi>
cd koladi
```

---

## 3. Start Docker

```bash
docker compose up -d
```

**Setup otomatis berjalan di background!** ✨

Yang dilakukan secara otomatis:
- ✅ Install Composer dependencies
- ✅ Copy `.env` dari `.env.docker.example`
- ✅ Generate `APP_KEY`
- ✅ Import database `Koladi.sql` (jika database kosong)
- ✅ Run database seeder
- ✅ Create storage symlink
- ✅ Fix permissions

Cek logs untuk melihat progress:
```bash
docker compose logs -f app
```

---

## 4. (Opsional) Konfigurasi Login Google (OAuth)

Aplikasi Koladi mendukung **Login dengan Google** menggunakan OAuth 2.0.

### Setup Google OAuth

1. Edit file `.env`:
   ```bash
   nano .env
   ```

2. Isi variabel berikut:
   ```env
   GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxxxxxxx
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```

3. Restart container:
   ```bash
   docker compose restart app
   ```

### Cara Mendapatkan GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET

Nilai tersebut **didapatkan dari Google Cloud Console**, **bukan dari Laravel atau Docker**.

Langkah singkat:

1. Buka **Google Cloud Console**
   👉 [https://console.cloud.google.com](https://console.cloud.google.com)

2. Buat atau pilih **Project**

3. Masuk ke menu **APIs & Services → OAuth consent screen**
   * User Type: **External**
   * Isi data dasar (App name, email)
   * Simpan

4. Masuk ke **APIs & Services → Credentials → Create Credentials → OAuth Client ID**
   * Application type: **Web application**
   * Tambahkan **Authorized Redirect URI**:
     ```
     http://localhost:8000/auth/google/callback
     ```

5. Setelah dibuat, Google akan menampilkan:
   * **Client ID**
   * **Client Secret**

6. Salin kedua nilai tersebut ke file `.env`

> ⚠️ Setiap developer **harus membuat OAuth Client sendiri** atau menggunakan credentials yang dibagikan oleh pemilik project.

---

## 5. Jalankan Frontend (WAJIB)

Di **terminal host (bukan Docker)**:

```bash
npm install
npm run dev
```

Tanpa ini, halaman seperti `/dashboard` akan error (`Vite manifest not found`).

---

## 6. Akses Aplikasi

```
http://localhost:8000
```

Login Admin Sistem (jika ada):
* Email: `admin@koladi.com`
* Password: sesuai seeder

---

## Ringkasan Setup (Laptop Baru)

```bash
# 1. Install Docker Desktop & Node.js

# 2. Clone dan start
git clone -b docker <repo-koladi>
cd koladi
docker compose up -d

# 3. (Opsional) Setup Google OAuth
nano .env  # isi GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET
docker compose restart app

# 4. Jalankan frontend
npm install
npm run dev

# 5. Buka browser
# http://localhost:8000
```

**3-5 langkah saja!** 🚀

---

## Command Berguna

### Lihat Logs
```bash
# Semua logs
docker compose logs

# Logs app saja (real-time)
docker compose logs -f app

# Logs database
docker compose logs db
```

### Restart Container
```bash
# Restart app saja
docker compose restart app

# Restart semua
docker compose restart
```

### Masuk ke Container
```bash
# Masuk ke app container
docker compose exec app bash

# Jalankan artisan command
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
```

### Stop & Start
```bash
# Stop container (data tetap ada)
docker compose down

# Start ulang
docker compose up -d
```

### Reset Database
```bash
# Hapus semua (⚠️ DATA HILANG!)
docker compose down -v

# Start ulang (akan import Koladi.sql otomatis)
docker compose up -d
```

---

## FAQ

### ❓ Apakah harus import database setiap kali restart container?

**TIDAK!** 

- `docker compose down` → hanya stop container, **data tetap ada**
- `docker compose up -d` → start ulang, **data otomatis kembali**

Import database **hanya sekali** saat pertama kali setup atau setelah `docker compose down -v`.

### ❓ Bagaimana jika ubah Dockerfile atau docker-compose.yml?

```bash
# Rebuild dan restart
docker compose down
docker compose build --no-cache
docker compose up -d
```

### ❓ Bagaimana jika ada error 403 saat load gambar avatar?

```bash
# Fix permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app php artisan storage:link
docker compose restart app
```

### ❓ Bagaimana cara update dependencies (composer/npm)?

```bash
# Update Composer
docker compose exec app composer update

# Update npm (di host)
npm update

# Tidak perlu restart container
```

### ❓ Command mana yang BAHAYA (hapus data)?

```bash
# ⚠️ Ini hapus DATABASE!
docker compose down -v

# ⚠️ Ini hapus semua (image, volume, container)
docker compose down -v --rmi all
```

**Rule of thumb:**
- `docker compose down` (tanpa `-v`) = **AMAN**, data tetap ada
- `docker compose down -v` = **BAHAYA**, data hilang

---

## Pembuktian Docker (Tanpa Dependency Lokal)

Yang **TIDAK perlu diinstall** di laptop:
* ❌ PHP
* ❌ Composer
* ❌ PostgreSQL

Semua sudah ada di Docker.

Yang **masih perlu** di laptop:
* ✅ Node.js (untuk `npm run dev`)

> Node.js **boleh dihapus** nanti jika frontend sudah di-build untuk production.

---

## Catatan Penting

* Ini **Fase Development**
* Masih menggunakan:
  * `php artisan serve`
  * `npm run dev`
* **Belum production ready** (belum Nginx, belum build asset)

---

## Troubleshooting

### Database connection refused
```bash
# Cek database running
docker compose ps db

# Lihat logs database
docker compose logs db

# Restart database
docker compose restart db
```

### Port 8000 sudah dipakai
```bash
# Cek process yang pakai port
lsof -i :8000  # Mac/Linux
netstat -ano | findstr :8000  # Windows

# Atau ubah port di docker-compose.yml
ports:
  - "9000:8000"  # Ubah 8000 jadi 9000
```

### Vite manifest not found
```bash
# Pastikan npm run dev sudah jalan
npm install
npm run dev
```

### Container tidak bisa start
```bash
# Lihat logs error
docker compose logs app

# Rebuild dari awal
docker compose down
docker compose build --no-cache
docker compose up -d
```