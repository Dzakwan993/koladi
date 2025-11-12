@vite(['resources/css/app.css', 'resources/js/app.js'])
@extends('layouts.app')
<script>
    // Base URL untuk asset()
    window.assetPath = "{{ asset('') }}";
</script>

@section('title', 'Dokumen dan File')

@section('content')
<div x-data="documentSearch()" 
         x-init="$store.workspace = { selectedMenu: 'dokumen' };
            // Inisialisasi data dari backend
            initData(@js($folders), @js($rootFiles));"  class="bg-[#f3f6fc] min-h-screen">

        {{-- Workspace Navigation --}}
        @include('components.workspace-nav', ['active' => 'dokumen'])

        {{-- All Modals --}}
        @include('components.dokumen-modal')

        {{-- Konten Halaman --}}
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            <div class="border border-gray-200 rounded-lg bg-white p-4 sm:p-6 flex flex-col h-[calc(100vh-140px)] sm:h-[calc(100vh-160px)] lg:h-[calc(100vh-200px)]">

                {{-- Komponen-komponen --}}
                @include('components.dokumen-header')
                @include('components.dokumen-breadcrumb')
                @include('components.dokumen-grid')
                @include('components.dokumen-search-info')
                @include('components.dokumen-selection-header')
                @include('components.dokumen-file-header')
                @include('components.dokumen-file-content')
                @include('components.balas-komentar')
                @include('components.dokumen-main-grid')
                @include('components.dokumen-default-view')

            </div>
        </div>
</div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    
@endsection