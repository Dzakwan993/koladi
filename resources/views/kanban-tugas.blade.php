@extends('layouts.app')

@section('title', 'Kanban Tugas')

@section('content')
    <div class="bg-gray-50 min-h-screen flex flex-col" x-data="kanbanApp()">


        {{-- Debug Info
<div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-4" x-data="{ showDebug: true }">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-sm font-bold">Workspace Isolation Debug</h3>
            <p class="text-xs">Current Workspace: {{ $workspace->name }} (ID: {{ $workspace->id }})</p>
            <p class="text-xs">Company: {{ $workspace->company->name }} (ID: {{ $workspace->company_id }})</p>
            <p class="text-xs">Active Company Session: {{ session('active_company_id') }}</p>
            <p class="text-xs" x-text="'Board Columns Count: ' + boardColumns.length"></p>
        </div>
    </div>
</div>

        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-4" x-data="{ showDebug: true }">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold">Debug Info</h3>
                    <p class="text-xs">Workspace: {{ $workspace->name ?? 'Tidak ada' }} (ID:
                        {{ $workspace->id ?? 'Tidak ada' }})</p>
                    <p class="text-xs">URL: {{ url()->current() }}</p>
                    <p class="text-xs" x-text="'Alpine Workspace ID: ' + getCurrentWorkspaceId()"></p>
                </div>
                <button @click="showDebug = !showDebug" class="text-xs bg-yellow-500 text-white px-2 py-1 rounded">
                    <span x-text="showDebug ? 'Sembunyikan' : 'Tampilkan'"></span> Debug
                </button>
            </div>

            <div x-show="showDebug" class="mt-2 text-xs">
                <p><strong>Workspace dari Controller:</strong> {{ $workspace->id ?? 'NULL' }}</p>
                <p><strong>Route Parameters:</strong> {{ json_encode(request()->route()->parameters() ?? []) }}</p>
                <p><strong>Query Parameters:</strong> {{ json_encode(request()->query() ?? []) }}</p>
                <p><strong>Board Columns Count:</strong> <span x-text="boardColumns.length"></span></p>
            </div>
        </div> --}}

        <div data-workspace-id="{{ $workspace->id }}" class="bg-gray-50 min-h-screen flex flex-col" x-data="kanbanApp()"
            x-init="init()">

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



            /* ===== EDITOR STYLES ===== */
            /* CKEditor untuk komentar utama */
            #task-main-comment-editor+.ck-editor .ck-content {
                min-height: 120px !important;
                max-height: 200px;
                overflow-y: auto;
            }

            #task-main-comment-editor+.ck-editor .ck-content ul,
            #task-main-comment-editor+.ck-editor .ck-content ol {
                padding-left: 1.5rem !important;
                margin-left: 0 !important;
                list-style-position: outside !important;
            }

            #task-main-comment-editor+.ck-editor .ck-content li {
                margin-left: 0 !important;
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
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

                    boardColumns: [],
                    loadingColumns: false,
                    addingColumn: false,
                    newListName: '',

                    // --- Task Form Data ---
                    taskForm: {
                        title: '',
                        phase: '',
                        members: [], // ✅ Pastikan ini array of objects dengan id
                        secret: false,
                        notes: '',
                        attachments: [],
                        // labels: [],
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


                    labelData: {
                        labels: [],
                        colors: [],
                        selectedLabelIds: [],
                        newLabelName: '',
                        newLabelColor: null,
                        searchLabel: ''
                    },

                    // --- Members ---
                    searchMember: '',
                    selectAll: false,
                    workspaceMembers: [], // Anggota workspace
                    assignedMembers: [], // Anggota yang sudah ditugaskan
                    selectedMemberIds: [], // ID anggota yang dipilih
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
                    // searchLabel: '',
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
                    // newLabelName: '',
                    // newLabelColor: null,
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

                    // ✅ UPDATE: Open task detail - load assignments
                    openDetail(taskId) {
                        const task = this.tasks.find(t => t.id === taskId);
                        if (!task) return;

                        this.currentTask = JSON.parse(JSON.stringify(task));
                        this.isEditMode = false;
                        this.openTaskDetail = true;

                        // Load task assignments
                        this.loadTaskAssignments(taskId);
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
                            // labels: [],
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




                    closeTaskDetail() {
                        this.openTaskDetail = false;
                        // Clean up editors - gunakan ID yang benar
                        destroyMainEditorForTask('task-main-comment-editor');
                        Object.keys(taskEditors).forEach(id => destroyEditorForTask(id));
                    },



                    // ✅ PERBAIKI: Method untuk mendapatkan workspace ID
                    getCurrentWorkspaceId() {
                        const workspaceElement = document.querySelector('[data-workspace-id]');
                        const workspaceId = workspaceElement ? workspaceElement.getAttribute('data-workspace-id') : null;

                        if (!workspaceId) {
                            console.error('Workspace ID tidak ditemukan!');
                            return null;
                        }

                        return workspaceId;
                    },

                    // ✅ PERBAIKI: Load board columns dengan error handling
                    async loadBoardColumns() {
                        this.loadingColumns = true;
                        try {
                            const workspaceId = this.getCurrentWorkspaceId();
                            if (!workspaceId) {
                                console.error('Tidak dapat memuat kolom: Workspace ID tidak valid');
                                return;
                            }

                            console.log('Loading columns untuk workspace:', workspaceId);

                            const response = await fetch(`/tasks/board-columns/${workspaceId}`);
                            const data = await response.json();

                            console.log('Response board columns:', data);

                            if (data.success) {
                                this.boardColumns = data.columns;
                                // Initialize Sortable setelah kolom dimuat
                                this.$nextTick(() => {
                                    this.initializeSortable();
                                });
                            } else {
                                console.error('Gagal memuat kolom:', data.message);
                            }
                        } catch (error) {
                            console.error('Error loading board columns:', error);
                        } finally {
                            this.loadingColumns = false;
                        }
                    },


                    getWorkspaceIdFromSession() {
                        // Implementasi sesuai kebutuhan aplikasi Anda
                        return null;
                    },

                    // ✅ NEW: Method untuk menambah kolom baru via API
                    async addNewColumn() {
                        if (!this.newListName.trim()) {
                            alert('Nama kolom tidak boleh kosong');
                            return;
                        }

                        try {
                            this.addingColumn = true;
                            const workspaceId = this.getCurrentWorkspaceId();
                            if (!workspaceId) {
                                alert('Workspace tidak valid');
                                return;
                            }

                            const response = await fetch('/tasks/board-columns', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    workspace_id: workspaceId,
                                    name: this.newListName.trim()
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.boardColumns.push(data.column);
                                this.newListName = '';
                                this.openModal = false;
                                this.showNotification('Kolom berhasil ditambahkan', 'success');
                            } else {
                                alert('Gagal menambahkan kolom: ' + data.message);
                            }
                        } catch (error) {
                            console.error('Error adding column:', error);
                            alert('Terjadi kesalahan saat menambahkan kolom');
                        } finally {
                            this.addingColumn = false;
                        }
                    },

                    // ✅ NEW: Method untuk menghapus kolom
                    async deleteColumn(columnId) {
                        if (!confirm('Hapus kolom ini?')) return;

                        try {
                            const response = await fetch(`/tasks/board-columns/${columnId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.boardColumns = this.boardColumns.filter(col => col.id !== columnId);
                            }
                        } catch (error) {
                            console.error('Error deleting column:', error);
                        }
                    },

                    // ✅ NEW: Initialize Sortable untuk semua kolom
                    initializeSortable() {
                        this.boardColumns.forEach(column => {
                            this.initializeSortableForColumn(column.id);
                        });
                    },

                    initializeSortableForColumn(columnId) {
                        const el = document.getElementById(`column-${columnId}`);
                        if (el) {
                            new Sortable(el, {
                                group: {
                                    name: 'kanban',
                                    pull: true,
                                    put: true
                                },
                                animation: 150,
                                ghostClass: 'bg-blue-300',
                                dragClass: 'bg-blue-100',
                                onEnd: (evt) => {
                                    this.handleTaskMove(evt, columnId);
                                }
                            });
                        }
                    },

                    // ✅ NEW: Handle ketika task dipindahkan
                    async handleTaskMove(evt, columnId) {
                        const taskId = evt.item.dataset.taskId;
                        const fromColumnId = evt.from.id.replace('column-', '');
                        const toColumnId = evt.to.id.replace('column-', '');

                        if (fromColumnId === toColumnId) return;

                        try {
                            // Update task position di database
                            const response = await fetch('/tasks/update-column', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    task_id: taskId,
                                    board_column_id: toColumnId
                                })
                            });

                            if (!response.ok) {
                                console.error('Gagal update task column');
                                // Optionally revert the move
                            }
                        } catch (error) {
                            console.error('Error updating task column:', error);
                        }
                    },

                    // ✅ NEW: Method untuk mendapatkan tasks berdasarkan kolom
                    getTasksByColumn(columnId) {
                        // Sesuaikan dengan struktur data tasks Anda
                        return this.tasks.filter(task => task.board_column_id === columnId);
                    },

                    // ✅ NEW: Method untuk filter tasks di kolom tertentu
                    getFilteredTasksByColumn(columnId) {
                        const columnTasks = this.tasks.filter(task => task.board_column_id === columnId);
                        return this.filterTasks(columnTasks);
                    },

                    // ✅ NEW: Helper untuk notification
                    showNotification(message, type = 'info') {
                        // Implementasi notification system Anda
                        alert(message); // Sementara pakai alert, bisa diganti dengan toast notification
                    },

                    // ✅ Update method addList yang lama
                    async addList() {
                        await this.addNewColumn();
                    },

                    // ✅ Initialize ketika component mounted
                    init() {
                        this.loadBoardColumns();

                        // Juga load tasks jika diperlukan
                        this.loadTasks();

                        this.loadWorkspaceMembers(); // ✅ Load workspace members

                        this.ensureMembersData(); // ✅ Pastikan data konsisten


                        // ✅ NEW: Load labels dan colors
                        this.loadLabels();
                        this.loadColors();

                        console.log('Aplikasi initialized');



                    },

                    // ✅ NEW: Method untuk load tasks dari database
                    async loadTasks() {
                        try {
                            const workspaceId = this.getCurrentWorkspaceId();
                            if (!workspaceId) return;

                            // ✅ Gunakan endpoint yang baru
                            const response = await fetch(`/tasks/workspace/${workspaceId}/list`);
                            const data = await response.json();

                            if (data.success) {
                                this.tasks = data.tasks;
                            }
                        } catch (error) {
                            console.error('Error loading tasks:', error);
                        }
                    },



                    async loadWorkspaceMembers() {
                        try {
                            const workspaceId = this.getCurrentWorkspaceId();
                            if (!workspaceId) return;

                            // ✅ Gunakan endpoint yang baru
                            const response = await fetch(`/tasks/workspace/${workspaceId}/task-members`);
                            const data = await response.json();

                            if (data.success) {
                                this.workspaceMembers = data.members.map(member => ({
                                    ...member,
                                    selected: false
                                }));
                            } else {
                                console.error('Gagal memuat anggota workspace:', data.message);
                            }
                        } catch (error) {
                            console.error('Error loading workspace members:', error);
                        }
                    },

                    // ✅ PERBAIKI: Load task assignments dengan sync state yang benar
                    async loadTaskAssignments(taskId) {
                        try {
                            const response = await fetch(`/tasks/${taskId}/assignments`);
                            const data = await response.json();

                            if (data.success) {
                                this.assignedMembers = data.assigned_members;
                                this.selectedMemberIds = data.assigned_members.map(member => member.id);

                                // Update selected state di workspaceMembers dengan benar
                                this.workspaceMembers.forEach(member => {
                                    member.selected = this.selectedMemberIds.includes(member.id);
                                });

                                // Update selectAll state
                                this.selectAll = this.selectedMemberIds.length === this.workspaceMembers.length;

                                console.log('Loaded assignments:', this.assignedMembers);
                                console.log('Selected IDs:', this.selectedMemberIds);
                            }
                        } catch (error) {
                            console.error('Error loading task assignments:', error);
                        }
                    },

                    // ✅ CSRF Token Method - TEMPATKAN DI SINI
                    getCsrfToken() {
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        if (metaTag) {
                            return metaTag.getAttribute('content');
                        }

                        const inputTag = document.querySelector('input[name="_token"]');
                        if (inputTag) {
                            return inputTag.value;
                        }

                        return document.querySelector('script[data-csrf]')?.dataset.csrf || '';
                    },



                    // ✅ NEW: Save selected members ke task
                    async saveTaskAssignments(taskId) {
                        try {
                            const csrfToken = this.getCsrfToken();

                            // ✅ Gunakan endpoint yang baru
                            const response = await fetch(`/tasks/${taskId}/assignments`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    user_ids: this.selectedMemberIds
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                // ✅ PERBAIKI: Update assignedMembers dengan data terbaru dari response
                                this.assignedMembers = data.assigned_members;

                                // ✅ PERBAIKI: Juga update selectedMemberIds dengan data terbaru
                                this.selectedMemberIds = data.assigned_members.map(member => member.id);

                                // ✅ PERBAIKI: Update UI state
                                this.workspaceMembers.forEach(member => {
                                    member.selected = this.selectedMemberIds.includes(member.id);
                                });

                                this.selectAll = this.selectedMemberIds.length === this.workspaceMembers.length;

                                this.openAddMemberModal = false;
                                this.showNotification('Anggota tugas berhasil diupdate', 'success');

                                // ✅ PERBAIKI: Refresh task detail jika sedang dibuka
                                if (this.currentTask && this.currentTask.id === taskId) {
                                    this.currentTask.members = data.assigned_members;

                                    // ✅ TAMBAHKAN: Juga update tasks array untuk konsistensi data
                                    const taskIndex = this.tasks.findIndex(t => t.id === taskId);
                                    if (taskIndex !== -1) {
                                        this.tasks[taskIndex].members = data.assigned_members;
                                    }
                                }

                                console.log('Updated assignments:', this.assignedMembers);
                                console.log('Updated selected IDs:', this.selectedMemberIds);
                            } else {
                                alert('Gagal menyimpan anggota: ' + data.message);
                            }
                        } catch (error) {
                            console.error('Error saving task assignments:', error);
                            alert('Terjadi kesalahan saat menyimpan anggota');
                        }
                    },




                    // ✅ NEW: Toggle member selection
                    // ✅ PERBAIKI: Toggle member selection dengan update UI yang benar
                    toggleMember(memberId) {
                        if (!memberId) {
                            console.error('Invalid member ID');
                            return;
                        }

                        const index = this.selectedMemberIds.indexOf(memberId);
                        if (index === -1) {
                            // Add member
                            this.selectedMemberIds.push(memberId);
                        } else {
                            // Remove member
                            this.selectedMemberIds.splice(index, 1);
                        }

                        // Update UI state immediately
                        const member = this.workspaceMembers.find(m => m.id === memberId);
                        if (member) {
                            member.selected = !member.selected;
                        }

                        // Update selectAll state
                        this.selectAll = this.selectedMemberIds.length === this.workspaceMembers.length;

                        console.log('Toggled member:', memberId, 'Selected IDs:', this.selectedMemberIds);
                    },



                    // ✅ PERBAIKI: Toggle select all dengan sync yang benar
                    toggleSelectAllMembers() {
                        if (this.selectAll) {
                            // Select all members
                            this.selectedMemberIds = this.workspaceMembers.map(member => member.id);
                            this.workspaceMembers.forEach(member => {
                                member.selected = true;
                            });
                        } else {
                            // Deselect all members
                            this.selectedMemberIds = [];
                            this.workspaceMembers.forEach(member => {
                                member.selected = false;
                            });
                        }

                        console.log('Select All:', this.selectAll, 'Selected IDs:', this.selectedMemberIds);
                    },



                    // ✅ NEW: Filter members berdasarkan search
                    get filteredWorkspaceMembers() {
                        if (!this.searchMember) {
                            return this.workspaceMembers;
                        }
                        const searchTerm = this.searchMember.toLowerCase();
                        return this.workspaceMembers.filter(member =>
                            member.name.toLowerCase().includes(searchTerm) ||
                            member.email.toLowerCase().includes(searchTerm)
                        );
                    },






                    // ✅ PERBAIKI: Method untuk apply members ke task
                    applyMembersToTask() {
                        if (this.currentTask && this.currentTask.id) {
                            // Untuk task yang sudah ada - save ke database
                            this.saveTaskAssignments(this.currentTask.id);
                        } else {
                            // Untuk task baru - simpan di form data
                            const selectedMembers = this.workspaceMembers
                                .filter(member => this.selectedMemberIds.includes(member.id))
                                .map(member => ({
                                    id: member.id,
                                    name: member.name,
                                    email: member.email,
                                    avatar: member.avatar
                                }));

                            // ✅ PERBAIKI: Update taskForm.members dengan semua anggota yang dipilih
                            this.taskForm.members = selectedMembers;

                            this.openAddMemberModal = false;

                            // ✅ PERBAIKI: Reset selected state UI
                            this.selectedMemberIds = [];
                            this.workspaceMembers.forEach(member => {
                                member.selected = false;
                            });
                            this.selectAll = false;

                            console.log('Updated members for new task:', this.taskForm.members);
                        }
                    },

                    // ✅ PERBAIKI: Method untuk membuka modal tambah anggota
                    openAddMemberModalForTask(task = null) {
                        this.openAddMemberModal = true;
                        this.searchMember = '';

                        if (task && task.id) {
                            // Untuk task yang sudah ada - load assignments
                            this.loadTaskAssignments(task.id);
                        } else {
                            // Untuk task baru - initialize dengan anggota yang sudah dipilih di form
                            this.selectedMemberIds = (this.taskForm.members || []).map(m => m.id);

                            // Update selected state di workspaceMembers
                            this.workspaceMembers.forEach(member => {
                                member.selected = this.selectedMemberIds.includes(member.id);
                            });

                            this.selectAll = this.selectedMemberIds.length === this.workspaceMembers.length;
                        }
                    },


                    // ✅ Method untuk remove assigned member
                    removeAssignedMember(memberId) {
                        if (this.currentTask && this.isEditMode) {
                            // Remove dari current task
                            this.currentTask.members = this.currentTask.members.filter(m => m.id !== memberId);
                            // Juga update selectedMemberIds
                            this.selectedMemberIds = this.selectedMemberIds.filter(id => id !== memberId);
                        } else {
                            // Remove dari task form (new task)
                            this.taskForm.members = this.taskForm.members.filter(m => m.id !== memberId);
                            this.selectedMemberIds = this.selectedMemberIds.filter(id => id !== memberId);
                        }
                    },


                    // ✅ Method untuk memastikan data members konsisten
                    ensureMembersData() {
                        // Pastikan taskForm.members selalu array
                        if (!Array.isArray(this.taskForm.members)) {
                            this.taskForm.members = [];
                        }

                        // Pastikan setiap member punya id
                        this.taskForm.members = this.taskForm.members.map((member, index) => ({
                            id: member.id || `temp-${index}`, // Fallback ID
                            name: member.name || 'Unknown',
                            avatar: member.avatar || 'https://i.pravatar.cc/32?img=0',
                            email: member.email || ''
                        }));
                    },




                    // untuk label dan color 

                    // ✅ PERBAIKI: Method loadLabels dengan error handling
                    async loadLabels() {
                        try {
                            const workspaceId = this.getCurrentWorkspaceId();
                            if (!workspaceId) {
                                console.error('Workspace ID tidak valid');
                                return;
                            }

                            console.log('Loading labels untuk workspace:', workspaceId);

                            const response = await fetch(`/tasks/workspace/${workspaceId}/labels`);

                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const data = await response.json();
                            console.log('Response labels:', data);

                            if (data.success) {
                                this.labelData.labels = data.labels.map(label => ({
                                    ...label,
                                    selected: false
                                }));
                                console.log('Labels loaded:', this.labelData.labels.length);
                            } else {
                                console.error('Gagal memuat labels:', data.message);
                            }
                        } catch (error) {
                            console.error('Error loading labels:', error);
                            // Fallback ke data dummy jika API error
                            this.labelData.labels = this.getFallbackLabels();
                        }
                    },

                    // ✅ Fallback labels jika API error
                    getFallbackLabels() {
                        return [{
                                id: 'fallback-1',
                                name: 'Reels',
                                color: {
                                    rgb: '#2563eb'
                                },
                                selected: false
                            },
                            {
                                id: 'fallback-2',
                                name: 'Feeds',
                                color: {
                                    rgb: '#16a34a'
                                },
                                selected: false
                            },
                            {
                                id: 'fallback-3',
                                name: 'Story',
                                color: {
                                    rgb: '#f59e0b'
                                },
                                selected: false
                            }
                        ];
                    },

                    async loadColors() {
                        try {
                            const response = await fetch('/tasks/colors');
                            const data = await response.json();

                            if (data.success) {
                                this.labelData.colors = data.colors;
                            }
                        } catch (error) {
                            console.error('Error loading colors:', error);
                        }
                    },

                    // ✅ PERBAIKI: Method createNewLabel dengan debugging
async createNewLabel() {
    if (!this.labelData.newLabelName.trim()) {
        alert('Nama label harus diisi');
        return null;
    }

    if (!this.labelData.newLabelColor) {
        alert('Warna label harus dipilih');
        return null;
    }

    try {
        const workspaceId = this.getCurrentWorkspaceId();
        if (!workspaceId) {
            alert('Workspace tidak valid');
            return null;
        }

        // Cari color object berdasarkan RGB
        const color = this.labelData.colors.find(c => c.rgb === this.labelData.newLabelColor);
        if (!color) {
            alert('Warna tidak valid');
            return null;
        }

        console.log('Creating label dengan data:', {
            name: this.labelData.newLabelName.trim(),
            color_id: color.id,
            workspace_id: workspaceId
        });

        const response = await fetch('/tasks/labels', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                name: this.labelData.newLabelName.trim(),
                color_id: color.id,
                workspace_id: workspaceId
            })
        });

        // Debug response
        console.log('Response status:', response.status);
        
        const data = await response.json();
        console.log('Response data:', data);

        if (data.success) {
            // Tambahkan label baru ke list
            this.labelData.labels.push({
                ...data.label,
                selected: false
            });

            // Reset form
            this.labelData.newLabelName = '';
            this.labelData.newLabelColor = null;

            this.showNotification('Label berhasil dibuat', 'success');
            return data.label.id;
        } else {
            alert('Gagal membuat label: ' + (data.message || 'Unknown error'));
            return null;
        }
    } catch (error) {
        console.error('Error creating label:', error);
        alert('Terjadi kesalahan saat membuat label: ' + error.message);
        return null;
    }
},
                    // ✅ PERBAIKI: Method saveTaskLabels dengan handling yang lebih baik
                    async saveTaskLabels(taskId = null) {
                        try {
                            const selectedLabelIds = this.labelData.labels
                                .filter(label => label.selected)
                                .map(label => label.id);

                            console.log('Menyimpan labels:', selectedLabelIds, 'untuk task:', taskId);

                            // Jika taskId null (task baru), simpan di form data
                            if (!taskId) {
                                const selectedLabels = this.labelData.labels
                                    .filter(label => label.selected)
                                    .map(label => ({
                                        id: label.id,
                                        name: label.name,
                                        color: label.color.rgb
                                    }));

                                this.taskForm.labels = selectedLabels;
                                this.openLabelModal = false;
                                this.showNotification('Label berhasil dipilih', 'success');
                                return;
                            }

                            // Untuk task yang sudah ada, simpan ke database
                            const response = await fetch(`/tasks/${taskId}/labels`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                },
                                body: JSON.stringify({
                                    label_ids: selectedLabelIds
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                // Update current task labels
                                if (this.currentTask) {
                                    this.currentTask.labels = data.labels;
                                }

                                // Reset selection
                                this.labelData.labels.forEach(label => label.selected = false);
                                this.openLabelModal = false;

                                this.showNotification('Label berhasil disimpan', 'success');
                            } else {
                                alert('Gagal menyimpan label: ' + data.message);
                            }
                        } catch (error) {
                            console.error('Error saving task labels:', error);
                            alert('Terjadi kesalahan saat menyimpan label');
                        }
                    },

                    async loadTaskLabels(taskId) {
                        try {
                            const response = await fetch(`/tasks/${taskId}/labels`);
                            const data = await response.json();

                            if (data.success) {
                                // Update selected state
                                this.labelData.labels.forEach(label => {
                                    label.selected = data.labels.some(taskLabel => taskLabel.id === label.id);
                                });
                            }
                        } catch (error) {
                            console.error('Error loading task labels:', error);
                        }
                    },

                    // Filter labels untuk search
                    filteredLabels() {
                        if (!this.labelData.searchLabel) {
                            return this.labelData.labels;
                        }
                        return this.labelData.labels.filter(label =>
                            label.name.toLowerCase().includes(this.labelData.searchLabel.toLowerCase())
                        );
                    },

                    // Open label modal
                    openLabelModalForTask(task = null) {
                        this.openLabelModal = true;
                        this.labelData.searchLabel = '';

                        if (task && task.id) {
                            this.loadTaskLabels(task.id);
                        } else {
                            // Reset selection untuk task baru
                            this.labelData.labels.forEach(label => label.selected = false);
                        }
                    },





                }


            }





            const taskEditors = {}; // map id -> editor instance untuk tugas

            // create editor in containerId (string) untuk tugas
            async function createEditorForTask(containerId, options = {}) {
                const el = document.getElementById(containerId);
                if (!el) {
                    console.warn('createEditorForTask: element not found', containerId);
                    return null;
                }

                // clear existing content to avoid duplicates
                el.innerHTML = '';

                // default toolbar (safe — avoids plugins that might not exist in CDN build)
                const baseConfig = {
                    toolbar: {
                        items: [
                            'undo', 'redo', '|',
                            'heading', '|',
                            'bold', 'italic', 'underline', 'strikethrough', '|',
                            'link', 'blockQuote', '|',
                            'bulletedList', 'numberedList', '|',
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
                            }
                        ]
                    },
                    placeholder: options.placeholder || ''
                };

                // try to create editor, fallback to textarea on error
                try {
                    const editor = await ClassicEditor.create(el, baseConfig);
                    taskEditors[containerId] = editor;

                    // safe: focus editor when created
                    try {
                        editor.editing.view.focus();
                    } catch (e) {}

                    // wire change event for debug (and to keep Alpine in sync via dispatch)
                    editor.model.document.on('change:data', () => {
                        const data = editor.getData();
                        // dispatch a custom event so Alpine can listen if needed
                        const ev = new CustomEvent('editor-change', {
                            detail: {
                                id: containerId,
                                data
                            }
                        });
                        window.dispatchEvent(ev);
                    });

                    return editor;
                } catch (err) {
                    console.error('createEditorForTask error for', containerId, err);
                    // fallback to textarea
                    el.innerHTML =
                        `<textarea id="${containerId}-fallback" class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none">${options.initial || ''}</textarea>`;
                    return null;
                }
            }

            function destroyEditorForTask(containerId) {
                const ed = taskEditors[containerId];
                if (ed) {
                    ed.destroy().then(() => {
                        delete taskEditors[containerId];
                    }).catch((e) => {
                        console.warn('destroyEditorForTask error', containerId, e);
                        delete taskEditors[containerId];
                    });
                } else {
                    // remove fallback textarea if existed
                    const ta = document.getElementById(containerId + '-fallback');
                    if (ta) ta.remove();
                }
            }

            function getTaskEditorData(containerId) {
                const ed = taskEditors[containerId];
                if (ed) return ed.getData();
                const ta = document.getElementById(containerId + '-fallback');
                return ta ? ta.value : '';
            }

            // helper to init main (top) editor untuk tugas
            function initMainEditorForTask(containerId = 'task-comment-editor') {
                return createEditorForTask(containerId, {
                    placeholder: 'Ketik komentar Anda di sini...'
                });
            }

            function destroyMainEditorForTask(containerId = 'task-comment-editor') {
                destroyEditorForTask(containerId);
            }

            // helper to init reply editor for a specific comment id untuk tugas
            function initReplyEditorForTask(commentId) {
                const containerId = 'task-reply-editor-' + commentId;
                return createEditorForTask(containerId, {
                    placeholder: 'Ketik balasan Anda di sini...'
                });
            }

            function destroyReplyEditorForTask(commentId) {
                const containerId = 'task-reply-editor-' + commentId;
                destroyEditorForTask(containerId);
            }

            function getTaskReplyEditorDataFor(commentId) {
                return getTaskEditorData('task-reply-editor-' + commentId);
            }












            // Tambahkan fungsi commentSection di dalam script Alpine.js
            function commentSection() {
                return {
                    comments: [
                        // Data dummy komentar untuk tugas
                        {
                            id: 1,
                            author: {
                                name: 'Risi Gustiar',
                                avatar: 'https://i.pravatar.cc/40?img=3'
                            },
                            content: 'Data transaksi sudah saya update di file Excel.',
                            createdAt: new Date(Date.now() - (1000 * 60 * 60 * 24)).toISOString(),
                            replies: [{
                                id: 11,
                                author: {
                                    name: 'Naufal',
                                    avatar: 'https://i.pravatar.cc/40?img=1'
                                },
                                content: 'Terima kasih, saya akan cek datanya.',
                                createdAt: new Date(Date.now() - (1000 * 60 * 60 * 12)).toISOString()
                            }]
                        },
                        {
                            id: 2,
                            author: {
                                name: 'Rendi Sinaga',
                                avatar: 'https://i.pravatar.cc/40?img=4'
                            },
                            content: 'Draft laporan hampir selesai, tinggal verifikasi',
                            createdAt: new Date(Date.now() - (1000 * 60 * 60 * 6)).toISOString(),
                            replies: []
                        }
                    ],

                    // replyView untuk inline reply form
                    replyView: {
                        active: false,
                        parentComment: null
                    },

                    // ✅ TAMBAHKAN INIT METHOD
                    init() {
                        // Inisialisasi editor komentar utama ketika komponen dimuat
                        this.$nextTick(() => {
                            setTimeout(() => {
                                createEditorForTask('task-main-comment-editor', {
                                    placeholder: 'Ketik komentar Anda di sini...'
                                });
                            }, 300);
                        });

                        this.loadBoardColumns();

                    },

                    /* toggle reply inline */
                    toggleReply(comment) {
                        if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                            this.closeReplyView();
                            return;
                        }
                        // close any previous reply editor
                        if (this.replyView.active && this.replyView.parentComment) {
                            destroyReplyEditorForTask(this.replyView.parentComment.id);
                        }
                        this.replyView.active = true;
                        this.replyView.parentComment = comment;

                        // give DOM time to render the template, kemudian inisialisasi editor untuk that comment
                        setTimeout(() => {
                            initReplyEditorForTask(comment.id);
                        }, 150);
                    },

                    closeReplyView() {
                        if (this.replyView.parentComment) {
                            destroyReplyEditorForTask(this.replyView.parentComment.id);
                        }
                        this.replyView.active = false;
                        this.replyView.parentComment = null;
                    },

                    /* submit reply dari editor inline */
                    submitReplyFromEditor() {
                        if (!this.replyView.parentComment) {
                            alert('Komentar induk tidak ditemukan');
                            return;
                        }
                        const parentId = this.replyView.parentComment.id;
                        const content = getTaskReplyEditorDataFor(parentId).trim();
                        if (!content) {
                            alert('Komentar balasan tidak boleh kosong!');
                            return;
                        }

                        const newReply = {
                            id: Date.now(),
                            author: {
                                name: 'Anda',
                                avatar: 'https://i.pravatar.cc/40?img=11'
                            },
                            content,
                            createdAt: new Date().toISOString()
                        };

                        // push ke parent comment
                        if (!this.replyView.parentComment.replies) {
                            this.replyView.parentComment.replies = [];
                        }
                        this.replyView.parentComment.replies.push(newReply);

                        // tutup & destroy editor
                        this.closeReplyView();
                    },

                    /* submit main (top) comment */
                    submitMain() {
                        // ✅ PERBAIKI: Gunakan ID yang benar
                        const content = getTaskEditorData('task-main-comment-editor').trim();
                        if (!content) {
                            alert('Komentar tidak boleh kosong!');
                            return;
                        }

                        this.comments.unshift({
                            id: Date.now(),
                            author: {
                                name: 'Anda',
                                avatar: 'https://i.pravatar.cc/40?img=11'
                            },
                            content,
                            createdAt: new Date().toISOString(),
                            replies: []
                        });

                        // ✅ PERBAIKI: Clear editor setelah submit
                        const editor = taskEditors['task-main-comment-editor'];
                        if (editor) {
                            editor.setData('');
                        }
                    },

                    /* helper tanggal */
                    formatCommentDate(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        const now = new Date();
                        const diffMs = Math.abs(now - date);
                        const diffMinutes = Math.floor(diffMs / (1000 * 60));
                        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

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

                };
            }
        </script>
    @endsection
