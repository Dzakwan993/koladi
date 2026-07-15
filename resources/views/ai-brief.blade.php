@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.sweet-alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div x-data="aiBriefComponent()" class="min-h-screen bg-[#e9effd] py-8 px-4">
        <div class="max-w-full mx-auto space-y-3">

            {{-- Header --}}
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Analisis Brief Proyek</h1>
                <p class="text-sm text-slate-500 mt-1">Unggah dokumen proyek Anda dan biarkan AI menyusun strategi awal.</p>
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
                    @php
                        $confidence = $brief['summary']['confidence'] ?? null;
                        $confidencePct = $confidence !== null ? (int)($confidence * 100) . '%' : '—';
                    @endphp
                    <span
                        class="text-xs font-medium bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full whitespace-nowrap">
                        Tingkat Keyakinan: {{ $confidencePct }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="bg-slate-50 rounded-lg p-8">
                        <p class="text-xs text-slate-400 mb-1">Tujuan Utama</p>
                        <p class="text-sm text-slate-700 font-medium">{{ $brief['summary']['project_description'] ?? '—' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-8">
                        <p class="text-xs text-slate-400 mb-1">Deliverables</p>
                        @php
                            $deliverables = $brief['summary']['deliverables'] ?? [];
                            $deliverablesLabel = is_array($deliverables) ? implode(', ', $deliverables) : $deliverables;
                        @endphp
                        <p class="text-sm text-slate-700 font-medium">{{ $deliverablesLabel ?: '—' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-8">
                        <p class="text-xs text-slate-400 mb-1">Deadline Utama</p>
                        <p class="text-sm font-medium text-rose-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                            {{ $brief['summary']['main_deadline'] ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Keputusan Kunci --}}
            @if(!empty($brief['decisions']))
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11 2L3 12h6l-1 6 8-10h-6l1-6z" />
                        </svg>
                        <h2 class="font-semibold text-slate-800">Keputusan Kunci</h2>
                    </div>
                </div>

                <ul class="space-y-3">
                    @foreach ($brief['decisions'] as $decision)
                        <li class="flex items-start justify-between gap-2 text-sm text-slate-600">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 text-emerald-500 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $decision }}
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button type="button" class="text-slate-400 hover:text-blue-600" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                <button type="button" class="text-slate-400 hover:text-rose-600" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6" />
                                    </svg>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

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
                        <div
                            class="flex items-start gap-3 bg-amber-50/50 border-l-4 border-amber-400 rounded-r-lg px-3 py-2.5">
                            <span class="text-xs font-semibold text-amber-500 mt-0.5">{{ $i + 1 }}.</span>
                            <p class="text-sm text-slate-600">{{ $question }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Draft Daftar Tugas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-5 8l2 2 4-4" />
                        </svg>
                        <h2 class="font-semibold text-slate-800">Draft Daftar Tugas</h2>
                    </div>
                    <button type="button" @click="addTask()"
                        class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Tugas
                    </button>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                            <th class="text-left font-medium py-2">Tugas</th>
                            <th class="text-left font-medium py-2">Pemilik</th>
                            <th class="text-left font-medium py-2">Priority</th>
                            <th class="text-left font-medium py-2">Tanggal</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(task, index) in tasks" :key="index">
                            <tr class="border-b border-slate-50 last:border-0">
                                <td class="py-3 pr-2">
                                    <p class="font-medium text-slate-700" x-text="task.title"></p>
                                    <p class="text-xs text-slate-400" x-text="task.description"></p>
                                </td>
                                <td class="py-3 pr-2">
                                    <span class="inline-flex items-center gap-1.5">
                                        <select
                                            :name="'tasks['+index+'][assignee_id]'"
                                            x-model="task.assignee_id"
                                            class="text-xs text-slate-600 border-slate-200 rounded-lg focus:border-indigo-400 focus:ring-0">
                                            <option value="">Pilih Anggota</option>
                                            @foreach($members as $member)
                                                <option value="{{ $member->id }}">{{ $member->full_name ?? $member->name }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                </td>
                                <td class="py-3 pr-2">
                                    <span
                                        :class="{
                                            'bg-rose-50 text-rose-500': task.priority === 'high' || task.priority === 'urgent',
                                            'bg-blue-50 text-blue-500': task.priority === 'medium',
                                            'bg-slate-50 text-slate-500': task.priority === 'low'
                                        }"
                                        class="text-[10px] font-semibold px-2 py-1 rounded-md"
                                        x-text="task.priority ? task.priority.toUpperCase() : 'MED'">
                                    </span>
                                    <input type="hidden" :name="'tasks['+index+'][priority]'" :value="task.priority">
                                </td>
                                <td class="py-3 pr-2 text-slate-500 text-xs whitespace-nowrap" x-text="task.deadline"></td>
                                <td class="py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="text-slate-400 hover:text-blue-600" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                        <button type="button" @click="removeTask(index)" class="text-slate-400 hover:text-rose-600" title="Hapus">
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

            {{-- Hidden form for approve submission (submitted programmatically on CTA click) --}}
            <form id="approve-form" action="{{ route('brief.approve') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="workspace_id" value="{{ $briefWorkspaceId ?? '' }}">
                <input type="hidden" name="project_name" value="{{ $brief['summary']['project_name'] ?? '' }}">
                <input type="hidden" name="project_goal" value="{{ $brief['summary']['executive_summary'] ?? '' }}">
                <input type="hidden" name="deliverables" value="{{ $deliverablesLabel ?? '' }}">
                <input type="hidden" name="deadline" value="{{ $brief['summary']['main_deadline'] ?? '' }}">
                @foreach($brief['clarification_questions'] ?? [] as $q)
                    <input type="hidden" name="clarification_questions[]" value="{{ $q }}">
                @endforeach
                {{-- Tasks injected via JS before submit --}}
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
                    Biarkan AI menyusun strategi awal, ringkasan eksekutif, serta daftar tugas yang terstruktur secara otomatis.
                </span>
            </button>

        </div>
    </div>

@php
    $taskData = collect($brief['tasks'] ?? [])->map(fn($t) => [
        'title'       => $t['title'] ?? '',
        'description' => $t['description'] ?? '',
        'priority'    => $t['priority'] ?? 'medium',
        'deadline'    => $t['deadline'] ?? '',
        'assignee_id' => $t['assignee_id'] ?? '',
    ])->values()->all();
@endphp

@push('scripts')
<script>
    function aiBriefComponent() {
        return {
            tasks: @json($taskData),

            addTask() {
                this.tasks.push({
                    title: 'Tugas Baru',
                    description: '',
                    priority: 'medium',
                    deadline: '',
                    assignee_id: '',
                });
            },

            removeTask(index) {
                this.tasks.splice(index, 1);
            },

            submitApprove() {
                const container = document.getElementById('task-inputs');
                container.innerHTML = '';

                this.tasks.forEach((task, i) => {
                    const fields = ['title', 'description', 'priority', 'deadline', 'assignee_id'];
                    fields.forEach(field => {
                        const input = document.createElement('input');
                        input.type  = 'hidden';
                        input.name  = `tasks[${i}][${field}]`;
                        input.value = task[field] ?? '';
                        container.appendChild(input);
                    });
                });

                document.getElementById('approve-form').submit();
            },
        };
    }
</script>
@endpush
@endsection
