@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.sweet-alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="min-h-screen bg-[#e9effd] py-8 px-4">
        <div class="max-w-full mx-auto space-y-3">

            {{-- Header --}}
            <div class="max-w-2xl mx-auto text-center">
                <h1 class="text-2xl font-bold text-slate-800">Unggah Sumber Konteks Project</h1>
                <p class="text-sm text-slate-500 mt-2">
                    Unggah dokumen proyek Anda dan biarkan AI menyusun strategi awal, ringkasan eksekutif, serta daftar
                    tugas yang terstruktur secara otomatis.
                </p>
            </div>

            {{-- Dropzone Card --}}
            <div
                class="relative max-w-2xl mx-auto bg-white border-2 border-dashed border-indigo-100 rounded-3xl p-10 overflow-hidden">
                {{-- decorative blobs --}}
                <div class="pointer-events-none absolute -top-8 -left-8 w-40 h-40 bg-purple-200/30 rounded-full blur-3xl">
                </div>
                <div class="pointer-events-none absolute -top-8 -right-8 w-40 h-40 bg-blue-200/30 rounded-full blur-3xl">
                </div>

                <div class="relative flex flex-col items-center text-center">

                    {{-- Icon --}}
                    <div class="relative mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center">
                            <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5"
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

                    <h2 class="font-semibold text-slate-800 text-lg">Unggah Sumber Brief</h2>
                    <p class="text-sm text-slate-500 mt-1">Tarik &amp; lepas file atau klik untuk memilih dari komputer</p>

                    {{-- File type chips --}}
                    <div class="flex items-center gap-2 mt-4">
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M8 3h5l5 5v11a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            PDF
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M8 3h5l5 5v11a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            DOCX
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M8 3h5l5 5v11a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2z" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            TXT
                        </span>
                    </div>

                    {{-- Upload button --}}
                    {{-- ini nanti untuk upload dokumenya button uploadnya ini  --}}
                    {{-- <input type="file" id="dokumen-brief" class="hidden" accept=".pdf,.docx,.txt">
                    <button type="button" onclick="document.getElementById('dokumen-brief').click()"
                        class="mt-5 w-full max-w-xs bg-blue-700 hover:bg-blue-800 transition-colors text-white font-semibold text-sm rounded-xl py-3">
                        Pilih File Dokumen
                    </button> --}}

                    <input type="file" id="dokumen-brief" class="hidden" accept=".pdf,.docx,.txt">
                    <a href="{{ route('ai-brief', $workspace->id) }}"
                        class="mt-5 w-full max-w-xs bg-blue-700 hover:bg-blue-800 transition-colors text-white font-semibold text-sm rounded-xl py-3 flex items-center justify-center">
                        Pilih File Dokumen
                    </a>

                    <div class="w-full border-t border-slate-200 mt-7"></div>
                    <p class="text-[11px] font-medium text-slate-400 tracking-wide mt-4">ATAU IMPOR DARI</p>

                    {{-- Import sources --}}
                    <div class="grid grid-cols-5 gap-3 w-full max-w-lg mx-auto mt-4">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 flex items-center justify-center">
                                <img src="{{ asset('images/icons/Meet.svg') }}" alt="Meeting Icon" class="w-8 h-8">
                            </div>
                            <p class="text-xs font-semibold text-slate-700 mt-2">Meeting</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Hasil transkrip meeting</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 flex items-center justify-center">
                                <img src="{{ asset('images/icons/Drive.svg') }}" alt="Drive Icon" class="w-8 h-8">
                            </div>
                            <p class="text-xs font-semibold text-slate-700 mt-2">Drive</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Dokumen brief proyek</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 flex items-center justify-center">
                                <img src="{{ asset('images/icons/Notion.svg') }}" alt="Notion Icon" class="w-8 h-8">
                            </div>
                            <p class="text-xs font-semibold text-slate-700 mt-2">Notion</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Catatan & brief proyek</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 flex items-center justify-center">
                                <img src="{{ asset('images/icons/Wa.svg') }}" alt="Wa Icon" class="w-8 h-8">
                            </div>
                            <p class="text-xs font-semibold text-slate-700 mt-2">WA</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Ringkasan chat sama brief</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 flex items-center justify-center">
                                <img src="{{ asset('images/icons/Gmail.svg') }}" alt="Gmail Icon" class="w-8 h-8">
                            </div>
                            <p class="text-xs font-semibold text-slate-700 mt-2">Email</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 leading-tight">Brief dari email klien / tim</p>
                        </div>
                    </div>

                    {{-- Info banner --}}

                    <div class="relative w-full mt-8">
                        <p class="text-xs text-slate-500">Maksimal ukuran file 25MB</p>
                        <div class="bg-gradient-to-r from-purple-50 via-pink-50 to-blue-50 rounded-2xl px-6 py-4">
                            <p
                                class="text-xs text-slate-600 flex items-center justify-center gap-1.5 flex-wrap text-center">
                                <span class="inline-flex items-center gap-1 font-semibold text-red-500 shrink-0">
                                    <img src="{{ asset('images/icons/Warning.svg') }}" alt="Warning Icon"
                                        class="w-4 h-4">
                                    Penting:
                                </span>
                                <span>Pastikan dokumen sudah di-export ke format PDF, DOCX, atau TXT terlebih dahulu.</span>
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Info cards --}}
            <div class="max-w-2xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
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
    </div>

@endsection
