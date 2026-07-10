@extends('layouts.app')

@section('title', 'Analisis Brief Proyek')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Analisis Brief Proyek</h1>
        <p class="text-sm text-gray-600">Unggah dokumen proyek Anda dan biarkan AI menyusun strategi awal.</p>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 mb-6">
        <form
            action="{{ route('brief.upload') }}"
            method="POST"
            enctype="multipart/form-data"
            id="uploadForm"
            class="space-y-6"
        >
            @csrf

            <!-- Upload Area -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Dokumen Brief Proyek</label>
                
                <div class="relative border-2 border-dashed border-gray-300 hover:border-[#225ad6] transition rounded-xl p-8 flex flex-col items-center justify-center cursor-pointer bg-gray-50" id="dropzone">
                    <input
                        type="file"
                        name="documents[]"
                        id="fileInput"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        multiple
                        required
                        accept=".pdf,.docx,.txt"
                        onchange="updateFileList()"
                    >
                    <div class="w-12 h-12 bg-blue-50 text-[#225ad6] rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-800 mb-1">Pilih berkas atau seret ke sini</span>
                    <span class="text-xs text-gray-500">Mendukung PDF, DOCX, TXT</span>
                </div>
            </div>

            <!-- File List Preview -->
            <div id="fileListContainer" class="hidden">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-2">Berkas yang dipilih:</span>
                <div id="fileList" class="space-y-2"></div>
            </div>

            <!-- Submit Button with Spinner -->
            <div class="flex justify-end">
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="bg-[#225ad6] hover:bg-[#1a44a6] text-white font-medium text-sm px-6 py-3 rounded-lg flex items-center gap-2 transition"
                >
                    <span>Analisis dengan AI</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Premium Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center hidden">
        <div class="bg-white p-8 rounded-2xl shadow-xl flex flex-col items-center max-w-sm w-full mx-4">
            <div class="relative w-16 h-16 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-[#225ad6] animate-spin"></div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Menganalisis Dokumen...</h3>
            <p class="text-xs text-gray-500 text-center leading-relaxed">AI sedang membaca brief Anda dan menyusun strategi proyek terbaik.</p>
        </div>
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
                item.className = 'flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg p-3';
                item.innerHTML = `
                    <div class="w-8 h-8 bg-blue-50 text-[#225ad6] rounded-md flex items-center justify-center flex-shrink-0">
                        <i class="far fa-file-alt"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 truncate">${file.name}</p>
                        <p class="text-[10px] text-gray-500">${(file.size / 1024).toFixed(1)} KB</p>
                    </div>
                `;
                list.appendChild(item);
            });
        } else {
            container.classList.add('hidden');
        }
    }

    // Handle Drag & Drop styling
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.add('border-[#225ad6]', 'bg-blue-50/50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-[#225ad6]', 'bg-blue-50/50');
        }, false);
    });

    // Show loading overlay on form submit
    document.getElementById('uploadForm').addEventListener('submit', function() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    });
</script>
@endpush
@endsection