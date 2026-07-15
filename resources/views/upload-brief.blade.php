@extends('layouts.app')

@section('title', 'Analisis Brief Proyek')

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

            {{-- Form wrapper --}}
            <form
                action="{{ route('brief.upload') }}"
                method="POST"
                enctype="multipart/form-data"
                id="uploadForm"
                class="space-y-4"
            >
                @csrf
                {{-- Kirim workspace_id jika brief diakses dari workspace tertentu --}}
                <input type="hidden" name="workspace_id" value="{{ $workspace->id ?? '' }}">

                {{-- Dropzone Card --}}
                <div
                    class="relative max-w-2xl mx-auto bg-white border-2 border-dashed border-indigo-100 rounded-3xl p-10 overflow-hidden cursor-pointer"
                    id="dropzone"
                >
                    {{-- decorative blobs --}}
                    <div class="pointer-events-none absolute -top-8 -left-8 w-40 h-40 bg-purple-200/30 rounded-full blur-3xl">
                    </div>
                    <div class="pointer-events-none absolute -top-8 -right-8 w-40 h-40 bg-blue-200/30 rounded-full blur-3xl">
                    </div>

                    <div class="relative flex flex-col items-center text-center">
                        {{-- File Input (Invisible but fills dropzone) --}}
                        <input
                            type="file"
                            name="documents[]"
                            id="fileInput"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            multiple
                            required
                            accept=".pdf,.docx,.txt"
                            onchange="updateFileList()"
                        >

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
                        <div class="flex items-center gap-2 mt-4 relative z-20">
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                                PDF
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                                DOCX
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                                TXT
                            </span>
                        </div>

                        {{-- Decorative Upload Button (pointer-events-none to let clicks pass through to file input) --}}
                        <div
                            class="mt-5 w-full max-w-xs bg-blue-700 hover:bg-blue-800 transition-colors text-white font-semibold text-sm rounded-xl py-3 flex items-center justify-center pointer-events-none"
                        >
                            Pilih File Dokumen
                        </div>

                        <div class="w-full border-t border-slate-200 mt-7"></div>
                        <p class="text-[11px] font-medium text-slate-400 tracking-wide mt-4">ATAU IMPOR DARI</p>

                        {{-- Import sources --}}
                        <div class="grid grid-cols-5 gap-3 w-full max-w-lg mx-auto mt-4 relative z-20">
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

                {{-- File List Preview --}}
                <div id="fileListContainer" class="hidden max-w-2xl mx-auto w-full bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-3">Berkas yang dipilih:</span>
                    <div id="fileList" class="space-y-2"></div>
                </div>

                {{-- Submit Button with Spinner --}}
                <div class="flex justify-center max-w-2xl mx-auto w-full pt-2">
                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-3.5 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-md"
                    >
                        <span>Analisis dengan AI</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </button>
                </div>
            </form>

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

    {{-- Premium Loading Overlay --}}
    <div id="loadingOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center hidden">
        <div class="bg-white p-8 rounded-3xl shadow-xl flex flex-col items-center max-w-sm w-full mx-4 border border-slate-100">
            <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-indigo-600 animate-spin"></div>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Menganalisis Dokumen...</h3>
            <p class="text-xs text-slate-500 text-center leading-relaxed">AI sedang membaca brief Anda dan menyusun strategi proyek terbaik.</p>
        </div>
    </div>

@push('scripts')
<script>
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

            // Otomatis scroll ke bawah secara smooth agar user langsung melihat preview file dan tombol submit
            setTimeout(() => {
                const mainElement = document.querySelector('main');
                if (mainElement) {
                    mainElement.scrollTo({
                        top: mainElement.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        } else {
            container.classList.add('hidden');
        }
    }

    // Handle Drag & Drop styling
    const dropzone = document.getElementById('dropzone');

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-indigo-100');
            dropzone.classList.add('border-indigo-500', 'bg-indigo-50/30');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-indigo-500', 'bg-indigo-50/30');
            dropzone.classList.add('border-indigo-100');
        }, false);
    });

    // Show loading overlay on form submit
    document.getElementById('uploadForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    });
</script>
@endpush
@endsection
