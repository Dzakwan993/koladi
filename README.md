<!-- <p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p> -->

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

## 3. File Environment (.env)

Salin file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

### Konfigurasi Database (Docker)

Pastikan konfigurasi database **sesuai dengan Docker Compose**:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=koladi
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

> File `.env` **tetap wajib ada**, meskipun aplikasi dijalankan menggunakan Docker.

---

### Konfigurasi Login Google (OAuth)

Aplikasi Koladi mendukung **Login dengan Google** menggunakan OAuth 2.0.
Untuk mengaktifkannya, isi variabel berikut di file `.env`:

```env
GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxxxxxxx
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

### Cara Mendapatkan GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET

Nilai tersebut **didapatkan dari Google Cloud Console**, **bukan dari Laravel atau Docker**.

Langkah singkat:

1. Buka **Google Cloud Console**
   👉 [https://console.cloud.google.com](https://console.cloud.google.com)

2. Buat atau pilih **Project**

3. Masuk ke menu
   **APIs & Services → OAuth consent screen**

   * User Type: **External**
   * Isi data dasar (App name, email)
   * Simpan

4. Masuk ke
   **APIs & Services → Credentials → Create Credentials → OAuth Client ID**

   * Application type: **Web application**
   * Tambahkan **Authorized Redirect URI**:

     ```
     http://localhost:8000/auth/google/callback
     ```

5. Setelah dibuat, Google akan menampilkan:

   * **Client ID**
   * **Client Secret**

6. Salin kedua nilai tersebut ke file `.env`

---

> ⚠️ Setiap developer **harus membuat OAuth Client sendiri** atau menggunakan credentials yang dibagikan oleh pemilik project.

---

## 4. Jalankan Docker (Database dulu)

```bash
docker compose up -d db
```

---

## 5. Install PHP Dependencies (Composer)

```bash
docker compose run --rm app composer install
```

---

## 6. Jalankan App Container

```bash
docker compose up -d
```

Cek container:

```bash
docker compose ps
```

Harusnya:
* koladi_db → running
* koladi_app → running

---

## 7. Import Database (.sql)

File SQL tersedia di:

```
koladi/Koladi.sql
```

Import dari host ke Postgres container:

```bash
docker compose exec -T db psql -U postgres -d koladi < Koladi.sql
```

Ini menjadi **baseline schema + data awal**.

---

## 8. Generate APP KEY

```bash
docker compose exec app php artisan key:generate
```

---

## 9. Jalankan Seeder

```bash
docker compose exec app php artisan db:seed
```

Seeder Koladi bersifat **aman (idempotent / semi-idempotent)** untuk:

* Role
* Subscription
* Color
* Admin Sistem

---

## 10. Setup Storage Link
```bash
docker compose exec app php artisan storage:link
```

Perintah ini membuat symbolic link agar file di `storage/app/public` bisa diakses via `public/storage`.

---

## 11. Fix Permissions (Jika Masih Error)

Jika masih ada error 403, jalankan:
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```
---

## 12. Jalankan Frontend (WAJIB)

Di **terminal host (bukan Docker)**:

```bash
npm install
npm run dev
```

Tanpa ini, halaman seperti `/dashboard` akan error (`Vite manifest not found`).

---

## 13. Akses Aplikasi

```text
http://localhost:8000
```

Login Admin Sistem (jika ada):

* Email: `admin@koladi.com`
* Password: sesuai seeder

---

## 14. Ringkasan Alur (Laptop Baru)

```text
1. Install Docker Desktop
2. Install Node.js
3. git clone repo
4. cp .env.example .env
5. docker compose up -d
6. import Koladi.sql
7. php artisan key:generate
8. php artisan migrate
9. php artisan db:seed
10. npm run dev
11. buka localhost:8000
```

---

## 15. Pembuktian Docker (Tanpa Dependency Lokal)

Yang **TIDAK perlu diinstall** di laptop:

* PHP
* Composer
* PostgreSQL

Semua sudah ada di Docker.

Node.js **boleh dihapus** nanti jika frontend sudah dibuild (production).

---

## 16. Catatan Penting

* Ini **Fase Development**
* Masih menggunakan:

  * `php artisan serve`
  * `npm run dev`
* **Belum production ready** (belum Nginx, belum build asset)

---



