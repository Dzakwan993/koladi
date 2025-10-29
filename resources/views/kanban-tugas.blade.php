@extends('layouts.app')

@section('title', 'Kanban Tugas')

@section('content')
    <div class="bg-gray-50 min-h-screen flex flex-col" x-data="kanbanApp()">

        {{-- Workspace Nav --}}
        @include('components.workspace-nav', ['active' => 'tugas'])

        {{-- Search & Filter Section --}}
        @include('components.pencarian-tugas')

        {{-- View Mode Toggle --}}
        <div class="bg-white border-b px-2 py-1 flex justify-between items-center">
            <div class="flex space-x-4 ml-6">
                <button @click="viewMode = 'kanban'"
                    :class="{ 'text-blue-600 border-b-2 border-blue-600': viewMode === 'kanban' }"
                    class="px-2 py-1 text-xs font-medium">
                    Kanban View
                </button>
                <button @click="viewMode = 'timeline'"
                    :class="{ 'text-blue-600 border-b-2 border-blue-600': viewMode === 'timeline' }"
                    class="px-2 py-2 text-xs font-medium">
                    Timeline View
                </button>
            </div>
        </div>

        <div class="flex-1" x-show="!replyView.active">

            {{-- Kanban Board --}}
            @include('components.kanban')

            {{-- Timeline View --}}
            @include('components.timeline')

        </div>

        {{-- Halaman Balas Komentar --}}

            @include('components.balas-komentar')

        {{-- All Modals --}}
        @include('components.modal-tugas')

    </div>

    {{-- Include CSS and Scripts --}}
    <style>
        /* ===== BASE STYLES ===== */
        [x-cloak] {
            display: none !important;
        }

        /* ===== TRANSITIONS & ANIMATIONS ===== */
        .page-transition {
            transition: all 0.3s ease-in-out;
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

        /* ===== EDITOR STYLES ===== */
        /* CKEditor List Styles */
        #editor-catatan+.ck-editor .ck-content ul,
        #editor-catatan+.ck-editor .ck-content ol {
            padding-left: 1.5rem !important;
            margin-left: 0 !important;
            list-style-position: outside !important;
        }

        #editor-catatan+.ck-editor .ck-content li {
            margin-left: 0 !important;
        }

        /* CKEditor Dimensions */
        .ck-editor__editable {
            min-height: 120px !important;
            max-height: 200px;
            overflow-y: auto;
        }

        .ck-editor__editable ul,
        .ck-editor__editable ol {
            margin-left: 1.5rem !important;
            padding-left: 1rem !important;
        }

        /* CKEditor Toolbar */
        .ck.ck-toolbar {
            font-size: 14px !important;
            padding: 6px 8px !important;
        }

        .ck.ck-toolbar .ck-button {
            margin: 0 2px !important;
        }

        /* ===== RESPONSIVE BREAKPOINTS ===== */
        /* Tablet & Desktop - Editor Height */
        @media (min-width: 768px) {
            .ck-editor__editable {
                min-height: 150px !important;
                max-height: 300px;
            }
        }

        /* ===== RESPONSIVE TYPOGRAPHY ===== */
        .responsive-text {
            font-size: 0.875rem;
        }


        /* Mobile Landscape & Small Tablets (640px - 767px) */
        @media (min-width: 640px) and (max-width: 767px) {
            .responsive-text {
                font-size: 0.9rem !important;
            }
        }

        /* Tablets (768px - 991px) */
        @media (min-width: 768px) and (max-width: 991px) {
            .responsive-text {
                font-size: 1rem !important;
            }

            .container-tablet {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }

        /* Desktop (992px - 1199px) */
        @media (min-width: 992px) and (max-width: 1199px) {
            .responsive-text {
                font-size: 1.1rem !important;
            }
        }

        /* Large Desktop (1200px+) */
        @media (min-width: 1200px) {
            .responsive-text {
                font-size: 1.125rem !important;
            }

            .container-xl {
                max-width: 1400px;
                margin: 0 auto;
            }
        }

        /* ===== UTILITY CLASSES ===== */
        /* Line Clamp */
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

        /* Smooth Scrolling */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        /* Hover Effects */
        @media (hover: hover) {
            .hover\:shadow-md:hover {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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

        /* Responsive Gap */
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

        /* Button Sizes */
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

        /* Text Truncation */
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

        /* Icon Sizing */
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

        /* ===== COMPONENT STYLES ===== */
        /* Modal Actions */
        .modal-actions-list {
            backdrop-filter: blur(4px);
        }

        /* Kanban Columns */
        .kanban-column-mobile {
            width: 85vw;
            min-width: 85vw;
        }

        .kanban-column-xs {
            width: 280px;
            min-width: 280px;
        }

        .kanban-column-sm {
            width: 300px;
            min-width: 300px;
        }

        .kanban-column-md {
            min-width: 320px;
        }

        .kanban-column-lg {
            min-width: 340px;
        }

        .kanban-column-xl {
            min-width: 360px;
        }

        /* Kanban Medium Variant */
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

        /* Kanban Spacing */
        .kanban-padding-medium {
            padding: 0.75rem !important;
        }

        @media (min-width: 476px) {
            .kanban-padding-medium {
                padding: 1rem !important;
            }
        }

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

        /* ===== GANTT CHART STYLES ===== */
        /* Base Container */
        .gantt-container {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .gantt-chart {
            min-width: 800px;
            position: relative;
        }

        /* Header */
        .gantt-header {
            display: flex;
            border-bottom: 2px solid #e5e7eb;
            background: #f8fafc;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .gantt-phase-column {
            width: 200px;
            min-width: 200px;
            padding: 1rem;
            border-right: 1px solid #e5e7eb;
            font-weight: 600;
            background: white;
        }

        .gantt-timeline-columns {
            display: flex;
            flex: 1;
        }

        .gantt-timeline-header {
            padding: 1rem 0.5rem;
            text-align: center;
            border-right: 1px solid #e5e7eb;
            font-weight: 500;
            font-size: 0.875rem;
            background: white;
            min-width: 80px;
        }

        /* Body */
        .gantt-body {
            display: flex;
        }

        .gantt-phases {
            width: 200px;
            min-width: 200px;
        }

        .gantt-phase-row {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
            background: white;
            font-weight: 500;
        }

        .gantt-timeline {
            background: repeating-linear-gradient(90deg,
                    transparent,
                    transparent 79px,
                    #f1f5f9 79px,
                    #f1f5f9 80px) !important;
        }



        .gantt-task-bar:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .gantt-task-progress {
            position: absolute;
            height: 100%;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.3);
        }

        /* Grid & Markers */
        .gantt-grid-line {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1px;
            background: #e5e7eb;
        }

        .gantt-current-date {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ef4444;
            z-index: 5;
        }

        .gantt-current-date::before {
            content: '';
            position: absolute;
            top: 0;
            left: -4px;
            width: 10px;
            height: 10px;
            background: #ef4444;
            border-radius: 50%;
        }

        /* Status Colors */
        .gantt-status-todo {
            background: #3b82f6;
        }

        .gantt-status-inprogress {
            background: #f59e0b;
        }

        .gantt-status-done {
            background: #10b981;
        }

        .gantt-status-cancel {
            background: #6b7280;
        }

        /* ===== HORIZONTAL GANTT STYLES ===== */
        .gantt-horizontal {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .gantt-task-column {
            width: 300px;
            min-width: 300px;
            padding: 1rem;
            background: #1e40af;
            border-right: 2px solid #1e3a8a;
            color: white;
        }

        .gantt-timeline-header {
            flex: 1;
            display: flex;
        }

        .gantt-month-section {
            flex: 1;
            text-align: center;
            padding: 0.75rem 0;
            border-right: 1px solid #1e3a8a;
            font-size: 0.9rem;
        }

        /* Days Container */
        .gantt-days-container {
            display: flex;
            background: #374151;
            color: white;
            font-weight: 500;
            position: sticky;
            top: 60px;
            z-index: 15;
        }

        .gantt-days-label {
            width: 300px;
            min-width: 300px;
            padding: 0.5rem 1rem;
            background: #374151;
            border-right: 2px solid #1e40af;
            font-size: 0.8rem;
        }

        .gantt-days {
            flex: 1;
            display: flex;
        }

        .gantt-day {
            flex: 1;
            text-align: center;
            padding: 0.5rem 0;
            border-right: 1px solid #4b5563;
            font-size: 0.75rem;
            min-width: 30px;
        }

        .gantt-day.weekend {
            background: #4b5563;
        }

        /* Task Rows */
        .gantt-tasks {
            width: 300px;
            min-width: 300px;
        }

        .gantt-task-row {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            background: white;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .gantt-task-row:hover {
            background: #f3f4f6;
        }

        .gantt-task-row.active {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
        }

        /* ===== TIMELINE PHASE BAR FIX ===== */

        /* Phase bar utama - SELALU berwarna */
        .phase-bar {
            position: absolute;
            height: 40px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* HILANGKAN background-color default */
        }

        /* Progress Bar - GANTI INI */
        .absolute.top-1\\/2.-translate-y-1\\/2.h-10.rounded-l-xl {
            border-radius: 6px !important;
            /* Buat rounded penuh */
            width: 100% !important;
            /* Force full width */
        }

        /* Hapus background gray yang menutupi */
        .bg-gradient-to-r.from-gray-100.to-gray-200 {
            display: none !important;
        }

        /* Outline Bar - pastikan tidak menutupi warna */
        .absolute.top-1\\/2.-translate-y-1\\/2.h-10.rounded-xl.border-2 {
            background: transparent !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
        }


        .phase-bar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .gantt-bar {
            position: absolute;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            padding: 0 12px;
            font-size: 0.75rem;
            color: white;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid rgba(255, 255, 255, 0.3);
        }

        .gantt-bar:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .gantt-bar-progress {
            position: absolute;
            height: 100%;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.2);
            transition: width 0.3s ease;
        }

        .gantt-milestone {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #dc2626;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            z-index: 10;
        }

        /* Timeline Container */
        .gantt-timeline-container {
            position: relative;
            overflow: hidden;
        }

        .gantt-progress-bar {
            display: none;
            /* HILANGKAN background progress */
        }

        /* Gantt duration bar - HILANGKAN yang tidak perlu */
        .gantt-duration-bar {
            display: none;
            /* HILANGKAN border dashed */
        }

        /* ===== PHASE COLOR SCHEMES ===== */
        .phase-planning {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
        }

        .phase-analysis {
            background: linear-gradient(135deg, #10b981, #047857) !important;
        }

        .phase-design {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
        }

        .phase-development {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important;
        }

        .phase-testing {
            background: linear-gradient(135deg, #ec4899, #db2777) !important;
        }

        .phase-deployment {
            background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        }



        .timeline-phase-bar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* HAPUS background percentage yang tidak perlu */
        .timeline-phase-progress {
            display: none;
            /* HILANGKAN progress bar background */
        }


        /* Timeline View Styles */
        .timeline-phase {
            transition: all 0.3s ease;
        }

        .timeline-phase:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .timeline-progress {
            transition: width 0.5s ease-in-out;
        }

        .timeline-task {
            transition: all 0.2s ease;
        }

        .timeline-task:hover {
            background-color: #f8fafc;
            border-color: #3b82f6;
        }

        /* Timeline Phase Borders & Backgrounds */
        .timeline-phase.phase-1 {
            border-left-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
        }

        .timeline-phase.phase-2 {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        }

        .timeline-phase.phase-3 {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
        }

        .timeline-phase.phase-4 {
            border-left-color: #8b5cf6;
            background: linear-gradient(135deg, #faf5ff, #e9d5ff);
        }

        .timeline-phase.phase-5 {
            border-left-color: #ec4899;
            background: linear-gradient(135deg, #fdf2f8, #fbcfe8);
        }

        .timeline-phase.phase-6 {
            border-left-color: #6366f1;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        }

        /* ===== RESPONSIVE ADJUSTMENTS ===== */
        /* Search & Filter Section */
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

        /* Modal Responsive */
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

        /* Gantt Chart Responsive */
        @media (max-width: 768px) {

            .gantt-phase-column,
            .gantt-phases {
                width: 150px;
                min-width: 150px;
            }

            .gantt-timeline-header {
                min-width: 60px;
                font-size: 0.75rem;
                padding: 0.5rem 0.25rem;
            }

            .timeline-phase {
                margin-bottom: 1rem;
            }
        }

        /* Horizontal Gantt Responsive */
        @media (max-width: 1024px) {

            .gantt-task-column,
            .gantt-days-label,
            .gantt-tasks {
                width: 250px;
                min-width: 250px;
            }
        }

        @media (max-width: 768px) {
            .gantt-horizontal {
                font-size: 0.8rem;
            }

            .gantt-task-column,
            .gantt-days-label,
            .gantt-tasks {
                width: 200px;
                min-width: 200px;
            }

            .gantt-day {
                min-width: 25px;
                font-size: 0.7rem;
                padding: 0.3rem 0;
            }

            .gantt-bar {
                height: 25px;
                font-size: 0.7rem;
                padding: 0 8px;
            }
        }


        /* Shimmer Animation */
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>

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
>>>>>>> backend-kelola-workspace
=======
>>>>>>> origin/main
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
                        phase: '', // Sekarang string kosong, bukan null/undefined

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

                    // === VIEW MODE & TIMELINE PROPERTIES ===
                    viewMode: 'kanban',
                    selectedPhase: null,

                    // === GANTT CHART PROPERTIES ===
                    phaseModal: {
                        open: false,
                        title: '',
                        description: '',
                        tasks: []
                    },

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
                    // --- Dummy Tasks Database dengan Phase ---
                    tasks: [{
                            id: 1,
                            title: "MENYELESAIKAN LAPORAN KEUANGAN",
                            phase: "Perencanaan",
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
                            startDate: "2024-01-15",
                            startTime: "08:00",
                            dueDate: "2024-01-30",
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
                            title: "Analisis Kebutuhan User",
                            phase: "Analisis",
                            status: "done",
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
                            notes: "Analisis kebutuhan user untuk fitur baru.",
                            attachments: [{
                                name: "User_Requirements.pdf",
                                type: "pdf"
                            }],
                            labels: [{
                                    name: 'Analisis',
                                    color: '#16a34a'
                                },
                                {
                                    name: 'Research',
                                    color: '#ec4899'
                                }
                            ],
                            checklist: [{
                                    name: "Interview user",
                                    done: true
                                },
                                {
                                    name: "Analisis data",
                                    done: true
                                },
                                {
                                    name: "Buat laporan kebutuhan",
                                    done: false
                                }
                            ],
                            startDate: "2024-02-01",
                            startTime: "09:00",
                            dueDate: "2024-02-15",
                            dueTime: "18:00",
                            comments: []
                        },
                        {
                            id: 3,
                            title: "Desain UI Dashboard",
                            phase: "Desain",
                            status: "done",
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
                            notes: "Design dashboard untuk monitoring project.",
                            attachments: [],
                            labels: [{
                                name: 'Design',
                                color: '#a855f7'
                            }],
                            checklist: [{
                                    name: "Wireframe",
                                    done: true
                                },
                                {
                                    name: "Mockup",
                                    done: true
                                },
                                {
                                    name: "Prototype",
                                    done: false
                                }
                            ],
                            startDate: "2024-03-01",
                            startTime: "10:00",
                            dueDate: "2024-03-20",
                            dueTime: "15:00",
                            comments: [{
                                author: "Fajar",
                                date: "Kamis, 23 Okt 2025",
                                text: "Design sudah 80% selesai."
                            }]
                        },
                        {
                            id: 4,
                            title: "Development Fitur Login",
                            phase: "Development",
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
                            notes: "Development fitur login dengan authentication.",
                            attachments: [{
                                    name: "Test_Cases.xlsx",
                                    type: "xlsx"
                                },
                                {
                                    name: "API_Documentation.pdf",
                                    type: "pdf"
                                }
                            ],
                            labels: [{
                                name: 'Development',
                                color: '#0ea5e9'
                            }],
                            checklist: [{
                                    name: "Backend API",
                                    done: true
                                },
                                {
                                    name: "Frontend integration",
                                    done: true
                                },
                                {
                                    name: "Testing",
                                    done: true
                                }
                            ],
                            startDate: "2024-04-01",
                            startTime: "08:00",
                            dueDate: "2024-04-25",
                            dueTime: "17:00",
                            comments: [{
                                author: "Naufal",
                                date: "Rabu, 22 Okt 2025",
                                text: "Semua test case passed."
                            }]
                        },
                        {
                            id: 5,
                            title: "Testing Sistem Integrasi",
                            phase: "Testing",
                            status: "inprogress",
                            members: [{
                                name: 'Dzakwan',
                                avatar: 'https://i.pravatar.cc/40?img=2'
                            }],
                            secret: true,
                            notes: "Testing integrasi antara modul sistem.",
                            attachments: [{
                                name: "Integration_Test_Plan.xlsx",
                                type: "xlsx"
                            }],
                            labels: [{
                                name: 'Testing',
                                color: '#f59e0b'
                            }],
                            checklist: [{
                                    name: "Unit testing",
                                    done: true
                                },
                                {
                                    name: "Integration testing",
                                    done: false
                                },
                                {
                                    name: "User acceptance testing",
                                    done: false
                                }
                            ],
                            startDate: "2024-05-01",
                            startTime: "09:00",
                            dueDate: "2024-05-30",
                            dueTime: "16:00",
                            comments: [{
                                author: "Manager",
                                date: "Senin, 20 Okt 2025",
                                text: "Progress testing 60%."
                            }]
                        },
                        {
                            id: 6,
                            title: "Deployment Production",
                            phase: "Deployment",
                            status: "todo",
                            members: [{
                                    name: 'Naufal',
                                    avatar: 'https://i.pravatar.cc/40?img=1'
                                },
                                {
                                    name: 'Fajar',
                                    avatar: 'https://i.pravatar.cc/40?img=5'
                                }
                            ],
                            secret: false,
                            notes: "Deploy aplikasi ke server production.",
                            attachments: [],
                            labels: [{
                                name: 'Deployment',
                                color: '#10b981'
                            }],
                            checklist: [{
                                    name: "Setup server",
                                    done: false
                                },
                                {
                                    name: "Database migration",
                                    done: false
                                },
                                {
                                    name: "Deploy aplikasi",
                                    done: false
                                }
                            ],
                            startDate: "2024-06-01",
                            startTime: "08:00",
                            dueDate: "2024-06-10",
                            dueTime: "17:00",
                            comments: []
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
                            phase: '',
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
                        this.taskForm.members = selected;
                        this.members.forEach(m => m.selected = false);
                        this.selectAll = false;
                        this.openAddMemberModal = false;
                    },

                    removeMember(index) {
                        if (this.isEditMode && this.currentTask) {
                            this.currentTask.members.splice(index, 1);
                        }
                    },

                    // Labels methods untuk form
                    filteredLabels() {
                        if (!this.searchLabel) return this.labels;
                        return this.labels.filter(l => l.name.toLowerCase().includes(this.searchLabel.toLowerCase()));
                    },

                    saveSelectedLabels() {
                        const selected = this.labels.filter(l => l.selected).map(l => ({
                            name: l.name,
                            color: l.color
                        }));
                        this.taskForm.labels = selected;
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

                    // Checklist methods untuk form
                    saveCeklis() {
                        if (this.newCeklisName.trim() === '') return;
                        const newItem = {
                            name: this.newCeklisName,
                            done: false
                        };
                        this.taskForm.checklist.push(newItem);
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


                    // Methods untuk Gantt Chart
                    getProjectPhases() {
                        return [{
                                id: 1,
                                name: 'Perencanaan',
                            },
                            {
                                id: 2,
                                name: 'Analisis',
                            },
                            {
                                id: 3,
                                name: 'Desain',
                            },
                            {
                                id: 4,
                                name: 'Development',
                            },
                            {
                                id: 5,
                                name: 'Testing',
                            },
                            {
                                id: 6,
                                name: 'Deployment',
                            }
                        ];
                    },

                    // Update method showPhaseTasks
                    showPhaseTasks(phaseId) {
                        const phase = this.getProjectPhases().find(p => p.id === phaseId);
                        if (!phase) return;

                        const phaseTasks = this.getTasksByPhaseId(phaseId);
                        const totalTasks = phaseTasks.length;
                        const completedTasks = phaseTasks.filter(task => task.status === 'done').length;
                        const progress = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;

                        this.selectedPhase = phaseId;
                        this.phaseModal = {
                            open: true,
                            title: phase.name,
                            description: phase.description,
                            tasks: phaseTasks,
                            progress: progress,
                            totalTasks: totalTasks,
                            completedTasks: completedTasks
                        };
                    },
                    getTasksByPhaseId(phaseId) {
                        // Mapping phase ID ke phase name yang sesuai dengan data tasks
                        const phaseMap = {
                            1: 'Perencanaan',
                            2: 'Analisis',
                            3: 'Desain',
                            4: 'Development',
                            5: 'Testing',
                            6: 'Deployment'
                        };

                        const phaseName = phaseMap[phaseId];
                        return this.tasks.filter(task => task.phase === phaseName);
                    },

                    showTaskDetails(task) {
                        this.openDetail(task.id);
                    },

                    // Methods untuk menghitung progress
                    calculateProgress(task) {
                        if (!task.checklist || task.checklist.length === 0) return 0;
                        const completed = task.checklist.filter(item => item.done).length;
                        return Math.round((completed / task.checklist.length) * 100);
                    },

                    // Method untuk menghitung persentase phase berdasarkan tugas selesai
                    calculatePhaseProgress(phaseId) {
                        const phaseTasks = this.getTasksByPhaseId(phaseId);
                        if (phaseTasks.length === 0) return 0;

                        const completedTasks = phaseTasks.filter(task => task.status === 'done').length;
                        return Math.round((completedTasks / phaseTasks.length) * 100);
                    },

                    // Method untuk mendapatkan statistik phase
                    getPhaseStats(phaseId) {
                        const phaseTasks = this.getTasksByPhaseId(phaseId);
                        const totalTasks = phaseTasks.length;
                        const completedTasks = phaseTasks.filter(task => task.status === 'done').length;
                        const inProgressTasks = phaseTasks.filter(task => task.status === 'inprogress').length;
                        const todoTasks = phaseTasks.filter(task => task.status === 'todo').length;

                        return {
                            total: totalTasks,
                            completed: completedTasks,
                            inProgress: inProgressTasks,
                            todo: todoTasks,
                            progress: totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0
                        };
                    },

                    formatDate(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                    },

                    // Open task detail modal
                    openDetail(taskId) {
                        const task = this.tasks.find(t => t.id === taskId);
                        if (!task) return;

                        this.currentTask = JSON.parse(JSON.stringify(task));
                        this.isEditMode = false;
                        this.openTaskDetail = true;
                    },


                    // Add this to your kanbanApp() methods
                    // Update method getTasksByPhase
                    getTasksByPhase(phaseId) {
                        const phaseMap = {
                            1: 'Perencanaan',
                            2: 'Analisis',
                            3: 'Desain',
                            4: 'Development',
                            5: 'Testing',
                            6: 'Deployment'
                        };

                        const phaseName = phaseMap[phaseId];
                        const phaseTasks = this.tasks.filter(task => task.phase === phaseName);

                        // Jika tidak ada tugas, return array kosong agar timeline tidak muncul
                        if (phaseTasks.length === 0) {
                            return [];
                        }

                        return phaseTasks;
                    },

                }
            }
        </script>
@endsection
