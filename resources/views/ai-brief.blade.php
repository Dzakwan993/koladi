@extends('layouts.app')

@section('title', 'Pengumuman')

    @section('content')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @include('components.sweet-alert')
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <div x-data="aiBriefComponent()" class="min-h-screen bg-[#e9effd] py-8 px-4">
            <div class="max-w-full mx-auto space-y-3">
                {{-- Header --}}
                <div class="flex flex-col gap-3">
                    <div class="flex">
                        @if ($briefWorkspaceId)
                            <a href="{{ route('upload-brief', $briefWorkspaceId) }}"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-blue-600 bg-white hover:bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl transition duration-200 shadow-sm hover:shadow group">
                                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform text-slate-500 group-hover:text-blue-600"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Kembali ke Unggah Dokumen</span>
                            </a>
                        @else
                            <a href="{{ route('brief.index') }}"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-blue-600 bg-white hover:bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl transition duration-200 shadow-sm hover:shadow group">
                                <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform text-slate-500 group-hover:text-blue-600"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>Kembali ke Unggah Dokumen</span>
                            </a>
                        @endif
                    </div>

                    <div>
                        <h1 class="text-2xl font-semibold text-slate-800">Analisis Brief Proyek</h1>
                        <p class="text-sm text-slate-500 mt-1">Unggah dokumen proyek Anda dan biarkan AI menyusun strategi awal.
                        </p>
                    </div>
                </div>

                {{-- Ringkasan AI --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2l1.6 4.6L16 8l-4.4 1.4L10 14l-1.6-4.6L4 8l4.4-1.4L10 2z" />
                            </svg>
                            <h2 class="font-semibold text-slate-800">Ringkasan AI</h2>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-slate-50 rounded-lg p-8 relative group">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs text-slate-400">Tujuan Utama</p>
                                <button type="button" @click="editGoal()"
                                    class="text-slate-400 hover:text-blue-600 focus:outline-none" title="Edit Tujuan Utama">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-slate-700 font-medium" x-text="projectGoal || '—'"></p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-8 relative group">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs text-slate-400">Deliverables</p>
                                <button type="button" @click="editDeliverables()"
                                    class="text-slate-400 hover:text-blue-600 focus:outline-none" title="Edit Deliverables">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-slate-700 font-medium" x-text="deliverables || '—'"></p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-8">
                            <p class="text-xs text-slate-400 mb-1">Deadline Utama</p>
                            <div class="text-sm font-medium text-rose-500 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <path d="M16 2v4M8 2v4M3 10h18" />
                                </svg>
                                <template x-if="!isEditingDeadline">
                                    <span class="flex items-center gap-2">
                                        <span x-text="formatDisplayDate(mainDeadline)"></span>
                                        <button type="button" @click="isEditingDeadline = true"
                                            class="text-slate-400 hover:text-blue-600 focus:outline-none" title="Edit Deadline">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template x-if="isEditingDeadline">
                                    <span class="flex items-center gap-1.5">
                                        <input type="date" x-model="mainDeadline"
                                            class="border border-slate-200 rounded px-2 py-0.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none text-slate-800 bg-white min-w-[130px]">
                                        <button type="button" @click="isEditingDeadline = false"
                                            class="text-emerald-500 hover:text-emerald-600 focus:outline-none" title="Simpan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Keputusan Kunci --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 2L3 12h6l-1 6 8-10h-6l1-6z" />
                            </svg>
                            <h2 class="font-semibold text-slate-800">Keputusan Kunci</h2>
                        </div>
                        <button type="button" @click="addDecision()"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Keputusan
                        </button>
                    </div>

                    <ul class="space-y-3">
                        <template x-for="(decision, index) in decisions" :key="index">
                            <li
                                class="flex items-start justify-between gap-2 text-sm text-slate-600 p-2 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 text-emerald-500 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-medium text-slate-800"
                                            x-text="typeof decision === 'object' ? decision.title : decision"></span>
                                        <div class="flex flex-wrap gap-1 mt-1"
                                            x-show="typeof decision === 'object' && decision.sources && decision.sources.length > 0">
                                            <template x-for="src in (decision.sources || [])" :key="src">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200"
                                                    x-text="src"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <button type="button" @click="editDecision(index)"
                                        class="text-slate-400 hover:text-blue-600" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="confirmDelete('decision', index)"
                                        class="text-slate-400 hover:text-rose-600" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6" />
                                        </svg>
                                    </button>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>


                {{-- Informasi yang Belum Tersedia --}}
                @if(!empty($brief['missing_information']))
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            <h2 class="font-semibold text-rose-600">Informasi yang Belum Tersedia</h2>
                        </div>
                        <ul class="space-y-2">
                            @foreach ($brief['missing_information'] as $item)
                                <li class="flex items-center gap-2 text-sm text-slate-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0"></span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                @endif

                {{-- Klarifikasi Tim ke Klien --}}
                @if(!empty($brief['clarification_questions']))
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10A8 8 0 11.001 10 8 8 0 0118 10zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-.25-8.75a.75.75 0 00-1.5 0v4.5a.75.75 0 001.5 0v-4.5z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                            <h2 class="font-semibold text-blue-600">Klarifikasi Tim ke Klien</h2>
                        </div>

                        <div class="space-y-3">
                            @foreach ($brief['clarification_questions'] as $i => $question)
                                <div class="flex items-start gap-3 bg-amber-50/50 border-l-4 border-amber-400 rounded-r-lg px-3 py-2.5">
                                    <span class="text-xs font-semibold text-amber-500 mt-0.5">{{ $i + 1 }}.</span>
                                    <p class="text-sm text-slate-600">{{ $question }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Draft Daftar Tugas & Timeline Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4">
                    <!-- Header Card: Judul di Kiri, Toggle & Tambah Tugas di Kanan -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2 pt-1">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-5 8l2 2 4-4" />
                            </svg>
                            <h2 class="font-semibold text-slate-800" x-text="viewMode === 'tasks' ? 'Draft Daftar Tugas' : 'Timeline Pekerjaan'"></h2>
                        </div>

                        <!-- Right Control: Toggle (Atas) & Tambah Tugas (Bawahnya) -->
                        <div class="flex flex-col items-end gap-2">
                            <!-- Toggle Mode -->
                            <div class="flex rounded-lg bg-blue-50 p-1 shadow-inner ring-1 ring-blue-100">
                                <button type="button" @click="viewMode = 'tasks'"
                                    :class="{
                                        'bg-blue-500 text-white shadow-sm': viewMode === 'tasks',
                                        'text-blue-700 hover:bg-blue-100': viewMode !== 'tasks'
                                    }"
                                    class="rounded-md px-3 py-1.5 text-xs font-medium transition-all duration-200">
                                    Draft Tugas
                                </button>
                                <button type="button" @click="viewMode = 'timeline'"
                                    :class="{
                                        'bg-blue-500 text-white shadow-sm': viewMode === 'timeline',
                                        'text-blue-700 hover:bg-blue-100': viewMode !== 'timeline'
                                    }"
                                    class="rounded-md px-3 py-1.5 text-xs font-medium transition-all duration-200">
                                    Draft Timeline
                                </button>
                            </div>

                            <!-- Tombol Tambah Tugas -->
                            <button type="button" @click="addTask()"
                                class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Tugas
                            </button>
                        </div>
                    </div>

                    <!-- MODE 1: Tabel Tugas (Tanpa Pemilik, dengan Tanggal Mulai & Selesai) -->
                    <div x-show="viewMode === 'tasks'" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                                    <th class="text-left font-medium py-2.5 w-5/12">Tugas &amp; Fase</th>
                                    <th class="text-left font-medium py-2.5 w-2/12">Priority</th>
                                    <th class="text-left font-medium py-2.5 w-2/12">Tanggal Mulai</th>
                                    <th class="text-left font-medium py-2.5 w-2/12">Tenggat Selesai</th>
                                    <th class="py-2.5 text-right w-1/12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(task, index) in sortedTasksList()" :key="index">
                                    <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3 pr-4">
                                            <p class="font-medium text-slate-800 text-sm" x-text="task.title || 'Tanpa Judul'"></p>
                                            <p class="text-xs text-slate-400 mt-0.5" x-text="task.description"></p>
                                            <template x-if="task.phase">
                                                <div class="inline-flex items-center gap-1 mt-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-md">
                                                    <span class="opacity-60">FASE:</span>
                                                    <span x-text="task.phase"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="py-3 pr-2 align-top pt-4">
                                            <span :class="{
                                                        'bg-rose-50 text-rose-500 border border-rose-200': task.priority === 'high' || task.priority === 'urgent',
                                                        'bg-blue-50 text-blue-500 border border-blue-200': task.priority === 'medium',
                                                        'bg-slate-50 text-slate-500 border border-slate-200': task.priority === 'low'
                                                    }" class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase"
                                                x-text="task.priority ? task.priority.toUpperCase() : 'MED'">
                                            </span>
                                        </td>
                                        <td class="py-3 pr-2 text-slate-600 text-xs whitespace-nowrap align-top pt-4 font-medium"
                                            x-text="formatDisplayDate(task.start_date)"></td>
                                        <td class="py-3 pr-2 text-slate-600 text-xs whitespace-nowrap align-top pt-4 font-medium"
                                            x-text="formatDisplayDate(task.deadline)"></td>
                                        <td class="py-3 text-right align-top pt-4">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" @click="editTaskByRef(task)"
                                                    class="text-slate-400 hover:text-blue-600 p-1 rounded-lg hover:bg-blue-50 transition" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                    </svg>
                                                </button>
                                                <button type="button" @click="confirmDelete('task', task)"
                                                    class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- MODE 2: Timeline Gantt View (Header Bulan + Minggu 1 2 3 4 & Bar per Tugas) -->
                    <div x-show="viewMode === 'timeline'" class="overflow-x-auto border border-gray-200 rounded-xl bg-white shadow-xs">
                        
                        <!-- Table Header: Phase / Progress vs Months & Weeks Grid -->
                        <div class="flex bg-gray-100 border-b text-gray-700 text-xs font-semibold select-none min-w-[900px]">
                            <!-- Kolom Kiri: Phase / Progress -->
                            <div class="w-56 px-4 py-3 border-r bg-gray-50 flex items-center justify-between">
                                <span>Phase / Progress</span>
                            </div>

                            <!-- Kolom Kanan: 12 Bulan dengan Sub-Header Minggu (1 2 3 4) -->
                            <div class="flex-1 grid grid-cols-12 divide-x divide-gray-200">
                                <template x-for="m in ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']" :key="m">
                                    <div class="flex flex-col">
                                        <!-- Header Bulan -->
                                        <div class="py-1.5 text-center text-xs font-bold text-gray-700 bg-gray-100 border-b border-gray-200" x-text="m"></div>
                                        <!-- Sub-Header Minggu (1 2 3 4) -->
                                        <div class="grid grid-cols-4 text-center text-[10px] text-gray-500 bg-gray-50 py-0.5 divide-x divide-gray-200/60">
                                            <span>1</span>
                                            <span>2</span>
                                            <span>3</span>
                                            <span>4</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Loop Phase Groups (Setiap baris tugas memiliki bar tersendiri) -->
                        <div class="divide-y divide-gray-200 min-w-[900px]">
                            <template x-for="(phase, pIdx) in getProjectPhases()" :key="pIdx">
                                <div class="flex hover:bg-gray-50/40 transition">
                                    
                                    <!-- Sisi Kiri: Info Box Fase (Blok Warna Phase) -->
                                    <div class="w-56 px-4 py-4 border-r flex flex-col justify-center select-none"
                                        :class="getPhaseSideColor(pIdx, phase.name)">
                                        <h3 class="text-sm font-bold truncate leading-tight" x-text="phase.name"></h3>
                                        
                                        <!-- Sub info tanggal / Segera -->
                                        <div class="text-xs mt-1.5">
                                            <template x-if="phase.is_immediate">
                                                <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-200 shadow-xs">
                                                    <svg class="w-2.5 h-2.5 text-amber-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    Segera
                                                </span>
                                            </template>
                                            <template x-if="!phase.is_immediate && (phase.start_date || phase.end_date)">
                                                <span class="text-gray-600 font-medium text-[11px]" x-text="formatDateRange(phase.start_date, phase.end_date)"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Sisi Kanan: Area Gantt Chart per Tugas (Masing-masing tugas 1 Bar) -->
                                    <div class="flex-1 relative bg-white flex flex-col justify-center divide-y divide-gray-100">
                                        
                                        <!-- Subtle 48 Weeks Vertical Grid Lines (12 bulan x 4 minggu) -->
                                        <div class="absolute inset-0 grid grid-cols-12 divide-x divide-gray-200/80 pointer-events-none opacity-40">
                                            <template x-for="i in 12" :key="i">
                                                <div class="h-full grid grid-cols-4 divide-x divide-gray-100">
                                                    <div></div><div></div><div></div><div></div>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Loop Setiap Tugas di dalam Fase (Setiap tugas punya 1 Bar Horizontal sendiri) -->
                                        <template x-for="(task, tIdx) in phase.tasks" :key="tIdx">
                                            <div class="relative py-2 px-2 flex items-center min-h-[44px]" @click="editTaskByRef(task)">
                                                
                                                <!-- Bar Gantt Horizontal per Tugas -->
                                                <div class="relative z-10 h-7 rounded-md transition-all duration-300 flex items-center px-2.5 shadow-xs"
                                                    :class="getTaskBarColor(pIdx, phase.name, phase.is_immediate)"
                                                    :style="`width: ${getTaskBarWidth(task, phase)}%; margin-left: ${getTaskBarLeft(task, phase)}%; min-width: 90px; max-width: 100%;`">
                                                    
                                                    <span class="text-white text-xs font-semibold truncate tracking-tight drop-shadow-xs"
                                                        x-text="task.title || 'Tugas Tanpa Judul'">
                                                    </span>
                                                </div>

                                            </div>
                                        </template>

                                    </div>

                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Hidden form for approve submission (submitted programmatically on CTA click) --}}
                <form id="approve-form" action="{{ route('brief.approve') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="workspace_id" value="{{ $briefWorkspaceId ?? '' }}">
                    <input type="hidden" name="project_name" value="{{ $brief['summary']['project_name'] ?? '' }}">
                    <input type="hidden" id="hidden-project-goal" name="project_goal"
                        value="{{ $brief['summary']['project_description'] ?? '' }}">
                    <input type="hidden" id="hidden-deliverables" name="deliverables" value="{{ $deliverablesLabel ?? '' }}">
                    <input type="hidden" id="hidden-deadline" name="deadline"
                        value="{{ $brief['summary']['main_deadline'] ?? '' }}">
                    @foreach($brief['clarification_questions'] ?? [] as $q)
                        <input type="hidden" name="clarification_questions[]" value="{{ $q }}">
                    @endforeach
                    {{-- Tasks dan Decisions diserialisasi via JS sebelum submit --}}
                    <div id="task-inputs"></div>
                </form>

                {{-- CTA --}}
                <button type="button" id="proses-ai-btn" @click="submitApprove()"
                    class="w-full bg-blue-700 hover:bg-blue-800 transition-colors text-white rounded-2xl py-4 flex flex-col items-center gap-1 text-center">
                    <span class="flex items-center gap-2 font-semibold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="4" y="4" width="16" height="16" rx="2" />
                            <path d="M9 9h6v6H9zM4 9h2M4 15h2M18 9h2M18 15h2M9 4v2M15 4v2M9 18v2M15 18v2" />
                        </svg>
                        Proses dengan AI
                    </span>
                    <span class="text-xs text-blue-100 font-normal ">
                        Biarkan AI menyusun strategi awal, ringkasan eksekutif, serta daftar tugas yang terstruktur secara
                        otomatis.
                    </span>
                </button>

            </div>

            {{-- Modal Edit Tujuan Utama --}}
            <div x-show="openEditGoalModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openEditGoalModal = false"></div>
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                <h3 class="text-base font-bold text-slate-800">Edit Tujuan Utama</h3>
                                <button type="button" @click="openEditGoalModal = false"
                                    class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tujuan Utama Proyek</label>
                                    <textarea x-model="editGoalForm.project_goal" rows="5"
                                        placeholder="Tulis tujuan proyek disini..."
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-y"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                            <button type="button" @click="saveGoal()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Simpan
                            </button>
                            <button type="button" @click="openEditGoalModal = false"
                                class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Edit Deliverables --}}
            <div x-show="openEditDeliverablesModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openEditDeliverablesModal = false"></div>
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                <h3 class="text-base font-bold text-slate-800">Edit Deliverables</h3>
                                <button type="button" @click="openEditDeliverablesModal = false"
                                    class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Deliverables Proyek</label>
                                    <textarea x-model="editDeliverablesForm.deliverables" rows="4"
                                        placeholder="Contoh: Dokumen SRS, Desain UI/UX Figma, Kode Produksi"
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-y"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                            <button type="button" @click="saveDeliverables()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Simpan
                            </button>
                            <button type="button" @click="openEditDeliverablesModal = false"
                                class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Tambah / Edit Tugas --}}
            <div x-show="openEditTaskModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openEditTaskModal = false"></div>
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                <h3 class="text-base font-bold text-slate-800"
                                    x-text="editingTaskIndex !== null ? 'Edit Tugas' : 'Tambah Tugas Baru'"></h3>
                                <button type="button" @click="openEditTaskModal = false"
                                    class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul Tugas <span
                                            class="text-rose-400">*</span></label>
                                    <input type="text" x-model="editTaskForm.title" placeholder="Masukkan judul tugas..."
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Deskripsi</label>
                                    <textarea x-model="editTaskForm.description" rows="2"
                                        placeholder="Deskripsi singkat tugas..."
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Fase / Tahapan Proyek (Opsional)</label>
                                    <select x-model="editTaskForm.phase"
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                                        <option value="">— Pilih Fase —</option>
                                        <template x-for="ph in getAvailablePhases()" :key="ph">
                                            <option :value="ph" x-text="ph"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Prioritas</label>
                                        <select x-model="editTaskForm.priority"
                                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Mulai</label>
                                        <input type="date" x-model="editTaskForm.start_date"
                                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none text-slate-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tenggat Selesai</label>
                                        <input type="date" x-model="editTaskForm.deadline"
                                            class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none text-slate-700">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                            <button type="button" @click="saveTask()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Simpan
                            </button>
                            <button type="button" @click="openEditTaskModal = false"
                                class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Tambah / Edit Keputusan --}}
            <div x-show="openEditDecisionModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                    @click="openEditDecisionModal = false"></div>

                {{-- Modal Content --}}
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                        <div class="bg-white px-6 pb-6 pt-6 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                                <h3 class="text-base font-bold text-slate-800"
                                    x-text="editingDecisionIndex !== null ? 'Edit Keputusan' : 'Tambah Keputusan Baru'"></h3>
                                <button type="button" @click="openEditDecisionModal = false"
                                    class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-4">
                                {{-- Input Title --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul Keputusan /
                                        Persetujuan</label>
                                    <textarea x-model="editDecisionForm.title" rows="3" placeholder="Tulis keputusan di sini..."
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
                                </div>

                                {{-- Checkbox Sources (List Files) --}}
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Dokumen Sumber
                                        Pendukung</label>
                                    <div
                                        class="space-y-2 max-h-36 overflow-y-auto border border-slate-100 rounded-xl p-3 bg-slate-50/50">
                                        <template x-for="fileName in availableFiles" :key="fileName">
                                            <label
                                                class="flex items-center gap-2.5 text-xs text-slate-600 cursor-pointer select-none">
                                                <input type="checkbox" :value="fileName" x-model="editDecisionForm.sources"
                                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                                                <span x-text="fileName"></span>
                                            </label>
                                        </template>
                                        <template x-if="availableFiles.length === 0">
                                            <p class="text-xs text-slate-400 italic">Tidak ada dokumen sumber terdeteksi.</p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                            <button type="button" @click="saveDecision()"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Simpan
                            </button>
                            <button type="button" @click="openEditDecisionModal = false"
                                class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Konfirmasi Hapus --}}
            <div x-show="confirmDeleteModal" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="confirmDeleteModal = false"></div>
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-sm p-6 text-center">
                        <!-- Icon Warning -->
                        <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Hapus Item?</h3>
                        <p class="text-xs text-slate-500 mb-5">Tindakan ini tidak dapat dibatalkan. Item akan dihapus dari draft.</p>
                        <div class="flex gap-2 justify-center">
                            <button type="button" @click="confirmDeleteModal = false"
                                class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="button" @click="doConfirmedDelete()"
                                class="bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $taskData = collect($brief['tasks'] ?? [])->map(fn($t) => [
                'title' => $t['title'] ?? '',
                'description' => $t['description'] ?? '',
                'priority' => $t['priority'] ?? 'medium',
                'start_date' => $t['start_date'] ?? '',
                'deadline' => $t['deadline'] ?? '',
                'assignee_id' => $t['assignee_id'] ?? '',
                'phase' => $t['phase'] ?? '',
                '_editing' => false,
            ])->values()->all();

            $decisionsData = collect($brief['decisions'] ?? [])->map(fn($d) => [
                'title' => is_array($d) ? ($d['title'] ?? '') : $d,
                'sources' => is_array($d) ? ($d['sources'] ?? []) : [],
            ])->values()->all();

            $availableFiles = array_keys(session('brief_files_mapping') ?? []);
        @endphp

        @push('scripts')
            <script>
                function aiBriefComponent() {
                    return {
                        // ─── State: View Mode ───────────────────────────────────
                        viewMode: 'tasks', // 'tasks' or 'timeline'

                        // ─── State: Tasks ───────────────────────────────────────
                        tasks: @json($taskData),

                        // ─── State: Decisions ────────────────────────────────────
                        decisions: @json($decisionsData),
                        openEditDecisionModal: false,
                        editingDecisionIndex: null,
                        editDecisionForm: {
                            title: '',
                            sources: []
                        },
                        availableFiles: @json($availableFiles),

                        // ─── State: Ringkasan ───────────────────────────────────
                        projectGoal: @json($brief['summary']['project_description'] ?? ''),
                        openEditGoalModal: false,
                        editGoalForm: {
                            project_goal: ''
                        },

                        deliverables: @json($deliverablesLabel ?? ''),
                        openEditDeliverablesModal: false,
                        editDeliverablesForm: {
                            deliverables: ''
                        },

                        // ─── State: Deadline ────────────────────────────────────
                        mainDeadline: @json(
                            isset($brief['summary']['main_deadline']) && strtotime($brief['summary']['main_deadline']) ?
                            date('Y-m-d', strtotime($brief['summary']['main_deadline'])) :
                            ($brief['summary']['main_deadline'] ?? '')
                        ),
                        isEditingDeadline: false,

                        init() {
                            // Normalize dates to YYYY-MM-DD for date inputs
                            this.tasks.forEach(t => {
                                const norm = (val) => this.parseDateToISO(val);
                                if (t.start_date) t.start_date = norm(t.start_date);
                                if (t.deadline) t.deadline = norm(t.deadline);
                            });
                        },

                        // Konversi berbagai format tanggal → YYYY-MM-DD (untuk <input type="date">)
                        parseDateToISO(str) {
                            if (!str) return '';
                            // Sudah ISO
                            if (/^\d{4}-\d{2}-\d{2}/.test(str)) return str.split('T')[0];

                            // Bulan Indonesia dan Inggris
                            const monthMap = {
                                januari:1, february:2, februari:2, maret:3, april:4, mei:5, may:5,
                                juni:6, june:6, juli:7, july:7, agustus:8, august:8,
                                september:9, oktober:10, october:10, november:11, desember:12, december:12,
                                jan:1, feb:2, mar:3, apr:4, jun:6, jul:7, agu:8, aug:8,
                                sep:9, okt:10, oct:10, nov:11, des:12, dec:12
                            };

                            // "1 September 2026" atau "September 1, 2026"
                            const m1 = str.match(/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/);
                            if (m1) {
                                const mon = monthMap[m1[2].toLowerCase()];
                                if (mon) return `${m1[3]}-${String(mon).padStart(2,'0')}-${String(m1[1]).padStart(2,'0')}`;
                            }
                            const m2 = str.match(/^([A-Za-z]+)\s+(\d{1,2}),?\s+(\d{4})$/);
                            if (m2) {
                                const mon = monthMap[m2[1].toLowerCase()];
                                if (mon) return `${m2[3]}-${String(mon).padStart(2,'0')}-${String(m2[2]).padStart(2,'0')}`;
                            }

                            // Fallback: biarkan apa adanya
                            return str;
                        },

                        formatDisplayDate(dateStr) {
                            if (!dateStr) return '—';
                            const iso = this.parseDateToISO(dateStr);
                            if (/^\d{4}-\d{2}-\d{2}$/.test(iso)) {
                                const parts = iso.split('-');
                                const date = new Date(parts[0], parts[1] - 1, parts[2]);
                                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
                            }
                            return dateStr;
                        },

                        formatDateRange(startDate, endDate) {
                            const s = this.formatDisplayDate(startDate);
                            const e = this.formatDisplayDate(endDate);
                            if (s !== '—' && e !== '—') {
                                return `${s} - ${e}`;
                            } else if (e !== '—') {
                                return `Tenggat: ${e}`;
                            } else if (s !== '—') {
                                return `Mulai: ${s}`;
                            }
                            return 'Belum ada tanggal';
                        },

                        // Daftar fase unik dari semua tasks (untuk dropdown fase di modal)
                        getAvailablePhases() {
                            const seen = new Set();
                            this.tasks.forEach(t => {
                                const p = (t.phase && t.phase.trim()) ? t.phase.trim() : null;
                                if (p) seen.add(p);
                            });
                            // Klarifikasi selalu pertama jika ada
                            const phases = [...seen];
                            phases.sort((a, b) => {
                                if (a.toLowerCase().includes('klarifikasi')) return -1;
                                if (b.toLowerCase().includes('klarifikasi')) return 1;
                                return a.localeCompare(b);
                            });
                            return phases;
                        },

                        // Tasks list dengan Klarifikasi di atas (untuk tabel Mode Draft Tugas)
                        sortedTasksList() {
                            return [...this.tasks].sort((a, b) => {
                                const aK = (a.phase || '').toLowerCase().includes('klarifikasi');
                                const bK = (b.phase || '').toLowerCase().includes('klarifikasi');
                                if (aK && !bK) return -1;
                                if (!aK && bK) return 1;
                                return 0;
                            });
                        },

                        // Edit task dari referensi object (dipakai dari Gantt bar)
                        editTaskByRef(taskObj) {
                            const idx = this.tasks.findIndex(t => t === taskObj || (t.title === taskObj.title && t.phase === taskObj.phase));
                            if (idx !== -1) this.editTask(idx);
                        },

                        getProjectPhases() {
                            const groups = {};
                            this.tasks.forEach(task => {
                                const phaseName = (task.phase && task.phase.trim()) ? task.phase.trim() : 'Fase Umum';
                                if (!groups[phaseName]) {
                                    groups[phaseName] = {
                                        name: phaseName,
                                        is_immediate: false,
                                        start_date: '',
                                        end_date: '',
                                        tasks: []
                                    };
                                }
                                groups[phaseName].tasks.push(task);

                                // Track earliest start and latest deadline for the phase
                                if (task.start_date) {
                                    if (!groups[phaseName].start_date || new Date(task.start_date) < new Date(groups[phaseName].start_date)) {
                                        groups[phaseName].start_date = task.start_date;
                                    }
                                }
                                if (task.deadline) {
                                    if (!groups[phaseName].end_date || new Date(task.deadline) > new Date(groups[phaseName].end_date)) {
                                        groups[phaseName].end_date = task.deadline;
                                    }
                                }
                            });

                            const phaseList = Object.values(groups);

                            // Calculate month-based position and bar width for Gantt visual
                            phaseList.forEach(phase => {
                                const isKlarifikasi = phase.name.toLowerCase().includes('klarifikasi');
                                phase.is_immediate = isKlarifikasi && !phase.start_date && !phase.end_date;

                                if (phase.is_immediate) {
                                    phase.bar_left = 0;
                                    phase.bar_width = 30; // compact front bar
                                } else {
                                    let startMonth = 0; // Jan = 0
                                    let endMonth = 11; // Dec = 11

                                    if (phase.start_date) {
                                        const d = new Date(phase.start_date);
                                        if (!isNaN(d.getTime())) startMonth = d.getMonth();
                                    }
                                    if (phase.end_date) {
                                        const d = new Date(phase.end_date);
                                        if (!isNaN(d.getTime())) endMonth = d.getMonth();
                                    } else {
                                        endMonth = startMonth;
                                    }

                                    if (endMonth < startMonth) endMonth = startMonth;

                                    const colWidthPercent = 100 / 12;
                                    phase.bar_left = Math.max(0, startMonth * colWidthPercent);
                                    const spanMonths = Math.max(1, (endMonth - startMonth + 1));
                                    phase.bar_width = Math.min(100 - phase.bar_left, Math.max(15, spanMonths * colWidthPercent));
                                }
                            });

                            // Sort: Klarifikasi ALWAYS at index 0 (top), followed by chronological order
                            phaseList.sort((a, b) => {
                                const aIsKlarifikasi = a.name.toLowerCase().includes('klarifikasi');
                                const bIsKlarifikasi = b.name.toLowerCase().includes('klarifikasi');
                                if (aIsKlarifikasi && !bIsKlarifikasi) return -1;
                                if (!aIsKlarifikasi && bIsKlarifikasi) return 1;

                                if (a.start_date && b.start_date) {
                                    return new Date(a.start_date) - new Date(b.start_date);
                                }
                                return 0;
                            });

                            return phaseList;
                        },

                        // Kalkulasi posisi & lebar bar per tugas (berdasarkan 48 minggu dalam 1 tahun)
                        getTaskBarLeft(task, phase) {
                            if (phase.is_immediate || (!task.start_date && !task.deadline)) {
                                return 0; // Full dari ujung kiri
                            }
                            const dateToUse = task.start_date || task.deadline;
                            const d = new Date(dateToUse);
                            if (isNaN(d.getTime())) return 0;

                            const month = d.getMonth(); // 0 - 11
                            const day = d.getDate(); // 1 - 31
                            const weekInMonth = Math.min(3, Math.floor((day - 1) / 7)); // 0 - 3
                            const totalWeekSlot = (month * 4) + weekInMonth; // 0 - 47

                            return Math.max(0, Math.min(92, (totalWeekSlot / 48) * 100));
                        },

                        getTaskBarWidth(task, phase) {
                            if (phase.is_immediate) {
                                return 100; // Full dari ujung ke ujung
                            }
                            if (!task.start_date && !task.deadline) {
                                return 100;
                            }

                            if (task.start_date && task.deadline) {
                                const s = new Date(task.start_date);
                                const e = new Date(task.deadline);
                                if (!isNaN(s.getTime()) && !isNaN(e.getTime()) && e >= s) {
                                    const sSlot = (s.getMonth() * 4) + Math.min(3, Math.floor((s.getDate() - 1) / 7));
                                    const eSlot = (e.getMonth() * 4) + Math.min(3, Math.floor((e.getDate() - 1) / 7));
                                    const diffWeeks = Math.max(1, (eSlot - sSlot + 1));
                                    const widthPercent = (diffWeeks / 48) * 100;
                                    return Math.max(8, Math.min(100, widthPercent));
                                }
                            }
                            return 12; // Default span 1-2 minggu minimal agar terbaca
                        },

                        getPhaseSideColor(idx, name) {
                            if (name.toLowerCase().includes('klarifikasi')) {
                                return 'bg-amber-50 text-amber-900 border-amber-200';
                            }
                            const colors = [
                                'bg-sky-50 text-sky-900 border-sky-100',
                                'bg-emerald-50 text-emerald-900 border-emerald-100',
                                'bg-amber-50 text-amber-900 border-amber-100',
                                'bg-purple-50 text-purple-900 border-purple-100',
                                'bg-rose-50 text-rose-900 border-rose-100',
                                'bg-indigo-50 text-indigo-900 border-indigo-100',
                            ];
                            return colors[idx % colors.length];
                        },

                        getTaskBarColor(pIdx, phaseName, isImmediate) {
                            if (isImmediate || phaseName.toLowerCase().includes('klarifikasi')) {
                                return 'bg-amber-500 hover:bg-amber-600 border border-amber-400';
                            }
                            const barColors = [
                                'bg-[#3b82f6] hover:bg-[#2563eb] border border-blue-400',    // Plan / Blue
                                'bg-[#10b981] hover:bg-[#059669] border border-emerald-400', // Develop / Green
                                'bg-[#f59e0b] hover:bg-[#d97706] border border-amber-400',   // Test / Yellow
                                'bg-[#8b5cf6] hover:bg-[#7c3aed] border border-purple-400',  // Purple
                                'bg-[#ef4444] hover:bg-[#dc2626] border border-red-400',     // Red
                                'bg-[#06b6d4] hover:bg-[#0891b2] border border-cyan-400',    // Cyan
                            ];
                            return barColors[pIdx % barColors.length];
                        },

                        // ─── Confirm Delete ───────────────────────────────────────
                        confirmDeleteModal: false,
                        _confirmDeleteType: null, // 'task' | 'decision'
                        _confirmDeletePayload: null,

                        confirmDelete(type, payload) {
                            this._confirmDeleteType = type;
                            this._confirmDeletePayload = payload;
                            this.confirmDeleteModal = true;
                        },

                        doConfirmedDelete() {
                            if (this._confirmDeleteType === 'task') {
                                this.removeTaskByRef(this._confirmDeletePayload);
                            } else if (this._confirmDeleteType === 'decision') {
                                this.removeDecision(this._confirmDeletePayload);
                            }
                            this.confirmDeleteModal = false;
                            this._confirmDeleteType = null;
                            this._confirmDeletePayload = null;
                        },

                        // ─── State: Task Modal ───────────────────────────────────
                        openEditTaskModal: false,
                        editingTaskIndex: null,
                        editTaskForm: {
                            title: '',
                            description: '',
                            priority: 'medium',
                            start_date: '',
                            deadline: '',
                            assignee_id: '',
                            _assignee_name: '',
                            phase: ''
                        },

                        // ─── Tasks CRUD ─────────────────────────────────────────
                        addTask() {
                            this.editingTaskIndex = null;
                            this.editTaskForm = {
                                title: '',
                                description: '',
                                priority: 'medium',
                                start_date: '',
                                deadline: '',
                                assignee_id: '',
                                _assignee_name: '',
                                phase: ''
                            };
                            this.openEditTaskModal = true;
                        },

                        editTask(index) {
                            this.editingTaskIndex = index;
                            const t = this.tasks[index];
                            this.editTaskForm = {
                                title: t.title || '',
                                description: t.description || '',
                                priority: t.priority || 'medium',
                                start_date: t.start_date || '',
                                deadline: t.deadline || '',
                                assignee_id: t.assignee_id || '',
                                _assignee_name: t._assignee_name || '',
                                phase: t.phase || ''
                            };
                            this.openEditTaskModal = true;
                        },

                        saveTask() {
                            if (!this.editTaskForm.title.trim()) return;
                            const entry = {
                                ...this.editTaskForm
                            };
                            if (this.editingTaskIndex !== null) {
                                this.tasks.splice(this.editingTaskIndex, 1, entry);
                            } else {
                                this.tasks.push(entry);
                            }
                            this.openEditTaskModal = false;
                        },

                        removeTask(index) {
                            this.tasks.splice(index, 1);
                        },

                        removeTaskByRef(taskObj) {
                            const idx = this.tasks.findIndex(t => t === taskObj || (t.title === taskObj.title && t.phase === taskObj.phase));
                            if (idx !== -1) this.tasks.splice(idx, 1);
                        },

                        // ─── Decisions CRUD ──────────────────────────────────────
                        addDecision() {
                            this.editingDecisionIndex = null;
                            this.editDecisionForm = {
                                title: '',
                                sources: []
                            };
                            this.openEditDecisionModal = true;
                        },

                        editDecision(index) {
                            this.editingDecisionIndex = index;
                            const d = this.decisions[index];
                            this.editDecisionForm = {
                                title: typeof d === 'object' ? d.title : d,
                                sources: typeof d === 'object' && d.sources ? [...d.sources] : [],
                            };
                            this.openEditDecisionModal = true;
                        },

                        removeDecision(index) {
                            this.decisions.splice(index, 1);
                        },

                        saveDecision() {
                            if (!this.editDecisionForm.title.trim()) return;
                            const entry = {
                                title: this.editDecisionForm.title.trim(),
                                sources: [...this.editDecisionForm.sources],
                            };
                            if (this.editingDecisionIndex !== null) {
                                this.decisions.splice(this.editingDecisionIndex, 1, entry);
                            } else {
                                this.decisions.push(entry);
                            }
                            this.openEditDecisionModal = false;
                        },

                        // ─── Goal & Deliverables CRUD ───────────────────────────
                        editGoal() {
                            this.editGoalForm.project_goal = this.projectGoal;
                            this.openEditGoalModal = true;
                        },

                        saveGoal() {
                            this.projectGoal = this.editGoalForm.project_goal;
                            this.openEditGoalModal = false;
                        },

                        editDeliverables() {
                            this.editDeliverablesForm.deliverables = this.deliverables;
                            this.openEditDeliverablesModal = true;
                        },

                        saveDeliverables() {
                            this.deliverables = this.editDeliverablesForm.deliverables;
                            this.openEditDeliverablesModal = false;
                        },

                        // ─── Form Submission ────────────────────────────────────
                        submitApprove() {
                            const container = document.getElementById('task-inputs');
                            container.innerHTML = '';

                            // Serialize tasks
                            this.tasks.forEach((task, i) => {
                                const fields = ['title', 'description', 'priority', 'start_date', 'deadline', 'assignee_id', 'phase'];
                                fields.forEach(field => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = `tasks[${i}][${field}]`;
                                    input.value = task[field] ?? '';
                                    container.appendChild(input);
                                });
                            });

                            // Serialize decisions
                            this.decisions.forEach((decision, i) => {
                                const titleInput = document.createElement('input');
                                titleInput.type = 'hidden';
                                titleInput.name = `decisions[${i}][title]`;
                                titleInput.value = typeof decision === 'object' ? decision.title : decision;
                                container.appendChild(titleInput);

                                const sources = typeof decision === 'object' && decision.sources ? decision.sources : [];
                                sources.forEach(src => {
                                    const srcInput = document.createElement('input');
                                    srcInput.type = 'hidden';
                                    srcInput.name = `decisions[${i}][sources][]`;
                                    srcInput.value = src;
                                    container.appendChild(srcInput);
                                });
                            });

                            // Update goal, deliverables, and deadline hidden inputs
                            const goalInput = document.getElementById('hidden-project-goal');
                            if (goalInput) goalInput.value = this.projectGoal;

                            const deliverablesInput = document.getElementById('hidden-deliverables');
                            if (deliverablesInput) deliverablesInput.value = this.deliverables;

                            const deadlineInput = document.getElementById('hidden-deadline');
                            if (deadlineInput) deadlineInput.value = this.mainDeadline;

                            document.getElementById('approve-form').submit();
                        },
                    };
                }
            </script>
        @endpush
    @endsection