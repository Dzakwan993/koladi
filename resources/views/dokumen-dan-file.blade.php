@vite(['resources/css/app.css', 'resources/js/app.js'])
@extends('layouts.app')
<script>
    // Base URL untuk asset()
    window.assetPath = "{{ asset('') }}";
</script>

@section('title', 'Dokumen dan File')

@section('content')
    <div x-data="documentSearch()" x-init="resetAllModals(); 
                $store.workspace = { selectedMenu: 'dokumen' };
                // Inisialisasi data dari backend
                initWorkspaceDocuments(@js($folders), @js($rootFiles), @js($workspace)); 
                " x-cloak class="bg-[#f3f6fc] min-h-screen">



        {{-- Workspace Navigation --}}
        @include('components.workspace-nav', ['active' => 'dokumen'])

        {{-- All Modals --}}
        @include('components.dokumen-modal')


        {{-- ✅ LOADING STATE: Tampil saat Alpine belum ready --}}
        <div x-show="!ready" x-cloak class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            <div
                class="border border-gray-200 rounded-lg bg-white p-6 flex items-center justify-center h-[calc(100vh-200px)]">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Memuat dokumen...</p>
                </div>
            </div>
        </div>

        {{-- Konten Halaman --}}
        <div x-show="ready" x-transition class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            <div
                class="border border-gray-200 rounded-lg bg-white p-4 sm:p-6 flex flex-col min-h-[calc(100vh-140px)] sm:min-h-[calc(100vh-160px)] lg:min-h-[calc(100vh-200px)]">

                {{-- Komponen-komponen --}}
                @include('components.dokumen-header')
                @include('components.dokumen-breadcrumb')
                @include('components.dokumen-search-info')
                @include('components.dokumen-selection-header')
                @include('components.dokumen-grid')
                <!-- 2 components di bawah ini dipanggil set berbarengan -->
                @include('components.dokumen-file-header')
                @include('components.dokumen-file-content')
                @include('components.dokumen-main-grid')
                @include('components.dokumen-default-view')

            </div>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

@endsection