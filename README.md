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
git clone <repo-koladi>
cd koladi
```

---

## 3. File Environment (.env)

Salin `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Pastikan konfigurasi database **SESUAI Docker**:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=koladi
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

> `.env` **tetap diperlukan**, walaupun Docker dipakai.

---

## 4. Jalankan Docker

```bash
docker compose up -d
```

Cek container:

```bash
docker compose ps
```

Harusnya:

* koladi_app → running
* koladi_db → running

---

## 5. Import Database (.sql)

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

## 6. Generate APP KEY

```bash
docker compose exec app php artisan key:generate
```

---

## 7. Jalankan Migration

```bash
docker compose exec app php artisan migrate
```

Catatan:

* Tabel yang **sudah ada di SQL tidak akan dibuat ulang**
* Migration baru tetap jalan

---

## 8. Jalankan Seeder

```bash
docker compose exec app php artisan db:seed
```

Seeder Koladi bersifat **aman (idempotent / semi-idempotent)** untuk:

* Role
* Subscription
* Color
* Admin Sistem

---

## 9. Jalankan Frontend (WAJIB)

Di **terminal host (bukan Docker)**:

```bash
npm install
npm run dev
```

Tanpa ini, halaman seperti `/dashboard` akan error (`Vite manifest not found`).

---

## 10. Akses Aplikasi

```text
http://localhost:8000
```

Login Admin Sistem (jika ada):

* Email: `admin@koladi.com`
* Password: sesuai seeder

---

## 11. Ringkasan Alur (Laptop Baru)

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

## 12. Pembuktian Docker (Tanpa Dependency Lokal)

Yang **TIDAK perlu diinstall** di laptop:

* PHP
* Composer
* PostgreSQL

Semua sudah ada di Docker.

Node.js **boleh dihapus** nanti jika frontend sudah dibuild (production).

---

## 13. Catatan Penting

* Ini **Fase Development**
* Masih menggunakan:

  * `php artisan serve`
  * `npm run dev`
* **Belum production ready** (belum Nginx, belum build asset)

---



