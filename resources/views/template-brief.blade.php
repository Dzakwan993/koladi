@extends('layouts.app')

@section('title', 'Rencana Kerja Proyek')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('components.sweet-alert')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-h-screen bg-[#f3f6fc] py-6 px-4 sm:px-6 lg:px-8 w-full" x-data="templateBriefComponent()" x-init="init()">
    <div class="w-full space-y-4">

        {{-- ─── HEADER MINIMALIS & SATU-SATUNYA TOMBOL SUBMIT AI ────────────── --}}
        <div class="flex items-center justify-between bg-white rounded-2xl p-4 sm:px-6 shadow-xs border border-slate-200/70">
            <div class="flex items-center gap-3">
                <a
                    href="{{ isset($workspace) ? route('upload-brief', $workspace) : route('brief.index') }}"
                    class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 flex items-center justify-center transition-colors shadow-xs"
                    title="Kembali ke Halaman Upload">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
                <h1 class="text-base sm:text-lg font-bold text-slate-800">Formulir Rencana Kerja Proyek</h1>
            </div>

            <div class="flex items-center gap-2.5">
                {{-- Template Switcher --}}
                <div class="relative" x-data="{ openSwitcher: false }">
                    <button
                        type="button"
                        @click="openSwitcher = !openSwitcher"
                        class="text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 px-3 py-2 rounded-xl transition-all flex items-center gap-1.5">
                        <span class="text-slate-400">Template:</span>
                        <span class="font-bold text-indigo-600 truncate max-w-[130px] sm:max-w-[200px]" x-text="selectedPresetName || 'Kustom'"></span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        x-show="openSwitcher"
                        @click.away="openSwitcher = false"
                        x-cloak
                        class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50">
                        <div class="px-3 py-1.5 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase">
                            Pilih Template:
                        </div>
                        <template x-for="(tmpl, idx) in availableTemplates" :key="idx">
                            <button
                                type="button"
                                @click="applyPreset(tmpl); openSwitcher = false;"
                                class="w-full text-left px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-between transition-colors"
                                :class="form.name === tmpl.name ? 'font-bold text-indigo-600 bg-indigo-50/60' : ''">
                                <span x-text="tmpl.name" class="truncate pr-2"></span>
                                <span class="text-[10px] text-slate-400" x-text="tmpl.period"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- SATU-SATUNYA TOMBOL ANALISIS AI --}}
                <button
                    type="button"
                    id="submitBtn"
                    @click="document.getElementById('templateBriefForm').requestSubmit()"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md shadow-indigo-500/20 flex items-center gap-1.5 active:scale-95">
                    <span>Analisis AI</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ─── 2-COLUMN FULL-WIDTH GRID ────────────────────────────────────── --}}
        <form
            action="{{ route('brief.upload') }}"
            method="POST"
            enctype="multipart/form-data"
            id="templateBriefForm"
            @submit="handleSubmit($event)">
            @csrf
            {{-- Hidden Inputs --}}
            <input type="hidden" name="workspace_id" value="{{ $workspace->id ?? '' }}">
            <input type="hidden" name="is_template" value="1">
            <input type="hidden" name="template_name" :value="form.name">
            <input type="hidden" name="template_goal" :value="form.goal">
            <input type="hidden" name="template_start_date" :value="form.start_date">
            <input type="hidden" name="template_end_date" :value="form.end_date">
            <input type="hidden" name="template_period" :value="form.period">
            <input type="hidden" name="template_phases" :value="compiledPhases">
            <input type="hidden" name="template_tasks" :value="compiledTasks">
            <input type="hidden" name="template_deliverables" :value="form.deliverables">
            <input type="hidden" name="template_scope" value="">
            <input type="hidden" name="template_roles" value="">
            <input type="hidden" name="template_budget" value="">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                
                {{-- ─── LEFT COLUMN: INFO PROYEK, DELIVERABLES & DOKUMEN (4 Kolom) ─────── --}}
                <div class="lg:col-span-4 space-y-4">
                    <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200/70 space-y-4">
                        <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            Informasi Proyek
                        </h2>

                        {{-- Nama Proyek --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700">
                                Nama Proyek <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.name"
                                placeholder="Misal: Pengembangan Website"
                                class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3.5 py-2.5 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                required>
                        </div>

                        {{-- Jadwal & Estimasi Durasi Proyek --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-700">
                                Jadwal &amp; Estimasi Durasi
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-400 font-medium block mb-0.5">Tanggal Mulai</span>
                                    <input
                                        type="date"
                                        x-model="form.start_date"
                                        @change="calculatePeriod()"
                                        class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 font-medium block mb-0.5">Deadline / Selesai</span>
                                    <input
                                        type="date"
                                        x-model="form.end_date"
                                        @change="calculatePeriod()"
                                        class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-medium block mb-0.5">Estimasi Durasi</span>
                                <input
                                    type="text"
                                    x-model="form.period"
                                    placeholder="Otomatis dari kalender atau ketik manual (misal: 3 Bulan)"
                                    class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        {{-- Tujuan Utama --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700">
                                Tujuan Utama Proyek <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                x-model="form.goal"
                                rows="3"
                                placeholder="Tujuan yang ingin dicapai..."
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-y leading-relaxed"
                                required></textarea>
                        </div>

                        {{-- Output Akhir (Deliverables) --}}
                        <div class="space-y-1 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-700">
                                Output Akhir (Deliverables)
                            </label>
                            <textarea
                                x-model="form.deliverables"
                                rows="3"
                                placeholder="Hasil nyata proyek (misal: Source code di GitHub, Desain Figma, Dokumen SRS)..."
                                class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-y leading-relaxed"></textarea>
                        </div>

                        {{-- Dokumen Pendukung (Opsional) --}}
                        <div class="space-y-1.5 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700">
                                    Dokumen Pendukung <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <span class="text-[10px] font-semibold text-slate-400">PDF, DOCX, TXT</span>
                            </div>

                            {{-- Mini File Picker --}}
                            <div
                                class="border-2 border-dashed border-slate-200 hover:border-indigo-400 rounded-xl p-3 bg-slate-50/60 hover:bg-indigo-50/20 text-center cursor-pointer transition-colors"
                                @click="$refs.attachedFileInput.click()">
                                <input
                                    type="file"
                                    name="documents[]"
                                    x-ref="attachedFileInput"
                                    multiple
                                    accept=".pdf,.docx,.txt"
                                    class="hidden"
                                    @change="handleFileChange($event)">
                                <div class="flex items-center justify-center gap-1.5 text-indigo-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    <span class="text-xs font-bold">Pilih Berkas Lampiran</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">Unggah PDF/DOCX/TXT sebagai referensi tambahan AI</p>
                            </div>

                            {{-- File Chips Preview --}}
                            <template x-if="attachedFiles.length > 0">
                                <div class="space-y-1.5 pt-1">
                                    <template x-for="(file, fIdx) in attachedFiles" :key="fIdx">
                                        <div class="flex items-center justify-between bg-slate-100/90 border border-slate-200/80 rounded-lg px-2.5 py-1.5 text-xs">
                                            <div class="flex items-center gap-2 min-w-0 pr-2">
                                                <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="font-medium text-slate-700 truncate" x-text="file.name"></span>
                                                <span class="text-[10px] text-slate-400 flex-shrink-0" x-text="'(' + (file.size / 1024).toFixed(0) + ' KB)'"></span>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeFile(fIdx)"
                                                class="text-slate-400 hover:text-rose-500 transition-colors p-0.5"
                                                title="Hapus file">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>

                {{-- ─── RIGHT COLUMN: TAHAPAN (FASE), DAFTAR TUGAS & TANGGAL (8 Kolom) ── --}}
                <div class="lg:col-span-8 space-y-4">
                    <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-xs border border-slate-200/70 space-y-4">
                        <div class="pb-3 border-b border-slate-100">
                            <h2 class="text-sm font-bold text-slate-800">Tahapan Proyek, Tugas &amp; Jadwal</h2>
                        </div>

                        {{-- Repeating Phase, Task & Date Cards --}}
                        <div class="space-y-3.5">
                            <template x-for="(row, idx) in form.phaseRows" :key="idx">
                                <div class="bg-slate-50/80 border border-slate-200/80 rounded-2xl p-4 transition-all relative">
                                    
                                    <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-200/60">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-md bg-indigo-600 text-white text-[11px] font-bold flex items-center justify-center" x-text="idx + 1"></span>
                                            <span class="text-xs font-bold text-slate-700" x-text="'Fase ' + (idx + 1)"></span>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removePhaseRow(idx)"
                                            x-show="form.phaseRows.length > 1"
                                            class="text-slate-400 hover:text-rose-600 hover:bg-rose-50 px-2 py-1 rounded-lg transition-colors flex items-center gap-1"
                                            title="Hapus Fase Ini">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            <span class="text-[11px]">Hapus</span>
                                        </button>
                                    </div>

                                    {{-- 3 Kolom: Nama Fase (3 cols), Tugas (6 cols), Tanggal Pengerjaan (3 cols) --}}
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-start">
                                        
                                        {{-- Kotak Kiri: Nama Fase (3 Kolom) --}}
                                        <div class="md:col-span-3 space-y-1">
                                            <label class="block text-[11px] font-bold text-slate-600">
                                                Nama Fase <span class="text-rose-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                x-model="row.phase_name"
                                                placeholder="Contoh: Fase Desain"
                                                class="w-full text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2.5 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                                required>
                                        </div>

                                        {{-- Kotak Tengah: Rincian Tugas (6 Kolom) --}}
                                        <div class="md:col-span-6 space-y-1">
                                            <label class="block text-[11px] font-bold text-slate-600">
                                                Rincian Tugas (1 baris per tugas)
                                            </label>
                                            <textarea
                                                x-model="row.tasks"
                                                rows="3"
                                                placeholder="- Tugas 1&#10;- Tugas 2&#10;- Tugas 3"
                                                class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-y leading-relaxed"></textarea>
                                        </div>

                                        {{-- Kotak Kanan: Tanggal Pengerjaan (3 Kolom) --}}
                                        <div class="md:col-span-3 space-y-1.5">
                                            <label class="block text-[11px] font-bold text-slate-600">
                                                Tanggal Pengerjaan
                                            </label>
                                            <div class="space-y-1.5">
                                                <div>
                                                    <span class="text-[10px] text-slate-400 font-medium block mb-0.5">Mulai</span>
                                                    <input
                                                        type="date"
                                                        x-model="row.start_date"
                                                        class="w-full text-[11px] border border-slate-200 rounded-lg px-2.5 py-1.5 bg-white text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-medium">
                                                </div>
                                                <div>
                                                    <span class="text-[10px] text-slate-400 font-medium block mb-0.5">Target Selesai</span>
                                                    <input
                                                        type="date"
                                                        x-model="row.end_date"
                                                        class="w-full text-[11px] border border-slate-200 rounded-lg px-2.5 py-1.5 bg-white text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-medium">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Tambah Fase Button --}}
                        <button
                            type="button"
                            @click="addPhaseRow()"
                            class="w-full py-2.5 border border-dashed border-indigo-200 hover:border-indigo-400 bg-indigo-50/40 hover:bg-indigo-50 text-indigo-600 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>+ Tambah Baris Fase &amp; Tugas Berikutnya</span>
                        </button>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
    function templateBriefComponent() {
        return {
            selectedPresetName: '',
            attachedFiles: [],
            form: {
                name: 'Pengembangan Website & Aplikasi',
                goal: 'Membangun sistem website responsif dan aplikasi yang mudah digunakan untuk efisiensi operasional.',
                start_date: '',
                end_date: '',
                period: '3 Bulan',
                phaseRows: [
                    {
                        phase_name: 'Fase 1: Persiapan & Desain UI/UX',
                        start_date: '',
                        end_date: '',
                        tasks: '- Wawancara kebutuhan pengguna & dokumen SRS\n- Perancangan arsitektur sistem\n- Desain High-Fidelity UI di Figma'
                    },
                    {
                        phase_name: 'Fase 2: Pengembangan & Integrasi Fitur',
                        start_date: '',
                        end_date: '',
                        tasks: '- Setup database & REST API\n- Slicing antarmuka responsif\n- Integrasi fitur utama & notifikasi'
                    },
                    {
                        phase_name: 'Fase 3: Pengujian & Peluncuran',
                        start_date: '',
                        end_date: '',
                        tasks: '- QA testing fungsional & perbaikan bug\n- UAT bersama tim & serah terima'
                    }
                ],
                deliverables: 'Source code siap pakai di GitHub, dokumen teknis, dan file desain Figma.'
            },

            availableTemplates: [
                {
                    name: 'Pengembangan Website & Aplikasi',
                    period: '3 Bulan',
                    goal: 'Membangun sistem website responsif dan aplikasi yang mudah digunakan untuk efisiensi operasional.',
                    phaseRows: [
                        {
                            phase_name: 'Fase 1: Persiapan & Desain UI/UX',
                            start_date: '',
                            end_date: '',
                            tasks: '- Wawancara kebutuhan pengguna & dokumen SRS\n- Perancangan arsitektur sistem\n- Desain High-Fidelity UI di Figma'
                        },
                        {
                            phase_name: 'Fase 2: Pengembangan & Integrasi Fitur',
                            start_date: '',
                            end_date: '',
                            tasks: '- Setup database & REST API\n- Slicing antarmuka responsif\n- Integrasi fitur utama & notifikasi'
                        },
                        {
                            phase_name: 'Fase 3: Pengujian & Peluncuran',
                            start_date: '',
                            end_date: '',
                            tasks: '- QA testing fungsional & perbaikan bug\n- UAT bersama tim & serah terima'
                        }
                    ],
                    deliverables: 'Source code siap pakai di GitHub, dokumen teknis, dan file desain Figma.'
                },
                {
                    name: 'Proyek Pembangunan & Konstruksi',
                    period: '6 Bulan',
                    goal: 'Melaksanakan pekerjaan fisik bangunan dan instalasi sesuai spesifikasi teknis dan standar K3.',
                    phaseRows: [
                        {
                            phase_name: 'Fase 1: Persiapan & Fondasi',
                            start_date: '',
                            end_date: '',
                            tasks: '- Pembersihan lahan & perizinan proyek\n- Galian tanah & pengecoran fondasi struktur bawah'
                        },
                        {
                            phase_name: 'Fase 2: Struktur Utama & MEP',
                            start_date: '',
                            end_date: '',
                            tasks: '- Pemasangan kolom beton & dinding bata\n- Instalasi perpipaan & kelistrikan MEP gedung'
                        },
                        {
                            phase_name: 'Fase 3: Finishing & Serah Terima',
                            start_date: '',
                            end_date: '',
                            tasks: '- Pemasangan keramik, plafon & pengecatan\n- Uji fungsi beban & serah terima BAST'
                        }
                    ],
                    deliverables: 'Bangunan fisik selesai 100%, gambar As-Built Drawing, dan dokumen BAST.'
                },
                {
                    name: 'Kampanye Digital Marketing & Branding',
                    period: '1 Bulan',
                    goal: 'Meningkatkan brand awareness dan akuisisi leads melalui strategi promosi multi-channel.',
                    phaseRows: [
                        {
                            phase_name: 'Fase 1: Riset & Strategi Angle Iklan',
                            start_date: '',
                            end_date: '',
                            tasks: '- Riset target audiens & kompetitor\n- Penentuan angle pesan & alokasi media plan'
                        },
                        {
                            phase_name: 'Fase 2: Produksi Konten & Setup Iklan',
                            start_date: '',
                            end_date: '',
                            tasks: '- Desain aset banner grafis & copy iklan\n- Setup campaign Meta Ads & Google Ads'
                        },
                        {
                            phase_name: 'Fase 3: Peluncuran & Evaluasi ROAS',
                            start_date: '',
                            end_date: '',
                            tasks: '- Peluncuran iklan serentak\n- A/B testing & laporan performa mingguan'
                        }
                    ],
                    deliverables: 'Paket materi iklan siap pakai, landing page aktif, dan laporan analitik performa.'
                },
                {
                    name: 'Penyelenggaraan Event / Acara',
                    period: '6 Minggu',
                    goal: 'Mengorganisir dan mengeksekusi seminar hybrid secara tertib, tepat waktu, dan memuaskan.',
                    phaseRows: [
                        {
                            phase_name: 'Fase 1: Perencanaan & Pra-Acara',
                            start_date: '',
                            end_date: '',
                            tasks: '- Pembentukan panitia & rundown acara\n- Booking venue & konfirmasi pembicara tamu\n- Setup registrasi tiket online'
                        },
                        {
                            phase_name: 'Fase 2: Promosi & Persiapan Teknis',
                            start_date: '',
                            end_date: '',
                            tasks: '- Publikasi media sosial & tiket peserta\n- Setup panggung, sound system & live stream\n- Gladi resik panitia'
                        },
                        {
                            phase_name: 'Fase 3: Hari-H & Evaluasi LPJ',
                            start_date: '',
                            end_date: '',
                            tasks: '- Manajemen registrasi peserta hari-H\n- Dokumentasi foto/video & LPJ keuangan'
                        }
                    ],
                    deliverables: 'Pelaksanaan event yang sukses, dokumentasi video/foto, dan laporan evaluasi.'
                },
                {
                    name: 'Desain UI/UX & Redesign Produk',
                    period: '4 Minggu',
                    goal: 'Merancang antarmuka aplikasi yang intuitif untuk mempermudah onboarding pengguna baru.',
                    phaseRows: [
                        {
                            phase_name: 'Fase 1: Riset Pengguna & Wireframing',
                            start_date: '',
                            end_date: '',
                            tasks: '- Wawancara pengguna target & UX audit\n- Pembuatan User Flow & Wireframe awal'
                        },
                        {
                            phase_name: 'Fase 2: Design System & Mockups',
                            start_date: '',
                            end_date: '',
                            tasks: '- Pembuatan komponen Design System Figma\n- High-Fidelity UI Mockup & Prototype interaktif'
                        },
                        {
                            phase_name: 'Fase 3: Usability Testing & Handoff',
                            start_date: '',
                            end_date: '',
                            tasks: '- Sesi testing dengan 10 pengguna target\n- Finalisasi aset ekspor untuk developer'
                        }
                    ],
                    deliverables: 'File Master Figma Design System, Interactive Prototype, dan laporan pengujian.'
                },
                {
                    name: 'Template Kustom (Blank Form)',
                    period: '',
                    goal: '',
                    phaseRows: [
                        {
                            phase_name: '',
                            start_date: '',
                            end_date: '',
                            tasks: ''
                        }
                    ],
                    deliverables: ''
                }
            ],

            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const presetParam = urlParams.get('preset');
                if (presetParam !== null && presetParam !== undefined) {
                    const presetIndex = parseInt(presetParam, 10);
                    if (!isNaN(presetIndex) && this.availableTemplates[presetIndex]) {
                        this.applyPreset(this.availableTemplates[presetIndex]);
                    } else {
                        const found = this.availableTemplates.find(t => t.name.toLowerCase() === presetParam.toLowerCase());
                        if (found) this.applyPreset(found);
                    }
                } else {
                    this.applyPreset(this.availableTemplates[0]);
                }
            },

            get compiledPhases() {
                return this.form.phaseRows
                    .map((r, i) => {
                        if (!r.phase_name.trim()) return '';
                        let dateStr = '';
                        if (r.start_date && r.end_date) {
                            dateStr = ` (${r.start_date} s/d ${r.end_date})`;
                        } else if (r.end_date) {
                            dateStr = ` (Target Selesai: ${r.end_date})`;
                        } else if (r.start_date) {
                            dateStr = ` (Mulai: ${r.start_date})`;
                        }
                        return `${i + 1}. ${r.phase_name.trim()}${dateStr}`;
                    })
                    .filter(Boolean)
                    .join('\n');
            },

            get compiledTasks() {
                return this.form.phaseRows
                    .map((r) => {
                        const pName = r.phase_name.trim() || 'Fase Umum';
                        const tContent = r.tasks.trim();
                        if (!tContent) return '';
                        let dateStr = '';
                        if (r.start_date && r.end_date) {
                            dateStr = ` [Periode: ${r.start_date} s/d ${r.end_date}]`;
                        } else if (r.end_date) {
                            dateStr = ` [Deadline: ${r.end_date}]`;
                        }
                        return `[${pName}${dateStr}]\n${tContent}`;
                    })
                    .filter(Boolean)
                    .join('\n\n');
            },

            applyPreset(tmpl) {
                this.selectedPresetName = tmpl.name === 'Template Kustom (Blank Form)' ? 'Kustom' : tmpl.name;
                this.form = {
                    name: tmpl.name === 'Template Kustom (Blank Form)' ? '' : tmpl.name,
                    goal: tmpl.goal || '',
                    start_date: '',
                    end_date: '',
                    period: tmpl.period || '',
                    phaseRows: JSON.parse(JSON.stringify(tmpl.phaseRows || [{ phase_name: '', start_date: '', end_date: '', tasks: '' }])),
                    deliverables: tmpl.deliverables || ''
                };
            },

            calculatePeriod() {
                if (this.form.start_date && this.form.end_date) {
                    const start = new Date(this.form.start_date);
                    const end = new Date(this.form.end_date);
                    if (end >= start) {
                        const diffTime = Math.abs(end - start);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        if (diffDays >= 30) {
                            const months = Math.round(diffDays / 30);
                            this.form.period = `${months} Bulan (${diffDays} Hari)`;
                        } else if (diffDays >= 7) {
                            const weeks = Math.round(diffDays / 7);
                            this.form.period = `${weeks} Minggu (${diffDays} Hari)`;
                        } else {
                            this.form.period = `${diffDays} Hari`;
                        }
                    }
                }
            },

            addPhaseRow() {
                this.form.phaseRows.push({
                    phase_name: '',
                    start_date: '',
                    end_date: '',
                    tasks: ''
                });
            },

            removePhaseRow(index) {
                if (this.form.phaseRows.length > 1) {
                    this.form.phaseRows.splice(index, 1);
                }
            },

            handleFileChange(event) {
                const files = Array.from(event.target.files);
                this.attachedFiles = files;
            },

            removeFile(index) {
                this.attachedFiles.splice(index, 1);
                const dt = new DataTransfer();
                this.attachedFiles.forEach(file => dt.items.add(file));
                if (this.$refs.attachedFileInput) {
                    this.$refs.attachedFileInput.files = dt.files;
                }
            },

            handleSubmit(event) {
                if (!this.form.name.trim()) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nama Proyek Wajib Diisi',
                        text: 'Silakan masukkan nama atau judul proyek terlebih dahulu.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                if (!this.form.goal.trim()) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tujuan Proyek Wajib Diisi',
                        text: 'Silakan isi tujuan utama proyek agar AI dapat merumuskan analisis dengan tepat.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                // Show loading spinner
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Menyusun Analisis...</span>
                    `;
                }
            }
        };
    }
</script>
@endpush
@endsection
