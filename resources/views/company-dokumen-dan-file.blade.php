@vite(['resources/css/app.css', 'resources/js/app.js'])
@extends('layouts.app')
<script>
    window.assetPath = "{{ asset('') }}";
</script>

@section('title', 'Dokumen dan File - ' . $company->name)

@section('content')
    <div x-data="documentSearch()" x-init="resetAllModals(); 
            $store.workspace = { selectedMenu: 'dokumen' };
            // ✅ PANGGIL initCompanyDocuments (bukan initWorkspaceDocuments)
            initCompanyDocuments(@js($folders), @js($rootFiles), @js($company)); 
            " x-cloak class="bg-[#f3f6fc] min-h-screen">

        <!-- ini harusnya ada company navigation -->

        {{-- Modals (sama seperti workspace) --}}
        @include('components.company-dokumen-modal')

        {{-- Loading State --}}
        <div x-show="!ready" x-cloak class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            <div
                class="border border-gray-200 rounded-lg bg-white p-6 flex items-center justify-center h-[calc(100vh-200px)]">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Memuat dokumen...</p>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div x-show="ready" x-transition class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            <div class="border border-gray-200 rounded-lg bg-white p-4 sm:p-6 flex flex-col min-h-[calc(100vh-140px)]">

                {{-- Components (sama seperti workspace) --}}
                @include('components.company-dokumen-header')
                @include('components.dokumen-breadcrumb')
                @include('components.dokumen-search-info')
                @include('components.dokumen-selection-header')
                @include('components.dokumen-grid')
                @include('components.dokumen-file-header')
                @include('components.dokumen-file-content')
                @include('components.dokumen-main-grid')
                @include('components.dokumen-default-view')

            </div>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
@endsection