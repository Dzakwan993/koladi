# Koladi Design System (Versi Saat Ini / Legacy)

Dokumen ini mendeskripsikan *design system* yang saat ini digunakan pada aplikasi Koladi ("All-in-One Digital Office").

---

## 1. Brand & Design Principles

*   **All-in-One Office**: Desain difokuskan untuk mengakomodasi banyak modul sekaligus (Chat, Pengumuman, Jadwal, Dokumen, Tugas).
*   **Corporate & Structured**: Menggunakan gaya visual yang rapi dan terstruktur, cocok untuk hierarki perusahaan.
*   **Light & Clean**: Mengandalkan *white-space* dan warna latar belakang yang terang untuk membedakan area konten.

---

## 2. Tipografi

Koladi menggunakan font **Inter** dari Google Fonts sebagai *font family* utama untuk memastikan keterbacaan yang tinggi pada berbagai ukuran layar.

*   **Primary Font**: `Inter`
*   **Weights yang digunakan**: Regular (400), Medium (500), Semi-Bold (600), Bold (700), Extra-Bold (800).
*   *Styling* teks sangat mengandalkan *utility classes* bawaan dari Tailwind CSS (seperti `text-sm`, `font-medium`, `text-xs`, dsb).

---

## 3. Palet Warna (Color Palette)

Sistem warna pada versi saat ini menggunakan kombinasi warna *custom* dan palet bawaan Tailwind CSS.

### A. Primary & Brand Colors
*   **Active Blue (Brand)**: `#225ad6` - Digunakan untuk teks menu yang sedang aktif, ikon yang sedang aktif, dan elemen interaktif utama.
*   **Active Background**: `#e9effd` - Latar belakang biru sangat muda untuk menu *sidebar* atau *item* yang sedang dipilih/aktif.

### B. Background Colors
*   **App Background**: `#f3f6fc` - Latar belakang utama aplikasi (warna abu-abu kebiruan muda).
*   **Surface/Card**: `#ffffff` (White) - Digunakan untuk *Sidebar*, *Topbar*, dan kotak konten (*cards*).

### C. Text Colors (Neutral/Gray)
*   **Primary Text**: `text-gray-700` (`#374151`) - Untuk teks penting atau ikon utama.
*   **Secondary Text**: `text-gray-600` (`#4b5563`) - Untuk teks menu reguler.
*   **Muted Text**: `text-gray-500` (`#6b7280`) - Untuk label kategori (*uppercase*) atau teks tambahan.
*   **Placeholder/Disabled**: `text-gray-400` (`#9ca3af`) - Untuk ikon yang tidak aktif, placeholder *search*, atau teks kosong.

### D. Semantic & Status Colors
*   **Success**: `green-500` - (Sering digunakan via SweetAlert atau penanda status sukses).
*   **Error/Danger**: `red-500` hingga `red-600` - Digunakan untuk notifikasi *error* (seperti *toast* akses ditolak) atau aksi berbahaya.
*   **Workspace Dots**: Menggunakan variasi warna Tailwind untuk penanda workspace: `blue-600` (Tim), `purple-600` (Proyek), `green-500`, `yellow-500`, `pink-500`, `indigo-500`.

---

## 4. Komponen Utama UI

### A. Navigasi & Sidebar
*   **Menu Items**: Memiliki *padding* `px-3 py-2.5`, sudut melengkung `rounded-lg`, dan efek transisi.
*   **State Normal**: Latar belakang transparan, ikon abu-abu, teks `text-gray-600`. Saat di-*hover*, latar belakang menjadi `hover:bg-gray-50`.
*   **State Aktif**: Latar belakang berubah menjadi `bg-[#e9effd]`, teks menjadi `text-[#225ad6] font-medium`, dan ikon SVG diberi *filter* khusus (`.filter-blue`) agar warnanya sesuai dengan biru *brand*.

### B. Ikon (Icons)
*   Menggunakan kombinasi **Font Awesome 6.4.0** dan ikon SVG *custom* (berada di direktori `public/images/icons/`).
*   Ukuran ikon menu standar adalah `w-5 h-5`.

### C. Alerts & Notifications
*   **SweetAlert2**: Digunakan secara ekstensif untuk berbagai notifikasi popup (Sukses, Peringatan, Konfirmasi), dengan kustomisasi warna tombol (`#3b82f6` untuk Confirm, `#6b7280` untuk Cancel).
*   **Toast Alert (Error)**: Tampil melayang di sudut kanan atas (`fixed top-4 right-4`), memiliki latar belakang gradasi merah (`bg-gradient-to-r from-red-500 to-red-600`), teks putih, dan animasi *progress bar*.

### D. Inputs & Search
*   **Search Box (Sidebar)**: Menggunakan `bg-gray-50` yang saat di-*hover* berubah menjadi `hover:bg-gray-100`, ukuran teks `text-xs`, dan menghilangkan *outline* bawaan peramban (`focus:ring-0`).

---

## 5. Layout & Struktur Halaman

*   **Global Layout**:
    *   **Full Screen (`h-screen`)**: Aplikasi mengambil tinggi layar penuh dan menyembunyikan *overflow* horizontal (`overflow-hidden`).
    *   **Sidebar (Kiri)**: Memiliki lebar tetap (`w-64`), latar putih (`bg-white`), *border* kanan (`border-r border-gray-200`), dan memuat logo serta navigasi utama. Responsif (menjadi *overlay* pada perangkat *mobile*).
    *   **Topbar (Atas)**: Berada di atas area konten utama.
    *   **Main Content**: Area luas dengan latar belakang `#f3f6fc` yang bisa di-*scroll* secara vertikal (`overflow-y-auto`).
*   **Interaktivitas**: Animasi dan transisi menu seperti buka/tutup *sidebar* dan *dropdown* dikelola menggunakan **Alpine.js**.

---

## 6. Daftar Modul Saat Ini (Berdasarkan Sidebar)
*   Dashboard
*   Ruang Kerja (Terbagi menjadi **Tim** dan **Proyek**)
*   Pengumuman (Level Perusahaan)
*   Chat Perusahaan
*   Jadwal Umum
*   Dokumen
*   Laporan Kinerja
*   Video Tutorial
