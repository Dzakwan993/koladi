# Koladi — Development Setup (Docker)

Dokumen ini menjelaskan **cara menjalankan Koladi di laptop mana pun (Mac/Linux/Windows)** menggunakan **Docker**.

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
  * Cek: `node -v` dan `npm -v`

---

## 2. Clone Repository

```bash
git clone https://github.com/Dzakwan993/koladi.git
cd koladi
```

---

## 3. Jalankan Aplikasi

Tersedia dua mode konfigurasi: **Local Development** dan **Production**.

### A. Mode Development (Lokal / Laptop)
Gunakan mode ini untuk koding sehari-hari di laptop.

1.  **Persiapan Environment**
    ```bash
    cp .env.docker.local.example .env
    ```

2.  **Jalankan Docker (Versi Lokal)**
    ```bash
    docker compose -f docker-compose.local.yml up -d
    ```

3.  **Jalankan Frontend (Vite)**
    Di terminal laptop kamu:
    ```bash
    npm install
    npm run dev
    ```

### B. Mode Production (Server)
Gunakan mode ini saat ingin deploy ke server atau simulasi lingkungan prod.

1.  **Persiapan Environment**
    ```bash
    cp .env.docker.prod.example .env
    ```

2.  **Jalankan Docker**
    ```bash
    docker compose up -d
    ```

3.  **Build Frontend**
    ```bash
    npm install
    npm run build
    ```

---

## 4. (Opsional) Konfigurasi Login Google (OAuth)

Aplikasi Koladi mendukung **Login dengan Google**. Nilai berikut didapatkan dari [Google Cloud Console](https://console.cloud.google.com).

1. Edit file `.env` dan isi:
   ```env
   GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxxxxxxx
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
   ```

2. Restart container:
   ```bash
   docker compose -f docker-compose.local.yml restart app
   ```

---

## 5. Akses Aplikasi

Akses melalui browser: **[http://localhost:8000](http://localhost:8000)**

---

## Command Berguna

### Lihat Logs (Real-time)
```bash
docker compose -f docker-compose.local.yml logs -f app
```

### Masuk ke Container (Artisan)
```bash
# Masuk ke app container
docker compose -f docker-compose.local.yml exec app bash

# Jalankan perintah artisan langsung
docker compose -f docker-compose.local.yml exec app php artisan migrate
```

### Reset Database (Hapus Semua Data)
```bash
docker compose -f docker-compose.local.yml down -v
docker compose -f docker-compose.local.yml up -d
```

### Perbaikan Izin Folder (Jika Error 403/500)
```bash
docker compose -f docker-compose.local.yml exec app chmod -R 777 storage bootstrap/cache
```