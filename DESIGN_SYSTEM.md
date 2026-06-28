# Koladi Design System (Versi Terbaru)

## 1. Pendahuluan
Design System ini menjadi panduan visual dan interaktif untuk pengembangan **Koladi**, yang kini berfokus sebagai **"AI Project Workspace untuk Tim Jasa Digital Project-Based"**. Desain harus mencerminkan profesionalisme agensi, kecepatan, transparansi untuk klien, dan integrasi kecerdasan buatan (AI) yang mulus.

---

## 2. Brand & Design Principles

*   **Agile & Focused**: Antarmuka harus bersih dari *clutter* (elemen yang tidak perlu). Fokus pada penyelesaian tugas dan *project delivery*.
*   **AI-Empowered**: Kehadiran AI (seperti Brief Parser, Recap, Chatbot) harus terasa *magical* namun tetap dalam kendali *user*. Gunakan penanda visual khusus (seperti kilauan/sparkles atau warna aksen khusus) untuk elemen yang di-*generate* oleh AI.
*   **Trust & Transparency**: Karena melibatkan klien, desain harus terlihat meyakinkan, aman, dan menyajikan log keputusan (*Decision Log*) secara jelas.
*   **Modern & Premium**: Menggunakan estetika modern (banyak *white-space*, bayangan lembut, tipografi tegas) yang cocok untuk agensi digital.

---

## 3. Tipografi

Koladi menggunakan jenis huruf *Sans-Serif* yang bersih dan sangat mudah dibaca baik di ukuran kecil maupun besar.

*   **Primary Font**: `Inter` atau `Plus Jakarta Sans`.
*   **Hierarki Teks**:
    *   **H1 (Page Title)**: Bold, 24px - 32px (cth: Nama Project, Judul Halaman Utama).
    *   **H2 (Section Title)**: Semi-Bold, 20px - 24px (cth: Judul Kolom Kanban, Nama Modul).
    *   **H3 (Card Title/Sub-section)**: Medium, 16px - 18px (cth: Judul Task, Nama File).
    *   **Body (Regular)**: Regular, 14px (cth: Deskripsi task, chat AI).
    *   **Caption/Small**: Regular, 12px (cth: Tanggal log, nama *assignee*, *timestamp*).

---

## 4. Palet Warna (Color Palette)

Warna harus mendukung fokus kerja jangka panjang (tidak menyakiti mata) dan memberikan penanda status yang jelas.

### A. Primary Colors (Brand & Action)
*   **Primary Blue/Indigo**: `#4F46E5` (Indigo 600) - Untuk tombol utama, link aktif, dan elemen navigasi *state* aktif.
*   **AI Accent (Purple/Violet)**: `#8B5CF6` (Violet 500) - **KHUSUS** untuk elemen AI (tombol "Generate with AI", *highlight* teks hasil ektraksi, ikon Chatbot).

### B. Neutral Colors (Background & Text)
*   **Background (App)**: `#F9FAFB` (Gray 50) - Latar belakang utama aplikasi yang terang namun lembut.
*   **Surface/Card**: `#FFFFFF` (White) - Latar belakang *Card*, *Modal*, dan *Sidebar*.
*   **Text (Primary)**: `#111827` (Gray 900) - Untuk judul dan teks utama.
*   **Text (Secondary)**: `#6B7280` (Gray 500) - Untuk deskripsi, teks *placeholder*, dan *caption*.
*   **Border/Divider**: `#E5E7EB` (Gray 200) - Pemisah antar elemen atau *border card*.

### C. Semantic Colors (Status & Alerts)
*   **Success (Selesai/On Track)**: `#10B981` (Emerald 500) - Latar badge: `#D1FAE5`.
*   **Warning (At Risk/Review)**: `#F59E0B` (Amber 500) - Latar badge: `#FEF3C7`.
*   **Danger/Error (Delayed/Blocker)**: `#EF4444` (Red 500) - Latar badge: `#FEE2E2`.
*   **Info (In Progress/New)**: `#3B82F6` (Blue 500) - Latar badge: `#DBEAFE`.

---

## 5. Komponen Utama UI (UI Components)

### A. Buttons
*   **Primary Button**: Background `Primary Blue`, Text `White`, *rounded corners* (misal: 6px atau 8px). Digunakan untuk *Call to Action* utama ("Create Task", "Save").
*   **AI Action Button**: Background bergradasi halus (misal: Indigo ke Violet) atau *border* beranimasi *glow* saat *hover*. Ikon '*Sparkles*' disertakan. ("Generate Draft", "Summarize").
*   **Secondary/Outline Button**: Background transparan, Border `Gray 300`, Text `Gray 700`. Untuk aksi sekunder ("Cancel", "View Log").
*   **Ghost/Text Button**: Tanpa background dan border. Untuk aksi tambahan yang tidak boleh mengganggu fokus ("Edit", "Delete" icon).

### B. Cards & Surfaces
*   **Project Card**: Menampilkan nama *client*, persentase *progress bar* (hijau/kuning/merah), anggota tim (avatar tumpuk), dan tombol masuk ke *dashboard*. Memiliki *subtle shadow* (`shadow-sm`).
*   **Task Card (Kanban)**: Memiliki judul task, *badge* prioritas/status, avatar *assignee*, dan ikon kecil penanda *file attachment* atau diskusi. *Hover state* menonjolkan bayangan (`shadow-md`) dan menampilkan ikon *edit/drag*.

### C. Forms & Inputs
*   **Text Field**: Border `Gray 300`, *focus state* dengan *ring* warna `Primary Blue`.
*   **File Upload Area**: Area *drag-and-drop* dengan *dashed border*, teks ajakan, dan ikon *Cloud/Upload*.
*   **AI Review Split-Screen**: Layout khusus (kiri: dokumen asli, kanan: form hasil ekstraksi AI) dengan tombol *Approve/Reject* per *item*.

### D. Badges & Tags
Berbentuk pil (*pill-shaped* / `rounded-full`) dengan warna teks yang lebih pekat dari latar belakangnya (lihat *Semantic Colors* di atas). Digunakan untuk status (To Do, In Progress, Done) atau label khusus.

---

## 6. Layout & Struktur Halaman

*   **Global Layout**:
    *   **Sidebar (Kiri)**: Daftar navigasi utama (Dashboard, Client Projects, Logs, Settings). Lebar sekitar 240px - 260px. Bisa di-*collapse* menjadi hanya ikon untuk memperluas area kerja.
    *   **Top Bar (Atas)**: Modul pencarian (*Global Search*), Notifikasi, Workspace Switcher, dan Profil User.
    *   **Main Content**: Area luas di tengah untuk Kanban *board*, *dashboard* grafik, atau antarmuka *split-screen*.
*   **Floating Elements**: AI Chatbot (*bottom right corner*) yang bisa diperluas menjadi panel samping kanan.

---

## 7. Interaksi Khusus & Micro-Animations

*   **AI Processing State**: Saat AI sedang "berpikir" (Parsing Brief, Merangkum Rapat), gunakan animasi *skeleton loading* yang elegan atau *shimmer effect* berwarna keunguan. Berikan teks proaktif (misal: "Membedah dokumen klien...", "Menyusun draft task...").
*   **Drag and Drop**: Kanban *board* harus terasa *snappy* (responsif). Berikan *visual cue* (area bayangan di bawah kursor) tempat *task* akan diletakkan.
*   **Transitions**: Perpindahan antar tab (seperti dari *Task Board* ke *Decision Log*) gunakan *fade* atau *slide* ringan agar tidak terasa kaku.

---

## 8. Panduan Khusus Modul (Berdasarkan Analisis Terbaru)

1.  **AI Brief Parser Review**: UI harus memberikan rasa "aman" bagi *Project PIC*. Jangan langsung menyimpan hasil AI. Sediakan UI konfirmasi berdampingan (sebelum/sesudah) agar PIC bisa mengedit *deadline* atau *assignee* jika rekomendasi AI meleset.
2.  **Task Dependency**: Berikan visualisasi sederhana pada *Task Card* berupa ikon peringatan kemerahan jika task tersebut sedang diblokir oleh task lain, atau memiliki garis panah jika di tampilan *Timeline View*.
3.  **Client Viewer Portal**: Versi antarmuka yang sangat bersih (dihilangkan tombol konfigurasi, edit task, dan AI prompt). Fokus pada grafik kemajuan, tabel *Decision Log*, dan daftar *Evidence* yang sudah disetujui. Gunakan *tone* warna yang lebih "korporat" dan netral untuk portal klien.
4.  **Activity & Evidence Log**: Tampilkan sebagai tabel padat (*data table*) atau *timeline* vertikal seperti riwayat aktivitas Github, yang mudah dibaca secara kronologis.

---

*Design system ini adalah panduan hidup (*living document*) yang akan terus berevolusi seiring iterasi produk Koladi.*
