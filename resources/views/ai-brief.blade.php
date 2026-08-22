@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
@php
$deliverablesLabel = is_array($brief['summary']['deliverables'] ?? null)
    ? implode(', ', $brief['summary']['deliverables'])
    : ($brief['summary']['deliverables'] ?? '');

$taskData = collect($brief['tasks'] ?? [])->map(fn($t) => [
    'title' => $t['title'] ?? '',
    'description' => $t['description'] ?? '',
    'priority' => $t['priority'] ?? 'medium',
    'deadline' => $t['deadline'] ?? '',
    'assignee_id' => $t['assignee_id'] ?? '',
    '_editing' => false,
])->values()->all();

$decisionsData = collect($brief['decisions'] ?? [])->map(fn($d) => [
    'title' => is_array($d) ? ($d['title'] ?? '') : $d,
    'sources' => is_array($d) ? ($d['sources'] ?? []) : [],
])->values()->all();

$availableFiles = isset($isHistory) && $isHistory
    ? array_keys($brief['files_mapping'] ?? [])
    : array_keys(session('brief_files_mapping') ?? []);
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('components.sweet-alert')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div x-data="aiBriefComponent()" class="min-h-screen bg-[#e9effd] py-8 px-4">
    <div class="max-w-full mx-auto space-y-3">

        {{-- Header --}}
        <div class="flex flex-col gap-3">
            <div class="flex">
                @if (isset($isHistory) && $isHistory)
                    <a href="{{ route('activity-log', $briefWorkspaceId) }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-blue-600 bg-white hover:bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl transition duration-200 shadow-sm hover:shadow group">
                        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform text-slate-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Kembali ke Log Aktivitas</span>
                    </a>
                @elseif ($briefWorkspaceId)
                    <a href="{{ route('upload-brief', $briefWorkspaceId) }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-blue-600 bg-white hover:bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl transition duration-200 shadow-sm hover:shadow group">
                        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform text-slate-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Kembali ke Unggah Dokumen</span>
                    </a>
                @else
                    <a href="{{ route('brief.index') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-blue-600 bg-white hover:bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl transition duration-200 shadow-sm hover:shadow group">
                        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform text-slate-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Kembali ke Unggah Dokumen</span>
                    </a>
                @endif
            </div>

            <div>
                <h1 class="text-2xl font-semibold text-slate-800">
                    {{ (isset($isHistory) && $isHistory) ? 'Detail AI Processing Log' : 'Analisis Brief Proyek' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ (isset($isHistory) && $isHistory) ? 'Riwayat strategi proyek dan daftar tugas yang terstruktur dari hasil pemrosesan AI.' : 'Unggah dokumen proyek Anda dan biarkan AI menyusun strategi awal.' }}
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
                        @if(!($isHistory ?? false))
                        <button type="button" @click="editGoal()" class="text-slate-400 hover:text-blue-600 focus:outline-none" title="Edit Tujuan Utama">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                        </button>
                        @endif
                    </div>
                    <p class="text-sm text-slate-700 font-medium" x-text="projectGoal || '—'"></p>
                </div>
                <div class="bg-slate-50 rounded-lg p-8 relative group">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs text-slate-400">Deliverables</p>
                        @if(!($isHistory ?? false))
                        <button type="button" @click="editDeliverables()" class="text-slate-400 hover:text-blue-600 focus:outline-none" title="Edit Deliverables">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                        </button>
                        @endif
                    </div>
                    <p class="text-sm text-slate-700 font-medium" x-text="deliverables || '—'"></p>
                </div>
                <div class="bg-slate-50 rounded-lg p-8">
                    <p class="text-xs text-slate-400 mb-1">Deadline Utama</p>
                    <div class="text-sm font-medium text-rose-500 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                        <template x-if="!isEditingDeadline">
                            <span class="flex items-center gap-2">
                                <span x-text="formatDisplayDate(mainDeadline)"></span>
                                @if(!($isHistory ?? false))
                                <button type="button" @click="isEditingDeadline = true" class="text-slate-400 hover:text-blue-600 focus:outline-none" title="Edit Deadline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </button>
                                @endif
                            </span>
                        </template>
                        <template x-if="isEditingDeadline">
                            <span class="flex items-center gap-1.5">
                                <input type="date" x-model="mainDeadline" class="border border-slate-200 rounded px-2 py-0.5 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none text-slate-800 bg-white min-w-[130px]">
                                <button type="button" @click="isEditingDeadline = false" class="text-emerald-500 hover:text-emerald-600 focus:outline-none" title="Simpan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
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
                @if(!($isHistory ?? false))
                <button type="button" @click="addDecision()"
                    class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Keputusan
                </button>
                @endif
            </div>

            <ul class="space-y-3">
                <template x-for="(decision, index) in decisions" :key="index">
                    <li class="flex items-start justify-between gap-2 text-sm text-slate-600 p-2 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-emerald-500 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="flex flex-col gap-0.5">
                                <span class="font-medium text-slate-800" x-text="typeof decision === 'object' ? decision.title : decision"></span>
                                <div class="flex flex-wrap gap-1 mt-1" x-show="typeof decision === 'object' && decision.sources && decision.sources.length > 0">
                                    <template x-for="src in (decision.sources || [])" :key="src">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200" x-text="src"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        @if(!($isHistory ?? false))
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="button" @click="editDecision(index)" class="text-slate-400 hover:text-blue-600" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </button>
                            <button type="button" @click="removeDecision(index)" class="text-slate-400 hover:text-rose-600" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6" />
                                </svg>
                            </button>
                        </div>
                        @endif
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
                @if(!($isHistory ?? false))
                <button type="button" @click="addTask()"
                    class="text-xs font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Tugas
                </button>
                @endif
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 uppercase border-b border-slate-100">
                        <th class="text-left font-medium py-2">Tugas</th>
                        <th class="text-left font-medium py-2">Pemilik</th>
                        <th class="text-left font-medium py-2">Priority</th>
                        <th class="text-left font-medium py-2">Tanggal</th>
                        @if(!($isHistory ?? false))
                        <th class="py-2"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(task, index) in tasks" :key="index">
                        <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50">
                            <td class="py-3 pr-2">
                                <p class="font-medium text-slate-700" x-text="task.title || 'Tanpa Judul'"></p>
                                <p class="text-xs text-slate-400" x-text="task.description"></p>
                            </td>
                            <td class="py-3 pr-2">
                                <span class="text-xs text-slate-600" x-text="task._assignee_name || '—'"></span>
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
                            </td>
                            <td class="py-3 pr-2 text-slate-500 text-xs whitespace-nowrap" x-text="task.deadline || '—'"></td>
                            @if(!($isHistory ?? false))
                            <td class="py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="editTask(index)" class="text-slate-400 hover:text-blue-600" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="removeTask(index)" class="text-slate-400 hover:text-rose-600" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            @endif
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
            <input type="hidden" id="hidden-project-goal" name="project_goal" value="{{ $brief['summary']['project_description'] ?? '' }}">
            <input type="hidden" id="hidden-deliverables" name="deliverables" value="{{ $deliverablesLabel ?? '' }}">
            <input type="hidden" id="hidden-deadline" name="deadline" value="{{ $brief['summary']['main_deadline'] ?? '' }}">
            @foreach($brief['clarification_questions'] ?? [] as $q)
            <input type="hidden" name="clarification_questions[]" value="{{ $q }}">
            @endforeach
            {{-- Tasks dan Decisions diserialisasi via JS sebelum submit --}}
            <div id="task-inputs"></div>
        </form>

        @if(!($isHistory ?? false))
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
        @else
        <div class="w-full bg-emerald-50 border border-emerald-200 rounded-2xl py-5 flex flex-col items-center gap-1 text-center">
            <span class="flex items-center gap-2 font-semibold text-emerald-800 text-sm">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Brief Telah Diproses
            </span>
            <span class="text-xs text-emerald-600 font-medium">
                Log AI Processing ini telah disetujui dan diterapkan sebagai proyek/tugas aktif.
            </span>
        </div>
        @endif

    </div>

    {{-- Modal Edit Tujuan Utama --}}
    <div x-show="openEditGoalModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openEditGoalModal = false"></div>
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-base font-bold text-slate-800">Edit Tujuan Utama</h3>
                        <button type="button" @click="openEditGoalModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tujuan Utama Proyek</label>
                            <textarea x-model="editGoalForm.project_goal" rows="5" placeholder="Tulis tujuan proyek disini..." class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-y"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                    <button type="button" @click="saveGoal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                        Simpan
                    </button>
                    <button type="button" @click="openEditGoalModal = false" class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
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
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-base font-bold text-slate-800">Edit Deliverables</h3>
                        <button type="button" @click="openEditDeliverablesModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Deliverables Proyek</label>
                            <textarea x-model="editDeliverablesForm.deliverables" rows="4" placeholder="Contoh: Dokumen SRS, Desain UI/UX Figma, Kode Produksi" class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-y"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                    <button type="button" @click="saveDeliverables()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                        Simpan
                    </button>
                    <button type="button" @click="openEditDeliverablesModal = false" class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
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
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-base font-bold text-slate-800" x-text="editingTaskIndex !== null ? 'Edit Tugas' : 'Tambah Tugas Baru'"></h3>
                        <button type="button" @click="openEditTaskModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul Tugas <span class="text-rose-400">*</span></label>
                            <input type="text" x-model="editTaskForm.title" placeholder="Masukkan judul tugas..." class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Deskripsi</label>
                            <textarea x-model="editTaskForm.description" rows="2" placeholder="Deskripsi singkat tugas..." class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Prioritas</label>
                                <select x-model="editTaskForm.priority" class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Deadline</label>
                                <input type="date" x-model="editTaskForm.deadline" class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none text-slate-700">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pemilik / Penanggung Jawab</label>
                            <select x-model="editTaskForm.assignee_id" @change="editTaskForm._assignee_name = $event.target.options[$event.target.selectedIndex].text" class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white">
                                <option value="">Pilih Anggota</option>
                                @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name ?? $member->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2 rounded-b-2xl">
                    <button type="button" @click="saveTask()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                        Simpan
                    </button>
                    <button type="button" @click="openEditTaskModal = false" class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah / Edit Keputusan --}}
    <div x-show="openEditDecisionModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openEditDecisionModal = false"></div>

        {{-- Modal Content --}}
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                <div class="bg-white px-6 pb-6 pt-6 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h3 class="text-base font-bold text-slate-800" x-text="editingDecisionIndex !== null ? 'Edit Keputusan' : 'Tambah Keputusan Baru'"></h3>
                        <button type="button" @click="openEditDecisionModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        {{-- Input Title --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul Keputusan / Persetujuan</label>
                            <textarea x-model="editDecisionForm.title" rows="3" placeholder="Tulis keputusan di sini..." class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
                        </div>

                        {{-- Checkbox Sources (List Files) --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Dokumen Sumber Pendukung</label>
                            <div class="space-y-2 max-h-36 overflow-y-auto border border-slate-100 rounded-xl p-3 bg-slate-50/50">
                                <template x-for="fileName in availableFiles" :key="fileName">
                                    <label class="flex items-center gap-2.5 text-xs text-slate-600 cursor-pointer select-none">
                                        <input type="checkbox" :value="fileName" x-model="editDecisionForm.sources" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
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
                    <button type="button" @click="saveDecision()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                        Simpan
                    </button>
                    <button type="button" @click="openEditDecisionModal = false" class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-medium text-xs px-4 py-2 rounded-xl transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@php
// Variables defined at the top of the file
@endphp

@push('scripts')
<script>
    function aiBriefComponent() {
        return {
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

            formatDisplayDate(dateStr) {
                if (!dateStr) return '—';
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                    const parts = dateStr.split('-');
                    const date = new Date(parts[0], parts[1] - 1, parts[2]);
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
                }
                return dateStr;
            },

            // ─── State: Task Modal ───────────────────────────────────
            openEditTaskModal: false,
            editingTaskIndex: null,
            editTaskForm: {
                title: '',
                description: '',
                priority: 'medium',
                deadline: '',
                assignee_id: '',
                _assignee_name: ''
            },

            // ─── Tasks CRUD ─────────────────────────────────────────
            addTask() {
                this.editingTaskIndex = null;
                this.editTaskForm = {
                    title: '',
                    description: '',
                    priority: 'medium',
                    deadline: '',
                    assignee_id: '',
                    _assignee_name: ''
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
                    deadline: t.deadline || '',
                    assignee_id: t.assignee_id || '',
                    _assignee_name: t._assignee_name || '',
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
                    const fields = ['title', 'description', 'priority', 'deadline', 'assignee_id'];
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