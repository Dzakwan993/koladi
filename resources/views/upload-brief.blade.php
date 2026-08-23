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
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Format Standar</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.508 1.333 1.508 2.316V18" />
                            </svg>
                            Panduan Catatan
                        </span>
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

                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-[11px] font-medium border border-slate-200">
                                    Opsional
                                </span>
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
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unggah File Fisik</span>
                        <span class="text-[11px] text-slate-400">PDF, DOCX, TXT</span>
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

            {{-- ─── SELECTED STATE PREVIEW ────────────────────────────────────────── --}}

            {{-- Template Selected Preview Card --}}
            <div x-show="isTemplateMode" x-cloak class="w-full bg-white border-2 border-indigo-200 rounded-3xl p-6 shadow-md relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 shadow-sm border border-indigo-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Template Konteks Terpilih
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-800 mt-0.5" x-text="'Template Konteks - ' + selectedTemplate.name"></h3>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <button
                            type="button"
                            @click="editCurrentTemplate()"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            <span>Edit Isi Template</span>
                        </button>
                        <button
                            type="button"
                            @click="clearSelectedTemplate()"
                            class="text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-2 rounded-xl transition-colors flex items-center gap-1 shadow-sm"
                            title="Batalkan Template &amp; Gunakan File">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Batal</span>
                        </button>
                    </div>
                </div>

                {{-- Template Details Summary --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 bg-slate-50 border border-slate-100 rounded-2xl p-4 text-xs">
                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mb-0.5">Tujuan Projek</p>
                        <p class="text-slate-700 line-clamp-2" x-text="selectedTemplate.goal || '—'"></p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mb-0.5">Periode Projek</p>
                        <p class="text-slate-700" x-text="selectedTemplate.period || '—'"></p>
                    </div>
                    <div class="sm:col-span-2 border-t border-slate-200/60 pt-2.5 mt-0.5" x-show="selectedTemplate.phases">
                        <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mb-0.5">Tahapan / Fase Projek</p>
                        <p class="text-slate-700 whitespace-pre-line line-clamp-2" x-text="selectedTemplate.phases || '—'"></p>
                    </div>
                    <div class="sm:col-span-2 border-t border-slate-200/60 pt-2.5 mt-0.5">
                        <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mb-0.5">Output Akhir Projek (Deliverables)</p>
                        <p class="text-slate-700 line-clamp-2" x-text="selectedTemplate.deliverables || '—'"></p>
                    </div>
                </div>
            </div>

            {{-- File List Preview (for uploaded files) --}}
            <div id="fileListContainer" class="hidden w-full bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-3">Berkas yang dipilih:</span>
                <div id="fileList" class="space-y-2"></div>
            </div>

            {{-- Submit Button with Spinner --}}
            <div class="flex justify-center w-full pt-2">
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full max-w-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-4 rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-indigo-500/20 active:scale-[0.99]">
                    <span>Analisis dengan AI</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </button>
            </div>
        </form>

        {{-- Info cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
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

    </div>

    {{-- ─── MODAL TEMPLATE PROJEK ────────────────────────────────────────── --}}
    <div
        x-show="showTemplateModal"
        x-cloak
        class="fixed inset-0 z-[9990] flex items-center justify-center p-4 overflow-y-auto"
        style="display: none;">
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="closeTemplateModal()"></div>

        {{-- Modal Content Card --}}
        <div
            class="relative bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-2xl my-8 overflow-hidden z-10 transform transition-all"
            style="animation: modalSlideIn 0.25s ease-out;">
            {{-- STEP 1: Pilihan Template Proyek --}}
            <template x-if="templateStep === 1">
                <div>
                    {{-- Modal Header --}}
                    <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center flex-shrink-0 text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 leading-tight">Template Projek</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Pilih salah satu template untuk melihat struktur dan rincian isinya.</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="closeTemplateModal()"
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body: Template Cards Grid --}}
                    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <template x-for="(tmpl, idx) in availableTemplates" :key="idx">
                                <div
                                    @click="selectTemplatePreset(tmpl)"
                                    class="group text-left border border-slate-200 hover:border-indigo-400 hover:shadow-md rounded-2xl p-4 bg-white hover:bg-indigo-50/20 transition-all cursor-pointer flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2.5">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                                :class="tmpl.badgeClass"
                                                x-text="tmpl.category"></span>
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors" x-text="tmpl.name"></h4>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed" x-text="tmpl.description"></p>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                                <path stroke-linecap="round" stroke-width="2" d="M12 6v6l4 2" />
                                            </svg>
                                            <span x-text="tmpl.period"></span>
                                        </span>
                                        <span class="font-semibold text-indigo-600 group-hover:underline">Pilih &rarr;</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- STEP 2: Form Kustomisasi Isi Template --}}
            <template x-if="templateStep === 2">
                <div>
                    {{-- Modal Header --}}
                    <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="templateStep = 1"
                                class="p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                                title="Kembali ke Daftar Template">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 leading-tight" x-text="'Kustomisasi: ' + formTemplate.name"></h3>
                                <p class="text-xs text-slate-500 mt-0.5">Tinjau dan sesuaikan isi field template di bawah ini.</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="closeTemplateModal()"
                            class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body: Form Fields --}}
                    <div class="p-6 max-h-[65vh] overflow-y-auto space-y-4">
                        {{-- Field 1: Nama Proyek --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Nama / Tipe Proyek <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="formTemplate.name"
                                placeholder="Misal: Pengembangan Website Perusahaan, Proyek Gedung..."
                                class="w-full text-sm font-medium border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none bg-white text-slate-800">
                        </div>

                        {{-- Field 2: Tujuan Projek --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Tujuan Projek <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                x-model="formTemplate.goal"
                                rows="3"
                                placeholder="Jelaskan apa yang ingin dicapai melalui proyek ini..."
                                class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none resize-y text-slate-800"></textarea>
                        </div>

                        {{-- Field 3: Periode & Kalender Tanggal Proyek --}}
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                                    Periode / Jadwal Proyek (Kalender)
                                </label>
                                <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100/60 px-2 py-0.5 rounded-md flex items-center gap-1">
                                    <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Pilih Rentang Tanggal
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                        Tanggal Mulai Proyek
                                    </label>
                                    <input
                                        type="date"
                                        x-model="formTemplate.start_date"
                                        @change="onDateChange()"
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                        Target Selesai / Deadline
                                    </label>
                                    <input
                                        type="date"
                                        x-model="formTemplate.end_date"
                                        @change="onDateChange()"
                                        class="w-full text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none shadow-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                                    Keterangan Periode / Estimasi Durasi
                                </label>
                                <input
                                    type="text"
                                    x-model="formTemplate.period"
                                    placeholder="Otomatis terisi dari kalender atau ketik manual (misal: 3 Bulan)"
                                    class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2 bg-white text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        {{-- Field: Fase & Tahapan Proyek --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                                    Fase / Tahapan Proyek (Milestones)
                                </label>
                                <span class="text-[10px] text-indigo-600 font-semibold bg-indigo-50 px-2 py-0.5 rounded-md">Penting untuk Timeline</span>
                            </div>
                            <textarea
                                x-model="formTemplate.phases"
                                rows="3"
                                placeholder="Contoh:&#10;Fase 1 (Bulan 1): Riset & Perancangan Desain UI/UX&#10;Fase 2 (Bulan 2): Pengembangan Fitur & Integrasi API&#10;Fase 3 (Bulan 3): QA Testing, UAT, & Peluncuran"
                                class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none resize-y text-slate-800"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">💡 Catat tahapan kerja utama per fase agar timeline proyek mudah dipetakan.</p>
                        </div>

                        {{-- Field 4: Output Akhir Projek --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Output Akhir Projek (Deliverables)
                            </label>
                            <textarea
                                x-model="formTemplate.deliverables"
                                rows="3"
                                placeholder="Sebutkan hasil akhir nyata (contoh: Source code web, Dokumen SRS, Bangunan fisik BAST...)"
                                class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none resize-y text-slate-800"></textarea>
                        </div>

                        {{-- Field 5: Ruang Lingkup --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Ruang Lingkup &amp; Detail Pekerjaan
                            </label>
                            <textarea
                                x-model="formTemplate.scope"
                                rows="3"
                                placeholder="Rincian modul, batasan pekerjaan, dan batasan teknis..."
                                class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none resize-y text-slate-800"></textarea>
                        </div>

                        {{-- Field 6: Tim / Role yang Terlibat (Opsional) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Tim / Role yang Terlibat <span class="text-slate-400 font-normal normal-case">(Opsional)</span>
                            </label>
                            <input
                                type="text"
                                x-model="formTemplate.roles"
                                placeholder="Contoh: Project Manager, UI/UX Designer, Frontend Developer, Backend Developer, QA"
                                class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none text-slate-800">
                        </div>

                        {{-- Field 7: Estimasi Anggaran & Kebutuhan Khusus (Opsional) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                Estimasi Anggaran &amp; Kebutuhan Khusus <span class="text-slate-400 font-normal normal-case">(Opsional)</span>
                            </label>
                            <textarea
                                x-model="formTemplate.budget"
                                rows="2"
                                placeholder="Contoh: Estimasi budget Rp 50.000.000, kebutuhan akun iklan Meta/Google, lisensi software..."
                                class="w-full text-sm border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none resize-y text-slate-800"></textarea>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <button
                            type="button"
                            @click="templateStep = 1"
                            class="px-4 py-2 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">
                            &larr; Kembali
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="closeTemplateModal()"
                                class="px-4 py-2 text-xs font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors">
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="applyTemplate()"
                                class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-md shadow-indigo-500/20 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                <span>Terapkan Template Ini</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ─── MODAL PERSETUJUAN PEMROSESAN AI ──────────────────────────────── --}}
    <div id="aiConsentModal" class="fixed inset-0 z-[9998] flex items-center justify-center p-4" style="display: none !important;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" id="aiConsentBackdrop"></div>

        {{-- Modal Card --}}
        <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-auto overflow-hidden" style="animation: modalSlideIn 0.2s ease-out;">

            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.955 11.955 0 003 12c0 2.458.728 4.745 1.98 6.65l.01.015M17.25 6.003A11.95 11.95 0 0121 12c0 2.264-.6 4.39-1.651 6.22M7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0z" />
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
                    onclick="closeAiConsentModal()"
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
                    phases: 'Fase 1 (Bulan 1): Perancangan database, arsitektur sistem, dan desain antarmuka pengguna (UI/UX Figma)\nFase 2 (Bulan 2): Pengembangan frontend, backend API, integrasi payment gateway, dan autentikasi pengguna\nFase 3 (Bulan 3): QA testing, security & performance audit, perbaikan bug, dan deployment cloud production',
                    deliverables: '1. Source code aplikasi siap produksi di repository GitHub\n2. Dokumen Software Requirements Specification (SRS) & Dokumentasi API\n3. Desain UI/UX High-Fidelity Figma & Design System\n4. Panduan Penggunaan (User Manual) & Panduan Setup Deployment',
                    scope: 'Perancangan database & arsitektur sistem, desain antarmuka pengguna (UI/UX), pengembangan frontend & backend, integrasi API & payment gateway, QA testing, dan deployment cloud.',
                    roles: 'Project Manager, UI/UX Designer, Frontend Developer, Backend Developer, QA Engineer',
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
                    phases: 'Fase 1 (Bulan 1-2): Pembersihan lahan, pekerjaan tanah, dan struktur fondasi bawah\nFase 2 (Bulan 3-4): Pekerjaan struktur atas, arsitektur dinding, dan instalasi Mekanikal Elektrikal Plumbing (MEP)\nFase 3 (Bulan 5-6): Finishing interior/eksterior, uji fungsi kelayakan fasilitas, audit kepatuhan K3, dan serah terima BAST',
                    deliverables: '1. Bangunan fisik selesai 100% siap operasional\n2. Dokumen As-Built Drawing & Dokumen Teknis\n3. Berita Acara Serah Terima (BAST) Pekerjaan\n4. Laporan Kepatuhan Mutu & Keselamatan Kerja (K3)',
                    scope: 'Pekerjaan persiapan & pembersihan lahan, pekerjaan struktur bawah & atas, pekerjaan arsitektur & finishing, instalasi Mekanikal Elektrikal Plumbing (MEP), uji fungsi, dan serah terima.',
                    roles: 'Site Manager, Pelaksana Lapangan, Ahli K3 Konstruksi, Drafter Arsitek, Mandor & Tukang',
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
                    phases: 'Fase 1 (Minggu 1): Riset target audiens, analisis benchmark kompetitor, dan formulasi materi kampanye promosi\nFase 2 (Minggu 2-3): Pembuatan materi konten promosi (copywriting & visual/video ads), optimasi landing page, dan setup akun Meta/Google Ads\nFase 3 (Minggu 4): Peluncuran iklan digital, monitoring performa harian, A/B testing materi, dan penyusunan laporan analitik performa',
                    deliverables: '1. Paket aset konten promosi visual & video siap tayang\n2. Landing Page konversi dengan integrasi tracking analitik\n3. Akun iklan aktif (Meta Ads & Google Ads) dengan materi kampanye\n4. Laporan performa mingguan dan analitik ROAS/CPA',
                    scope: 'Riset target audiens & benchmark kompetitor, pembuatan materi konten promosi (copywriting & visual), optimasi landing page, setup kampanye periklanan digital, dan evaluasi hasil harian.',
                    roles: 'Digital Marketer, Copywriter, Graphic Designer, Video Editor, Media Buyer',
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
                    phases: 'Fase 1 (Minggu 1-2): Booking venue, perizinan, kurasi pembicara, pencarian sponsor, dan publikasi sistem registrasi tiket\nFase 2 (Minggu 3-4): Pengadaan konsumsi & souvenir, penataan panggung/AV, gladi resik teknis, dan briefing panitia\nFase 3 (Minggu 5-6): Manajemen operasional hari-H, dokumentasi foto/video, serta evaluasi kepuasan peserta dan pelaporan sponsor',
                    deliverables: '1. Pelaksanaan acara seminar hybrid yang sukses dan tertib\n2. Dokumentasi foto & video profesional pasca acara\n3. Laporan evaluasi kepuasan peserta & feedback sponsor\n4. Laporan pertanggungjawaban keuangan & sponsorship',
                    scope: 'Booking venue & pengurusan izin, kurasi pembicara & sponsorship, publikasi & sistem registrasi tiket, penyediaan konsumsi & souvenir, gladi resik, dan manajemen operasional hari-H.',
                    roles: 'Ketua Panitia, Divisi Acara, Divisi Logistik/Venue, Divisi Sponsorship, Divisi Publikasi & Dokumentasi',
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
                    phases: 'Fase 1 (Minggu 1): Wawancara pengguna target, audit heuristik UX aplikasi saat ini, pembuatan User Flow, dan Low-Fidelity Wireframes\nFase 2 (Minggu 2-3): Perancangan High-Fidelity UI Mockups di Figma, Design System komponen, dan pembuatan Interactive Prototype\nFase 3 (Minggu 4): Usability testing dengan 10 pengguna target, iterasi perbaikan desain, dan handoff asset ke tim pengembang',
                    deliverables: '1. File Master Figma Design System & Komponen Lengkap\n2. High-Fidelity Interactive Prototype yang siap diuji\n3. Laporan Hasil Usability Testing & Rekomendasi UX\n4. Asset export & Design Handoff untuk developer',
                    scope: 'Wawancara pengguna & audit heuristik UX, perancangan User Flow & Wireframe, High-Fidelity UI Mockups, pembuatan Prototype interaktif, dan pengujian dengan 10 pengguna target.',
                    roles: 'Lead UI/UX Designer, UX Researcher, Product Manager',
                    budget: 'Akses Figma Professional dan tools testing pengguna.'
                },
                {
                    name: 'Template Kustom (Blank Form)',
                    category: 'Kustom',
                    badgeClass: 'bg-slate-50 text-slate-600 border border-slate-200',
                    start_date: '',
                    end_date: '',
                    period: 'Sesuai Kebutuhan',
                    description: 'Mulai dari formulir kosong dengan struktur brief standar untuk kebutuhan proyek spesifik Anda.',
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

            selectTemplatePreset(tmpl) {
                this.formTemplate = {
                    name: tmpl.name === 'Template Kustom (Blank Form)' ? 'Proyek Baru' : tmpl.name,
                    goal: tmpl.goal || '',
                    start_date: tmpl.start_date || '',
                    end_date: tmpl.end_date || '',
                    period: tmpl.period === 'Sesuai Kebutuhan' ? '' : (tmpl.period || ''),
                    phases: tmpl.phases || '',
                    deliverables: tmpl.deliverables || '',
                    scope: tmpl.scope || '',
                    roles: tmpl.roles || '',
                    budget: tmpl.budget || '',
                };
                this.templateStep = 2;
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

            handleFormSubmit(event) {
                // If template mode is not active, check if files are selected
                if (!this.isTemplateMode) {
                    const fileInput = document.getElementById('fileInput');
                    if (!fileInput.files || fileInput.files.length === 0) {
                        event.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Berkas atau Template',
                            text: 'Silakan unggah dokumen brief proyek Anda atau pilih Template Projek siap pakai.',
                            confirmButtonColor: '#4f46e5'
                        });
                        return false;
                    }
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

    // ── AI Consent Modal Logic ─────────────────────────────────────────────────

    function handleFileInputClick(event) {
        if (_consentGiven) {
            return true;
        }
        event.preventDefault();
        _pendingAction = 'filePicker';
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

    function closeAiConsentModal() {
        const modal = document.getElementById('aiConsentModal');
        modal.style.display = 'none';
        _pendingAction = null;
    }

    function handleConsentCheckbox() {
        const checked = document.getElementById('aiConsentCheckbox').checked;
        document.getElementById('aiConsentAgreeBtn').disabled = !checked;
    }

    function handleAiConsentApproved() {
        if (!document.getElementById('aiConsentCheckbox').checked) return;
        _consentGiven = true;
        closeAiConsentModal();

        if (_pendingAction === 'filePicker') {
            document.getElementById('fileInput').click();
        } else if (_pendingAction === 'formSubmit') {
            document.getElementById('loadingOverlay').classList.remove('hidden');
            document.getElementById('uploadForm').submit();
        }
        _pendingAction = null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const backdrop = document.getElementById('aiConsentBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                closeAiConsentModal();
            });
        }
    });

    // ── File List Preview ─────────────────────────────────────────────────────

    function updateFileList() {
        const input = document.getElementById('fileInput');
        const container = document.getElementById('fileListContainer');
        const list = document.getElementById('fileList');

        list.innerHTML = '';

        if (input.files.length > 0) {
            container.classList.remove('hidden');
            Array.from(input.files).forEach(file => {
                const item = document.createElement('div');
                item.className = 'flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-xl p-3';
                item.innerHTML = `
                    <div class="w-8 h-8 bg-indigo-50 text-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-700 truncate">${file.name}</p>
                        <p class="text-[10px] text-slate-400">${(file.size / 1024).toFixed(1)} KB</p>
                    </div>
                `;
                list.appendChild(item);
            });

            setTimeout(() => {
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 100);
        } else {
            container.classList.add('hidden');
        }
    }

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
</script>
@endpush
@endsection