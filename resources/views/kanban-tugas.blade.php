@extends('layouts.app')

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Transisi untuk halaman balas komentar */
    .page-transition {
        transition: all 0.3s ease-in-out;
    }

    /* Untuk list di CKEditor */
    #editor-catatan+.ck-editor .ck-content ul,
    #editor-catatan+.ck-editor .ck-content ol {
        padding-left: 1.5rem !important;
        margin-left: 0 !important;
        list-style-position: outside !important;
    }

    #editor-catatan+.ck-editor .ck-content li {
        margin-left: 0 !important;
    }

    /* Tinggi minimal editor */
    .ck-editor__editable {
        min-height: 120px !important;
        max-height: 200px;
        overflow-y: auto;
    }

    @media (min-width: 768px) {
        .ck-editor__editable {
            min-height: 150px !important;
            max-height: 300px;
        }
    }

    /* Biar toolbar lebih lega */
    .ck.ck-toolbar {
        font-size: 14px !important;
        padding: 6px 8px !important;
    }

    /* Supaya tombol-tombol toolbar tidak terlalu rapat */
    .ck.ck-toolbar .ck-button {
        margin: 0 2px !important;
    }

    /* Rapikan list (bullet & numbering) agar tidak kepotong */
    .ck-editor__editable ul,
    .ck-editor__editable ol {
        margin-left: 1.5rem !important;
        padding-left: 1rem !important;
    }

    /* Custom breakpoints untuk responsivitas lebih baik */

    /* Mobile First - Extra Small (default: < 476px) */

    /* Small Mobile (476px - 639px) */
    @media (min-width: 476px) and (max-width: 639px) {

        /* xs devices - Small Mobile */
        .responsive-text {
            font-size: 0.875rem !important;
        }
    }

    /* Mobile Landscape & Small Tablets (640px - 767px) */
    @media (min-width: 640px) and (max-width: 767px) {

        /* sm devices - Mobile Landscape */
        .responsive-text {
            font-size: 0.9rem !important;
        }
    }

    /* Tablets (768px - 991px) */
    @media (min-width: 768px) and (max-width: 991px) {

        /* md devices - Tablets */
        .responsive-text {
            font-size: 1rem !important;
        }

        /* Optimasi untuk tablet */
        .container-tablet {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    /* Desktop (992px - 1199px) */
    @media (min-width: 992px) and (max-width: 1199px) {

        /* lg devices - Desktop */
        .responsive-text {
            font-size: 1.1rem !important;
        }
    }

    /* Large Desktop (1200px+) */
    @media (min-width: 1200px) {

        /* xl devices - Large Desktop */
        .responsive-text {
            font-size: 1.125rem !important;
        }

        /* Optimasi untuk large screens */
        .container-xl {
            max-width: 1400px;
            margin: 0 auto;
        }
    }

    /* Line clamp utility */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 475px) {
        .line-clamp-2 {
            -webkit-line-clamp: 1;
        }
    }

    /* Smooth scrolling untuk mobile */
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }

    /* Improved hover effects untuk mobile */
    @media (hover: hover) {
        .hover\:shadow-md:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    }

    /* Responsive padding utilities */
    .responsive-padding {
        padding: 0.5rem;
    }

    @media (min-width: 476px) {
        .responsive-padding {
            padding: 0.75rem;
        }
    }

    @media (min-width: 768px) {
        .responsive-padding {
            padding: 1rem;
        }
    }

    @media (min-width: 992px) {
        .responsive-padding {
            padding: 1.25rem;
        }
    }

    /* Responsive gap utilities */
    .responsive-gap {
        gap: 0.5rem;
    }

    @media (min-width: 476px) {
        .responsive-gap {
            gap: 0.75rem;
        }
    }

    @media (min-width: 768px) {
        .responsive-gap {
            gap: 1rem;
        }
    }

    /* Search & Filter Section Responsive Styles */
    @media (max-width: 475px) {
        .search-filter-mobile {
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-dropdowns-mobile {
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }
    }

    @media (min-width: 476px) and (max-width: 639px) {
        .search-filter-xs {
            flex-direction: row;
            align-items: center;
        }
    }

    /* Modal responsive adjustments */
    @media (max-width: 475px) {
        .modal-mobile {
            margin: 0.5rem;
            max-height: calc(100vh - 1rem);
        }

        .modal-content-mobile {
            padding: 1rem;
        }
    }

    @media (min-width: 476px) and (max-width: 767px) {
        .modal-small {
            margin: 1rem;
            max-height: calc(100vh - 2rem);
        }
    }

    /* Kanban board responsive */
    @media (max-width: 475px) {
        .kanban-column-mobile {
            width: 85vw;
            min-width: 85vw;
        }
    }

    @media (min-width: 476px) and (max-width: 639px) {
        .kanban-column-xs {
            width: 280px;
            min-width: 280px;
        }
    }

    @media (min-width: 640px) and (max-width: 767px) {
        .kanban-column-sm {
            width: 300px;
            min-width: 300px;
        }
    }

    @media (min-width: 768px) and (max-width: 991px) {
        .kanban-column-md {
            min-width: 320px;
        }
    }

    @media (min-width: 992px) and (max-width: 1199px) {
        .kanban-column-lg {
            min-width: 340px;
        }
    }

    @media (min-width: 1200px) {
        .kanban-column-xl {
            min-width: 360px;
        }
    }

    /* Button responsive sizes */
    .btn-responsive {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    @media (min-width: 768px) {
        .btn-responsive {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }
    }

    /* Text truncation for different screens */
    .truncate-mobile {
        max-width: 100px;
    }

    @media (min-width: 476px) {
        .truncate-mobile {
            max-width: 120px;
        }
    }

    @media (min-width: 640px) {
        .truncate-mobile {
            max-width: none;
        }
    }

    /* Icon responsive sizing */
    .icon-responsive {
        width: 1rem;
        height: 1rem;
    }

    @media (min-width: 476px) {
        .icon-responsive {
            width: 1.25rem;
            height: 1.25rem;
        }
    }

    @media (min-width: 768px) {
        .icon-responsive {
            width: 1.5rem;
            height: 1.5rem;
        }
    }



    /* untuk aksi list*/
    /* Style untuk modal aksi list */
    .modal-actions-list {
        backdrop-filter: blur(4px);
    }

    /* Animasi untuk modal */
    .modal-enter {
        opacity: 0;
        transform: scale(0.95);
    }

    .modal-enter-active {
        opacity: 1;
        transform: scale(1);
        transition: opacity 200ms ease-out, transform 200ms ease-out;
    }

    .modal-leave {
        opacity: 1;
        transform: scale(1);
    }

    .modal-leave-active {
        opacity: 0;
        transform: scale(0.95);
        transition: opacity 150ms ease-in, transform 150ms ease-in;
    }


    /* Ukuran kolom kanban medium */
    .kanban-column-medium {
        width: 75vw !important;
        min-width: 240px !important;
        max-width: 280px !important;
    }

    @media (min-width: 476px) {
        .kanban-column-medium {
            width: 220px !important;
            min-width: 220px !important;
            max-width: 260px !important;
        }
    }

    @media (min-width: 640px) {
        .kanban-column-medium {
            width: 240px !important;
            min-width: 240px !important;
            max-width: 280px !important;
        }
    }

    @media (min-width: 768px) {
        .kanban-column-medium {
            width: 260px !important;
            min-width: 260px !important;
            max-width: 300px !important;
        }
    }

    @media (min-width: 1024px) {
        .kanban-column-medium {
            width: 280px !important;
            min-width: 280px !important;
            max-width: 320px !important;
        }
    }

    /* Padding medium */
    .kanban-padding-medium {
        padding: 0.75rem !important;
    }

    @media (min-width: 476px) {
        .kanban-padding-medium {
            padding: 1rem !important;
        }
    }

    /* Gap medium */
    .kanban-gap-medium {
        gap: 0.75rem !important;
    }

    @media (min-width: 476px) {
        .kanban-gap-medium {
            gap: 1rem !important;
        }
    }

    @media (min-width: 768px) {
        .kanban-gap-medium {
            gap: 1.25rem !important;
        }
    }
</style>

@section('title', 'kanban-tugas')

@section('content')

    <div class="bg-gray-50 min-h-screen flex flex-col" x-data="kanbanApp()">


        {{-- Workspace Nav --}}
        @include('components.workspace-nav', ['active' => 'tugas'])


        {{-- 🔍 Search & Filter Section --}}
        <div class="bg-white border-b px-3 xs:px-4 sm:px-5 md:px-6 py-2 xs:py-3 sm:py-4 shadow-sm">
            <div class="flex flex-col xs:flex-row xs:items-center xs:justify-between gap-2 xs:gap-3 sm:gap-4">


                {{-- 🔎 Search & Filters --}}
                <div class="w-full xs:w-auto flex flex-col sm:flex-row sm:items-center gap-2 xs:gap-3">
                    {{-- Search Input --}}
                    <div class="relative flex-1 min-w-0 xs:min-w-[200px] sm:min-w-[250px] md:min-w-[300px]">
                        <input type="text" x-model="searchQuery" placeholder="Cari tugas..."
                            class="w-full pl-8 xs:pl-9 pr-3 py-2 xs:py-2.5 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-xs xs:text-sm sm:text-base">
                        <svg class="absolute left-2.5 xs:left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 xs:h-4 xs:w-4 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    {{-- Filter Dropdowns --}}
                    <div class="flex flex-wrap gap-1 xs:gap-2">
                        {{-- Label Filter --}}
                        <select x-model="selectedLabel"
                            class="px-2 xs:px-3 pr-6 xs:pr-7 py-1.5 xs:py-2 border border-gray-300 rounded text-xs xs:text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition bg-white min-w-[100px] xs:min-w-[110px]">
                            <option value="">Semua Label</option>
                            <template x-for="label in availableLabels" :key="label.name">
                                <option :value="label.name" x-text="label.name"></option>
                            </template>
                        </select>

                        {{-- Member Filter --}}
                        <select x-model="selectedMember"
                            class="px-2 xs:px-3 pr-6 xs:pr-7 py-1.5 xs:py-2 border border-gray-300 rounded text-xs xs:text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition bg-white min-w-[120px] xs:min-w-[130px]">
                            <option value="">Semua Peserta</option>
                            <template x-for="member in availableMembers" :key="member.name">
                                <option :value="member.name" x-text="member.name"></option>
                            </template>
                        </select>

                        {{-- Deadline Filter --}}
                        <select x-model="selectedDeadline"
                            class="px-2 xs:px-3 pr-6 xs:pr-7 py-1.5 xs:py-2 border border-gray-300 rounded text-xs xs:text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition bg-white min-w-[110px] xs:min-w-[120px]">
                            <option value="">Semua Tenggat</option>
                            <option value="segera">Segera</option>
                            <option value="hari-ini">Hari Ini</option>
                            <option value="terlambat">Terlambat</option>
                        </select>

                        {{-- Reset Button --}}
                        <button @click="resetFilters()"
                            class="px-2 xs:px-3 py-1.5 xs:py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded text-xs xs:text-sm font-medium transition whitespace-nowrap">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            {{-- 🎯 Active Filters Display --}}
            <div x-show="hasActiveFilters()" class="mt-2 xs:mt-3 flex flex-wrap gap-1 xs:gap-2">
                <template x-if="searchQuery">
                    <span
                        class="inline-flex items-center gap-1 px-2 xs:px-2.5 py-1 xs:py-1.5 bg-blue-100 text-blue-800 text-xs xs:text-sm rounded">
                        Pencarian: "<span x-text="searchQuery"
                            class="max-w-[100px] xs:max-w-[120px] sm:max-w-none truncate"></span>"
                        <button @click="searchQuery = ''"
                            class="ml-0.5 text-blue-600 hover:text-blue-800 font-bold text-xs xs:text-sm">×</button>
                    </span>
                </template>

                <template x-if="selectedLabel">
                    <span
                        class="inline-flex items-center gap-1 px-2 xs:px-2.5 py-1 xs:py-1.5 bg-green-100 text-green-800 text-xs xs:text-sm rounded">
                        Label: <span x-text="selectedLabel"
                            class="max-w-[80px] xs:max-w-[100px] sm:max-w-none truncate"></span>
                        <button @click="selectedLabel = ''"
                            class="ml-0.5 text-green-600 hover:text-green-800 font-bold text-xs xs:text-sm">×</button>
                    </span>
                </template>

                <template x-if="selectedMember">
                    <span
                        class="inline-flex items-center gap-1 px-2 xs:px-2.5 py-1 xs:py-1.5 bg-purple-100 text-purple-800 text-xs xs:text-sm rounded">
                        Peserta: <span x-text="selectedMember"
                            class="max-w-[80px] xs:max-w-[100px] sm:max-w-none truncate"></span>
                        <button @click="selectedMember = ''"
                            class="ml-0.5 text-purple-600 hover:text-purple-800 font-bold text-xs xs:text-sm">×</button>
                    </span>
                </template>

                <template x-if="selectedDeadline">
                    <span
                        class="inline-flex items-center gap-1 px-2 xs:px-2.5 py-1 xs:py-1.5 bg-orange-100 text-orange-800 text-xs xs:text-sm rounded">
                        <span x-text="getDeadlineFilterText()"
                            class="max-w-[80px] xs:max-w-[100px] sm:max-w-none truncate"></span>
                        <button @click="selectedDeadline = ''"
                            class="ml-0.5 text-orange-600 hover:text-orange-800 font-bold text-xs xs:text-sm">×</button>
                    </span>
                </template>
            </div>
        </div>

        {{-- 🎯 Kanban Board --}}
        <div x-show="!replyView.active">
            <div class="flex-1 overflow-x-auto" @click.outside="openListMenu = null">
                <div id="kanban-board" class="flex kanban-gap-medium p-5 xs:p-4 min-w-max">
                    {{-- 📋 To Do Column --}}
                    <div
                        class="bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col">
                        <div class="flex items-center justify-between mb-1 xs:mb-2">
                            <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base">To Do List</h2>
                            <button @click="openListMenu = openListMenu === 'todo' ? null : 'todo'"
                                class="text-gray-500 hover:text-gray-700 text-xs p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex flex-col flex-1">
                            <div id="todo"
                                class="space-y-2 flex-1 overflow-y-auto max-h-[50vh] xs:max-h-[55vh] sm:max-h-[60vh] pr-1">
                                <template x-for="task in getFilteredTasks('todo')" :key="task.id">
                                    <div @click="openDetail(task.id)"
                                        class="bg-white p-1.5 xs:p-2 sm:p-3 rounded shadow hover:shadow-md cursor-move border border-gray-200 transition-all duration-200 text-xs xs:text-sm">
                                        {{-- Header (Label + Tanggal) --}}
                                        <div class="flex items-center justify-between mb-1 xs:mb-2 flex-wrap gap-1">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="label in task.labels" :key="label.name">
                                                    <span class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
                                                        :style="`background: ${label.color}20; color: ${label.color}`"
                                                        x-text="label.name"></span>
                                                </template>
                                            </div>
                                            <span x-show="task.dueDate"
                                                class="font-semibold px-1.5 py-0.5 bg-yellow-100 text-gray-700 rounded text-xs xs:text-sm"
                                                x-text="formatDate(task.dueDate)"></span>
                                        </div>

                                        {{-- Judul --}}
                                        <p class="font-medium text-gray-800 mb-1 xs:mb-2 line-clamp-2 text-xs xs:text-sm"
                                            x-text="task.title"></p>

                                        {{-- Info --}}
                                        <div class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.attachments ? task.attachments.length : 0"></span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.comments ? task.comments.length : 0"></span>
                                            </div>
                                        </div>

                                        {{-- Progress + Avatars --}}
                                        <div class="mt-1 xs:mt-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="w-full bg-gray-200 h-1 xs:h-1.5 rounded-full mr-2">
                                                    <div class="bg-blue-500 h-1 xs:h-1.5 rounded-full transition-all duration-300"
                                                        :style="`width: ${calculateProgress(task)}%`"></div>
                                                </div>
                                                <span class="font-medium text-gray-700 text-xs xs:text-sm"
                                                    x-text="`${calculateProgress(task)}%`"></span>
                                            </div>
                                            <div class="flex mt-1 xs:mt-2 justify-end -space-x-1 xs:-space-x-2">
                                                <template x-for="member in task.members" :key="member.name">
                                                    <img :src="member.avatar"
                                                        class="w-4 h-4 xs:w-5 xs:h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white shadow-sm"
                                                        :alt="member.name" :title="member.name">
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- No tasks message --}}
                                <div x-show="getFilteredTasks('todo').length === 0"
                                    class="text-center text-gray-500 text-xs xs:text-sm py-3 xs:py-4">
                                    Tidak ada tugas
                                </div>
                            </div>

                            <button @click="openTaskModal = true"
                                class="w-full mt-3 py-2 text-sm font-medium text-gray-600 bg-white rounded-lg border border-gray-300 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-all duration-200 shadow-sm">
                                + Buat Tugas
                            </button>
                        </div>
                    </div>

                    {{-- 🔄 Dikerjakan Column --}}
                    <div
                        class="bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col">
                        <div class="flex items-center justify-between mb-1 xs:mb-2">
                            <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base">Dikerjakan</h2>
                            <button @click="openListMenu = openListMenu === 'inprogress' ? null : 'inprogress'"
                                class="text-gray-500 hover:text-gray-700 text-xs p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex flex-col flex-1">
                            <div id="inprogress"
                                class="space-y-2 flex-1 overflow-y-auto max-h-[50vh] xs:max-h-[55vh] sm:max-h-[60vh] pr-1">
                                <template x-for="task in getFilteredTasks('inprogress')" :key="task.id">
                                    <div @click="openDetail(task.id)"
                                        class="bg-white p-2 xs:p-3 rounded shadow hover:shadow-md cursor-move border border-gray-200 transition-all duration-200 text-xs xs:text-sm">
                                        {{-- Header (Label + Tanggal) --}}
                                        <div class="flex items-center justify-between mb-1 xs:mb-2 flex-wrap gap-1">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="label in task.labels" :key="label.name">
                                                    <span class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
                                                        :style="`background: ${label.color}20; color: ${label.color}`"
                                                        x-text="label.name"></span>
                                                </template>
                                            </div>
                                            <span x-show="task.dueDate"
                                                class="font-semibold px-1.5 py-0.5 bg-yellow-100 text-gray-700 rounded text-xs xs:text-sm"
                                                x-text="formatDate(task.dueDate)"></span>
                                        </div>

                                        {{-- Judul --}}
                                        <p class="font-medium text-gray-800 mb-1 xs:mb-2 line-clamp-2 text-xs xs:text-sm"
                                            x-text="task.title"></p>

                                        {{-- Info --}}
                                        <div class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.attachments ? task.attachments.length : 0"></span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.comments ? task.comments.length : 0"></span>
                                            </div>
                                        </div>

                                        {{-- Progress + Avatars --}}
                                        <div class="mt-1 xs:mt-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="w-full bg-gray-200 h-1 xs:h-1.5 rounded-full mr-2">
                                                    <div class="bg-blue-500 h-1 xs:h-1.5 rounded-full transition-all duration-300"
                                                        :style="`width: ${calculateProgress(task)}%`"></div>
                                                </div>
                                                <span class="font-medium text-gray-700 text-xs xs:text-sm"
                                                    x-text="`${calculateProgress(task)}%`"></span>
                                            </div>
                                            <div class="flex mt-1 xs:mt-2 justify-end -space-x-1 xs:-space-x-2">
                                                <template x-for="member in task.members" :key="member.name">
                                                    <img :src="member.avatar"
                                                        class="w-4 h-4 xs:w-5 xs:h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white shadow-sm"
                                                        :alt="member.name" :title="member.name">
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- No tasks message --}}
                                <div x-show="getFilteredTasks('inprogress').length === 0"
                                    class="text-center text-gray-500 text-xs xs:text-sm py-3 xs:py-4">
                                    Tidak ada tugas
                                </div>
                            </div>

                            <button @click="openTaskModal = true"
                                class="w-full mt-2 xs:mt-3 py-1.5 xs:py-2 text-xs xs:text-sm font-medium text-gray-600 bg-white rounded-lg border border-gray-300 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-all duration-200 shadow-sm">
                                + Buat Tugas
                            </button>
                        </div>
                    </div>

                    {{-- ✅ Selesai Column --}}
                    <div
                        class="bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col">
                        <div class="flex items-center justify-between mb-1 xs:mb-2">
                            <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base">Selesai</h2>
                            <button @click="openListMenu = openListMenu === 'done' ? null : 'done'"
                                class="text-gray-500 hover:text-gray-700 text-xs p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex flex-col flex-1">
                            <div id="done"
                                class="space-y-2 flex-1 overflow-y-auto max-h-[50vh] xs:max-h-[55vh] sm:max-h-[60vh] pr-1">
                                <template x-for="task in getFilteredTasks('done')" :key="task.id">
                                    <div @click="openDetail(task.id)"
                                        class="bg-white p-2 xs:p-3 rounded shadow hover:shadow-md cursor-move border border-gray-200 transition-all duration-200 text-xs xs:text-sm">
                                        {{-- Header (Label + Tanggal) --}}
                                        <div class="flex items-center justify-between mb-1 xs:mb-2 flex-wrap gap-1">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="label in task.labels" :key="label.name">
                                                    <span class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
                                                        :style="`background: ${label.color}20; color: ${label.color}`"
                                                        x-text="label.name"></span>
                                                </template>
                                            </div>
                                            <span x-show="task.dueDate"
                                                class="font-semibold px-1.5 py-0.5 bg-yellow-100 text-gray-700 rounded text-xs xs:text-sm"
                                                x-text="formatDate(task.dueDate)"></span>
                                        </div>

                                        {{-- Judul --}}
                                        <p class="font-medium text-gray-800 mb-1 xs:mb-2 line-clamp-2 text-xs xs:text-sm"
                                            x-text="task.title"></p>

                                        {{-- Info --}}
                                        <div class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.attachments ? task.attachments.length : 0"></span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.comments ? task.comments.length : 0"></span>
                                            </div>
                                        </div>

                                        {{-- Progress + Avatars --}}
                                        <div class="mt-1 xs:mt-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="w-full bg-gray-200 h-1 xs:h-1.5 rounded-full mr-2">
                                                    <div class="bg-green-500 h-1 xs:h-1.5 rounded-full transition-all duration-300"
                                                        :style="`width: ${calculateProgress(task)}%`"></div>
                                                </div>
                                                <span class="font-medium text-gray-700 text-xs xs:text-sm"
                                                    x-text="`${calculateProgress(task)}%`"></span>
                                            </div>
                                            <div class="flex mt-1 xs:mt-2 justify-end -space-x-1 xs:-space-x-2">
                                                <template x-for="member in task.members" :key="member.name">
                                                    <img :src="member.avatar"
                                                        class="w-4 h-4 xs:w-5 xs:h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white shadow-sm"
                                                        :alt="member.name" :title="member.name">
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- No tasks message --}}
                                <div x-show="getFilteredTasks('done').length === 0"
                                    class="text-center text-gray-500 text-xs xs:text-sm py-3 xs:py-4">
                                    Tidak ada tugas
                                </div>
                            </div>

                            <button @click="openTaskModal = true"
                                class="w-full mt-2 xs:mt-3 py-1.5 xs:py-2 text-xs xs:text-sm font-medium text-gray-600 bg-white rounded-lg border border-gray-300 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-all duration-200 shadow-sm">
                                + Buat Tugas
                            </button>
                        </div>
                    </div>

                    {{-- ❌ Batal Column --}}
                    <div
                        class="bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col">
                        <div class="flex items-center justify-between mb-1 xs:mb-2">
                            <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base">Batal</h2>
                            <button @click="openListMenu = openListMenu === 'cancel' ? null : 'cancel'"
                                class="text-gray-500 hover:text-gray-700 text-xs p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex flex-col flex-1">
                            <div id="cancel"
                                class="space-y-2 flex-1 overflow-y-auto max-h-[50vh] xs:max-h-[55vh] sm:max-h-[60vh] pr-1">
                                <template x-for="task in getFilteredTasks('cancel')" :key="task.id">
                                    <div @click="openDetail(task.id)"
                                        class="bg-white p-2 xs:p-3 rounded shadow hover:shadow-md cursor-move border border-gray-200 transition-all duration-200 text-xs xs:text-sm">
                                        {{-- Header (Label + Tanggal) --}}
                                        <div class="flex items-center justify-between mb-1 xs:mb-2 flex-wrap gap-1">
                                            <div class="flex flex-wrap gap-1">
                                                <template x-for="label in task.labels" :key="label.name">
                                                    <span class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
                                                        :style="`background: ${label.color}20; color: ${label.color}`"
                                                        x-text="label.name"></span>
                                                </template>
                                            </div>
                                            <span x-show="task.dueDate"
                                                class="font-semibold px-1.5 py-0.5 bg-yellow-100 text-gray-700 rounded text-xs xs:text-sm"
                                                x-text="formatDate(task.dueDate)"></span>
                                        </div>

                                        {{-- Judul --}}
                                        <p class="font-medium text-gray-800 mb-1 xs:mb-2 line-clamp-2 text-xs xs:text-sm"
                                            x-text="task.title"></p>

                                        {{-- Info --}}
                                        <div class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.attachments ? task.attachments.length : 0"></span>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 01-4-.8L3 20l1.8-3.6A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                                <span class="text-xs xs:text-sm"
                                                    x-text="task.comments ? task.comments.length : 0"></span>
                                            </div>
                                        </div>

                                        {{-- Progress + Avatars --}}
                                        <div class="mt-1 xs:mt-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="w-full bg-gray-200 h-1 xs:h-1.5 rounded-full mr-2">
                                                    <div class="bg-red-500 h-1 xs:h-1.5 rounded-full transition-all duration-300"
                                                        :style="`width: ${calculateProgress(task)}%`"></div>
                                                </div>
                                                <span class="font-medium text-gray-700 text-xs xs:text-sm"
                                                    x-text="`${calculateProgress(task)}%`"></span>
                                            </div>
                                            <div class="flex mt-1 xs:mt-2 justify-end -space-x-1 xs:-space-x-2">
                                                <template x-for="member in task.members" :key="member.name">
                                                    <img :src="member.avatar"
                                                        class="w-4 h-4 xs:w-5 xs:h-5 sm:w-6 sm:h-6 rounded-full border-2 border-white shadow-sm"
                                                        :alt="member.name" :title="member.name">
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- No tasks message --}}
                                <div x-show="getFilteredTasks('cancel').length === 0"
                                    class="text-center text-gray-500 text-xs xs:text-sm py-3 xs:py-4">
                                    Tidak ada tugas
                                </div>
                            </div>

                            <button @click="openTaskModal = true"
                                class="w-full mt-2 xs:mt-3 py-1.5 xs:py-2 text-xs xs:text-sm font-medium text-gray-600 bg-white rounded-lg border border-gray-300 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-all duration-200 shadow-sm">
                                + Buat Tugas
                            </button>
                        </div>
                    </div>

                    {{-- ➕ Tombol Tambah List --}}
                    <div class="flex items-start justify-center pr-2 xs:pr-3">
                        <button @click="openModal = true"
                            class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1.5 xs:px-3 xs:py-2 rounded-lg text-xs xs:text-sm shadow-md hover:shadow-lg transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden xs:inline">Tambah List</span>
                            <span class="xs:hidden">Tambah</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        {{-- Halaman Balas Komentar --}}
        @include('components.balas-komentar')



        <!-- Modal Aksi List -->
        <div x-show="openListMenu && !replyView.active" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition
            @click.self="openListMenu = null">

            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xs mx-auto">
                <!-- Header -->
                <div class="px-6 py-4 border-b">
                    <h2 class="text-center font-bold text-lg text-gray-800">Aksi List</h2>
                </div>

                <!-- Menu Options -->
                <div class="p-4 space-y-3">
                    <!-- Urutkan tugas dari tenggat waktu terdekat -->
                    <button @click="sortTasks('deadline-asc'); openListMenu = null"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                        <div class="font-medium">Urutkan tugas dari tenggat waktu terdekat</div>
                    </button>

                    <!-- Urutkan tugas dari tenggat waktu terjauh -->
                    <button @click="sortTasks('deadline-desc'); openListMenu = null"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                        <div class="font-medium">Urutkan tugas dari tenggat waktu terjauh</div>
                    </button>

                    <!-- Urutkan tugas dari waktu dibuat terdekat -->
                    <button @click="sortTasks('created-asc'); openListMenu = null"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                        <div class="font-medium">Urutkan tugas dari waktu dibuat terdekat</div>
                    </button>

                    <!-- Urutkan tugas dari waktu dibuat terjauh -->
                    <button @click="sortTasks('created-desc'); openListMenu = null"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                        <div class="font-medium">Urutkan tugas dari waktu dibuat terjauh</div>
                    </button>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t bg-gray-50 rounded-b-2xl">
                    <button @click="openListMenu = null"
                        class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition-colors duration-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>


        <!-- Modal Tambah List -->
        <div x-show="openModal && !replyView.active"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded-xl w-80 shadow-lg">
                <h2 class="text-center font-bold text-lg mb-4">Kanban List</h2>
                <input type="text" x-model="newListName" placeholder="Masukkan nama list"
                    class="w-full border rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring focus:ring-blue-300" />
                <div class="flex justify-end gap-3">
                    <button @click="openModal = false" class="px-4 py-2 rounded-lg bg-red-400 text-white">Batal</button>
                    <button @click="addList()" class="px-4 py-2 rounded-lg bg-blue-800 text-white">
                        Simpan
                    </button>

                </div>
            </div>
        </div>



        <!-- Modal Tambah Tugas -->
        <div x-show="openTaskModal && !replyView.active" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition
            @click.self="openTaskModal = false"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition>

            <div class="bg-white rounded-xl w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <!-- Header Modal -->
                <div class="bg-white px-6 py-5 border-b">
                    <h2 class="text-center font-bold text-xl text-gray-800">Buat Tugas Baru</h2>
                    <p class="text-center text-sm text-gray-500 mt-1">Didalam To do list di HQ</p>
                </div>

                <!-- Form Content -->
                <form class="p-6 space-y-4">
                    <!-- Nama Tugas -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Nama Tugas <span
                                class="text-red-500">*</span></label>
                        <input type="text" placeholder="Masukkan nama tugas..."
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                    </div>

                    <!-- Anggota & Tugas Rahasia -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Anggota <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <button type="button" @click="openAddMemberModal = true"
                                    class="text-gray-500 text-xl hover:text-gray-700 font-light">+</button>

                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-600">Rahasia hanya untuk yang terlibat?</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div
                                        class="w-12 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                                    </div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Tugas Rahasia</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tambah Peserta -->
                    <div x-show="openAddMemberModal" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                        x-transition>
                        <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b">
                                <h2 class="text-center font-bold text-lg text-gray-800">Tambah Peserta</h2>
                            </div>

                            <!-- Isi Modal -->
                            <div class="p-6 space-y-4">
                                <!-- Input Cari -->
                                <div class="relative">
                                    <input type="text" placeholder="Cari anggota..."
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                        x-model="searchMember">
                                </div>

                                <!-- Pilih Semua -->
                                <div class="flex items-center justify-between border-b pb-2">
                                    <span class="font-medium text-gray-700 text-sm">Pilih Semua</span>
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll">
                                </div>

                                <!-- List Anggota -->
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    <template x-for="(member, index) in filteredMembers()" :key="index">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <img :src="member.avatar" class="w-8 h-8 rounded-full" alt="">
                                                <span class="text-sm font-medium text-gray-700"
                                                    x-text="member.name"></span>
                                            </div>
                                            <input type="checkbox" x-model="member.selected">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-3 p-4 border-t">
                                <button type="button" @click="openAddMemberModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedMembers()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Catatan <span class="text-red-500">*</span>
                        </label>
                        <div class="border rounded-lg overflow-hidden">
                            <textarea id="editor-catatan" name="catatan"></textarea>
                        </div>
                    </div>

                    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            ClassicEditor
                                .create(document.querySelector('#editor-catatan'), {
                                    toolbar: {
                                        items: [
                                            'undo', 'redo', '|',
                                            'heading', '|',
                                            'bold', 'italic', 'underline', 'strikethrough', '|',
                                            'fontColor', 'fontBackgroundColor', '|', // 🎨 warna teks & background
                                            'link', 'blockQuote', 'code', '|',
                                            'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                                            'insertTable', 'imageUpload', 'mediaEmbed'
                                        ],
                                        shouldNotGroupWhenFull: true
                                    },
                                    heading: {
                                        options: [{
                                                model: 'paragraph',
                                                title: 'Paragraf',
                                                class: 'ck-heading_paragraph'
                                            },
                                            {
                                                model: 'heading1',
                                                view: 'h1',
                                                title: 'Heading 1',
                                                class: 'ck-heading_heading1'
                                            },
                                            {
                                                model: 'heading2',
                                                view: 'h2',
                                                title: 'Heading 2',
                                                class: 'ck-heading_heading2'
                                            },
                                            {
                                                model: 'heading3',
                                                view: 'h3',
                                                title: 'Heading 3',
                                                class: 'ck-heading_heading3'
                                            }
                                        ]
                                    },
                                    fontColor: {
                                        colors: [{
                                                color: 'black',
                                                label: 'Hitam'
                                            },
                                            {
                                                color: 'red',
                                                label: 'Merah'
                                            },
                                            {
                                                color: 'blue',
                                                label: 'Biru'
                                            },
                                            {
                                                color: 'green',
                                                label: 'Hijau'
                                            },
                                            {
                                                color: 'orange',
                                                label: 'Oranye'
                                            },
                                            {
                                                color: 'purple',
                                                label: 'Ungu'
                                            }
                                        ]
                                    },
                                    fontBackgroundColor: {
                                        colors: [{
                                                color: 'yellow',
                                                label: 'Kuning'
                                            },
                                            {
                                                color: 'lightgreen',
                                                label: 'Hijau Muda'
                                            },
                                            {
                                                color: 'lightblue',
                                                label: 'Biru Muda'
                                            },
                                            {
                                                color: 'pink',
                                                label: 'Merah Muda'
                                            },
                                            {
                                                color: 'gray',
                                                label: 'Abu-abu'
                                            }
                                        ]
                                    },
                                    image: {
                                        toolbar: [
                                            'imageTextAlternative', 'imageStyle:inline',
                                            'imageStyle:block', 'imageStyle:side'
                                        ]
                                    },
                                    table: {
                                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                                    },
                                    mediaEmbed: {
                                        previewsInData: true
                                    }
                                })
                                .then(editor => {
                                    console.log('CKEditor siap dipakai:', editor);

                                    // Simpan value ke textarea saat submit form
                                    const form = document.querySelector("form");
                                    form?.addEventListener("submit", () => {
                                        document.querySelector("#editor-catatan").value = editor.getData();
                                    });
                                })
                                .catch(error => console.error(error));
                        });
                    </script>



                    <!-- Lampiran -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Lampiran</label>
                        <label
                            class="border border-gray-300 rounded-md px-4 py-2.5 flex items-center justify-between hover:border-gray-400 cursor-pointer bg-white">
                            <span class="text-sm text-gray-500">Unggah File</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <input type="file" class="hidden" />
                        </label>
                    </div>



                    <!-- Tombol Pilih Label -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Label</label>
                        <button type="button" @click="openLabelModal = true"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm text-left text-gray-600 hover:bg-gray-50 flex items-center justify-between bg-white shadow-sm">
                            <span>Pilih Label Tugas</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Pilih Label -->
                    <div x-show="openLabelModal" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Pilih Label</h2>

                            <!-- Search -->
                            <input type="text" x-model="searchLabel" placeholder="Cari label..."
                                class="w-full border rounded-lg p-2 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Tombol Tambah Label -->
                            <button type="button" @click="openAddLabelModal = true; openLabelModal = false"
                                class="text-blue-600 text-sm hover:underline font-medium mb-3">
                                + Tambah Label
                            </button>

                            <!-- List Label -->
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                <template x-for="label in filteredLabels()" :key="label.name">
                                    <label
                                        class="flex items-center cursor-pointer border rounded-lg px-3 py-2 hover:bg-gray-50 transition">
                                        <input type="checkbox" x-model="label.selected"
                                            class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <div class="flex-1">
                                            <span
                                                class="block w-full text-center px-3 py-1 rounded-md text-white text-sm font-medium shadow-sm"
                                                :style="`background:${label.color}`" x-text="label.name">
                                            </span>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end items-center mt-6 space-x-2">
                                <button type="button" @click="openLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedLabels"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>


                    <!-- Modal Tambah Label -->
                    <div x-show="openAddLabelModal" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Label Baru</h2>

                            <!-- Input nama -->
                            <input type="text" x-model="newLabelName" placeholder="Nama Label"
                                class="w-full border rounded-lg p-2 text-sm mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Pilihan Warna -->
                            <div class="grid grid-cols-6 gap-2 mb-4 max-h-40 overflow-y-auto pr-1">
                                <template x-for="color in colorPalette" :key="color">
                                    <div class="w-8 h-8 rounded-lg cursor-pointer border shadow-sm"
                                        :style="`background:${color}`" @click="newLabelColor = color"
                                        :class="{ 'ring-2 ring-offset-2 ring-blue-600': newLabelColor === color }">
                                    </div>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="openAddLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="addNewLabel"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>


                    <!-- Ceklis -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Ceklis</label>
                        <button type="button" @click="openCeklisModal = true"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm text-left text-gray-500 hover:bg-gray-50 flex items-center justify-between bg-white">
                            <span>Buat ceklis</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Tambah Ceklis -->
                    <div x-show="openCeklisModal"
                        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" x-cloak>
                        <div class="bg-blue-50 rounded-xl shadow-lg w-96 p-6 text-center">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Ceklis</h2>
                            <input type="text" x-model="newCeklisName" placeholder="Masukkan nama ceklis"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

                            <div class="flex justify-center gap-3">
                                <button type="button" @click="openCeklisModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 hover:bg-gray-50 text-sm font-medium">
                                    Batal
                                </button>
                                <button type="button" @click="saveCeklis()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>


                    <!-- Tanggal & Jam -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam</label>
                            <input type="time"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tenggat</label>
                            <input type="date"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam</label>
                            <input type="time"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-center gap-3 pt-4">
                        <button type="button" @click="openTaskModal = false"
                            class="px-10 py-2 rounded-md bg-white  hover:bg-gray-50 text-blue-600 border border-blue-600 font-medium text-sm">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-10 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm">
                            Simpan
                        </button>

                    </div>
                </form>
            </div>
        </div>





        <!-- Modal Detail Tugas -->
        <div x-show="openTaskDetail && !replyView.active" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-transition
            @click.self="openTaskDetail = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-transition>
            <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

                <!-- Header -->
                <div class="bg-white px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-800 text-center mb-1">
                        MENYELESAIKAN LAPORAN KEUANGANnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn
                    </h2>
                    <p class="text-xs text-gray-500 text-center">
                        Ditambahkan ke To-Do List di HQ pada 27 September.
                    </p>
                </div>

                <!-- Scrollable Content -->
                <div class="overflow-y-auto flex-1 px-6 py-4">
                    <!-- Tombol Pindahkan -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pindahkan</label>
                        <button
                            class="border border-gray-300 rounded-md px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                            @click="openMoveModal = true">
                            <span>Pindahkan Tugas</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Pindahkan -->
                    <div x-show="openMoveModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                        x-transition>
                        <div class="bg-blue-50 rounded-xl shadow-xl w-full max-w-xs p-6">
                            <h2 class="text-center font-semibold text-gray-800 text-lg mb-4">Pindahkan</h2>

                            <!-- Tujuan List -->
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan list</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm">
                                        <option>To do list</option>
                                        <option>Dikerjakan</option>
                                        <option>Selesai</option>
                                        <option>Bata;</option>
                                    </select>
                                    <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm">
                                        <option>1</option>
                                        <option>2</option>
                                        <option>3</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Tujuan Tim -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan tim</label>
                                <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm">
                                    <option>HQ</option>
                                    <option>Finance</option>
                                    <option>Marketing</option>
                                    <option>Proyek jalan</option>
                                </select>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" @click="openMoveModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 hover:bg-gray-50 text-sm ">
                                    Batal
                                </button>
                                <button type="button"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>



                    <!-- Anggota & Tugas Rahasia -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Anggota <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <button type="button" @click="openAddMemberModal = true"
                                    class="text-gray-500 text-xl hover:text-gray-700 font-light">+</button>

                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-600">Rahasia hanya untuk yang terlibat?</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div
                                        class="w-12 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                                    </div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Tugas Rahasia</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tambah Peserta -->
                    <div x-show="openAddMemberModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                        x-transition>
                        <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b">
                                <h2 class="text-center font-bold text-lg text-gray-800">Tambah Peserta</h2>
                            </div>

                            <!-- Isi Modal -->
                            <div class="p-6 space-y-4">
                                <!-- Input Cari -->
                                <div class="relative">
                                    <input type="text" placeholder="Cari anggota..."
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                        x-model="searchMember">
                                </div>

                                <!-- Pilih Semua -->
                                <div class="flex items-center justify-between border-b pb-2">
                                    <span class="font-medium text-gray-700 text-sm">Pilih Semua</span>
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll">
                                </div>

                                <!-- List Anggota -->
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    <template x-for="(member, index) in filteredMembers()" :key="index">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <img :src="member.avatar" class="w-8 h-8 rounded-full" alt="">
                                                <span class="text-sm font-medium text-gray-700"
                                                    x-text="member.name"></span>
                                            </div>
                                            <input type="checkbox" x-model="member.selected">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-3 p-4 border-t">
                                <button type="button" @click="openAddMemberModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedMembers()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4" x-data="{ editing: false, editor: null }">
                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                            Catatan
                            <button type="button" class="p-1 rounded hover:bg-green-100"
                                @click="
                if (!editing) {
                    editing = true;
                    ClassicEditor.create($refs.catatanEditor).then(ed => editor = ed);
                }
            ">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </label>

                        <!-- Textarea biasa -->
                        <textarea x-show="!editing"
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            rows="3" x-ref="catatanText" readonly>Laporan keuangan Q4 harus diselesaikan sebelum tanggal 30 September. Data transaksi sudah ada di sistem, tinggal verifikasi dan penyusunan format final PDF.</textarea>

                        <!-- Textarea CKEditor -->
                        <div x-show="editing" class="border rounded-lg overflow-hidden">
                            <textarea id="editor-catatan" x-ref="catatanEditor">
Laporan keuangan Q4 harus diselesaikan sebelum tanggal 30 September. Data transaksi sudah ada di sistem, tinggal verifikasi dan penyusunan format final PDF.
        </textarea>
                        </div>

                        <!-- Tombol Simpan & Batal -->
                        <div x-show="editing" class="flex justify-end gap-2 mt-2">
                            <button type="button"
                                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                @click="
                $refs.catatanText.value = editor.getData();
                editor.destroy();
                editor = null;
                editing = false;
            ">
                                Simpan
                            </button>
                            <button type="button"
                                class="px-3 py-1.5 text-sm bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
                                @click="
                editor.destroy();
                editor = null;
                editing = false;
            ">
                                Batal
                            </button>
                        </div>
                    </div>

                    <!-- CKEditor -->
                    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

                    <!-- Alpine.js -->
                    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

                    <script>
                        document.addEventListener("alpine:init", () => {
                            console.log("Alpine siap digunakan!");
                        });
                    </script>

                    <!-- Lampiran -->
                    <div class="mb-4" x-data="lampiranHandler()">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran</label>

                        <!-- Daftar Lampiran -->
                        <div class="space-y-2">
                            <template x-for="(file, index) in files" :key="index">
                                <div class="flex items-center justify-between border border-gray-300 rounded-lg p-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                                        </svg>
                                        <span class="text-sm" x-text="file.name"></span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs">
                                        <button class="text-blue-600 hover:underline"
                                            @click="downloadFile(file)">Unduh</button>
                                        <button class="text-red-600 hover:underline"
                                            @click="removeFile(index)">Hapus</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Tombol Tambah -->
                        <button type="button" @click="$refs.fileInput.click()"
                            class="mt-3 px-2 py-1 text-xs text-white bg-blue-600 rounded-md hover:bg-blue-700 flex items-center gap-1">
                            <span>Tambah</span>
                            <span class="text-base font-light">+</span>
                        </button>

                        <!-- Input File Tersembunyi -->
                        <input type="file" x-ref="fileInput" class="hidden" @change="addFile">
                    </div>

                    <!-- Alpine.js -->
                    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

                    <script>
                        function lampiranHandler() {
                            return {
                                files: [{
                                        name: 'Draft_Laporan_Q4.docx'
                                    },
                                    {
                                        name: 'Data_Transaksi_Q4.xlsx'
                                    }
                                ],

                                addFile(event) {
                                    const file = event.target.files[0];
                                    if (file) {
                                        this.files.push({
                                            name: file.name
                                        });
                                    }
                                    event.target.value = ''; // reset input file
                                },

                                removeFile(index) {
                                    this.files.splice(index, 1);
                                },

                                downloadFile(file) {
                                    alert(`Mengunduh: ${file.name}`);
                                    // di sini bisa disesuaikan kalau ingin fungsi unduh nyata
                                }
                            }
                        }
                    </script>


                    <!-- Label -->
                    <div class="mb-4">
                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                            Label
                            <button type="button" @click="openLabelModal = true">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </label>
                        <span
                            class="inline-block w-full px-4 py-2 rounded-md bg-yellow-100 text-yellow-700 text-sm text-center font-medium">Finance</span>
                    </div>

                    <!-- Modal Pilih Label -->
                    <div x-show="openLabelModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Pilih Label</h2>

                            <!-- Search -->
                            <input type="text" x-model="searchLabel" placeholder="Cari label..."
                                class="w-full border rounded-lg p-2 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Tombol Tambah Label -->
                            <button type="button" @click="openAddLabelModal = true; openLabelModal = false"
                                class="text-blue-600 text-sm hover:underline font-medium mb-3">
                                + Tambah Label
                            </button>

                            <!-- List Label -->
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                <template x-for="label in filteredLabels()" :key="label.name">
                                    <label
                                        class="flex items-center cursor-pointer border rounded-lg px-3 py-2 hover:bg-gray-50 transition">
                                        <input type="checkbox" x-model="label.selected"
                                            class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <div class="flex-1">
                                            <span
                                                class="block w-full text-center px-3 py-1 rounded-md text-white text-sm font-medium shadow-sm"
                                                :style="`background:${label.color}`" x-text="label.name">
                                            </span>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end items-center mt-6 space-x-2">
                                <button type="button" @click="openLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedLabels"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>


                    <!-- Modal Tambah Label -->
                    <div x-show="openAddLabelModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Label Baru</h2>

                            <!-- Input nama -->
                            <input type="text" x-model="newLabelName" placeholder="Nama Label"
                                class="w-full border rounded-lg p-2 text-sm mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Pilihan Warna -->
                            <div class="grid grid-cols-6 gap-2 mb-4 max-h-40 overflow-y-auto pr-1">
                                <template x-for="color in colorPalette" :key="color">
                                    <div class="w-8 h-8 rounded-lg cursor-pointer border shadow-sm"
                                        :style="`background:${color}`" @click="newLabelColor = color"
                                        :class="{ 'ring-2 ring-offset-2 ring-blue-600': newLabelColor === color }">
                                    </div>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="openAddLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="addNewLabel"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Ceklis -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ceklis</label>
                        <div class="space-y-2 border border-gray-300 rounded-lg p-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" checked class="rounded border-gray-300 text-blue-600">
                                <span class="line-through text-gray-500">Kumpulkan data transaksi</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span>Verifikasi data dengan tim Finance</span>
                            </label>
                        </div>
                        <button type="button" @click="openCeklisModal = true"
                            class="mt-3 px-2 py-1 text-xs text-white bg-blue-600 rounded-md hover:bg-blue-700 flex items-center gap-1">
                            <span>Tambah</span>
                            <span class="text-base font-light">+</span>
                        </button>
                    </div>

                    <!-- Modal Tambah Ceklis -->
                    <div x-show="openCeklisModal && !replyView.active"
                        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" x-cloak>
                        <div class="bg-blue-50 rounded-xl shadow-lg w-96 p-6 text-center">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Ceklis</h2>
                            <input type="text" x-model="newCeklisName" placeholder="Masukkan nama ceklis"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

                            <div class="flex justify-center gap-3">
                                <button type="button" @click="openCeklisModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 hover:bg-gray-50 text-sm font-medium">
                                    Batal
                                </button>
                                <button type="button" @click="saveCeklis()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal & Jam -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" value="2025-10-04"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                            <input type="time" value="14:30"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tenggat</label>
                            <input type="date" value="2025-10-05"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Tenggat</label>
                            <input type="time" value="16:00"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>


                    <!-- Komentar Section -->

                    <!-- Komentar Section -->
                    <div class="border-t pt-4 mt-6">
                        <div class="space-y-4">

                            <!-- Tambah Komentar -->
                            <div class="mb-6">
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Tulis Komentar</label>
                                <div class="border rounded-lg overflow-hidden">
                                    <textarea id="editor-komentar" name="komentar"></textarea>
                                </div>
                                <div class="flex justify-end gap-2 mt-2">
                                    <button type="button"
                                        class="mt-3 px-4 py-2 text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 text-sm">
                                        Batal
                                    </button>
                                    <button type="button"
                                        class="mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                                        Kirim
                                    </button>
                                </div>
                            </div>

                            <!-- Komentar 1 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-semibold text-gray-800">Risi Gustiar</p>
                                    <span class="text-xs text-gray-500">Sabtu, 27 Sep 2025</span>
                                </div>
                                <p class="text-sm text-gray-700">
                                    Data transaksi sudah saya update di file Excel.
                                </p>
                                <button
                                    @click="openReplyFromModal({
                    id: 1,
                    author: { name: 'Risi Gustiar', avatar: 'https://i.pravatar.cc/40?img=3' },
                    content: 'Data transaksi sudah saya update di file Excel.',
                    createdAt: '2025-09-27T10:00:00',
                    replies: []
                })"
                                    class="mt-2 flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    <span>balas</span>
                                    <span class="ml-1">2 balasan</span>
                                </button>
                            </div>

                            <!-- Komentar 2 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-semibold text-gray-800">Rendi Sinaga</p>
                                    <span class="text-xs text-gray-500">Minggu, 28 Sep 2025</span>
                                </div>
                                <p class="text-sm text-gray-700">
                                    Draft laporan hampir selesai, tinggal verifikasi
                                </p>
                                <button
                                    @click="openReplyFromModal({
                    id: 2,
                    author: { name: 'Rendi Sinaga', avatar: 'https://i.pravatar.cc/40?img=4' },
                    content: 'Draft laporan hampir selesai, tinggal verifikasi',
                    createdAt: '2025-09-28T14:30:00',
                    replies: []
                })"
                                    class="mt-2 flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    <span>balas</span>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- CKEditor Script -->
                    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            ClassicEditor
                                .create(document.querySelector('#editor-komentar'), {
                                    toolbar: {
                                        items: [
                                            'undo', 'redo', '|',
                                            'heading', '|',
                                            'bold', 'italic', 'underline', 'strikethrough', '|',
                                            'fontColor', 'fontBackgroundColor', '|',
                                            'link', 'blockQuote', 'code', '|',
                                            'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                                            'insertTable', 'imageUpload', 'mediaEmbed'
                                        ],
                                        shouldNotGroupWhenFull: true
                                    },
                                    heading: {
                                        options: [{
                                                model: 'paragraph',
                                                title: 'Paragraf',
                                                class: 'ck-heading_paragraph'
                                            },
                                            {
                                                model: 'heading1',
                                                view: 'h1',
                                                title: 'Heading 1',
                                                class: 'ck-heading_heading1'
                                            },
                                            {
                                                model: 'heading2',
                                                view: 'h2',
                                                title: 'Heading 2',
                                                class: 'ck-heading_heading2'
                                            },
                                            {
                                                model: 'heading3',
                                                view: 'h3',
                                                title: 'Heading 3',
                                                class: 'ck-heading_heading3'
                                            }
                                        ]
                                    },
                                    fontColor: {
                                        colors: [{
                                                color: 'black',
                                                label: 'Hitam'
                                            },
                                            {
                                                color: 'red',
                                                label: 'Merah'
                                            },
                                            {
                                                color: 'blue',
                                                label: 'Biru'
                                            },
                                            {
                                                color: 'green',
                                                label: 'Hijau'
                                            },
                                            {
                                                color: 'orange',
                                                label: 'Oranye'
                                            },
                                            {
                                                color: 'purple',
                                                label: 'Ungu'
                                            }
                                        ]
                                    },
                                    fontBackgroundColor: {
                                        colors: [{
                                                color: 'yellow',
                                                label: 'Kuning'
                                            },
                                            {
                                                color: 'lightgreen',
                                                label: 'Hijau Muda'
                                            },
                                            {
                                                color: 'lightblue',
                                                label: 'Biru Muda'
                                            },
                                            {
                                                color: 'pink',
                                                label: 'Merah Muda'
                                            },
                                            {
                                                color: 'gray',
                                                label: 'Abu-abu'
                                            }
                                        ]
                                    },
                                    image: {
                                        toolbar: [
                                            'imageTextAlternative',
                                            'imageStyle:inline',
                                            'imageStyle:block',
                                            'imageStyle:side'
                                        ]
                                    },
                                    table: {
                                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                                    },
                                    mediaEmbed: {
                                        previewsInData: true
                                    }
                                })
                                .then(editor => {
                                    console.log('CKEditor siap dipakai untuk komentar:', editor);

                                    // Simpan value ke textarea saat submit form
                                    const form = document.querySelector("form");
                                    form?.addEventListener("submit", () => {
                                        document.querySelector("#editor-komentar").value = editor.getData();
                                    });
                                })
                                .catch(error => console.error(error));
                        });
                    </script>



                </div>
            </div>
        </div>




        {{-- Script SortableJS --}}
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            ['todo', 'inprogress', 'done', 'cancel'].forEach(id => {
                let el = document.getElementById(id);
                if (el) {
                    new Sortable(el, {
                        group: 'kanban',
                        animation: 150,
                        ghostClass: 'bg-blue-300'
                    });
                }
            });

            function kanbanApp() {
                return {
                    // --- Modal state ---
                    openModal: false,
                    openTaskModal: false,
                    openAddMemberModal: false,
                    openLabelModal: false,
                    openAddLabelModal: false,
                    openCeklisModal: false,
                    openTaskDetail: false, // untuk modal detail tugas
                    openMoveModal: false,
                    openListMenu: null,
                    newCeklisName: '',
                    newListName: '',

                    // --- Task Form Data ---
                    taskForm: {
                        title: '',
                        members: [],
                        secret: false,
                        notes: '',
                        attachments: [],
                        labels: [],
                        checklist: [],
                        startDate: '',
                        startTime: '',
                        dueDate: '',
                        dueTime: ''
                    },

                    // --- Current Task (for detail/edit) ---
                    currentTask: null,
                    isEditMode: false,


                    // --- NEW: State untuk halaman balas komentar ---
                    showReplyView: false,
                    currentComment: null,
                    replyContent: '',


                    // === SEARCH & FILTER PROPERTIES ===
                    searchQuery: '',
                    selectedLabel: '',
                    selectedMember: '',
                    selectedDeadline: '',

                    // --- Members ---
                    searchMember: '',
                    selectAll: false,
                    members: [{
                            name: 'Naufal',
                            avatar: 'https://i.pravatar.cc/40?img=1',
                            selected: false
                        },
                        {
                            name: 'Dzakwan',
                            avatar: 'https://i.pravatar.cc/40?img=2',
                            selected: false
                        },
                        {
                            name: 'Risi',
                            avatar: 'https://i.pravatar.cc/40?img=3',
                            selected: false
                        },
                        {
                            name: 'Rendi',
                            avatar: 'https://i.pravatar.cc/40?img=4',
                            selected: false
                        },
                        {
                            name: 'Fajar',
                            avatar: 'https://i.pravatar.cc/40?img=5',
                            selected: false
                        },
                        {
                            name: 'Dina',
                            avatar: 'https://i.pravatar.cc/40?img=6',
                            selected: false
                        },
                    ],

                    // --- Labels ---
                    searchLabel: '',
                    labels: [{
                            name: 'Reels',
                            color: '#2563eb',
                            selected: false
                        },
                        {
                            name: 'Feeds',
                            color: '#16a34a',
                            selected: false
                        },
                        {
                            name: 'Story',
                            color: '#f59e0b',
                            selected: false
                        },
                        {
                            name: 'Finance',
                            color: '#3b82f6',
                            selected: false
                        },
                        {
                            name: 'Design',
                            color: '#a855f7',
                            selected: false
                        },
                    ],
                    newLabelName: '',
                    newLabelColor: null,
                    colorPalette: [
                        "#EF4444", "#F97316", "#F59E0B", "#EAB308", "#84CC16",
                        "#22C55E", "#10B981", "#14B8A6", "#06B6D4", "#0EA5E9",
                        "#3B82F6", "#6366F1", "#8B5CF6", "#A855F7", "#D946EF",
                        "#EC4899", "#F43F5E", "#6B7280", "#1F2937", "#000000"
                    ],

                    // --- Dummy Tasks Database ---
                    // --- Dummy Tasks Database ---
                    tasks: [{
                            id: 1,
                            title: "MENYELESAIKAN LAPORAN KEUANGAN",
                            status: "todo",
                            members: [{
                                    name: 'Naufal',
                                    avatar: 'https://i.pravatar.cc/40?img=1'
                                },
                                {
                                    name: 'Dzakwan',
                                    avatar: 'https://i.pravatar.cc/40?img=2'
                                }
                            ],
                            secret: true,
                            notes: "Laporan keuangan Q4 harus diselesaikan sebelum tanggal 30 September.",
                            attachments: [{
                                    name: "Draft_Laporan_Q4.docx",
                                    type: "docx"
                                },
                                {
                                    name: "Data_Transaksi_Q4.xlsx",
                                    type: "xlsx"
                                }
                            ],
                            labels: [{
                                name: 'Finance',
                                color: '#3b82f6'
                            }],
                            checklist: [{
                                    name: "Kumpulkan data transaksi",
                                    done: true
                                },
                                {
                                    name: "Verifikasi data dengan tim Finance",
                                    done: false
                                }
                            ],
                            startDate: "2025-09-27",
                            startTime: "08:00",
                            dueDate: "2025-09-30",
                            dueTime: "17:00",
                            comments: [{
                                    author: "Risi Gustiar",
                                    date: "Sabtu, 27 Sep 2025",
                                    text: "Data transaksi sudah saya update di file Excel."
                                },
                                {
                                    author: "Rendi Sinaga",
                                    date: "Minggu, 28 Sep 2025",
                                    text: "Draft laporan hampir selesai, tinggal verifikasi."
                                }
                            ]
                        },
                        {
                            id: 2,
                            title: "Tugas Content IG",
                            status: "todo",
                            members: [{
                                    name: 'Risi',
                                    avatar: 'https://i.pravatar.cc/40?img=3'
                                },
                                {
                                    name: 'Rendi',
                                    avatar: 'https://i.pravatar.cc/40?img=4'
                                }
                            ],
                            secret: false,
                            notes: "Buat content untuk Instagram feed dan story.",
                            attachments: [{
                                name: "Content_Plan.pdf",
                                type: "pdf"
                            }],
                            labels: [{
                                    name: 'Content',
                                    color: '#16a34a'
                                },
                                {
                                    name: 'Social Media',
                                    color: '#ec4899'
                                }
                            ],
                            checklist: [{
                                    name: "Riset tren terkini",
                                    done: true
                                },
                                {
                                    name: "Buat draft content",
                                    done: true
                                },
                                {
                                    name: "Review oleh tim",
                                    done: false
                                }
                            ],
                            startDate: "2025-10-20",
                            startTime: "09:00",
                            dueDate: "2025-10-24",
                            dueTime: "18:00",
                            comments: []
                        },
                        {
                            id: 3,
                            title: "Buat Banner Promo",
                            status: "inprogress",
                            members: [{
                                    name: 'Fajar',
                                    avatar: 'https://i.pravatar.cc/40?img=5'
                                },
                                {
                                    name: 'Dina',
                                    avatar: 'https://i.pravatar.cc/40?img=6'
                                }
                            ],
                            secret: false,
                            notes: "Design banner untuk promo bulan November.",
                            attachments: [],
                            labels: [{
                                name: 'Design',
                                color: '#a855f7'
                            }],
                            checklist: [{
                                    name: "Concept design",
                                    done: true
                                },
                                {
                                    name: "Revisi 1",
                                    done: true
                                },
                                {
                                    name: "Finalisasi",
                                    done: false
                                }
                            ],
                            startDate: "2025-10-15",
                            startTime: "10:00",
                            dueDate: "2025-10-25",
                            dueTime: "15:00",
                            comments: [{
                                author: "Fajar",
                                date: "Kamis, 23 Okt 2025",
                                text: "Design sudah 80% selesai."
                            }]
                        },
                        {
                            id: 4,
                            title: "Testing Fitur Login",
                            status: "done",
                            members: [{
                                    name: 'Naufal',
                                    avatar: 'https://i.pravatar.cc/40?img=1'
                                },
                                {
                                    name: 'Risi',
                                    avatar: 'https://i.pravatar.cc/40?img=3'
                                }
                            ],
                            secret: false,
                            notes: "Testing comprehensive untuk fitur login baru.",
                            attachments: [{
                                    name: "Test_Cases.xlsx",
                                    type: "xlsx"
                                },
                                {
                                    name: "Bug_Report.pdf",
                                    type: "pdf"
                                }
                            ],
                            labels: [{
                                name: 'Testing',
                                color: '#0ea5e9'
                            }],
                            checklist: [{
                                    name: "Unit testing",
                                    done: true
                                },
                                {
                                    name: "Integration testing",
                                    done: true
                                },
                                {
                                    name: "User acceptance testing",
                                    done: true
                                }
                            ],
                            startDate: "2025-10-10",
                            startTime: "08:00",
                            dueDate: "2025-10-22",
                            dueTime: "17:00",
                            comments: [{
                                author: "Naufal",
                                date: "Rabu, 22 Okt 2025",
                                text: "Semua test case passed."
                            }]
                        },
                        {
                            id: 5,
                            title: "Riset Vendor Baru",
                            status: "cancel",
                            members: [{
                                name: 'Dzakwan',
                                avatar: 'https://i.pravatar.cc/40?img=2'
                            }],
                            secret: true,
                            notes: "Riset vendor untuk kebutuhan project Q1 2026.",
                            attachments: [{
                                name: "Vendor_List.xlsx",
                                type: "xlsx"
                            }],
                            labels: [{
                                name: 'Research',
                                color: '#f59e0b'
                            }],
                            checklist: [{
                                    name: "Identifikasi kebutuhan",
                                    done: true
                                },
                                {
                                    name: "Cari vendor potensial",
                                    done: false
                                },
                                {
                                    name: "Evaluasi proposal",
                                    done: false
                                }
                            ],
                            startDate: "2025-10-05",
                            startTime: "09:00",
                            dueDate: "2025-10-20",
                            dueTime: "16:00",
                            comments: [{
                                author: "Manager",
                                date: "Senin, 20 Okt 2025",
                                text: "Project ditunda sampai further notice."
                            }]
                        }
                    ],

                    // === METHODS ===

                    // Open task detail modal
                    openDetail(taskId) {
                        const task = this.tasks.find(t => t.id === taskId);
                        if (!task) return;

                        this.currentTask = JSON.parse(JSON.stringify(task));
                        this.isEditMode = false;
                        this.openTaskDetail = true;
                    },

                    // Enable edit mode
                    enableEditMode() {
                        this.isEditMode = true;
                    },

                    // Save edited task
                    saveTaskEdit() {
                        if (!this.currentTask) return;
                        const index = this.tasks.findIndex(t => t.id === this.currentTask.id);
                        if (index !== -1) {
                            this.tasks[index] = JSON.parse(JSON.stringify(this.currentTask));
                        }
                        this.isEditMode = false;
                        this.openTaskDetail = false;
                        alert("Tugas berhasil diperbarui!");
                    },

                    // Cancel edit
                    cancelEdit() {
                        if (!this.currentTask) return;
                        const task = this.tasks.find(t => t.id === this.currentTask.id);
                        this.currentTask = JSON.parse(JSON.stringify(task));
                        this.isEditMode = false;
                    },

                    // Create new task
                    createTask() {
                        const newTask = {
                            id: Date.now(),
                            ...this.taskForm,
                            comments: []
                        };
                        this.tasks.push(newTask);
                        this.resetTaskForm();
                        this.openTaskModal = false;
                        alert("Tugas berhasil dibuat!");
                    },

                    // Reset form
                    resetTaskForm() {
                        this.taskForm = {
                            title: '',
                            members: [],
                            secret: false,
                            notes: '',
                            attachments: [],
                            labels: [],
                            checklist: [],
                            startDate: '',
                            startTime: '',
                            dueDate: '',
                            dueTime: ''
                        };
                    },

                    // Members
                    filteredMembers() {
                        if (!this.searchMember) return this.members;
                        return this.members.filter(m => m.name.toLowerCase().includes(this.searchMember.toLowerCase()));
                    },

                    toggleSelectAll() {
                        this.members.forEach(m => m.selected = this.selectAll);
                    },

                    saveSelectedMembers() {
                        const selected = this.members.filter(m => m.selected).map(m => ({
                            name: m.name,
                            avatar: m.avatar
                        }));
                        if (this.isEditMode && this.currentTask) {
                            selected.forEach(member => {
                                if (!this.currentTask.members.find(m => m.name === member.name)) {
                                    this.currentTask.members.push(member);
                                }
                            });
                        } else {
                            this.taskForm.members = selected;
                        }
                        this.members.forEach(m => m.selected = false);
                        this.selectAll = false;
                        this.openAddMemberModal = false;
                    },

                    removeMember(index) {
                        if (this.isEditMode && this.currentTask) {
                            this.currentTask.members.splice(index, 1);
                        }
                    },

                    // Labels
                    filteredLabels() {
                        if (!this.searchLabel) return this.labels;
                        return this.labels.filter(l => l.name.toLowerCase().includes(this.searchLabel.toLowerCase()));
                    },

                    saveSelectedLabels() {
                        const selected = this.labels.filter(l => l.selected).map(l => ({
                            name: l.name,
                            color: l.color
                        }));
                        if (this.isEditMode && this.currentTask) {
                            this.currentTask.labels = selected;
                        } else {
                            this.taskForm.labels = selected;
                        }
                        this.labels.forEach(l => l.selected = false);
                        this.openLabelModal = false;
                    },

                    addNewLabel() {
                        if (this.newLabelName.trim() === '' || !this.newLabelColor) return;
                        this.labels.push({
                            name: this.newLabelName,
                            color: this.newLabelColor,
                            selected: false
                        });
                        this.newLabelName = '';
                        this.newLabelColor = null;
                        this.openAddLabelModal = false;
                        this.openLabelModal = true;
                    },

                    // Checklist
                    saveCeklis() {
                        if (this.newCeklisName.trim() === '') return;
                        const newItem = {
                            name: this.newCeklisName,
                            done: false
                        };
                        if (this.isEditMode && this.currentTask) {
                            this.currentTask.checklist.push(newItem);
                        } else {
                            this.taskForm.checklist.push(newItem);
                        }
                        this.newCeklisName = '';
                        this.openCeklisModal = false;
                    },

                    toggleChecklistItem(index) {
                        if (this.currentTask) {
                            this.currentTask.checklist[index].done = !this.currentTask.checklist[index].done;
                        }
                    },

                    removeChecklistItem(index) {
                        if (this.currentTask) {
                            this.currentTask.checklist.splice(index, 1);
                        }
                    },

                    // Attachments
                    removeAttachment(index) {
                        if (this.isEditMode && this.currentTask) {
                            this.currentTask.attachments.splice(index, 1);
                        }
                    },

                    // Add new list
                    // Add new list
                    addList() {
                        let newListName = this.newListName.trim();
                        if (newListName === '') return;

                        let listId = newListName.toLowerCase().replace(/\s+/g, '-') + '-' + Date.now();
                        const board = document.getElementById('kanban-board');

                        const div = document.createElement('div');
                        div.className =
                            'bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col';
                        div.innerHTML = `
        <div class="flex items-center justify-between mb-1 xs:mb-2">
            <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base">${newListName}</h2>
            <button @click="openListMenu = openListMenu === '${listId}' ? null : '${listId}'"
                    class="text-gray-500 hover:text-gray-700 text-xs p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                </svg>
            </button>
        </div>
        <div class="flex flex-col flex-1">
            <div class="space-y-2 flex-1 overflow-y-auto max-h-[50vh] xs:max-h-[55vh] sm:max-h-[60vh] pr-1" id="${listId}">
                <div class="text-center text-gray-500 text-xs xs:text-sm py-3 xs:py-4">
                    Tidak ada tugas
                </div>
            </div>
            <button @click="openTaskModal = true"
                class="w-full mt-3 py-2 text-sm font-medium text-gray-600 bg-white rounded-lg border border-gray-300 hover:bg-blue-500 hover:text-white hover:border-blue-500 transition-all duration-200 shadow-sm">
                + Buat Tugas
            </button>
        </div>
    `;

                        board.insertBefore(div, board.lastElementChild);

                        // Initialize Sortable for the new list
                        new Sortable(document.getElementById(listId), {
                            group: 'kanban',
                            animation: 150,
                            ghostClass: 'bg-blue-300'
                        });

                        this.newListName = '';
                        this.openModal = false;
                    },


                    // === NEW METHODS FOR REPLY INTERACTION ===


                    replyView: {
                        active: false,
                        parentComment: null,
                        replyContent: '',
                        currentTask: null,
                        context: 'task' // Konteks tugas
                    },


                    // Buka halaman balas komentar dari modal detail
                    // Fungsi untuk membuka halaman balas komentar (TUGAS)
                    openReplyFromModal(comment) {
                        this.replyView.active = true;
                        this.replyView.parentComment = comment;
                        this.replyView.replyContent = '';
                        this.replyView.currentTask = this.currentTask;
                        this.replyView.context = 'task';

                        // Tutup modal detail
                        this.openTaskDetail = false;
                    },

                    // Fungsi untuk kembali (TUGAS)
                    closeReplyView() {
                        this.replyView.active = false;
                        this.replyView.parentComment = null;
                        this.replyView.replyContent = '';
                        this.replyView.currentTask = null;

                        // Buka kembali modal detail
                        this.openTaskDetail = true;
                    },

                    // Submit balasan komentar
                    // Fungsi untuk submit balasan komentar (TUGAS)
                    submitReply() {
                        const content = this.replyView.replyContent ? this.replyView.replyContent.trim() : '';
                        if (!content || !this.replyView.parentComment) return;

                        const newReply = {
                            id: Date.now(),
                            author: {
                                name: 'Anda',
                                avatar: 'https://i.pravatar.cc/40?img=11'
                            },
                            content: content,
                            createdAt: new Date().toISOString()
                        };

                        if (!this.replyView.parentComment.replies) {
                            this.replyView.parentComment.replies = [];
                        }

                        this.replyView.parentComment.replies.push(newReply);
                        this.closeReplyView();

                        alert('Balasan berhasil dikirim!');
                    },

                    // Format tanggal untuk komentar
                    formatCommentDate(dateString) {
                        if (!dateString) return '';

                        const date = new Date(dateString);
                        const now = new Date();
                        const diffTime = Math.abs(now - date);
                        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
                        const diffMinutes = Math.floor(diffTime / (1000 * 60));

                        if (diffMinutes < 1) return 'beberapa detik yang lalu';
                        if (diffMinutes < 60) return `${diffMinutes} menit yang lalu`;
                        if (diffHours < 24) return `${diffHours} jam yang lalu`;
                        if (diffDays < 7) return `${diffDays} hari yang lalu`;

                        return date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                    },



                    // === SEARCH & FILTER METHODS ===
                    filterTasks(tasks) {
                        if (!Array.isArray(tasks)) return [];

                        return tasks.filter(task => {
                            // Search by task title
                            if (this.searchQuery && !task.title.toLowerCase().includes(this.searchQuery
                                    .toLowerCase())) {
                                return false;
                            }

                            // Filter by label
                            if (this.selectedLabel && (!task.labels || !task.labels.some(label =>
                                    label.name.toLowerCase().includes(this.selectedLabel.toLowerCase())))) {
                                return false;
                            }

                            // Filter by member
                            if (this.selectedMember && (!task.members || !task.members.some(member =>
                                    member.name.toLowerCase().includes(this.selectedMember.toLowerCase())))) {
                                return false;
                            }

                            // Filter by deadline
                            if (this.selectedDeadline && task.dueDate) {
                                const today = new Date();
                                today.setHours(0, 0, 0, 0);

                                const dueDate = new Date(task.dueDate);
                                dueDate.setHours(0, 0, 0, 0);

                                switch (this.selectedDeadline) {
                                    case 'segera':
                                        const threeDaysFromNow = new Date(today);
                                        threeDaysFromNow.setDate(threeDaysFromNow.getDate() + 3);
                                        if (dueDate < today || dueDate > threeDaysFromNow) {
                                            return false;
                                        }
                                        break;

                                    case 'hari-ini':
                                        if (dueDate.getTime() !== today.getTime()) {
                                            return false;
                                        }
                                        break;

                                    case 'terlambat':
                                        if (dueDate >= today) {
                                            return false;
                                        }
                                        break;
                                }
                            }

                            return true;
                        });
                    },

                    get availableLabels() {
                        const labels = new Set();
                        this.tasks.forEach(task => {
                            if (task.labels && task.labels.length > 0) {
                                task.labels.forEach(label => labels.add(label.name));
                            }
                        });
                        return Array.from(labels).map(name => ({
                            name
                        }));
                    },

                    get availableMembers() {
                        const members = new Set();
                        this.tasks.forEach(task => {
                            if (task.members && task.members.length > 0) {
                                task.members.forEach(member => members.add(member.name));
                            }
                        });
                        return Array.from(members).map(name => ({
                            name
                        }));
                    },

                    getFilteredTasks(columnName) {
                        const columnTasks = this.tasks.filter(task => task.status === columnName);
                        return this.filterTasks(columnTasks);
                    },

                    resetFilters() {
                        this.searchQuery = '';
                        this.selectedLabel = '';
                        this.selectedMember = '';
                        this.selectedDeadline = '';
                    },

                    hasActiveFilters() {
                        return this.searchQuery || this.selectedLabel || this.selectedMember || this.selectedDeadline;
                    },

                    getDeadlineFilterText() {
                        switch (this.selectedDeadline) {
                            case 'segera':
                                return 'Tenggat Segera';
                            case 'hari-ini':
                                return 'Tenggat Hari Ini';
                            case 'terlambat':
                                return 'Terlambat';
                            default:
                                return '';
                        }
                    },

                    formatDate(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short'
                        });
                    },

                    calculateProgress(task) {
                        if (!task.checklist || task.checklist.length === 0) return 0;
                        const completed = task.checklist.filter(item => item.done).length;
                        return Math.round((completed / task.checklist.length) * 100);
                    },



                    // untuk modal aksi list
                    // untuk modal aksi list
                    sortTasks(sortType) {
                        const currentList = this.openListMenu; // 'todo', 'inprogress', dll. atau ID list baru

                        if (!currentList) return;

                        // Untuk list default (todo, inprogress, done, cancel)
                        if (['todo', 'inprogress', 'done', 'cancel'].includes(currentList)) {
                            const listTasks = this.tasks.filter(task => task.status === currentList);

                            // Implementasi sorting berdasarkan jenis
                            switch (sortType) {
                                case 'deadline-asc': // Tenggat waktu terdekat
                                    listTasks.sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate));
                                    break;

                                case 'deadline-desc': // Tenggat waktu terjauh
                                    listTasks.sort((a, b) => new Date(b.dueDate) - new Date(a.dueDate));
                                    break;

                                case 'created-asc': // Waktu dibuat terdekat
                                    listTasks.sort((a, b) => a.id - b.id);
                                    break;

                                case 'created-desc': // Waktu dibuat terjauh
                                    listTasks.sort((a, b) => b.id - a.id);
                                    break;
                            }

                            // Update tasks dengan urutan baru
                            this.tasks = this.tasks.filter(task => task.status !== currentList);
                            this.tasks = [...this.tasks, ...listTasks];
                        } else {
                            // Untuk list baru (akan diimplementasikan nanti)
                            console.log(`Sorting untuk list baru: ${currentList} dengan tipe: ${sortType}`);
                            alert('Fitur sorting untuk list baru akan segera tersedia!');
                        }

                        console.log(`Tasks di ${currentList} diurutkan dengan: ${sortType}`);
                    },

                }
            }
        </script>





    @endsection
