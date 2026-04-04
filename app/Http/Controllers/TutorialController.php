<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function index()
    {
        $videos = $this->videos();
        return view('tutorial', compact('videos'));
    }

    public function publik()
    {
        $videos = $this->videos();
        return view('tutorial-publik', compact('videos'));
    }

    private function videos(): array
    {
        return [
            [
                'youtube_id' => 'fE5I8HjvAX4',
                'title'      => 'Mulai dari Buat Perusahaan',
                'desc'       => 'Buat perusahaan pertamamu di Koladi dan mulai setup workspace tim. Video ini cocok untuk pemilik bisnis atau admin yang baru pertama kali menggunakan Koladi.',
            ],
            [
                'youtube_id' => 'Lp-2PeoIdXw',
                'title'      => 'Aktivasi Paket Koladi',
                'desc'       => 'Pilih paket yang sesuai kebutuhan timmu dan selesaikan pembayaran hingga Koladi siap digunakan secara penuh.',
            ],
            [
                'youtube_id' => '62h_hgE-6bU',
                'title'      => 'Invite Karyawan & Atur Hak Akses',
                'desc'       => 'Undang anggota tim ke Koladi dan tentukan peran mereka agar kerja tim lebih terstruktur dan terorganisir.',
            ],
            [
                'youtube_id' => 'HnpudbBbxFg',
                'title'      => 'Masuk ke Koladi Lewat Undangan',
                'desc'       => 'Terima undangan dari perusahaanmu dan pelajari cara bergabung ke dalam workspace Koladi dengan mudah.',
            ],
            [
                'youtube_id' => 'IP9nHuLgorA',
                'title'      => 'Kenalan dengan Dashboard Koladi',
                'desc'       => 'Lihat ringkasan aktivitas tim, jadwal, pengumuman, dan navigasi utama sebelum mulai bekerja di Koladi.',
            ],
            [
                'youtube_id' => 'pKHHXWYoXoU',
                'title'      => 'Buat Ruangan Tim & Proyek',
                'desc'       => 'Pisahkan kerjaan berdasarkan tim atau proyek agar koordinasi lebih rapi dan mudah dipantau.',
            ],
            [
                'youtube_id' => 'mwhZ5qjJsMk',
                'title'      => 'Kelola Tugas dengan Kanban & Timeline',
                'desc'       => 'Buat tugas, atur progres kerja, dan pantau timeline proyek dalam satu tampilan yang terstruktur.',
            ],
            [
                'youtube_id' => '9dOqnAnN3jk',
                'title'      => 'Chat Grup & Chat Pribadi',
                'desc'       => 'Diskusi dengan tim melalui chat perusahaan, chat ruang kerja, atau chat pribadi tanpa pindah aplikasi.',
            ],
            [
                'youtube_id' => 'X3igrS1xjGg',
                'title'      => 'Buat Pengumuman Tim',
                'desc'       => 'Sampaikan informasi penting ke seluruh tim atau anggota ruang kerja dengan fitur pengumuman.',
            ],
            [
                'youtube_id' => 'xus1DGvA9hY',
                'title'      => 'Atur Jadwal & Notulensi',
                'desc'       => 'Buat jadwal rapat, atur agenda, dan lihat notulensi langsung di Koladi.',
            ],
            [
                'youtube_id' => 'xrJiLK8d4Ts',
                'title'      => 'Rencanakan dengan Mindmap',
                'desc'       => 'Susun ide, strategi, dan alur kerja tim menggunakan mindmap visual yang mudah dipahami.',
            ],
            [
                'youtube_id' => 's4PSFgk_Zs4',
                'title'      => 'Simpan & Kelola Dokumen Tim',
                'desc'       => 'Upload file, buat folder, dan kelola dokumen perusahaan secara terpusat.',
            ],
            [
                'youtube_id' => 'y9M-6FjctPk',
                'title'      => 'Lihat Laporan Kinerja Tim',
                'desc'       => 'Pantau performa tim dengan laporan kinerja dan insight berbasis DSS dari Koladi.',
            ],
        ];
    }
}
