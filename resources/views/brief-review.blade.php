@extends('layouts.app')

@section('title', 'Analisis Brief Proyek')

@section('content')
    <div x-data="briefReviewComponent()" class="max-w-6xl mx-auto px-6 py-10">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Analisis Brief Proyekaaa</h1>
                <p class="text-sm text-gray-600">Unggah dokumen proyek Anda dan biarkan AI menyusun strategi awal.</p>
            </div>
            <a href="{{ route('brief.index') }}"
                class="text-sm font-semibold text-gray-500 hover:text-gray-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <form action="{{ route('brief.approve') }}" method="POST" class="space-y-8">
            @csrf

            <!-- 1. Ringkasan AI Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-150 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1a1 1 0 112 0v1a1 1 0 11-2 0zM5.657 13.05a1 1 0 10-1.414 1.414l.707.707a1 1 0 001.414-1.414l-.707-.707zM15.657 14.95a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707z" />
                            </svg>
                        </div>
                        <h2 class="text-base font-bold text-gray-900">Ringkasan AI</h2>
                    </div>

                    <!-- Confidence Score -->
                    @if(isset($brief['project']['confidence_level']))
                        <div
                            class="bg-blue-50 border border-blue-200 text-blue-700 font-medium text-xs px-3 py-1.5 rounded-full">
                            Tingkat Keyakinan: {{ $brief['project']['confidence_level'] }}%
                        </div>
                    @endif
                </div>

                <!-- Summary Content Grid -->
                <div class="p-6 bg-gray-50/50 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Tujuan Utama -->
                    <div class="bg-white rounded-xl border border-gray-150 p-4 shadow-sm">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tujuan
                            Utama</label>
                        <input type="text" name="project_name" value="{{ $brief['project']['name'] ?? '' }}"
                            class="w-full text-sm font-bold text-gray-800 border-none p-0 focus:ring-0"
                            placeholder="Nama Proyek / Tujuan Utama">
                        <textarea name="project_goal" rows="2"
                            class="w-full text-xs text-gray-500 border-none p-0 focus:ring-0 mt-2 resize-none"
                            placeholder="Deskripsi tujuan proyek...">{{ $brief['project']['goal'] ?? '' }}</textarea>
                    </div>

                    <!-- Deliverables -->
                    <div class="bg-white rounded-xl border border-gray-150 p-4 shadow-sm">
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Deliverables</label>
                        <textarea name="deliverables" rows="3"
                            class="w-full text-sm font-semibold text-gray-800 border-none p-0 focus:ring-0 resize-none"
                            placeholder="Detail deliverable proyek...">{{ $brief['project']['deliverables'] ?? '' }}</textarea>
                    </div>

                    <!-- Deadline Utama -->
                    <div class="bg-white rounded-xl border border-gray-150 p-4 shadow-sm flex flex-col justify-between">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Deadline
                                Utama</label>
                            <div class="flex items-center gap-2 text-red-600 font-semibold text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <input type="date" name="deadline" value="{{ $brief['project']['deadline'] ?? '' }}"
                                    class="border-none p-0 text-red-600 focus:ring-0 text-sm font-semibold w-full">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Draft Daftar Tugas & Timeline Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Header Card dengan Switcher Mode & Tombol Tambah Tugas -->
                <div class="p-5 sm:p-6 border-b border-gray-150 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#225ad6] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900" x-text="viewMode === 'tasks' ? 'Draft Daftar Tugas' : 'Draft Timeline Pekerjaan'"></h2>
                            <p class="text-xs text-gray-500" x-text="viewMode === 'tasks' ? 'Tinjau rincian tugas, prioritas, dan tanggal pengerjaan.' : 'Visualisasi jadwal tahapan dan rentang waktu tugas.'"></p>
                        </div>
                    </div>

                    <!-- Switcher Mode & Tambah Tugas -->
                    <div class="flex items-center gap-3">
                        <!-- Switcher Pill: Mode Tugas | Mode Timeline -->
                        <div class="inline-flex p-1 bg-[#1970e6] rounded-full shadow-inner text-xs font-semibold text-white select-none">
                            <button type="button" @click="viewMode = 'tasks'"
                                :class="viewMode === 'tasks' ? 'bg-white text-[#1970e6] shadow-sm' : 'text-white hover:text-blue-100'"
                                class="px-3.5 py-1.5 rounded-full transition-all duration-150">
                                Mode Tugas
                            </button>
                            <span class="text-blue-200 self-center px-0.5">|</span>
                            <button type="button" @click="viewMode = 'timeline'"
                                :class="viewMode === 'timeline' ? 'bg-white text-[#1970e6] shadow-sm' : 'text-white hover:text-blue-100'"
                                class="px-3.5 py-1.5 rounded-full transition-all duration-150">
                                Mode Timeline
                            </button>
                        </div>

                        <button type="button" @click="addTask()"
                            class="text-[#225ad6] hover:text-[#1a44a6] hover:bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition">
                            <span class="text-base leading-none">+</span> Tambah Tugas
                        </button>
                    </div>
                </div>

                <!-- MODE 1: Tasks Table (Mode Tugas) -->
                <div x-show="viewMode === 'tasks'" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-5/12">
                                    Tugas &amp; Fase</th>
                                <th scope="col"
                                    class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">
                                    Priority</th>
                                <th scope="col"
                                    class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">
                                    Tanggal Mulai</th>
                                <th scope="col"
                                    class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-2/12">
                                    Tenggat Selesai</th>
                                <th scope="col"
                                    class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/12">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(task, index) in tasks" :key="index">
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <!-- Tugas, Deskripsi & Fase Input -->
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <input type="text" :name="'tasks['+index+'][title]'" x-model="task.title"
                                                class="w-full font-bold text-sm text-gray-900 border-none p-0 focus:ring-0"
                                                placeholder="Nama Tugas">
                                            <input type="text" :name="'tasks['+index+'][description]'"
                                                x-model="task.description"
                                                class="w-full text-xs text-gray-500 border-none p-0 focus:ring-0"
                                                placeholder="Tulis deskripsi detail tugas di sini...">
                                            
                                            <!-- Tag / Input Fase -->
                                            <div class="flex items-center gap-1.5 pt-1">
                                                <span class="text-[10px] uppercase font-bold text-slate-400">Fase:</span>
                                                <input type="text" :name="'tasks['+index+'][phase]'" x-model="task.phase"
                                                    class="text-[11px] font-semibold text-indigo-700 bg-indigo-50/70 border border-indigo-100 rounded-md px-2 py-0.5 focus:ring-1 focus:ring-indigo-400 focus:bg-white w-56"
                                                    placeholder="Nama Fase (Opsional)">
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Priority badge select -->
                                    <td class="px-4 py-4 align-top pt-5">
                                        <select :name="'tasks['+index+'][priority]'" x-model="task.priority" :class="{
                                                'bg-red-50 text-red-700 border-red-200': task.priority === 'urgent',
                                                'bg-orange-50 text-orange-700 border-orange-200': task.priority === 'high',
                                                'bg-blue-50 text-blue-700 border-blue-200': task.priority === 'medium',
                                                'bg-gray-50 text-gray-700 border-gray-200': task.priority === 'low'
                                            }" class="text-xs font-bold rounded-full px-3 py-1.5 border focus:ring-0 cursor-pointer">
                                            <option value="low">LOW</option>
                                            <option value="medium">MED</option>
                                            <option value="high">HIGH</option>
                                            <option value="urgent">URGENT</option>
                                        </select>
                                    </td>

                                    <!-- Tanggal Mulai -->
                                    <td class="px-4 py-4 align-top pt-4">
                                        <input type="date" :name="'tasks['+index+'][start_date]'" x-model="task.start_date"
                                            class="text-xs text-gray-700 border-gray-200 rounded-lg focus:border-[#225ad6] focus:ring-0 w-full">
                                    </td>

                                    <!-- Tenggat Selesai -->
                                    <td class="px-4 py-4 align-top pt-4">
                                        <input type="date" :name="'tasks['+index+'][deadline]'" x-model="task.deadline"
                                            class="text-xs text-gray-700 border-gray-200 rounded-lg focus:border-[#225ad6] focus:ring-0 w-full">
                                    </td>

                                    <!-- Delete Action -->
                                    <td class="px-4 py-4 text-center align-top pt-4">
                                        <button type="button" @click="removeTask(index)"
                                            class="text-red-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition"
                                            title="Hapus Tugas">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- MODE 2: Timeline Gantt View (Mode Timeline) -->
                <div x-show="viewMode === 'timeline'" class="p-4 sm:p-6 bg-slate-50/50 space-y-6">
                    <template x-for="(phaseGroup, pIdx) in getPhasesGrouped()" :key="pIdx">
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
                            
                            <!-- Phase Header Bar (Gaya Gantt Phase Banner) -->
                            <div class="px-4 py-2.5 font-bold text-xs flex items-center justify-between border-b"
                                :class="getPhaseHeaderClass(pIdx, phaseGroup.name)">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span class="tracking-wide uppercase font-extrabold" x-text="phaseGroup.name"></span>
                                </div>
                                <span class="text-[11px] font-semibold opacity-90" x-text="`${phaseGroup.tasks.length} Tugas`"></span>
                            </div>

                            <!-- List Tugas di dalam Fase -->
                            <div class="divide-y divide-slate-100">
                                <template x-for="(taskItem, tIdx) in phaseGroup.tasks" :key="tIdx">
                                    <div class="px-4 py-3 hover:bg-slate-50/80 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-3">
                                        
                                        <!-- Nama Tugas & Keterangan -->
                                        <div class="flex-1 min-w-0 pr-4">
                                            <div class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                <h4 class="text-xs font-bold text-slate-800 truncate" x-text="taskItem.title || 'Tugas Tanpa Judul'"></h4>
                                                
                                                <span :class="{
                                                    'bg-red-50 text-red-700 border-red-200': taskItem.priority === 'urgent',
                                                    'bg-orange-50 text-orange-700 border-orange-200': taskItem.priority === 'high',
                                                    'bg-blue-50 text-blue-700 border-blue-200': taskItem.priority === 'medium',
                                                    'bg-gray-50 text-gray-700 border-gray-200': taskItem.priority === 'low'
                                                }" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border">
                                                    <span x-text="taskItem.priority || 'MED'"></span>
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5 pl-3.5 line-clamp-1" x-text="taskItem.description || '-'"></p>
                                        </div>

                                        <!-- Rentang Waktu / Gantt Bar Mini -->
                                        <div class="flex items-center gap-3 flex-shrink-0">
                                            <div class="text-[11px] text-slate-600 bg-slate-100/90 border border-slate-200/80 rounded-lg px-2.5 py-1 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span class="font-medium" x-text="formatDateRange(taskItem.start_date, taskItem.deadline)"></span>
                                            </div>
                                        </div>

                                    </div>
                                </template>
                            </div>

                        </div>
                    </template>
                </div>
            </div>

            <!-- 3. Klarifikasi Tim ke Klien Card -->
            <template x-if="questions.length > 0">
                <div class="bg-amber-50/50 border border-amber-200/80 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-base font-bold text-amber-800">Klarifikasi Tim ke Klien</h2>
                    </div>

                    <ol class="list-decimal list-inside space-y-3">
                        <template x-for="(question, qIndex) in questions" :key="qIndex">
                            <li class="text-sm text-amber-800 leading-relaxed font-medium">
                                <span x-text="question"></span>
                                <!-- Silent submit of questions to description -->
                                <input type="hidden" name="clarification_questions[]" :value="question">
                            </li>
                        </template>
                    </ol>
                </div>
            </template>

            <!-- Submit actions -->
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('brief.index') }}"
                    class="bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-medium text-sm px-6 py-3 rounded-lg transition">
                    Batal
                </a>
                <button type="submit"
                    class="bg-[#225ad6] hover:bg-[#1a44a6] text-white font-medium text-sm px-6 py-3 rounded-lg transition">
                    Setujui &amp; Buat Proyek
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function briefReviewComponent() {
                return {
                    viewMode: 'tasks', // 'tasks' or 'timeline'
                    tasks: @json($brief['tasks'] ?? []),
                    questions: @json($brief['clarification_questions'] ?? []),

                    init() {
                        // Initialize default date format correctly
                        this.tasks.forEach(task => {
                            if (task.start_date) {
                                task.start_date = this.formatDate(task.start_date);
                            } else {
                                task.start_date = '';
                            }

                            if (task.deadline) {
                                task.deadline = this.formatDate(task.deadline);
                            } else {
                                task.deadline = '';
                            }

                            if (!task.phase) {
                                task.phase = '';
                            }
                        });
                    },

                    formatDate(dateStr) {
                        if (!dateStr) return '';
                        try {
                            const date = new Date(dateStr);
                            if (isNaN(date.getTime())) return '';
                            return date.toISOString().split('T')[0];
                        } catch (e) {
                            return '';
                        }
                    },

                    formatDateRange(startDate, endDate) {
                        if (startDate && endDate) {
                            return `${startDate} s/d ${endDate}`;
                        } else if (endDate) {
                            return `Tenggat: ${endDate}`;
                        } else if (startDate) {
                            return `Mulai: ${startDate}`;
                        }
                        return 'Belum ada tanggal';
                    },

                    getPhasesGrouped() {
                        const groups = {};
                        this.tasks.forEach(task => {
                            const phaseName = (task.phase && task.phase.trim()) ? task.phase.trim() : 'Fase Umum / Lainnya';
                            if (!groups[phaseName]) {
                                groups[phaseName] = {
                                    name: phaseName,
                                    tasks: []
                                };
                            }
                            groups[phaseName].tasks.push(task);
                        });
                        return Object.values(groups);
                    },

                    getPhaseHeaderClass(idx, name) {
                        if (name.toLowerCase().includes('klarifikasi')) {
                            return 'bg-amber-100 text-amber-900 border-amber-200';
                        }
                        const colors = [
                            'bg-orange-100 text-orange-950 border-orange-200',
                            'bg-blue-100 text-blue-950 border-blue-200',
                            'bg-emerald-100 text-emerald-950 border-emerald-200',
                            'bg-yellow-100 text-yellow-950 border-yellow-200',
                            'bg-purple-100 text-purple-950 border-purple-200',
                            'bg-pink-100 text-pink-950 border-pink-200',
                        ];
                        return colors[idx % colors.length];
                    },

                    addTask() {
                        this.tasks.push({
                            title: 'Tugas Baru',
                            description: '',
                            priority: 'medium',
                            start_date: '',
                            deadline: '',
                            phase: ''
                        });
                    },

                    removeTask(index) {
                        this.tasks.splice(index, 1);
                    }
                }
            }
        </script>
    @endpush
@endsection