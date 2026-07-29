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
                        {{-- File Input (Invisible - triggered only after modal consent) --}}
                        <input
                            type="file"
                            name="documents[]"
                            id="fileInput"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            multiple
                            required
                            accept=".pdf,.docx,.txt"
                            onchange="updateFileList()"
                            onclick="handleFileInputClick(event)"
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

    {{-- Modal Persetujuan Pemrosesan Dokumen dengan AI --}}
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
                        <p class="text-sm font-semibold text-slate-800 mb-0.5">Dokumen Anda akan diproses oleh AI</p>
                        <p class="text-xs text-slate-500 leading-relaxed">Koladi akan menganalisis dokumen untuk membantu membuat draft project, seperti ringkasan, deliverable, task, deadline, dan pertanyaan klarifikasi.</p>
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
                            <span class="text-xs text-slate-600 leading-relaxed">Dokumen hanya diproses untuk menghasilkan draft project pada workspace ini.</span>
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
                            <span class="text-xs text-slate-600 leading-relaxed">Hindari mengunggah data yang tidak diperlukan, seperti <strong class="text-slate-700">password, nomor kartu pembayaran, data kesehatan</strong>, atau informasi sangat sensitif lainnya.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-3.5 h-3.5 text-emerald-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs text-slate-600 leading-relaxed">Data Anda diproses secara aman hanya untuk menghasilkan analisis yang Anda minta dan tidak digunakan untuk melatih model AI, sesuai dengan ketentuan privasi Google.</span>
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
                            onchange="handleConsentCheckbox()"
                        >
                    </div>
                    <span class="text-xs text-slate-600 leading-relaxed group-hover:text-slate-800 transition-colors">
                        Saya memahami informasi di atas dan menyetujui pemrosesan dokumen menggunakan AI.
                    </span>
                </label>

            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end gap-2">
                <button
                    type="button"
                    id="aiConsentCancelBtn"
                    onclick="closeAiConsentModal()"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-colors"
                >
                    Batal
                </button>
                <button
                    type="button"
                    id="aiConsentAgreeBtn"
                    onclick="agreeAndOpenFilePicker()"
                    disabled
                    class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:bg-indigo-700 flex items-center gap-2"
                >
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
            <h3 class="text-lg font-bold text-slate-800 mb-1">Menganalisis Dokumen...</h3>
            <p class="text-xs text-slate-500 text-center leading-relaxed">AI sedang membaca brief Anda dan menyusun strategi proyek terbaik.</p>
        </div>
    </div>

    <style>
        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-12px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0)  scale(1); }
        }
    </style>

@push('scripts')
<script>
    // ── AI Consent Modal Logic ─────────────────────────────────────────────────

    let _consentGiven = false; // tracks whether user has agreed in this session

    /**
     * Intercept the native file picker click.
     * If consent has already been given, allow normally.
     * Otherwise, show the consent modal and block the click.
     */
    function handleFileInputClick(event) {
        if (_consentGiven) {
            // Consent already given — let the click proceed normally
            return true;
        }
        // Block the native file picker
        event.preventDefault();
        openAiConsentModal();
        return false;
    }

    function openAiConsentModal() {
        const modal = document.getElementById('aiConsentModal');
        // Reset checkbox state every time the modal opens
        document.getElementById('aiConsentCheckbox').checked = false;
        document.getElementById('aiConsentAgreeBtn').disabled = true;
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
    }

    function closeAiConsentModal() {
        const modal = document.getElementById('aiConsentModal');
        modal.style.display = 'none';
    }

    function handleConsentCheckbox() {
        const checked = document.getElementById('aiConsentCheckbox').checked;
        document.getElementById('aiConsentAgreeBtn').disabled = !checked;
    }

    function agreeAndOpenFilePicker() {
        if (!document.getElementById('aiConsentCheckbox').checked) return;
        _consentGiven = true;          // remember consent for subsequent clicks
        closeAiConsentModal();
        // Programmatically trigger the file picker
        document.getElementById('fileInput').click();
    }

    // Close modal when clicking the backdrop
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('aiConsentBackdrop').addEventListener('click', function () {
            closeAiConsentModal();
        });
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

    // ── Drag & Drop Styling ───────────────────────────────────────────────────

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
