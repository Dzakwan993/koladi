@extends('layouts.app')

@section('title', 'Analisis Brief Proyek')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('components.sweet-alert')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="min-h-screen bg-[#e9effd] py-8 px-4" x-data="uploadBriefComponent()">
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="max-w-2xl mx-auto text-center">
            <h1 class="text-2xl font-bold text-slate-800">Unggah Sumber Konteks Project</h1>
            <p class="text-sm text-slate-500 mt-2">
                Unggah dokumen brief proyek Anda atau pilih template siap pakai agar AI dapat menyusun strategi, deliverables, dan daftar tugas secara otomatis.
            </p>
        </div>

        {{-- Form wrapper --}}
        <form
            action="{{ route('brief.upload') }}"
            method="POST"
            enctype="multipart/form-data"
            id="uploadForm"
            class="space-y-6"
            @submit="handleFormSubmit($event)">
            @csrf
            {{-- Kirim workspace_id jika brief diakses dari workspace tertentu --}}
            <input type="hidden" name="workspace_id" value="{{ $workspace->id ?? '' }}">

            {{-- Hidden Inputs for Template Mode --}}
            <input type="hidden" name="is_template" :value="isTemplateMode ? '1' : '0'">
            <input type="hidden" name="template_name" :value="selectedTemplate.name || ''">
            <input type="hidden" name="template_goal" :value="selectedTemplate.goal || ''">
            <input type="hidden" name="template_start_date" :value="selectedTemplate.start_date || ''">
            <input type="hidden" name="template_end_date" :value="selectedTemplate.end_date || ''">
            <input type="hidden" name="template_period" :value="selectedTemplate.period || ''">
            <input type="hidden" name="template_phases" :value="selectedTemplate.phases || ''">
            <input type="hidden" name="template_deliverables" :value="selectedTemplate.deliverables || ''">
            <input type="hidden" name="template_scope" :value="selectedTemplate.scope || ''">
            <input type="hidden" name="template_roles" :value="selectedTemplate.roles || ''">
            <input type="hidden" name="template_budget" :value="selectedTemplate.budget || ''">

            {{-- Side-by-Side Layout: Template Project (Left) & Dropzone (Right) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

                {{-- ─── LEFT COLUMN: PANDUAN MEETING & TEMPLATE PROJEK (OPSIONAL) ──────────────────────── --}}
                <div class="lg:col-span-5 flex flex-col justify-between relative">

                    {{-- Header / Subtitle --}}
                    <div class="flex items-center justify-between mb-2.5 select-none">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Template project</span>
                        
                    </div>

                    {{-- Template Projek Card (Clean SaaS style, aligned with Koladi) --}}
                    <div
                        @click="openTemplateModal()"
                        class="relative flex-1 bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 hover:border-blue-400 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer overflow-hidden group flex flex-col justify-between">
                        {{-- Card Top Content --}}
                        <div class="relative z-10 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-[#225AD6] group-hover:bg-[#225AD6] group-hover:text-white transition-colors duration-200 shadow-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>

                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-800 leading-snug group-hover:text-[#225AD6] transition-colors">Panduan &amp; Template Projek</h2>
                                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                    Belum punya dokumen tertulis? Gunakan panduan struktur ini sebagai acuan poin-poin penting yang perlu dicatat saat meeting bersama tim atau klien.
                                </p>
                            </div>

                            {{-- Features / Checklist List --}}
                            <div class="space-y-2.5 pt-3 border-t border-slate-100">
                                <div class="flex items-center gap-2.5 text-xs text-slate-600">
                                    <div class="w-4 h-4 rounded-full bg-blue-50 text-[#225AD6] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <span>Rekomendasi pencatatan tujuan &amp; target deliverables</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-xs text-slate-600">
                                    <div class="w-4 h-4 rounded-full bg-blue-50 text-[#225AD6] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <span>Template preset: Website, Event, Marketing, dll.</span>
                                </div>
                                <div class="flex items-center gap-2.5 text-xs text-slate-600">
                                    <div class="w-4 h-4 rounded-full bg-blue-50 text-[#225AD6] flex items-center justify-center flex-shrink-0">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>
                                    <span>Panduan estimasi periode &amp; batasan scope kerja</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Bottom CTA Button --}}
                        <div class="relative z-10 mt-6 pt-4 border-t border-slate-100">
                            <button
                                type="button"
                                class="w-full bg-slate-50 hover:bg-[#225AD6] border border-slate-200 hover:border-[#225AD6] text-slate-700 hover:text-white font-semibold text-xs py-2.5 rounded-xl transition duration-150 flex items-center justify-center gap-2 shadow-none group-hover:shadow-sm">
                                <span>Buka Panduan &amp; Template</span>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

                {{-- ─── RIGHT COLUMN: DROPZONE UNGGAH DOKUMEN ──────────────────────── --}}
                <div class="lg:col-span-7 flex flex-col">
                    <div class="flex items-center justify-between mb-2.5 select-none">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unggah File</span>
                    </div>

                    {{-- Dropzone Card --}}
                    <div
                        class="relative flex-1 bg-white border-2 border-dashed border-slate-200 rounded-3xl p-6 sm:p-8 overflow-hidden cursor-pointer transition-all hover:border-blue-400 hover:bg-blue-50/20 flex flex-col justify-between text-center group"
                        id="dropzone">
                        <div class="relative flex flex-col items-center">
                            {{-- File Input (Invisible - triggered only after modal consent) --}}
                            <input
                                type="file"
                                name="documents[]"
                                id="fileInput"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                multiple
                                accept=".pdf,.docx,.txt"
                                onchange="updateFileList()"
                                onclick="handleFileInputClick(event)">

                            {{-- Icon --}}
                            <div class="relative mb-3">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#225AD6] flex items-center justify-center group-hover:bg-[#225AD6] group-hover:text-white transition-colors duration-200 shadow-sm">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5"
                                        viewBox="0 0 24 24">
                                        <path d="M8 3h5l5 5v11a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <path d="M13 3v5h5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <span
                                    class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center">
                                    <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3"
                                        viewBox="0 0 24 24">
                                        <path d="M12 4v16m8-8H4" stroke-linecap="round" />
                                    </svg>
                                </span>
                            </div>

                            <h3 class="font-semibold text-slate-800 text-base">Unggah Sumber Brief</h3>
                            <p class="text-xs text-slate-500 mt-1">Tarik &amp; lepas file atau klik untuk memilih dokumen</p>

                            {{-- File type chips --}}
                            <div class="flex items-center gap-2 mt-3 relative z-20">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1">
                                    PDF
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1">
                                    DOCX
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1">
                                    TXT
                                </span>
                            </div>

                            {{-- Decorative Upload Button --}}
                            <div
                                class="mt-4 w-full max-w-xs bg-blue-700 hover:bg-blue-800 transition-colors text-white font-semibold text-xs rounded-xl py-2.5 flex items-center justify-center pointer-events-none shadow-md shadow-blue-500/20">
                                Pilih File Dokumen
                            </div>

                            <div class="w-full border-t border-slate-100 mt-5"></div>
                            <p class="text-[10px] font-medium text-slate-400 tracking-wide mt-3">ATAU IMPOR DARI</p>

                            {{-- Import sources --}}
                            <div class="grid grid-cols-5 gap-2 w-full max-w-sm mx-auto mt-3 relative z-20">
                                <div class="text-center">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-purple-50 flex items-center justify-center">
                                        <img src="{{ asset('images/icons/Meet.svg') }}" alt="Meeting Icon" class="w-6 h-6">
                                    </div>
                                    <p class="text-[11px] font-semibold text-slate-700 mt-1">Meeting</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-purple-50 flex items-center justify-center">
                                        <img src="{{ asset('images/icons/Drive.svg') }}" alt="Drive Icon" class="w-6 h-6">
                                    </div>
                                    <p class="text-[11px] font-semibold text-slate-700 mt-1">Drive</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-purple-50 flex items-center justify-center">
                                        <img src="{{ asset('images/icons/Notion.svg') }}" alt="Notion Icon" class="w-6 h-6">
                                    </div>
                                    <p class="text-[11px] font-semibold text-slate-700 mt-1">Notion</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-purple-50 flex items-center justify-center">
                                        <img src="{{ asset('images/icons/Wa.svg') }}" alt="Wa Icon" class="w-6 h-6">
                                    </div>
                                    <p class="text-[11px] font-semibold text-slate-700 mt-1">WA</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-purple-50 flex items-center justify-center">
                                        <img src="{{ asset('images/icons/Gmail.svg') }}" alt="Gmail Icon" class="w-6 h-6">
                                    </div>
                                    <p class="text-[11px] font-semibold text-slate-700 mt-1">Email</p>
                                </div>
                            </div>
                        </div>

                        {{-- Info banner --}}
                        <div class="relative w-full mt-5">
                            <div class="bg-gradient-to-r from-purple-50 via-pink-50 to-blue-50 rounded-xl px-4 py-2.5">
                                <p class="text-[11px] text-slate-600 flex items-center justify-center gap-1.5 flex-wrap text-center">
                                    <span class="inline-flex items-center gap-1 font-semibold text-red-500 shrink-0">
                                        <img src="{{ asset('images/icons/Warning.svg') }}" alt="Warning Icon" class="w-3.5 h-3.5">
                                        Penting:
                                    </span>
                                    <span>Pastikan dokumen berformat PDF, DOCX, atau TXT (Maks. 25MB).</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- 3 Info Cards (Automasi Tugas, Ringkasan Pintar, Analisis Celah) --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-5 8l2 2 4-4"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <h3 class="font-semibold text-slate-800 mt-2 text-sm">Automasi Tugas</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Draft daftar tugas akan langsung dibuat
                        berdasarkan timeline proyek.</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M8 3h5l5 5v11a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M13 3v5h5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <h3 class="font-semibold text-slate-800 mt-2 text-sm">Ringkasan Pintar</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">AI akan mengekstrak poin-poin krusial dari
                        dokumen brief Anda yang kompleks.</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <circle cx="12" cy="10" r="2.5" />
                    </svg>
                    <h3 class="font-semibold text-slate-800 mt-2 text-sm">Analisis Celah</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Identifikasi informasi yang kurang dari klien
                        sebelum proyek dimulai.</p>
                </div>
            </div>

            {{-- File List Preview (for template & uploaded files) --}}
            @php
                $savedTemplate = session('pending_template_brief');
            @endphp
            <div id="fileListContainer" class="{{ empty($savedTemplate) ? 'hidden ' : '' }}w-full bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-3">Berkas yang dipilih:</span>
                <div id="fileList" class="space-y-2">
                    @if(!empty($savedTemplate))
                    <div id="templateFileItem" class="flex items-center justify-between gap-3 bg-indigo-50/50 border border-indigo-100 rounded-xl p-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ $savedTemplate['name'] ?? 'Rencana Kerja' }}.txt</p>
                                <p class="text-[10px] text-indigo-600 font-medium">Template: {{ $savedTemplate['preset_name'] ?? 'Formulir Rencana Kerja' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ isset($workspace) ? route('workspace.brief.template', $workspace) : route('brief.template') }}"
                                class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 bg-white hover:bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-lg transition-colors">
                                Edit
                            </a>
                            <button
                                type="button"
                                @click="removePendingTemplate()"
                                class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition-colors"
                                title="Hapus Berkas Template">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Submit Button with Spinner (Full Width matching parent cards) --}}
            <div class="w-full pt-2">
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-4 rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-indigo-500/20 active:scale-[0.99]">
                    <span>Analisis dengan AI</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </button>
            </div>
        </form>

    </div>

    {{-- ─── MODAL TEMPLATE PROJEK (WIDE 2-COLUMN MODAL) ────────────────────── --}}
    <div
        x-show="showTemplateModal"
        x-cloak
        class="fixed inset-0 z-[9990] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
        style="display: none;">
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="closeTemplateModal()"></div>

        {{-- Modal Content Card (Wide max-w-5xl) --}}
        <div
            class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-5xl my-4 sm:my-8 overflow-hidden z-10 transform transition-all"
            style="animation: modalSlideIn 0.25s ease-out;">

            {{-- STEP 1: Pilihan Preset Template Proyek (Redirects to Dedicated Page on Click) --}}
            <div>
                {{-- Modal Header --}}
                <div class="px-6 sm:px-8 pt-6 pb-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-50 flex items-center justify-center flex-shrink-0 text-indigo-600 shadow-sm border border-indigo-100/60">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-slate-800 leading-tight">Pilih Panduan &amp; Template Projek</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pilih salah satu template siap pakai untuk membuka formulir rencana kerja terstruktur di halaman baru.</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="closeTemplateModal()"
                        class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition-colors"
                        title="Tutup">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body: Template Cards Grid (3 Columns on Large Screens) --}}
                <div class="p-6 sm:p-8 max-h-[72vh] overflow-y-auto space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="(tmpl, idx) in availableTemplates" :key="idx">
                            <div
                                @click="selectTemplatePreset(tmpl, idx)"
                                class="group text-left border border-slate-200 hover:border-indigo-400 hover:shadow-lg rounded-2xl p-5 bg-white hover:bg-indigo-50/20 transition-all duration-200 cursor-pointer flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                                            :class="tmpl.badgeClass"
                                            x-text="tmpl.category"></span>
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-indigo-600 group-hover:text-white text-slate-400 flex items-center justify-center transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors" x-text="tmpl.name"></h4>
                                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed line-clamp-3" x-text="tmpl.description"></p>
                                </div>

                                <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                    <span class="flex items-center gap-1.5 font-medium text-slate-600">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke-width="2" />
                                            <path stroke-linecap="round" stroke-width="2" d="M12 6v6l4 2" />
                                        </svg>
                                        <span x-text="tmpl.period"></span>
                                    </span>
                                    <span class="font-bold text-indigo-600 group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                                        <span>Gunakan</span>
                                        <span>&rarr;</span>
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── MODAL PERSETUJUAN PENGGUNAAN AI (CONSENT MODAL) ─────────────── --}}
    <div id="aiConsentModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="display: none !important;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeAiConsentModal(true)"></div>

        {{-- Modal Card --}}
        <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg mx-auto overflow-hidden z-10" style="animation: modalSlideIn 0.2s ease-out;">
            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center flex-shrink-0 text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 leading-tight">Persetujuan Pemrosesan Dokumen dengan AI</h3>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4">
                {{-- Info utama --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2l1.6 4.6L16 8l-4.4 1.4L10 14l-1.6-4.6L4 8l4.4-1.4L10 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 mb-0.5">Dokumen &amp; Konteks Anda akan diproses oleh AI</p>
                        <p class="text-xs text-slate-500 leading-relaxed">Koladi akan menganalisis dokumen atau template untuk menyusun draft project, seperti ringkasan, deliverables, daftar tugas, deadline, dan pertanyaan klarifikasi.</p>
                    </div>
                </div>

                {{-- Sebelum melanjutkan --}}
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-3">Sebelum melanjutkan</p>
                    <ul class="space-y-2.5">
                        <li class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs text-slate-600 leading-relaxed">Data hanya diproses untuk menghasilkan draft project pada workspace ini.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs text-slate-600 leading-relaxed">Hasil AI dapat mengandung kesalahan sehingga perlu ditinjau, diedit, atau ditolak sebelum disimpan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs text-slate-600 leading-relaxed">Hindari mengunggah data sensitif yang tidak diperlukan, seperti password, nomor kartu kredit, atau data keuangan pribadi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-emerald-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs text-slate-600 leading-relaxed">Data Anda diproses secara aman untuk analisis dan tidak digunakan untuk melatih model AI publik.</span>
                        </li>
                    </ul>
                </div>

                {{-- Checkbox Persetujuan --}}
                <label id="aiConsentLabel" class="flex items-start gap-3 cursor-pointer select-none group">
                    <div class="relative mt-0.5 flex-shrink-0">
                        <input
                            type="checkbox"
                            id="aiConsentCheckbox"
                            class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            onchange="handleConsentCheckbox()">
                    </div>
                    <span class="text-xs text-slate-600 leading-relaxed group-hover:text-slate-800 transition-colors">
                        Saya memahami informasi di atas dan menyetujui pemrosesan dokumen/konteks menggunakan AI.
                    </span>
                </label>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end gap-2">
                <button
                    type="button"
                    id="aiConsentCancelBtn"
                    onclick="closeAiConsentModal(true)"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button
                    type="button"
                    id="aiConsentAgreeBtn"
                    onclick="handleAiConsentApproved()"
                    disabled
                    class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Setuju &amp; Proses
                </button>
            </div>
        </div>
    </div>

    {{-- ─── MODAL PILIHAN SUMBER BERKAS (LOKAL vs WORKSPACE) ─────────────── --}}
    <div id="sourceSelectionModal" class="fixed inset-0 z-[9998] flex items-center justify-center p-4" style="display: none !important;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeSourceSelectionModal()"></div>

        {{-- Modal Card --}}
        <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg mx-auto overflow-hidden z-10" style="animation: modalSlideIn 0.2s ease-out;">
            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 leading-tight">Pilih Sumber Dokumen Brief</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan dari mana Anda ingin mengambil berkas konteks.</p>
                    </div>
                </div>
                <button type="button" onclick="closeSourceSelectionModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body Options --}}
            <div class="p-6 space-y-3.5">
                {{-- Opsi 1: Dari Komputer Lokal --}}
                <div onclick="selectLocalComputerSource()" class="group relative flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-500 bg-white hover:bg-indigo-50/20 transition-all cursor-pointer shadow-sm hover:shadow-md">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center flex-shrink-0 transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Unggah dari Komputer Lokal</h4>
                            <span class="text-xs font-semibold text-indigo-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pilih file baru berformat PDF, DOCX, atau TXT dari perangkat Anda.</p>
                    </div>
                </div>

                {{-- Opsi 2: Dari Penyimpanan Workspace --}}
                <div onclick="selectWorkspaceStorageSource()" class="group relative flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-500 bg-white hover:bg-indigo-50/20 transition-all cursor-pointer shadow-sm hover:shadow-md">
                    <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center flex-shrink-0 transition-colors shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">Penyimpanan Workspace</h4>
                            <span class="text-xs font-semibold text-indigo-600 group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pilih dari berkas atau transkrip meeting yang sudah tersimpan di <span class="font-semibold text-slate-700">{{ $workspace->name ?? 'Workspace ini' }}</span>.</p>
                        <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-bold border border-purple-100">
                            {{ count($workspaceFiles ?? []) }} Dokumen Tersedia
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── MODAL PILIH BERKAS WORKSPACE (MULTI-SELECT) ────────────────────── --}}
    <div id="workspaceFilePickerModal" class="fixed inset-0 z-[9998] flex items-center justify-center p-4 sm:p-6 overflow-y-auto" style="display: none !important;">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeWorkspaceFilePickerModal()"></div>

        {{-- Modal Content Card --}}
        <div class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-2xl my-4 overflow-hidden z-10" style="animation: modalSlideIn 0.2s ease-out;">
            {{-- Header --}}
            <div class="px-6 sm:px-8 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0 shadow-sm border border-purple-100/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-tight">Pilih Dokumen dari Workspace</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih satu atau beberapa berkas untuk dianalisis bersama oleh AI.</p>
                    </div>
                </div>
                <button type="button" onclick="closeWorkspaceFilePickerModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search & Selection Toolbar --}}
            <div class="px-6 sm:px-8 py-3 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        id="workspaceFileSearchInput"
                        oninput="filterWorkspaceFiles()"
                        placeholder="Cari nama dokumen atau transkrip..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-700">
                </div>
                <span id="selectedCountBadge" class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg shrink-0">
                    0 dipilih
                </span>
            </div>

            {{-- File List Item Selection --}}
            <div class="p-6 sm:p-8 max-h-[50vh] overflow-y-auto space-y-2" id="workspaceFileListContainer">
                @forelse($workspaceFiles ?? [] as $wsFile)
                    @php
                        $ext = strtolower(pathinfo($wsFile->file_name, PATHINFO_EXTENSION) ?: $wsFile->file_type);
                        $isTxt = in_array($ext, ['txt', 'text/plain']);
                        $isPdf = in_array($ext, ['pdf', 'application/pdf']);
                        $isDocx = in_array($ext, ['docx', 'doc']);
                    @endphp
                    <div
                        data-file-id="{{ $wsFile->id }}"
                        data-file-name="{{ $wsFile->file_name }}"
                        data-file-size="{{ $wsFile->file_size }}"
                        onclick="toggleWorkspaceFileSelection('{{ $wsFile->id }}')"
                        class="workspace-file-row group flex items-center justify-between gap-3.5 p-3.5 rounded-2xl border border-slate-200 hover:border-indigo-400 bg-white hover:bg-indigo-50/20 cursor-pointer transition-all">
                        <div class="flex items-center gap-3.5 min-w-0">
                            {{-- Checkbox --}}
                            <input
                                type="checkbox"
                                value="{{ $wsFile->id }}"
                                class="workspace-file-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer pointer-events-none"
                                onchange="updateSelectedWorkspaceFileCount()">

                            {{-- File Icon --}}
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isPdf ? 'bg-rose-50 text-rose-600' : ($isDocx ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600') }}">
                                @if($isPdf)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                @elseif($isDocx)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                    </svg>
                                @endif
                            </div>

                            {{-- File Info --}}
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate group-hover:text-indigo-600 transition-colors">{{ $wsFile->file_name }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    {{ strtoupper($ext) }} &bull; {{ number_format($wsFile->file_size / 1024, 1) }} KB &bull; {{ \Carbon\Carbon::parse($wsFile->uploaded_at)->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <span class="text-[10px] font-semibold text-slate-400 group-hover:text-indigo-600 uppercase tracking-wider shrink-0">
                            Pilih
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-slate-700">Belum ada dokumen di workspace ini</p>
                        <p class="text-[11px] text-slate-500 mt-1">Silakan pilih opsi "Unggah dari Komputer Lokal" untuk mengunggah dokumen baru.</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="px-6 sm:px-8 py-4 bg-slate-50 rounded-b-3xl border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="closeWorkspaceFilePickerModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button
                    type="button"
                    id="confirmWorkspaceFilesBtn"
                    onclick="confirmWorkspaceFilesSelection()"
                    class="px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-md shadow-indigo-500/20 flex items-center gap-2">
                    <span>Gunakan Berkas Terpilih</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Overlay Menunggu Transkrip Fireflies --}}
    <div id="transcriptWaitingOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center hidden">
        <div class="bg-white p-8 rounded-3xl shadow-xl flex flex-col items-center max-w-sm w-full mx-4 border border-slate-100" id="transcriptWaitingCard">
            <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-indigo-600 animate-spin"></div>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Menunggu Meeting Selesai...</h3>
            <p class="text-xs text-slate-500 text-center leading-relaxed">Transkrip akan otomatis masuk begitu meeting berakhir. Halaman ini tidak perlu di-refresh.</p>
        </div>
    </div>

    {{-- Premium Loading Overlay --}}
    <div id="loadingOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center hidden">
        <div class="bg-white p-8 rounded-3xl shadow-xl flex flex-col items-center max-w-sm w-full mx-4 border border-slate-100">
            <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-indigo-600 animate-spin"></div>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Menganalisis Konteks Proyek...</h3>
            <p class="text-xs text-slate-500 text-center leading-relaxed">AI sedang memproses brief Anda dan menyusun strategi proyek, timeline, serta daftar tugas terbaik.</p>
        </div>
    </div>
</div>

<style>
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-12px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes arrowBounce {

        0%,
        100% {
            transform: translateY(0) rotate(10deg);
        }

        50% {
            transform: translateY(6px) rotate(16deg);
        }
    }

    .arrow-bounce {
        animation: arrowBounce 1.6s ease-in-out infinite;
    }
</style>

@push('scripts')
<script>
    let _consentGiven = false;
    let _pendingAction = null; // 'filePicker' or 'formSubmit'
    let _selectedWorkspaceFiles = []; // Array of { id, name, size }

    function uploadBriefComponent() {
        return {
            showTemplateModal: false,
            templateStep: 1, // 1: List, 2: Form/Customization
            isTemplateMode: false,
            selectedTemplate: {
                name: '',
                goal: '',
                start_date: '',
                end_date: '',
                period: '',
                phases: '',
                deliverables: '',
                scope: '',
                roles: '',
                budget: '',
            },
            formTemplate: {
                name: '',
                goal: '',
                start_date: '',
                end_date: '',
                period: '',
                phases: '',
                deliverables: '',
                scope: '',
                roles: '',
                budget: '',
            },
            availableTemplates: [{
                    name: 'Pengembangan Website & Aplikasi',
                    category: 'Software & IT',
                    badgeClass: 'bg-indigo-50 text-indigo-600 border border-indigo-100',
                    start_date: '',
                    end_date: '',
                    period: '3 Bulan',
                    description: 'Pengembangan sistem web responsif, aplikasi e-commerce, portal perusahaan, atau platform SaaS terintegrasi.',
                    goal: 'Membangun aplikasi web dan mobile yang responsif, aman, skalabel, dengan arsitektur modern dan antarmuka intuitif untuk meningkatkan efisiensi operasional dan kepuasan pengguna.',
                    phases: '• Fase Persiapan & Perancangan:\n  - Wawancara kebutuhan pengguna & penyusunan dokumen SRS (PIC: Dzakwan Pratama)\n  - Perancangan arsitektur database & sistem (PIC: Budi Santoso)\n  - Pembuatan User Flow & Wireframe (PIC: Sarah Amanda)\n  - Desain High-Fidelity UI & Design System Figma (PIC: Sarah Amanda)\n\n• Fase Pengembangan & Integrasi:\n  - Setup repository & environment server cloud (PIC: Kevin Wijaya)\n  - Pembuatan REST API, database & autentikasi pengguna (PIC: Budi Santoso)\n  - Slicing antarmuka responsif & integrasi frontend (PIC: Rizky Ramadhan)\n  - Integrasi Payment Gateway & notifikasi sistem (PIC: Budi Santoso)\n\n• Fase Pengujian & Peluncuran:\n  - QA testing fungsional & audit keamanan (PIC: Dewi Lestari)\n  - Pelaksanaan UAT bersama klien & perbaikan bug (PIC: Dzakwan Pratama)\n  - Deployment ke server production & serah terima manual (PIC: Kevin Wijaya)',
                    deliverables: '1. Source code aplikasi siap produksi di repository GitHub\n2. Dokumen Software Requirements Specification (SRS) & Dokumentasi API\n3. Desain UI/UX High-Fidelity Figma & Design System\n4. Panduan Penggunaan (User Manual) & Panduan Setup Deployment',
                    scope: 'Perancangan database & arsitektur sistem, desain antarmuka pengguna (UI/UX), pengembangan frontend & backend, integrasi API & payment gateway, QA testing, dan deployment cloud.',
                    roles: '',
                    budget: 'Sesuai kesepakatan kontrak, penyediaan server VPS/Cloud dan domain aktif.'
                },
                {
                    name: 'Proyek Pembangunan & Konstruksi',
                    category: 'Konstruksi & Sipil',
                    badgeClass: 'bg-amber-50 text-amber-600 border border-amber-100',
                    start_date: '',
                    end_date: '',
                    period: '6 Bulan',
                    description: 'Pembangunan gedung, renovasi kantor, instalasi fasilitas fisik, dan pekerjaan sipil terstandarisasi.',
                    goal: 'Melaksanakan pembangunan fisik dan renovasi fasilitas gedung sesuai standar spesifikasi teknis arsitektur, RAB yang telah disetujui, dan target keselamatan kerja (K3).',
                    phases: '• Fase Persiapan & Struktur Bawah:\n  - Pembersihan lahan, pengukuran elevasi & perizinan PBG (PIC: Ir. Hendra Setiawan)\n  - Pengujian sondir tanah & pekerjaan galian fondasi (PIC: Andi Saputra)\n  - Pengecoran fondasi tiang & sloof struktur bawah (PIC: Andi Saputra)\n  - Inspeksi keselamatan kerja (K3) & kepatuhan mutu awal (PIC: Rian Firmansyah)\n\n• Fase Pekerjaan Struktur Atas & MEP:\n  - Pemasangan kolom beton, balok, dan plat lantai (PIC: Andi Saputra)\n  - Pemasangan dinding bata & plesteran acian (PIC: Pak Slamet)\n  - Instalasi pipa air & kelistrikan MEP gedung (PIC: Agus Wahyudi)\n  - Pembuatan draft gambar kerja As-Built Drawing (PIC: Maya Anggraini)\n\n• Fase Finishing & Serah Terima:\n  - Pemasangan lantai keramik, plafon & pengecatan (PIC: Pak Slamet)\n  - Uji fungsi beban listrik, tekanan air & sanitasi (PIC: Agus Wahyudi)\n  - Audit akhir keselamatan K3 & uji kelayakan gedung (PIC: Rian Firmansyah)\n  - Penyusunan BAST & serah terima fisik bangunan (PIC: Ir. Hendra Setiawan)',
                    deliverables: '1. Bangunan fisik selesai 100% siap operasional\n2. Dokumen As-Built Drawing & Dokumen Teknis\n3. Berita Acara Serah Terima (BAST) Pekerjaan\n4. Laporan Kepatuhan Mutu & Keselamatan Kerja (K3)',
                    scope: 'Pekerjaan persiapan & pembersihan lahan, pekerjaan struktur bawah & atas, pekerjaan arsitektur & finishing, instalasi Mekanikal Elektrikal Plumbing (MEP), uji fungsi, dan serah terima.',
                    roles: '',
                    budget: 'Sesuai Rencana Anggaran Biaya (RAB) yang telah disepakati dan jadwal termin pencairan.'
                },
                {
                    name: 'Kampanye Digital Marketing & Branding',
                    category: 'Marketing & Ads',
                    badgeClass: 'bg-rose-50 text-rose-600 border border-rose-100',
                    start_date: '',
                    end_date: '',
                    period: '1 Bulan',
                    description: 'Peluncuran produk baru, kampanye multi-channel media sosial, akuisisi leads, dan periklanan digital.',
                    goal: 'Meluncurkan kampanye pemasaran digital multi-channel untuk meningkatkan brand awareness sebesar 40% dan mengakuisisi minimal 500 prospek/leads berkualitas.',
                    phases: '• Fase Riset & Perencanaan Strategi:\n  - Riset persona audiens & analisis kompetitor (PIC: Nadia Putri)\n  - Perumusan key message & konsep angle promosi (PIC: Dimas Wicaksono)\n  - Alokasi anggaran iklan per channel & media plan (PIC: Fajar Nugraha)\n\n• Fase Produksi Konten & Setup Iklan:\n  - Penulisan copy iklan feed, story & search ads (PIC: Dimas Wicaksono)\n  - Desain aset banner visual & carousel grafis (PIC: Tiara Larasati)\n  - Produksi & editing video reels / short ads (PIC: Bagus Kurnia)\n  - Setup landing page konversi & pixel tracking (PIC: Nadia Putri)\n  - Setup kampanye & targeting Meta & Google Ads (PIC: Fajar Nugraha)\n\n• Fase Peluncuran & Optimasi Kampanye:\n  - Peluncuran kampanye iklan serentak multi-channel (PIC: Fajar Nugraha)\n  - Monitoring performa harian & A/B testing materi iklan (PIC: Nadia Putri)\n  - Optimasi bid & scale up ad set berkinerja terbaik (PIC: Fajar Nugraha)\n  - Penyusunan laporan analitik performa akhir & ROAS (PIC: Nadia Putri)',
                    deliverables: '1. Paket aset konten promosi visual & video siap tayang\n2. Landing Page konversi dengan integrasi tracking analitik\n3. Akun iklan aktif (Meta Ads & Google Ads) dengan materi kampanye\n4. Laporan performa mingguan dan analitik ROAS/CPA',
                    scope: 'Riset target audiens & benchmark kompetitor, pembuatan materi konten promosi (copywriting & visual), optimasi landing page, setup kampanye periklanan digital, dan evaluasi hasil harian.',
                    roles: '',
                    budget: 'Alokasi ad spend Meta Ads & Google Ads disiapkan oleh klien, akses akun Business Manager.'
                },
                {
                    name: 'Penyelenggaraan Event / Acara',
                    category: 'Event Organizer',
                    badgeClass: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                    start_date: '',
                    end_date: '',
                    period: '6 Minggu',
                    description: 'Perencanaan dan eksekusi seminar hybrid, konferensi bisnis, workshop, atau pameran eksibisi.',
                    goal: 'Mengorganisir dan mengeksekusi seminar hybrid berkapasitas 500+ peserta dengan tingkat kepuasan peserta minimal 90% secara tepat waktu dan sesuai anggaran.',
                    phases: '• Fase Perencanaan & Pra-Acara:\n  - Pembentukan panitia & penyusunan rundown acara (PIC: Dzakwan Pratama)\n  - Pemilihan & booking venue acara serta perizinan (PIC: Radit Pratama)\n  - Kurasi & konfirmasi pembicara tamu (PIC: Annisa Maharani)\n  - Pembuatan proposal sponsorship & kontak mitra (PIC: Farhan Maulana)\n  - Setup landing page registrasi & tiket online (PIC: Sinta Bella)\n\n• Fase Promosi & Persiapan Teknis:\n  - Publikasi media sosial & kampanye tiket peserta (PIC: Sinta Bella)\n  - Pengadaan konsumsi, suvenir & perlengkapan acara (PIC: Radit Pratama)\n  - Setup panggung, tata suara, lighting & live streaming (PIC: Eko Prasetyo)\n  - Briefing seluruh panitia & gladi resik teknis (PIC: Annisa Maharani)\n\n• Fase Eksekusi Hari-H & Evaluasi:\n  - Manajemen registrasi tiket & alur peserta hari-H (PIC: Seluruh Tim)\n  - Pengawalan rundown panggung & pembicara (PIC: Annisa Maharani)\n  - Dokumentasi foto & rekaman video profesional (PIC: Gilang Ramadhan)\n  - Rekap kuesioner kepuasan & penyusunan LPJ sponsor (PIC: Dzakwan Pratama)',
                    deliverables: '1. Pelaksanaan acara seminar hybrid yang sukses dan tertib\n2. Dokumentasi foto & video profesional pasca acara\n3. Laporan evaluasi kepuasan peserta & feedback sponsor\n4. Laporan pertanggungjawaban keuangan & sponsorship',
                    scope: 'Booking venue & pengurusan izin, kurasi pembicara & sponsorship, publikasi & sistem registrasi tiket, penyediaan konsumsi & souvenir, gladi resik, dan manajemen operasional hari-H.',
                    roles: '',
                    budget: 'Target sponsorship dan penerimaan registrasi tiket seminar.'
                },
                {
                    name: 'Desain UI/UX & Redesign Produk',
                    category: 'UI/UX & Creative',
                    badgeClass: 'bg-purple-50 text-purple-600 border border-purple-100',
                    start_date: '',
                    end_date: '',
                    period: '4 Minggu',
                    description: 'User research, wireframing, high-fidelity design system Figma, dan usability testing produk.',
                    goal: 'Melakukan riset pengalaman pengguna dan merancang ulang antarmuka aplikasi guna menurunkan bounce rate dan mempermudah onboarding pengguna baru.',
                    phases: '• Fase Riset Pengguna & Wireframing:\n  - Wawancara pengguna target & audit UX aplikasi saat ini (PIC: Gita Savitri)\n  - Pembuatan User Persona & Journey Mapping (PIC: Sarah Amanda)\n  - Perancangan arsitektur informasi & Low-Fi Wireframe (PIC: Sarah Amanda)\n  - Review konsep alur wireframe bersama PM (PIC: Dzakwan Pratama)\n\n• Fase UI Design & Interactive Prototyping:\n  - Pembuatan Design System token & komponen UI (PIC: Sarah Amanda)\n  - Perancangan High-Fidelity UI mockups seluruh flow (PIC: Sarah Amanda)\n  - Pembuatan Interactive Prototype di Figma (PIC: Sarah Amanda)\n  - Review kelayakan teknis bersama tech lead (PIC: Kevin Wijaya)\n\n• Fase Usability Testing & Design Handoff:\n  - Pelaksanaan sesi Usability Testing dengan 10 user (PIC: Gita Savitri)\n  - Analisis feedback UX & iterasi perbaikan desain (PIC: Sarah Amanda)\n  - Finalisasi aset ekspor & sesi Design Handoff (PIC: Kevin Wijaya)',
                    deliverables: '1. File Master Figma Design System & Komponen Lengkap\n2. High-Fidelity Interactive Prototype yang siap diuji\n3. Laporan Hasil Usability Testing & Rekomendasi UX\n4. Asset export & Design Handoff untuk developer',
                    scope: 'Wawancara pengguna & audit heuristik UX, perancangan User Flow & Wireframe, High-Fidelity UI Mockups, pembuatan Prototype interaktif, dan pengujian dengan 10 pengguna target.',
                    roles: '',
                    budget: 'Akses Figma Professional dan tools testing pengguna.'
                },
                {
                    name: 'Template Kustom (Blank Form)',
                    category: 'Kustom',
                    badgeClass: 'bg-slate-50 text-slate-600 border border-slate-200',
                    start_date: '',
                    end_date: '',
                    period: 'Sesuai Kebutuhan',
                    description: 'Mulai dari formulir kosong dengan struktur fase dan daftar tugas standar untuk kebutuhan proyek spesifik Anda.',
                    goal: '',
                    phases: '',
                    deliverables: '',
                    scope: '',
                    roles: '',
                    budget: ''
                }
            ],

            openTemplateModal() {
                this.templateStep = 1;
                this.showTemplateModal = true;
            },

            closeTemplateModal() {
                this.showTemplateModal = false;
            },

            onDateChange() {
                if (this.formTemplate.start_date && this.formTemplate.end_date) {
                    const start = new Date(this.formTemplate.start_date);
                    const end = new Date(this.formTemplate.end_date);

                    const formatIndo = (d) => {
                        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                    };

                    const diffTime = end - start;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    let durationText = '';
                    if (diffDays >= 30) {
                        const months = Math.round(diffDays / 30);
                        durationText = ` (~${months} Bulan)`;
                    } else if (diffDays > 0) {
                        durationText = ` (${diffDays} Hari)`;
                    }

                    this.formTemplate.period = `${formatIndo(start)} s/d ${formatIndo(end)}${durationText}`;
                } else if (this.formTemplate.end_date) {
                    const end = new Date(this.formTemplate.end_date);
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    this.formTemplate.period = `Deadline: ${end.getDate()} ${months[end.getMonth()]} ${end.getFullYear()}`;
                } else if (this.formTemplate.start_date) {
                    const start = new Date(this.formTemplate.start_date);
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    this.formTemplate.period = `Mulai: ${start.getDate()} ${months[start.getMonth()]} ${start.getFullYear()}`;
                }
            },

            selectTemplatePreset(tmpl, idx) {
                const workspaceId = '{{ $workspace->id ?? '' }}';
                const baseUrl = workspaceId ? `/workspace/${workspaceId}/brief/template` : `{{ route('brief.template') }}`;
                window.location.href = `${baseUrl}?preset=${idx}`;
            },

            editCurrentTemplate() {
                this.formTemplate = {
                    ...this.selectedTemplate
                };
                this.templateStep = 2;
                this.showTemplateModal = true;
            },

            applyTemplate() {
                if (!this.formTemplate.name.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nama Proyek Wajib Diisi',
                        text: 'Silakan masukkan nama atau tipe proyek terlebih dahulu.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                if (!this.formTemplate.goal.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tujuan Proyek Wajib Diisi',
                        text: 'Silakan isi tujuan utama proyek agar AI dapat menyusun analisis dengan tepat.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                this.selectedTemplate = {
                    ...this.formTemplate
                };
                this.isTemplateMode = true;
                this.showTemplateModal = false;

                // Reset native file input if any
                const fileInput = document.getElementById('fileInput');
                if (fileInput) {
                    fileInput.value = '';
                    fileInput.removeAttribute('required');
                }
                const fileListContainer = document.getElementById('fileListContainer');
                if (fileListContainer) fileListContainer.classList.add('hidden');

                // Smooth scroll down to submit section
                setTimeout(() => {
                    const submitBtn = document.getElementById('submitBtn');
                    if (submitBtn) {
                        submitBtn.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }, 100);
            },

            clearSelectedTemplate() {
                this.isTemplateMode = false;
                this.selectedTemplate = {
                    name: '',
                    goal: '',
                    start_date: '',
                    end_date: '',
                    period: '',
                    phases: '',
                    deliverables: '',
                    scope: '',
                    roles: '',
                    budget: '',
                };
                const fileInput = document.getElementById('fileInput');
                if (fileInput) {
                    fileInput.setAttribute('required', 'required');
                }
            },

            removePendingTemplate() {
                // POST ke brief.template.clear via fetch, lalu reload
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ isset($workspace) ? route("brief.template.clear") : route("brief.template.clear") }}';
                form.innerHTML = `
                    @csrf
                    <input type="hidden" name="workspace_id" value="{{ $workspace->id ?? '' }}">
                `;
                document.body.appendChild(form);
                form.submit();
            },

            handleFormSubmit(event) {
                const hasPendingTemplate = {{ session()->has('pending_template_brief') ? 'true' : 'false' }};
                const fileInput = document.getElementById('fileInput');
                const hasFiles = fileInput && fileInput.files && fileInput.files.length > 0;
                const hasWorkspaceSelected = _selectedWorkspaceFiles.length > 0;

                if (!hasPendingTemplate && !hasFiles && !hasWorkspaceSelected) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Berkas atau Template',
                        text: 'Silakan unggah dokumen brief dari komputer, pilih dari workspace, atau gunakan Formulir Template.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return false;
                }

                // If consent has not been given yet, block submit and open consent modal
                if (!_consentGiven) {
                    event.preventDefault();
                    _pendingAction = 'formSubmit';
                    openAiConsentModal();
                    return false;
                }

                // Show loading overlay
                document.getElementById('loadingOverlay').classList.remove('hidden');
                return true;
            }
        };
    }

    let _isProgrammaticClick = false;

    // ── AI Consent Modal Logic ─────────────────────────────────────────────────

    function handleFileInputClick(event) {
        if (_isProgrammaticClick) {
            _isProgrammaticClick = false;
            return true;
        }
        event.preventDefault();
        if (_consentGiven) {
            openSourceSelectionModal();
            return false;
        }
        _pendingAction = 'sourcePicker';
        openAiConsentModal();
        return false;
    }

    function openAiConsentModal() {
        const modal = document.getElementById('aiConsentModal');
        document.getElementById('aiConsentCheckbox').checked = false;
        document.getElementById('aiConsentAgreeBtn').disabled = true;
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
    }

    function closeAiConsentModal(clearAction = false) {
        const modal = document.getElementById('aiConsentModal');
        if (modal) modal.style.display = 'none';
        if (clearAction) {
            _pendingAction = null;
        }
    }

    function handleConsentCheckbox() {
        const checked = document.getElementById('aiConsentCheckbox').checked;
        document.getElementById('aiConsentAgreeBtn').disabled = !checked;
    }

    function handleAiConsentApproved() {
        if (!document.getElementById('aiConsentCheckbox').checked) return;
        _consentGiven = true;
        const currentAction = _pendingAction;
        _pendingAction = null;
        closeAiConsentModal();

        if (currentAction === 'formSubmit') {
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('uploadForm').submit();
        } else if (currentAction === 'sourcePicker') {
            openSourceSelectionModal();
        }
    }

    // ── Source Selection Modal Logic ──────────────────────────────────────────

    function openSourceSelectionModal() {
        const modal = document.getElementById('sourceSelectionModal');
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
    }

    function closeSourceSelectionModal() {
        const modal = document.getElementById('sourceSelectionModal');
        modal.style.display = 'none';
    }

    function selectLocalComputerSource() {
        closeSourceSelectionModal();
        _isProgrammaticClick = true;
        document.getElementById('fileInput').click();
    }

    function selectWorkspaceStorageSource() {
        closeSourceSelectionModal();
        openWorkspaceFilePickerModal();
    }

    // ── Workspace File Picker Modal Logic (Multi-select) ──────────────────────

    function openWorkspaceFilePickerModal() {
        const modal = document.getElementById('workspaceFilePickerModal');
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
        syncWorkspaceFileCheckboxes();
    }

    function closeWorkspaceFilePickerModal() {
        const modal = document.getElementById('workspaceFilePickerModal');
        modal.style.display = 'none';
    }

    function syncWorkspaceFileCheckboxes() {
        const checkboxes = document.querySelectorAll('.workspace-file-checkbox');
        checkboxes.forEach(cb => {
            const isSelected = _selectedWorkspaceFiles.some(f => f.id === cb.value);
            cb.checked = isSelected;
            const row = cb.closest('.workspace-file-row');
            if (row) {
                if (isSelected) {
                    row.classList.add('border-indigo-500', 'bg-indigo-50/30');
                } else {
                    row.classList.remove('border-indigo-500', 'bg-indigo-50/30');
                }
            }
        });
        updateSelectedWorkspaceFileCount();
    }

    function toggleWorkspaceFileSelection(fileId) {
        const row = document.querySelector(`.workspace-file-row[data-file-id="${fileId}"]`);
        if (!row) return;

        const fileName = row.getAttribute('data-file-name');
        const fileSize = parseFloat(row.getAttribute('data-file-size')) || 0;
        const cb = row.querySelector('.workspace-file-checkbox');

        const existingIndex = _selectedWorkspaceFiles.findIndex(f => f.id === fileId);
        if (existingIndex > -1) {
            _selectedWorkspaceFiles.splice(existingIndex, 1);
            if (cb) cb.checked = false;
            row.classList.remove('border-indigo-500', 'bg-indigo-50/30');
        } else {
            _selectedWorkspaceFiles.push({ id: fileId, name: fileName, size: fileSize });
            if (cb) cb.checked = true;
            row.classList.add('border-indigo-500', 'bg-indigo-50/30');
        }

        updateSelectedWorkspaceFileCount();
    }

    function updateSelectedWorkspaceFileCount() {
        const badge = document.getElementById('selectedCountBadge');
        if (badge) {
            badge.innerText = `${_selectedWorkspaceFiles.length} dipilih`;
        }
    }

    function filterWorkspaceFiles() {
        const q = (document.getElementById('workspaceFileSearchInput').value || '').toLowerCase();
        const rows = document.querySelectorAll('.workspace-file-row');
        rows.forEach(row => {
            const name = (row.getAttribute('data-file-name') || '').toLowerCase();
            if (name.includes(q)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function confirmWorkspaceFilesSelection() {
        closeWorkspaceFilePickerModal();
        updateCombinedFileList();
    }

    function removeWorkspaceFile(fileId) {
        _selectedWorkspaceFiles = _selectedWorkspaceFiles.filter(f => f.id !== fileId);
        updateCombinedFileList();
    }

    // ── Combined File List Preview ────────────────────────────────────────────

    function updateFileList() {
        updateCombinedFileList();
    }

    function updateCombinedFileList() {
        const input = document.getElementById('fileInput');
        const container = document.getElementById('fileListContainer');
        const list = document.getElementById('fileList');

        // Bersihkan item upload & workspace (jangan hapus template item jika ada)
        const itemsToRemove = list.querySelectorAll('.custom-file-item');
        itemsToRemove.forEach(el => el.remove());

        // Bersihkan hidden inputs workspace lama di form
        const oldHiddenWsInputs = document.querySelectorAll('input[name="workspace_file_ids[]"]');
        oldHiddenWsInputs.forEach(el => el.remove());

        const form = document.getElementById('uploadForm');
        let totalItemsCount = 0;

        // 1. Render Berkas dari Komputer Lokal
        if (input && input.files && input.files.length > 0) {
            Array.from(input.files).forEach((file, index) => {
                totalItemsCount++;
                const item = document.createElement('div');
                item.className = 'custom-file-item flex items-center justify-between gap-3 bg-slate-50 border border-slate-100 rounded-xl p-3';
                item.innerHTML = `
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-700 truncate">${file.name}</p>
                            <p class="text-[10px] text-slate-400">Komputer Lokal &bull; ${(file.size / 1024).toFixed(1)} KB</p>
                        </div>
                    </div>
                `;
                list.appendChild(item);
            });
        }

        // 2. Render Berkas dari Workspace
        _selectedWorkspaceFiles.forEach(wsFile => {
            totalItemsCount++;

            // Tambahkan hidden input ke form
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'workspace_file_ids[]';
            hiddenInput.value = wsFile.id;
            form.appendChild(hiddenInput);

            const item = document.createElement('div');
            item.className = 'custom-file-item flex items-center justify-between gap-3 bg-purple-50/40 border border-purple-100 rounded-xl p-3';
            item.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">${wsFile.name}</p>
                        <p class="text-[10px] text-purple-600 font-medium">Dari Workspace &bull; ${(wsFile.size / 1024).toFixed(1)} KB</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="removeWorkspaceFile('${wsFile.id}')"
                    class="text-slate-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition-colors"
                    title="Hapus Berkas">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            list.appendChild(item);
        });

        const hasTemplate = document.getElementById('templateFileItem') !== null;

        if (totalItemsCount > 0 || hasTemplate) {
            container.classList.remove('hidden');
            setTimeout(() => {
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100);
        } else {
            container.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const backdrop = document.getElementById('aiConsentBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                closeAiConsentModal();
            });
        }

        // Auto-scroll ke daftar berkas jika baru saja menyimpan template rencana kerja
        @if(session()->has('pending_template_brief'))
        setTimeout(() => {
            const container = document.getElementById('fileListContainer');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 200);
        @endif
    });

    // ── Drag & Drop Styling ───────────────────────────────────────────────────

    const dropzone = document.getElementById('dropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-slate-200');
                dropzone.classList.add('border-blue-500', 'bg-blue-50/40');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-blue-500', 'bg-blue-50/40');
                dropzone.classList.add('border-slate-200');
            }, false);
        });
    }

    // ── Polling Transkrip Meeting Fireflies ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const waitingEvent = params.get('waiting_event');
        const workspaceId = '{{ $workspace->id ?? '' }}';

        if (waitingEvent && workspaceId) {
            const overlay = document.getElementById('transcriptWaitingOverlay');
            if (overlay) overlay.classList.remove('hidden');
            pollTranscript(workspaceId, waitingEvent);
        }
    });

    function pollTranscript(workspaceId, eventId) {
        const url = `/workspace/${workspaceId}/brief/transcript-status?event=${eventId}`;
        let attempts = 0;
        const MAX_ATTEMPTS = 540; // 540 x 5 detik = 45 menit

        const interval = setInterval(async () => {
            attempts++;

            if (attempts > MAX_ATTEMPTS) {
                clearInterval(interval);
                const card = document.getElementById('transcriptWaitingCard');
                if (card) {
                    card.innerHTML = `
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Transkrip belum juga masuk</p>
                            <p class="text-xs text-gray-500 mb-4">Ada kemungkinan bot Fireflies gagal join meeting. Cek halaman Dokumen, atau unggah transkrip secara manual.</p>
                            <button onclick="document.getElementById('transcriptWaitingOverlay').classList.add('hidden')"
                                class="text-xs bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition-colors">
                                Tutup
                            </button>
                        </div>
                    `;
                }
                return;
            }

            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                if (data.status === 'ready' && data.file_id) {
                    clearInterval(interval);
                    const overlay = document.getElementById('transcriptWaitingOverlay');
                    if (overlay) overlay.classList.add('hidden');

                    Swal.fire({
                        icon: 'success',
                        title: 'Transkrip Siap!',
                        text: `"${data.file_name}" berhasil masuk. Klik "Analisis Sekarang" untuk lanjut.`,
                        confirmButtonText: 'Analisis Sekarang',
                        confirmButtonColor: '#4f46e5',
                        showCancelButton: true,
                        cancelButtonText: 'Nanti Saja'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitTranscriptFile(data.file_id);
                        }
                    });
                }
            } catch (e) {
                console.error('Polling error:', e);
            }
        }, 5000);
    }

    async function submitTranscriptFile(fileId) {
        document.getElementById('loadingOverlay').classList.remove('hidden');

        try {
            const res = await fetch(`{{ route('brief.fromTranscript') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    file_id: fileId
                })
            });
            const data = await res.json();

            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                document.getElementById('loadingOverlay').classList.add('hidden');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.error || 'Terjadi kesalahan.'
                });
            }
        } catch (e) {
            document.getElementById('loadingOverlay').classList.add('hidden');
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan jaringan.'
            });
        }
    }
</script>
@endpush
@endsection