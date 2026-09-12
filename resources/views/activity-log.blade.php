@vite(['resources/css/app.css', 'resources/js/app.js'])
@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')

<script>
    window.__activities = @json($sortedActivities);
</script>

<div
    x-data="{
        tab: 'decision',
        activities: window.__activities || []
    }"
    class="bg-[#f3f6fc] min-h-screen">

    {{-- Workspace Navigation --}}
    @include('components.workspace-nav', ['active' => 'activity-log'])

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

            {{-- Header + Tabs --}}
            <div class="bg-white rounded-t-xl">

                {{-- Header --}}
                <div class="px-8 py-6 border-b border-gray-200">

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                        <div>

                            <h1 class="text-2xl font-bold text-slate-800">
                                Log Aktivitas & Keputusan 👋
                            </h1>

                            <p class="text-gray-500 mt-1 text-sm">
                                Pantau semua jejak langkah proyek dalam satu pusat kendali.
                            </p>

                        </div>

                        {{-- <div class="flex items-center gap-3">

                        <button
                            class="px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 text-sm flex items-center gap-2">

                            <svg class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 5h18M6 12h12M10 19h4"/>

                            </svg>

                            Filter

                        </button>

                        <button
                            class="px-4 py-2 rounded-lg bg-[#225ad6] hover:bg-[#1b4ec0] text-white text-sm flex items-center gap-2">

                            <svg class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 5v14m0 0l5-5m-5 5l-5-5"/>

                            </svg>

                            Export Laporan

                        </button>

                    </div> --}}

                    </div>

                </div>

                {{-- Sticky Tabs --}}
                <div class="sticky top-0 z-30 bg-white border-b border-gray-200">

                    <div class="flex">

                        <button @click="tab='decision'" class="px-6 py-4 text-sm font-medium border-b-2 transition"
                            :class="tab == 'decision' ?
                                    'border-blue-600 text-blue-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700'">

                            Decision Log

                        </button>

                        <button @click="tab='activity'" class="px-6 py-4 text-sm font-medium border-b-2 transition"
                            :class="tab == 'activity' ?
                                    'border-blue-600 text-blue-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700'">

                            Activity Log

                        </button>

                        <button @click="tab='ai-processing'" class="px-6 py-4 text-sm font-medium border-b-2 transition"
                            :class="tab == 'ai-processing' ?
                                    'border-blue-600 text-blue-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700'">

                            AI Processing Log

                        </button>

                    </div>

                </div>

            </div>
            {{-- /Sticky Header + Tabs --}}

            <div class="rounded-b-xl overflow-hidden">

                {{-- ========================= --}}
                {{-- DECISION LOG --}}
                {{-- ========================= --}}

                <div x-show="tab=='decision'" x-transition>

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead>

                                <tr class="bg-gray-50 text-left text-gray-600 text-sm">

                                    <th class="px-8 py-4 font-semibold">
                                        Keputusan
                                    </th>

                                    <th class="px-8 py-4 font-semibold w-48">
                                        Tanggal
                                    </th>

                                    <th class="px-8 py-4 font-semibold w-48">
                                        Evidence
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($decisions as $item)
                                <tr class="border-t hover:bg-gray-50 transition">

                                    <td class="px-8 py-6">
                                        <h3 class="font-semibold text-slate-800">{{ $item->title }}</h3>
                                        <p class="text-sm text-gray-500 mt-2">{{ $item->description }}</p>
                                    </td>

                                    <td class="px-8 py-6 text-gray-500 text-sm">
                                        {{ $item->decision_date->format('d M Y') }}
                                    </td>

                                    <td class="px-8 py-6">

                                        @if ($item->evidenceFile)
                                        <a href="{{ route('dokumen-dan-file', ['workspace' => $workspace->id]) }}?file={{ $item->evidenceFile->id }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs text-gray-700 border border-gray-200 transition">

                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>

                                            <span>{{ $item->evidenceFile->file_name ?? 'Lihat Dokumen' }}</span>

                                        </a>
                                        @else
                                        <span class="text-xs text-gray-400 italic">Tidak ada evidence</span>
                                        @endif

                                    </td>

                                </tr>

                                @empty

                                <tr>
                                    <td colspan="3" class="px-8 py-10 text-center text-gray-400 text-sm">
                                        Belum ada keputusan yang tercatat.
                                    </td>
                                </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- ========================= --}}
                {{-- ACTIVITY LOG --}}
                {{-- ========================= --}}

                <div x-show="tab=='activity'" x-transition class="py-10 px-4 md:px-10">

                    <div class="relative">

                        {{-- Garis Timeline --}}
                        <div
                            class="hidden md:block absolute left-1/2 top-0 bottom-0 w-[2px] bg-slate-200 -translate-x-1/2">
                        </div>

                        <template x-for="(item, index) in activities" :key="index">

                            <div class="relative mb-10 last:mb-0">

                                {{-- Desktop --}}
                                <div class="hidden md:flex items-center">

                                    {{-- Card Kiri --}}
                                    <div class="w-1/2 pr-12">

                                        <div x-show="item.side=='left'"
                                            class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition p-5">

                                            <div class="flex items-center justify-between mb-2">
                                                <h3 class="font-semibold text-slate-800 text-sm md:text-base" x-text="item.title"></h3>
                                                <span class="text-xs text-gray-400 font-medium" x-text="item.time"></span>
                                            </div>

                                            {{-- Task description --}}
                                            <template x-if="item.type=='task'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> membuat tugas baru:
                                                    <span class="font-medium text-slate-700" x-text="item.task_title"></span>.
                                                </p>
                                            </template>
                                            {{-- Task moved description --}}
                                            <template x-if="item.type=='task_moved'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> memindahkan tugas <span class="font-medium text-slate-700" x-text="item.task_title"></span>
                                                    <span x-show="item.old_column">dari kolom <span class="font-medium text-slate-700" x-text="item.old_column"></span></span>
                                                    ke kolom <span class="font-medium text-slate-700" x-text="item.new_column"></span>.
                                                </p>
                                            </template>
                                            {{-- File description --}}
                                            <template x-if="item.type=='file'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> mengupload file:
                                                    <span class="font-medium text-slate-700" x-text="item.file_name"></span>
                                                    <span x-show="item.folder_name">di folder <span class="font-medium text-slate-700" x-text="item.folder_name"></span></span>
                                                </p>
                                            </template>
                                            {{-- Custom/Fallback description --}}
                                            <template x-if="item.desc">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed" x-html="item.desc">
                                                </p>
                                            </template>

                                            {{-- Member description --}}
                                            <template x-if="item.type=='member'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span class="font-medium text-slate-700" x-text="item.creator"></span> bergabung ke workspace.
                                                </p>
                                            </template>

                                            {{-- Decision description --}}
                                            <template x-if="item.type=='decision'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> menetapkan keputusan:
                                                    <span class="font-semibold text-indigo-600 block mt-1" x-text="item.task_title"></span>
                                                    <span x-show="item.desc" class="text-xs text-gray-400 italic block mt-1" x-text="item.desc"></span>
                                                </p>
                                            </template>

                                        </div>

                                    </div>

                                    {{-- Icon Tengah --}}
                                    <div class="absolute left-1/2 -translate-x-1/2 z-20">

                                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white shadow-lg ring-4 ring-[#f3f6fc]"
                                            :class="{
                                                    'bg-blue-600': item.color=='blue',
                                                    'bg-green-600': item.color=='green',
                                                    'bg-red-500': item.color=='red',
                                                    'bg-purple-600': item.color=='purple',
                                                    'bg-indigo-600': item.color=='indigo'
                                                }">

                                            {{-- Upload --}}
                                            <template x-if="item.icon=='upload'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 20h16" />
                                                </svg>
                                            </template>

                                            {{-- Check --}}
                                            <template x-if="item.icon=='check'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>

                                            {{-- Calendar --}}
                                            <template x-if="item.icon=='calendar'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </template>

                                            {{-- User --}}
                                            <template x-if="item.icon=='user'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </template>

                                            {{-- Gavel (Decision) --}}
                                            <template x-if="item.icon=='gavel'">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </template>

                                        </div>

                                    </div>

                                    {{-- Card Kanan --}}
                                    <div class="w-1/2 pl-12">

                                        <div x-show="item.side=='right'"
                                            class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition p-5">

                                            <div class="flex items-center justify-between mb-2">
                                                <h3 class="font-semibold text-slate-800 text-sm md:text-base" x-text="item.title"></h3>
                                                <span class="text-xs text-gray-400 font-medium" x-text="item.time"></span>
                                            </div>

                                            {{-- Task description --}}
                                            <template x-if="item.type=='task'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> membuat tugas baru:
                                                    <span class="font-medium text-slate-700" x-text="item.task_title"></span>.
                                                </p>
                                            </template>
                                            {{-- Task moved description --}}
                                            <template x-if="item.type=='task_moved'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> memindahkan tugas <span class="font-medium text-slate-700" x-text="item.task_title"></span>
                                                    <span x-show="item.old_column">dari kolom <span class="font-medium text-slate-700" x-text="item.old_column"></span></span>
                                                    ke kolom <span class="font-medium text-slate-700" x-text="item.new_column"></span>.
                                                </p>
                                            </template>
                                            {{-- File description --}}
                                            <template x-if="item.type=='file'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> mengupload file:
                                                    <span class="font-medium text-slate-700" x-text="item.file_name"></span>
                                                    <span x-show="item.folder_name">di folder <span class="font-medium text-slate-700" x-text="item.folder_name"></span></span>
                                                </p>
                                            </template>
                                            {{-- Custom/Fallback description --}}
                                            <template x-if="item.desc">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed" x-html="item.desc">
                                                </p>
                                            </template>

                                            {{-- Member description --}}
                                            <template x-if="item.type=='member'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span class="font-medium text-slate-700" x-text="item.creator"></span> bergabung ke workspace.
                                                </p>
                                            </template>

                                            {{-- Decision description --}}
                                            <template x-if="item.type=='decision'">
                                                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                    <span x-text="item.creator"></span> menetapkan keputusan:
                                                    <span class="font-semibold text-indigo-600 block mt-1" x-text="item.task_title"></span>
                                                    <span x-show="item.desc" class="text-xs text-gray-400 italic block mt-1" x-text="item.desc"></span>
                                                </p>
                                            </template>

                                        </div>

                                    </div>

                                </div>

                                {{-- Mobile --}}
                                <div class="md:hidden flex gap-4">

                                    <div class="w-10 flex flex-col items-center">

                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white shrink-0"
                                            :class="{
                                                    'bg-blue-600': item.color=='blue',
                                                    'bg-green-600': item.color=='green',
                                                    'bg-red-500': item.color=='red',
                                                    'bg-purple-600': item.color=='purple',
                                                    'bg-indigo-600': item.color=='indigo'
                                                }">

                                            <template x-if="item.icon=='upload'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M12 16V4m0 0l-4 4m4-4l4 4M4 20h16" />
                                                </svg>
                                            </template>

                                            <template x-if="item.icon=='check'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>

                                            <template x-if="item.icon=='calendar'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </template>

                                            <template x-if="item.icon=='user'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </template>

                                            <template x-if="item.icon=='gavel'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </template>

                                        </div>

                                        <div
                                            x-show="index < activities.length - 1"
                                            class="w-[2px] flex-1 bg-slate-200 mt-2">
                                        </div>

                                    </div>

                                    <div class="flex-1 bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-2">

                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="font-semibold text-slate-800 text-sm" x-text="item.title"></h3>
                                            <span class="text-xs text-gray-400 font-medium" x-text="item.time"></span>
                                        </div>

                                        {{-- Task description --}}
                                        <template x-if="item.type=='task'">
                                            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                <span x-text="item.creator"></span> membuat tugas baru:
                                                <span class="font-medium text-slate-700" x-text="item.task_title"></span>.
                                            </p>
                                        </template>
                                        {{-- Task moved description --}}
                                        <template x-if="item.type=='task_moved'">
                                            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                <span x-text="item.creator"></span> memindahkan tugas <span class="font-medium text-slate-700" x-text="item.task_title"></span>
                                                <span x-show="item.old_column">dari kolom <span class="font-medium text-slate-700" x-text="item.old_column"></span></span>
                                                ke kolom <span class="font-medium text-slate-700" x-text="item.new_column"></span>.
                                            </p>
                                        </template>
                                        {{-- File description --}}
                                        <template x-if="item.type=='file'">
                                            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                <span x-text="item.creator"></span> mengupload file:
                                                <span class="font-medium text-slate-700" x-text="item.file_name"></span>
                                                <span x-show="item.folder_name">di folder <span class="font-medium text-slate-700" x-text="item.folder_name"></span></span>
                                            </p>
                                        </template>
                                        {{-- Member description --}}
                                        <template x-if="item.type=='member'">
                                            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                <span class="font-medium text-slate-700" x-text="item.creator"></span> bergabung ke workspace.
                                            </p>
                                        </template>

                                        {{-- Decision description --}}
                                        <template x-if="item.type=='decision'">
                                            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                                                <span x-text="item.creator"></span> menetapkan keputusan:
                                                <span class="font-semibold text-indigo-600 block mt-1" x-text="item.task_title"></span>
                                                <span x-show="item.desc" class="text-xs text-gray-400 italic block mt-1" x-text="item.desc"></span>
                                            </p>
                                        </template>
                                        {{-- Custom/Fallback description --}}
                                        <template x-if="item.desc">
                                            <p class="text-sm text-gray-500 mt-2 leading-relaxed" x-html="item.desc">
                                            </p>
                                        </template>

                                    </div>

                                </div>

                            </div>

                        </template>

                    </div>

                </div>

                {{-- ========================= --}}
                {{-- AI PROCESSING LOG --}}
                {{-- ========================= --}}
                <div x-show="tab=='ai-processing'" x-transition>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 text-left text-gray-600 text-sm">
                                    <th class="px-8 py-4 font-semibold">Proyek</th>
                                    <th class="px-8 py-4 font-semibold">Dokumen yang Diproses</th>
                                    <th class="px-8 py-4 font-semibold">Diproses Oleh</th>
                                    <th class="px-8 py-4 font-semibold">Waktu Processing</th>
                                    <th class="px-8 py-4 font-semibold w-32 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($aiProcessingLogs as $log)
                                <tr class="border-t hover:bg-gray-50 transition">
                                    <td class="px-8 py-6">
                                        <div class="font-semibold text-slate-800">{{ $log->project_name }}</div>
                                    </td>
                                    <td class="px-8 py-6 text-gray-600 text-sm">
                                        @if(!empty($log->payload['files_mapping']))
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach(array_keys($log->payload['files_mapping']) as $filename)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 border border-indigo-100 text-xs text-indigo-700">
                                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        {{ $filename }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Tidak ada dokumen</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-6 text-gray-600 text-sm">
                                        {{ $log->user->full_name ?? 'Seseorang' }}
                                    </td>
                                    <td class="px-8 py-6 text-gray-500 text-sm">
                                        {{ $log->created_at->format('d M Y H:i') }}
                                        <span class="block text-[11px] text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <a href="{{ route('ai-processing-log.show', ['workspace' => $workspace->id, 'log' => $log->id]) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-xs font-semibold text-blue-700 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-10 text-center text-gray-400 text-sm">
                                        Belum ada log AI Processing yang tercatat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            {{-- /Tab Panels --}}

        </div>

    </div>

</div>

@endsection