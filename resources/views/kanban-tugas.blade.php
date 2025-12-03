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

                <div data-workspace-id="{{ $workspace->id }}" class="bg-gray-50 min-h-screen flex flex-col"
                    x-data="kanbanApp()" x-init="init()">

                    {{-- Workspace Nav --}}
                    @include('components.workspace-nav', ['active' => 'tugas'])

                    {{-- Search & Filter Section --}}
                    @include('components.pencarian-tugas')

                    {{-- View Mode Toggle
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
                    </div> --}}

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


                    .secret-task-badge {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 2px 8px;
                        border-radius: 12px;
                        font-size: 0.7rem;
                        font-weight: 600;
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                    }

                    /* Style untuk task card secret */
                    .task-card-secret {
                        border-left: 4px solid #764ba2;
                        background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
                    }


                    /* Drag & Drop Styles */
                    .drag-over {
                        border-color: #3b82f6 !important;
                        background-color: #eff6ff !important;
                    }

                    /* File Upload Styles */
                    .file-item {
                        transition: all 0.2s ease;
                    }

                    .file-item:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    }

                    /* Progress Bar Animation */
                    .progress-bar {
                        transition: width 0.3s ease;
                    }

                    /* Modal Preview */
                    .preview-modal {
                        backdrop-filter: blur(4px);
                    }


                    /* Tambahkan di section style */
                    .modal-layer-1 {
                        z-index: 50;
                    }

                    /* Modal dasar */
                    .modal-layer-2 {
                        z-index: 60;
                    }

                    /* Modal Detail Tugas */
                    .modal-layer-3 {
                        z-index: 70;
                    }

                    /* Modal anak (anggota, label) */
                    .modal-layer-4 {
                        z-index: 80;
                    }

                    /* Modal cucu (tambah label baru) */


                    /* Style untuk komentar */
                    .comment-content a {
                        color: #2563eb !important;
                        text-decoration: underline;
                        cursor: pointer;
                    }

                    .comment-content a:hover {
                        color: #1d4ed8 !important;
                        text-decoration: none;
                    }

                    /* CKEditor styling untuk komentar */
                    .ck-editor__editable {
                        min-height: 120px !important;
                        max-height: 200px;
                        overflow-y: auto;
                    }


                    /* Tambahkan di section style */
                    /* Phase Progress Colors */
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

                    /* Progress percentage colors */
                    .text-progress-high {
                        color: #10b981;
                    }

                    .text-progress-medium {
                        color: #3b82f6;
                    }

                    .text-progress-low {
                        color: #f59e0b;
                    }

                    .text-progress-none {
                        color: #ef4444;
                    }


                    /* Tambahkan di section style */
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

                    /* Phase Color Classes */
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

                    /* Smooth transitions */
                    .transition-all {
                        transition-property: all;
                        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                        transition-duration: 300ms;
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
                            // Di dalam kanbanApp() - update taskForm
                            taskForm: {
                                title: '',
                                phase: '',
                                members: [], // Array of {id, name, avatar}
                                user_ids: [], // Array of user IDs untuk backend
                                is_secret: false,
                                description: '',
                                attachments: [],
                                checklists: [],
                                labels: [], // Array of label objects
                                label_ids: [], // Array of label IDs untuk backend
                                start_datetime: '', // Format: Y-m-d H:i:s
                                due_datetime: '' // Format: Y-m-d H:i:s
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

                            uploading: false,
                            uploadProgress: 0,
                            previewModal: {
                                open: false,
                                url: '',
                                file: null
                            },

                            timelineData: [],
                            loadingTimeline: false,


                            currentColumnId: null,

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
                                    status: "   ",
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

                            // ✅ UPDATE: Open task detail - load dari database
                            // ✅ PERBAIKI: Method untuk membuka detail tugas
                            // ✅ PERBAIKI: Method untuk membuka detail tugas
                            async openDetail(taskId) {
                                try {
                                    console.log('🔄 Loading task detail for:', taskId);

                                    const response = await fetch(`/tasks/${taskId}/detail`, {
                                        headers: {
                                            'Accept': 'application/json'
                                        }
                                    });

                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }

                                    const data = await response.json();

                                    if (data.success) {
                                        // SET CURRENT TASK dengan comments
                                        this.currentTask = {
                                            id: data.task.id,
                                            title: data.task.title,
                                            phase: data.task.phase,
                                            description: data.task.description,
                                            is_secret: data.task.is_secret,
                                            status: data.task.status,
                                            priority: data.task.priority,
                                            startDate: data.task.start_datetime ? data.task.start_datetime.split('T')[0] : '',
                                            startTime: data.task.start_datetime ? data.task.start_datetime.split('T')[1]
                                                ?.substring(0, 5) : '',
                                            dueDate: data.task.due_datetime ? data.task.due_datetime.split('T')[0] : '',
                                            dueTime: data.task.due_datetime ? data.task.due_datetime.split('T')[1]?.substring(0,
                                                5) : '',
                                            members: data.task.assigned_members || [],
                                            labels: data.task.labels || [],
                                            checklist: data.task.checklists?.map(cl => ({
                                                id: cl.id,
                                                title: cl.title,
                                                is_done: cl.is_done,
                                                position: cl.position
                                            })) || [],
                                            attachments: data.task.attachments || [],
                                            comments: data.task.comments || [], // ✅ Include comments
                                            progress_percentage: data.task.progress_percentage,
                                            is_overdue: data.task.is_overdue,
                                            created_at: data.task.created_at,
                                            updated_at: data.task.updated_at,
                                            board_column: data.task.board_column
                                        };

                                        // Update assigned members
                                        this.assignedMembers = data.task.assigned_members || [];
                                        this.selectedMemberIds = this.assignedMembers.map(member => member.id);

                                        // Buka modal
                                        this.isEditMode = false;
                                        this.openTaskDetail = true;

                                        console.log('✅ Task detail loaded with', data.task.comments?.length || 0, 'comments');

                                    } else {
                                        this.showNotification('Gagal memuat detail tugas: ' + data.message, 'error');
                                    }

                                } catch (error) {
                                    console.error('❌ Error loading task detail:', error);
                                    this.showNotification('Terjadi kesalahan saat memuat detail tugas', 'error');
                                }
                            },

                            // Di method saveTaskEdit() di Alpine.js
                            async saveTaskEdit() {
                                if (!this.currentTask) return;

                                try {
                                    // ✅ VALIDASI
                                    if (!this.currentTask.title?.trim()) {
                                        this.showNotification('Judul tugas harus diisi', 'error');
                                        return;
                                    }

                                    console.log('🔄 Saving task edit for:', this.currentTask.id);

                                    // ✅ DAPATKAN CONTENT CKEDITOR
                                    let description = '';
                                    const editorId = 'editor-catatan-edit';

                                    // Try multiple methods to get editor content
                                    if (window.taskEditors && window.taskEditors[editorId]) {
                                        description = window.taskEditors[editorId].getData();
                                        console.log('✅ Got description from global taskEditors');
                                    } else {
                                        const editorElement = document.querySelector(`#${editorId} + .ck-editor .ck-content`);
                                        if (editorElement) {
                                            description = editorElement.innerHTML;
                                            console.log('✅ Got description from editor element');
                                        } else {
                                            description = this.currentTask.description || '';
                                            console.log('✅ Using existing description from currentTask');
                                        }
                                    }

                                    console.log('📝 Description length:', description.length);

                                    // Simpan judul jika berubah
                                    await this.saveTitleChange();

                                    // ✅ FORMAT DATA UNTUK BACKEND
                                    const formData = {
                                        title: this.currentTask.title,
                                        phase: this.currentTask.phase,
                                        description: description,
                                        is_secret: this.currentTask.is_secret,
                                        user_ids: this.assignedMembers.map(member => member.id),
                                        label_ids: this.currentTask.labels.map(label => label.id), // ✅ KIRIM LABEL IDS
                                        board_column_id: this.currentTask.board_column?.id
                                    };

                                    // ✅ TAMBAHKAN DATETIME JIKA ADA
                                    if (this.currentTask.startDate && this.currentTask.startTime) {
                                        formData.start_datetime = `${this.currentTask.startDate} ${this.currentTask.startTime}:00`;
                                    }

                                    if (this.currentTask.dueDate && this.currentTask.dueTime) {
                                        formData.due_datetime = `${this.currentTask.dueDate} ${this.currentTask.dueTime}:00`;
                                    }

                                    console.log('📤 Sending update request:', formData);

                                    // ✅ REQUEST KE BACKEND
                                    const response = await fetch(`/tasks/${this.currentTask.id}/update`, {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken(),
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify(formData)
                                    });

                                    console.log('📥 Response status:', response.status);
                                    console.log('📥 Response ok:', response.ok);

                                    // ✅ CEK RESPONSE STATUS
                                    if (!response.ok) {
                                        let errorMessage = 'Gagal memperbarui tugas';

                                        try {
                                            const errorData = await response.json();
                                            errorMessage = errorData.message || errorMessage;
                                        } catch (e) {
                                            errorMessage = `HTTP Error ${response.status}: ${response.statusText}`;
                                        }

                                        console.error('❌ HTTP Error:', response.status, errorMessage);
                                        throw new Error(errorMessage);
                                    }

                                    // ✅ PARSE JSON RESPONSE
                                    let data;
                                    try {
                                        const responseText = await response.text();
                                        console.log('📥 Raw response:', responseText.substring(0, 200));

                                        data = JSON.parse(responseText);
                                        console.log('📥 Parsed response:', data);
                                    } catch (parseError) {
                                        console.error('❌ JSON Parse Error:', parseError);
                                        throw new Error('Response bukan format JSON yang valid');
                                    }

                                    // ✅ CHECK SUCCESS FLAG
                                    if (data.success) {
                                        this.showNotification('Tugas berhasil diperbarui', 'success');
                                        this.isEditMode = false;

                                        // ✅ UPDATE CURRENTTASK DENGAN DATA TERBARU
                                        if (data.task) {
                                            Object.assign(this.currentTask, {
                                                ...data.task,
                                                description: data.task.description || description,
                                                labels: data.task.labels || this.currentTask.labels, // ✅ UPDATE LABELS
                                                // ✅ PASTIKAN SEMUA FIELD DI-UPDATE
                                                members: data.task.assigned_members || this.currentTask.members,
                                                checklists: data.task.checklists || this.currentTask.checklists,
                                                attachments: data.task.attachments || this.currentTask.attachments
                                            });

                                            // ✅ SYNC ASSIGNED MEMBERS
                                            if (data.task.assigned_members) {
                                                this.assignedMembers = data.task.assigned_members;
                                                this.selectedMemberIds = data.task.assigned_members.map(m => m.id);
                                            }
                                        }

                                        // ✅ REFRESH KANBAN DATA
                                        await this.loadKanbanTasks();

                                        console.log('✅ Task updated successfully with all changes');
                                    } else {
                                        throw new Error(data.message || 'Gagal memperbarui tugas');
                                    }

                                } catch (error) {
                                    console.error('❌ Error in saveTaskEdit:', error);
                                    console.error('❌ Error stack:', error.stack);

                                    // ✅ ERROR MESSAGE YANG LEBIH SPESIFIK
                                    let errorMessage = 'Gagal memperbarui tugas';

                                    if (error.message) {
                                        if (error.message.includes('HTTP')) {
                                            errorMessage += ' - Terjadi masalah koneksi';
                                        } else if (error.message.includes('JSON')) {
                                            errorMessage += ' - Response tidak valid';
                                        } else {
                                            errorMessage += `: ${error.message}`;
                                        }
                                    }

                                    this.showNotification(errorMessage, 'error');
                                }
                            },



                            // Di dalam kanbanApp() - tambahkan method ini

                            // ✅ NEW: Method untuk konfirmasi dan hapus tugas
                            async confirmDeleteTask() {
                                if (!this.currentTask || !this.currentTask.id) {
                                    this.showNotification('Task tidak ditemukan', 'error');
                                    return;
                                }

                                // Konfirmasi sebelum hapus
                                if (!confirm(`Apakah Anda yakin ingin menghapus tugas "${this.currentTask.title}"?`)) {
                                    return;
                                }

                                // Konfirmasi tambahan untuk tugas penting
                                if (this.currentTask.is_secret || this.currentTask.priority === 'high' || this.currentTask
                                    .priority === 'urgent') {
                                    const confirmed = confirm(
                                        '⚠️ Tugas ini memiliki prioritas tinggi/rahasia. Anda yakin ingin menghapus?');
                                    if (!confirmed) return;
                                }

                                await this.deleteTask(this.currentTask.id);
                            },

                            // ✅ NEW: Method untuk hapus tugas via API
                            async deleteTask(taskId) {
                                try {
                                    console.log('🔄 Deleting task:', taskId);

                                    const response = await fetch(`/tasks/${taskId}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken(),
                                            'Accept': 'application/json'
                                        }
                                    });

                                    // ✅ Check response status
                                    if (!response.ok) {
                                        const errorData = await response.json().catch(() => ({}));
                                        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                                    }

                                    const data = await response.json();

                                    if (data.success) {
                                        this.showNotification('Tugas berhasil dihapus', 'success');

                                        // ✅ Tutup modal detail
                                        this.openTaskDetail = false;

                                        // ✅ Hapus dari array tasks lokal
                                        const taskIndex = this.tasks.findIndex(t => t.id === taskId);
                                        if (taskIndex !== -1) {
                                            this.tasks.splice(taskIndex, 1);
                                        }

                                        // ✅ Refresh kanban board
                                        await this.loadKanbanTasks();

                                        console.log('✅ Task deleted successfully');
                                    } else {
                                        throw new Error(data.message || 'Gagal menghapus tugas');
                                    }
                                } catch (error) {
                                    console.error('❌ Error deleting task:', error);
                                    this.showNotification('Gagal menghapus tugas: ' + error.message, 'error');
                                }
                            },

                            // ✅ OPTIONAL: Method untuk force delete (permanen)
                            async forceDeleteTask(taskId) {
                                if (!confirm('⚠️ Hapus permanen? Tugas tidak dapat dikembalikan!')) {
                                    return;
                                }

                                try {
                                    const response = await fetch(`/tasks/${taskId}/force`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        this.showNotification('Tugas berhasil dihapus permanen', 'success');
                                        this.openTaskDetail = false;
                                        await this.loadKanbanTasks();
                                    } else {
                                        alert('Gagal menghapus permanen: ' + data.message);
                                    }
                                } catch (error) {
                                    console.error('Error force deleting task:', error);
                                    alert('Terjadi kesalahan saat menghapus permanen');
                                }
                            },

                            // ✅ OPTIONAL: Method untuk restore task
                            async restoreTask(taskId) {
                                try {
                                    const response = await fetch(`/tasks/${taskId}/restore`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        this.showNotification('Tugas berhasil dikembalikan', 'success');
                                        await this.loadKanbanTasks();
                                    } else {
                                        alert('Gagal mengembalikan tugas: ' + data.message);
                                    }
                                } catch (error) {
                                    console.error('Error restoring task:', error);
                                    alert('Terjadi kesalahan saat mengembalikan tugas');
                                }
                            },





                            // ✅ NEW: Method untuk menghapus checklist item
                            // Di dalam kanbanApp() - GANTI method removeChecklistItemFromDetail
                            async removeChecklistItemFromDetail(index) {
                                if (!this.currentTask || !this.currentTask.checklist) return;

                                const item = this.currentTask.checklist[index];

                                if (!confirm(`Hapus item "${item.title}"?`)) {
                                    return;
                                }

                                try {
                                    // ✅ JIKA ITEM BARU (temp-), LANGSUNG HAPUS DARI ARRAY
                                    if (item.id && item.id.toString().startsWith('temp-')) {
                                        this.currentTask.checklist.splice(index, 1);
                                        console.log('✅ Temporary item removed:', item.id);
                                        return;
                                    }

                                    // ✅ JIKA ITEM SUDAH ADA DI DATABASE, HAPUS VIA API
                                    if (item.id && !item.id.toString().startsWith('temp-')) {
                                        console.log('🔄 Deleting checklist item:', item.id);

                                        const response = await fetch(`/tasks/checklists/${item.id}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                                'Accept': 'application/json'
                                            }
                                        });

                                        // ✅ CHECK RESPONSE STATUS
                                        if (!response.ok) {
                                            const errorData = await response.json().catch(() => ({}));
                                            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                                        }

                                        const data = await response.json();

                                        if (!data.success) {
                                            throw new Error(data.message || 'Gagal menghapus checklist');
                                        }

                                        // ✅ HAPUS DARI ARRAY JIKA API SUCCESS
                                        this.currentTask.checklist.splice(index, 1);
                                        console.log('✅ Checklist deleted successfully');
                                        this.showNotification('Checklist berhasil dihapus', 'success');
                                    }
                                } catch (error) {
                                    console.error('❌ Error deleting checklist item:', error);
                                    this.showNotification('Gagal menghapus checklist: ' + error.message, 'error');

                                    // ✅ RELOAD TASK DETAIL JIKA GAGAL
                                    if (this.currentTask?.id) {
                                        await this.openDetail(this.currentTask.id);
                                    }
                                }
                            },
                            // ✅ NEW: Method untuk menambah checklist item di detail
                            addChecklistItemToDetail() {
                                if (!this.currentTask.checklist) {
                                    this.currentTask.checklist = [];
                                }

                                const newItem = {
                                    id: 'temp-' + Date.now(),
                                    title: 'Item checklist baru',
                                    is_done: false,
                                    done: false,
                                    position: this.currentTask.checklist.length
                                };

                                this.currentTask.checklist.push(newItem);

                                // Focus ke input baru
                                this.$nextTick(() => {
                                    const inputs = document.querySelectorAll('#detail-checklist-container input[type="text"]');
                                    if (inputs.length > 0) {
                                        inputs[inputs.length - 1].focus();
                                        inputs[inputs.length - 1].select();
                                    }
                                });
                            },

                            // ✅ NEW: Method untuk update checklist item di detail
                            // Di dalam kanbanApp() - GANTI method updateChecklistItemInDetail
                            async updateChecklistItemInDetail(item) {
                                if (!item.title?.trim()) {
                                    this.showNotification('Judul checklist tidak boleh kosong', 'error');
                                    return;
                                }

                                try {
                                    // ✅ JIKA ITEM BARU (ID starts with 'temp-'), SKIP UPDATE KE API
                                    if (item.id && item.id.toString().startsWith('temp-')) {
                                        console.log('⏭️ Skip API update for temporary item:', item.id);
                                        return; // Item baru akan di-save saat saveTaskEdit()
                                    }

                                    // ✅ JIKA ITEM SUDAH ADA DI DATABASE, UPDATE VIA API
                                    if (item.id && !item.id.toString().startsWith('temp-')) {
                                        console.log('🔄 Updating checklist item:', item.id);

                                        const response = await fetch(`/tasks/checklists/${item.id}`, {
                                            method: 'PUT',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                title: item.title,
                                                is_done: item.is_done
                                            })
                                        });

                                        // ✅ CHECK RESPONSE STATUS
                                        if (!response.ok) {
                                            const errorData = await response.json().catch(() => ({}));
                                            throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                                        }

                                        const data = await response.json();

                                        if (!data.success) {
                                            throw new Error(data.message || 'Gagal menyimpan perubahan');
                                        }

                                        console.log('✅ Checklist updated successfully');
                                        this.showNotification('Checklist berhasil diupdate', 'success');
                                    }
                                } catch (error) {
                                    console.error('❌ Error updating checklist item:', error);
                                    this.showNotification('Gagal mengupdate checklist: ' + error.message, 'error');

                                    // ✅ REVERT PERUBAHAN JIKA GAGAL
                                    // Reload task detail untuk get fresh data
                                    if (this.currentTask?.id) {
                                        await this.openDetail(this.currentTask.id);
                                    }
                                }
                            },

                            // ✅ NEW: Calculate progress untuk task detail
                            calculateTaskProgress(task) {
                                if (!task.checklist || task.checklist.length === 0) return 0;
                                const completed = task.checklist.filter(item => item.is_done || item.done).length;
                                return Math.round((completed / task.checklist.length) * 100);
                            },

                            // ✅ NEW: Format date untuk detail
                            formatDetailDate(dateString) {
                                if (!dateString) return '';
                                const date = new Date(dateString);
                                return date.toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric'
                                });
                            },

                            // ✅ NEW: Method untuk menghapus attachment dari detail
                            removeAttachmentFromDetail(index) {
                                if (!this.currentTask?.attachments) return;

                                const file = this.currentTask.attachments[index];
                                if (confirm(`Hapus file ${file.name}?`)) {
                                    // Hapus dari server jika perlu
                                    if (file.id) {
                                        this.deleteAttachmentFromServer(file.id);
                                    }
                                    this.currentTask.attachments.splice(index, 1);
                                    // this.showNotification('File berhasil dihapus', 'success');
                                }
                            },

                            openTaskModalForColumn(columnId = null) {
                                this.currentColumnId = columnId;
                                this.openTaskModal = true;

                                // ✅ TAMBAHKAN: Initialize editor setelah modal terbuka
                                this.$nextTick(() => {
                                    setTimeout(() => {
                                        this.initializeTaskFormEditor();
                                    }, 300);
                                });
                            },

                            // Enable edit mode
                            // Di method enableEditMode() atau saat modal dibuka
                            // ✅ PERBAIKI: Method untuk enable edit mode dengan inisialisasi editor yang lebih reliable
                            // ✅ PERBAIKI: Method enableEditMode dengan timing yang lebih baik
                            async enableEditMode() {
                                console.log('🔄 Enabling edit mode...');

                                this.isEditMode = true;

                                // Tunggu Alpine.js selesai update DOM
                                await this.$nextTick();

                                // Beri waktu tambahan untuk DOM rendering
                                await new Promise(resolve => setTimeout(resolve, 200));

                                console.log('✅ DOM should be ready, initializing editors...');

                                // Inisialisasi editor
                                await this.initEditModeEditors();
                            },

                            // ✅ PERBAIKI: Inisialisasi editor untuk edit mode
                            // ✅ PERBAIKI: Method initEditModeEditors dengan error handling yang lebih baik
                            async initEditModeEditors() {
                                try {
                                    console.log('🔄 Initializing edit mode editors...');

                                    // Target element untuk editor catatan
                                    const editorElementId = 'editor-catatan-edit';
                                    const editorElement = document.getElementById(editorElementId);

                                    console.log('🔍 Looking for editor element:', editorElementId);
                                    console.log('📝 Element found:', !!editorElement);

                                    if (editorElement) {
                                        console.log('🎯 Element details:', {
                                            id: editorElement.id,
                                            className: editorElement.className,
                                            parent: editorElement.parentElement?.id
                                        });

                                        // Clear element content first
                                        editorElement.innerHTML = '';

                                        // Set placeholder text sementara
                                        editorElement.innerHTML = '<p>Loading editor...</p>';

                                        // Initialize CKEditor dengan timeout
                                        setTimeout(async () => {
                                            try {
                                                await this.initializeCKEditor(editorElementId);
                                            } catch (error) {
                                                console.error('❌ Failed to initialize CKEditor:', error);
                                                this.fallbackToTextarea(editorElementId);
                                            }
                                        }, 100);

                                    } else {
                                        console.error('❌ Editor element not found:', editorElementId);
                                        // Coba cari alternatif element
                                        this.findAlternativeEditorElement();
                                    }

                                } catch (error) {
                                    console.error('❌ Error in initEditModeEditors:', error);
                                }
                            },




                            // ✅ NEW: Method khusus untuk initialize CKEditor
                            async initializeCKEditor(editorId) {
                                return new Promise(async (resolve, reject) => {
                                    try {
                                        console.log(`🔄 Initializing CKEditor for: ${editorId}`);

                                        const element = document.getElementById(editorId);
                                        if (!element) {
                                            throw new Error(`Element ${editorId} not found`);
                                        }

                                        // Pastikan CKEditor tersedia
                                        if (typeof ClassicEditor === 'undefined') {
                                            throw new Error('CKEditor ClassicEditor not loaded');
                                        }

                                        console.log('✅ CKEditor is available, creating instance...');

                                        // Clear element
                                        element.innerHTML = '';

                                        // Buat CKEditor instance
                                        const editor = await ClassicEditor.create(element, {
                                            toolbar: {
                                                items: [
                                                    'undo', 'redo', '|',
                                                    'heading', '|',
                                                    'bold', 'italic', 'underline', 'strikethrough', '|',
                                                    'fontColor', 'fontBackgroundColor', '|',
                                                    'link', 'blockQuote', 'code', '|',
                                                    'bulletedList', 'numberedList',
                                                    '|',
                                                    'insertTable',
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
                                            placeholder: 'Tulis catatan tugas di sini...'
                                        });

                                        // Set initial data
                                        if (this.currentTask?.description) {
                                            editor.setData(this.currentTask.description);
                                            console.log('✅ Set initial content to editor');
                                        }

                                        // Simpan instance
                                        if (typeof window.taskEditors === 'undefined') {
                                            window.taskEditors = {};
                                        }
                                        window.taskEditors[editorId] = editor;

                                        console.log('✅ CKEditor initialized successfully for:', editorId);
                                        resolve(editor);

                                    } catch (error) {
                                        console.error('❌ CKEditor initialization failed:', error);
                                        this.fallbackToTextarea(editorId);
                                        reject(error);
                                    }
                                });
                            },


                            // ✅ NEW: Cari element editor alternatif
                            findAlternativeEditorElement() {
                                console.log('🔍 Searching for alternative editor elements...');

                                const possibleSelectors = [
                                    '#editor-catatan-edit',
                                    '[x-model="currentTask.description"]',
                                    '.modal-layer-2 textarea',
                                    '#editor-catatan-edit-fallback'
                                ];

                                possibleSelectors.forEach(selector => {
                                    const element = document.querySelector(selector);
                                    if (element) {
                                        console.log(`✅ Found element with selector: ${selector}`, element);
                                    }
                                });
                            },

                            // ✅ NEW: Fallback ke textarea jika CKEditor gagal
                            fallbackToTextarea(editorId) {
                                const editorElement = document.getElementById(editorId);
                                if (editorElement) {
                                    editorElement.innerHTML = `
            <textarea id="${editorId}-fallback" 
                      class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none"
                      x-model="currentTask.description">${this.currentTask?.description || ''}</textarea>
        `;
                                    console.log('✅ Fallback to textarea for:', editorId);
                                }
                            },



                            // Cancel edit
                            async cancelEdit() {
                                if (this.currentTask && this.currentTask.id) {
                                    // Reload task detail untuk mendapatkan data asli
                                    await this.openDetail(this.currentTask.id);
                                }
                                this.isEditMode = false;
                            },



                            async handleFileSelectDetail(event) {
                                const files = Array.from(event.target.files);
                                console.log('📁 Detail: Files selected:', files.length);

                                if (files.length === 0) return;

                                await this.processFilesDetail(files);
                                event.target.value = '';
                            },

                            async handleFileDropDetail(event) {
                                const files = Array.from(event.dataTransfer.files);
                                console.log('📁 Detail: Files dropped:', files.length);

                                if (files.length === 0) return;

                                await this.processFilesDetail(files);
                            },

                            async processFilesDetail(files) {
                                console.log('🔄 Detail: Processing', files.length, 'files...');

                                for (const file of files) {
                                    await this.uploadFileDetail(file);
                                }
                            },

                            async uploadFileDetail(file) {
                                if (!this.currentTask || !this.currentTask.id) {
                                    this.showNotification('Task ID tidak ditemukan', 'error');
                                    return;
                                }

                                this.uploadingDetail = true;
                                this.uploadProgressDetail = 0;

                                try {
                                    // Validasi
                                    const allowedTypes = [
                                        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-powerpoint',
                                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                        'text/plain',
                                        'application/zip',
                                        'application/x-rar-compressed'
                                    ];

                                    if (!allowedTypes.includes(file.type)) {
                                        throw new Error('Tipe file tidak didukung');
                                    }

                                    if (file.size > 10 * 1024 * 1024) {
                                        throw new Error('File terlalu besar. Maksimal 10MB');
                                    }

                                    const formData = new FormData();
                                    formData.append('file', file);

                                    console.log('📤 Uploading to task:', this.currentTask.id);

                                    const response = await fetch(`/tasks/${this.currentTask.id}/attachments/add`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        },
                                        body: formData
                                    });

                                    if (!response.ok) {
                                        const errorData = await response.json();
                                        throw new Error(errorData.message || `HTTP ${response.status}`);
                                    }

                                    const data = await response.json();

                                    if (data.success && data.attachment) {
                                        if (!this.currentTask.attachments) {
                                            this.currentTask.attachments = [];
                                        }

                                        const uploadedFile = {
                                            id: data.attachment.id,
                                            name: data.attachment.file_name || file.name,
                                            size: data.attachment.file_size || file.size,
                                            type: this.getFileTypeFromMime(data.attachment.mime_type || file.type),
                                            url: data.attachment.file_url.startsWith('http') ?
                                                data.attachment.file_url : '/storage/' + data.attachment.file_url,
                                            uploaded_at: data.attachment.uploaded_at || new Date().toISOString()
                                        };

                                        this.currentTask.attachments.push(uploadedFile);

                                        console.log('✅ File added to task:', uploadedFile);
                                        // this.showNotification(`File ${file.name} berhasil diupload`, 'success');
                                    } else {
                                        throw new Error(data.message || 'Upload gagal');
                                    }
                                } catch (error) {
                                    console.error('❌ Error uploading file:', error);
                                    this.showNotification(`Gagal upload file: ${error.message}`, 'error');
                                } finally {
                                    this.uploadingDetail = false;
                                    this.uploadProgressDetail = 0;
                                }
                            },

                            // ✅ NEW: Remove label dari task
                            async removeLabelFromTask(labelId) {
                                if (!this.currentTask?.labels) return;

                                try {
                                    const currentLabelIds = this.currentTask.labels.map(label => label.id);
                                    const updatedLabelIds = currentLabelIds.filter(id => id !== labelId);

                                    const response = await fetch(`/tasks/${this.currentTask.id}/labels/update`, {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        },
                                        body: JSON.stringify({
                                            label_ids: updatedLabelIds
                                        })
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        this.currentTask.labels = data.labels;
                                        // this.showNotification('Label berhasil dihapus', 'success');
                                    } else {
                                        throw new Error(data.message || 'Gagal menghapus label');
                                    }
                                } catch (error) {
                                    console.error('Error removing label:', error);
                                    this.showNotification('Gagal menghapus label', 'error');
                                }
                            },

                            // ✅ NEW: Save title changes
                            async saveTitleChange() {
                                if (!this.currentTask?.title?.trim()) {
                                    this.showNotification('Judul tidak boleh kosong', 'error');
                                    return;
                                }

                                try {
                                    const response = await fetch(`/tasks/${this.currentTask.id}/update-title`, {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        },
                                        body: JSON.stringify({
                                            title: this.currentTask.title
                                        })
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        // this.showNotification('Judul berhasil diperbarui', 'success');
                                    } else {
                                        throw new Error(data.message || 'Gagal memperbarui judul');
                                    }
                                } catch (error) {
                                    console.error('Error updating title:', error);
                                    this.showNotification('Gagal memperbarui judul', 'error');
                                }
                            },

                            // ✅ NEW: Load tasks dengan filter hak akses
                            async loadTasksWithAccess() {
                                try {
                                    const workspaceId = this.getCurrentWorkspaceId();
                                    if (!workspaceId) return;

                                    const response = await fetch(`/tasks/workspace/${workspaceId}/tasks-with-access`);
                                    const data = await response.json();

                                    if (data.success) {
                                        this.tasks = data.tasks;
                                        console.log('Tasks loaded with access filter:', {
                                            total: data.tasks.length,
                                            secret: data.tasks.filter(t => t.is_secret).length,
                                            user_role: data.user_role
                                        });
                                    }
                                } catch (error) {
                                    console.error('Error loading tasks with access:', error);
                                }
                            },

                            // Create new task
                            // Di dalam kanbanApp() - perbaiki method createTask
                            async createTask() {
                                try {
                                    const catatanContent = this.getCKEditorContent('editor-catatan');
                                    this.taskForm.description = catatanContent;

                                    console.log('CKEditor content:', catatanContent);

                                    // Validasi
                                    if (!this.taskForm.title?.trim()) {
                                        this.showNotification('Judul tugas harus diisi', 'error');
                                        return;
                                    }

                                    if (!this.taskForm.phase?.trim()) {
                                        this.showNotification('Phase harus diisi', 'error');
                                        return;
                                    }

                                    if (!this.currentColumnId) {
                                        this.showNotification('Kolom tujuan tidak ditemukan', 'error');
                                        return;
                                    }

                                    try {
                                        const workspaceId = this.getCurrentWorkspaceId();
                                        if (!workspaceId) {
                                            this.showNotification('Workspace tidak valid', 'error');
                                            return;
                                        }

                                        // Siapkan data untuk backend
                                        const formData = {
                                            workspace_id: this.getCurrentWorkspaceId(),
                                            board_column_id: this.currentColumnId,
                                            title: this.taskForm.title,
                                            description: this.taskForm.description,
                                            phase: this.taskForm.phase,
                                            user_ids: this.taskForm.members.map(m => m.id),
                                            is_secret: this.taskForm.is_secret,
                                            label_ids: this.taskForm.labels.map(l => l.id),
                                            checklists: this.taskForm.checklists.map(item => ({
                                                title: item.title,
                                                is_done: item.is_done || false
                                            })),
                                            attachment_ids: this.taskForm.attachments.map(att => att.id)
                                        };

                                        // Tambahkan datetime jika ada
                                        if (this.taskForm.startDate && this.taskForm.startTime) {
                                            formData.start_datetime = `${this.taskForm.startDate} ${this.taskForm.startTime}:00`;
                                        }
                                        if (this.taskForm.dueDate && this.taskForm.dueTime) {
                                            formData.due_datetime = `${this.taskForm.dueDate} ${this.taskForm.dueTime}:00`;
                                        }

                                        // Hapus null values
                                        Object.keys(formData).forEach(key => {
                                            if (formData[key] === null || formData[key] === undefined || formData[key] ===
                                                '') {
                                                delete formData[key];
                                            }
                                        });

                                        console.log('Sending task data:', formData);

                                        const response = await fetch('/tasks/create-with-assignments', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify(formData)
                                        });

                                        const data = await response.json();

                                        if (!response.ok) {
                                            throw new Error(data.message || `HTTP error! status: ${response.status}`);
                                        }

                                        if (data.success) {
                                            this.showNotification('Tugas berhasil dibuat!', 'success');

                                            // ✅ TAMBAHKAN: Update state Alpine.js dengan tugas baru
                                            this.addNewTaskToKanban(data.task);

                                            this.resetTaskForm();
                                            this.openTaskModal = false;

                                        } else {
                                            throw new Error(data.message || 'Gagal membuat tugas');
                                        }

                                    } catch (error) {
                                        console.error('Error creating task:', error);
                                        this.showNotification(`Gagal membuat tugas: ${error.message}`, 'error');
                                    }
                                } catch (error) {
                                    console.error('Error in createTask:', error);
                                    this.showNotification('Terjadi kesalahan saat membuat tugas', 'error');
                                }
                            },



                            // ✅ UPDATE: Method untuk menambahkan tugas baru ke kanban
                            addNewTaskToKanban(newTaskData) {
                                console.log('🔄 Adding new task to kanban:', newTaskData);

                                // Transform data dari backend ke format frontend
                                const newTask = {
                                    id: newTaskData.id,
                                    title: newTaskData.title,
                                    phase: newTaskData.phase,
                                    status: newTaskData.board_column_id, // Gunakan board_column_id sebagai status
                                    board_column_id: newTaskData.board_column_id,
                                    members: newTaskData.assignees || [], // ✅ Gunakan assignees dari backend
                                    secret: newTaskData.is_secret,
                                    is_secret: newTaskData.is_secret,
                                    notes: newTaskData.description,
                                    description: newTaskData.description,
                                    attachments: newTaskData.attachments || [],
                                    labels: newTaskData.labels || [],
                                    checklist: (newTaskData.checklists || []).map(cl => ({
                                        id: cl.id,
                                        name: cl.title,
                                        title: cl.title,
                                        done: cl.is_done,
                                        is_done: cl.is_done,
                                        position: cl.position
                                    })),
                                    startDate: newTaskData.start_datetime ? newTaskData.start_datetime.split('T')[0] : '',
                                    startTime: newTaskData.start_datetime ? newTaskData.start_datetime.split('T')[1]?.substring(0,
                                        5) : '',
                                    dueDate: newTaskData.due_datetime ? newTaskData.due_datetime.split('T')[0] : '',
                                    dueTime: newTaskData.due_datetime ? newTaskData.due_datetime.split('T')[1]?.substring(0, 5) :
                                        '',
                                    priority: newTaskData.priority,
                                    progress_percentage: newTaskData.progress_percentage || 0,
                                    is_overdue: newTaskData.is_overdue || false,
                                    created_at: newTaskData.created_at,
                                    updated_at: newTaskData.updated_at
                                };

                                // Tambahkan tugas baru ke array tasks
                                this.tasks.push(newTask);

                                console.log('✅ New task added to kanban:', newTask);
                                console.log('📊 Total tasks now:', this.tasks.length);

                                // Trigger Alpine.js reactivity
                                this.$nextTick(() => {
                                    console.log('🔄 Kanban board updated with new task');
                                });
                            },



                            // Method untuk mendapatkan ID kolom default (To Do)
                            getDefaultBoardColumnId() {
                                const todoColumn = this.boardColumns.find(col =>
                                    col.name.toLowerCase().includes('todo') ||
                                    col.name.toLowerCase().includes('to do')
                                );
                                return todoColumn ? todoColumn.id : (this.boardColumns[0]?.id || null);
                            },

                            // Method untuk show notification yang lebih baik
                            // Di dalam kanbanApp() - UPDATE method showNotification
                            showNotification(message, type = 'info') {
                                const bgColor = type === 'success' ? 'bg-green-500' :
                                    type === 'error' ? 'bg-red-500' :
                                    type === 'warning' ? 'bg-yellow-500' :
                                    'bg-blue-500';

                                // Untuk sementara pakai alert
                                // Nanti bisa diganti dengan toast notification library
                                if (type === 'error') {
                                    alert('❌ ' + message);
                                } else if (type === 'warning') {
                                    alert('⚠️ ' + message);
                                } else if (type === 'info') {
                                    alert('ℹ️ ' + message);
                                } else {
                                    alert('✅ ' + message);
                                }
                            },

                            filteredLabels() {
                                if (!this.labelData.searchLabel) {
                                    return this.labelData.labels;
                                }
                                return this.labelData.labels.filter(label =>
                                    label.name.toLowerCase().includes(this.labelData.searchLabel.toLowerCase())
                                );
                            },


                            // Tambahkan method untuk handle error response
                            handleApiError(error, defaultMessage = 'Terjadi kesalahan') {
                                console.error('API Error:', error);

                                if (error.response) {
                                    // The request was made and the server responded with a status code
                                    // that falls out of the range of 2xx
                                    const message = error.response.data?.message || defaultMessage;
                                    this.showNotification(message, 'error');
                                } else if (error.request) {
                                    // The request was made but no response was received
                                    this.showNotification('Tidak ada response dari server', 'error');
                                } else {
                                    // Something happened in setting up the request that triggered an Error
                                    this.showNotification(defaultMessage, 'error');
                                }
                            },





                            // ✅ NEW: Method untuk mendapatkan content CKEditor
                            // ✅ PERBAIKI: Method untuk mendapatkan content CKEditor
                            // ✅ PERBAIKI: Method untuk mendapatkan content CKEditor dengan fallback yang lebih robust
                            getCKEditorContent(editorId) {
                                console.log('Getting content for editor:', editorId);

                                // Coba ambil dari instance CKEditor global
                                if (window.taskEditors && window.taskEditors[editorId]) {
                                    const content = window.taskEditors[editorId].getData();
                                    console.log('✅ Got content from CKEditor instance:', content);
                                    return content;
                                }

                                // Coba ambil dari element CKEditor langsung
                                const editorElement = document.querySelector(`#${editorId} + .ck-editor .ck-content`);
                                if (editorElement) {
                                    const content = editorElement.innerHTML;
                                    console.log('✅ Got content from editor element:', content);
                                    return content;
                                }

                                // Fallback: coba ambil dari textarea fallback
                                const fallbackTextarea = document.getElementById(editorId + '-fallback');
                                if (fallbackTextarea) {
                                    console.log('✅ Got content from fallback textarea:', fallbackTextarea.value);
                                    return fallbackTextarea.value;
                                }

                                // Fallback: coba ambil dari textarea biasa
                                const textarea = document.querySelector(`#${editorId}`);
                                if (textarea) {
                                    console.log('✅ Got content from textarea:', textarea.value);
                                    return textarea.value;
                                }

                                console.warn('❌ No editor or textarea found for:', editorId);
                                return '';
                            },

                            // ✅ NEW: Method untuk reset CKEditor
                            resetCKEditor(editorId) {
                                const editor = taskEditors[editorId];
                                if (editor) {
                                    editor.setData('');
                                }

                                const fallbackTextarea = document.getElementById(editorId + '-fallback');
                                if (fallbackTextarea) {
                                    fallbackTextarea.value = '';
                                }

                                const textarea = document.querySelector(`#${editorId}`);
                                if (textarea) {
                                    textarea.value = '';
                                }
                            },




                            // Update method resetTaskForm
                            resetTaskForm() {
                                console.log('🔄 Resetting task form...');

                                // Destroy CKEditor
                                const editorId = 'editor-catatan';
                                const el = document.getElementById(editorId);

                                if (el && el._editor) {
                                    try {
                                        el._editor.destroy()
                                            .then(() => {
                                                el._editor = null;
                                                el.innerHTML = '';
                                                if (window.taskEditors?.[editorId]) {
                                                    delete window.taskEditors[editorId];
                                                }
                                            })
                                            .catch(() => {
                                                el._editor = null;
                                                el.innerHTML = '';
                                            });
                                    } catch (err) {
                                        el._editor = null;
                                        el.innerHTML = '';
                                    }
                                }

                                // Reset form data
                                this.taskForm = {
                                    title: '',
                                    phase: '',
                                    members: [],
                                    is_secret: false,
                                    description: '',
                                    attachments: [], // ✅ Reset attachments
                                    checklists: [],
                                    labels: [],
                                    startDate: '',
                                    startTime: '',
                                    dueDate: '',
                                    dueTime: ''
                                };

                                // Reset label selected state
                                this.labelData.labels.forEach(label => {
                                    label.selected = false;
                                });

                                this.uploading = false;
                                this.uploadProgress = 0;
                                this.currentColumnId = null;

                                console.log('✅ Task form reset complete');

                                // Re-initialize editor
                                this.$nextTick(() => {
                                    setTimeout(() => {
                                        this.initializeTaskFormEditor();
                                    }, 300);
                                });
                            },

                            debugAttachmentState() {
                                console.log('📊 ATTACHMENT DEBUG:');
                                console.log('- taskForm.attachments:', this.taskForm.attachments);
                                console.log('- currentTask.attachments:', this.currentTask?.attachments);
                                console.log('- uploading:', this.uploading);
                                console.log('- uploadingDetail:', this.uploadingDetail);
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
                                if (!this.taskForm.attachments || !this.taskForm.attachments[index]) {
                                    console.error('❌ Invalid attachment index:', index);
                                    return;
                                }

                                const file = this.taskForm.attachments[index];

                                if (confirm(`Hapus file ${file.name}?`)) {
                                    // Hapus dari server jika sudah ada ID
                                    if (file.id && !file.id.toString().startsWith('temp-')) {
                                        this.deleteAttachmentFromServer(file.id);
                                    }

                                    // Hapus dari array
                                    this.taskForm.attachments.splice(index, 1);
                                    console.log('✅ File removed:', file.name);
                                    console.log('📊 Remaining attachments:', this.taskForm.attachments.length);
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
                                const completed = task.checklist.filter(item => item.is_done || item.done).length;
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
                            // Di dalam kanbanApp() - PERBAIKI method addNewColumn
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
                                        // Tambahkan kolom baru ke array
                                        this.boardColumns.push(data.column);

                                        // Reset form
                                        this.newListName = '';
                                        this.openModal = false;

                                        // ✅ PENTING: Inisialisasi Sortable untuk kolom baru
                                        this.$nextTick(() => {
                                            setTimeout(() => {
                                                this.initializeSortableForColumn(data.column.id);
                                            }, 100);
                                        });

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
                            // Di dalam kanbanApp() - PERBAIKI method initializeSortable
initializeSortable() {
    // Tunggu DOM siap
    this.$nextTick(() => {
        setTimeout(() => {
            this.boardColumns.forEach(column => {
                this.initializeSortableForColumn(column.id);
            });
        }, 300);
    });
},

                            // Di kanbanApp() - update method initializeSortableForColumn
                            // Di dalam kanbanApp() - PERBAIKI method ini
                            // Di dalam kanbanApp() - PERBAIKI method initializeSortableForColumn
initializeSortableForColumn(columnId) {
    // Tunggu sedikit untuk memastikan DOM sudah dirender
    this.$nextTick(() => {
        setTimeout(() => {
            const el = document.getElementById(`column-${columnId}`);
            
            if (el && !el._sortableInstance) {
                el._sortableInstance = new Sortable(el, {
                    group: {
                        name: 'kanban',
                        pull: true,
                        put: true
                    },
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    filter: '.ignore-elements', // Filter elemen yang tidak bisa didrag
                    preventOnFilter: false,
                    forceFallback: false, // Gunakan native HTML5 DnD
                    fallbackClass: 'sortable-fallback',
                    fallbackOnBody: true,
                    fallbackTolerance: 0,
                    scroll: true,
                    scrollSensitivity: 30,
                    scrollSpeed: 10,
                    bubbleScroll: true,
                    
                    // Event handlers
                    onStart: (evt) => {
                        evt.item.classList.add('dragging-active');
                        console.log('Drag started:', evt.item.dataset.taskId);
                    },
                    
                    onEnd: (evt) => {
                        evt.item.classList.remove('dragging-active');
                        this.handleTaskMove(evt, columnId);
                    },
                    
                    onAdd: (evt) => {
                        console.log('Task added to column:', columnId);
                    },
                    
                    onRemove: (evt) => {
                        console.log('Task removed from column:', columnId);
                    },
                    
                    // Untuk mengatasi blur text
                    setData: (dataTransfer, dragEl) => {
                        // Gunakan text/plain untuk mencegah browser rendering default
                        dataTransfer.setData('text/plain', dragEl.dataset.taskId);
                    },
                    
                    // Optional: Custom drag image
                    dragImage: (dragEl) => {
                        // Buat custom drag image untuk visual yang lebih baik
                        const dragImage = dragEl.cloneNode(true);
                        dragImage.style.opacity = '0.7';
                        dragImage.style.transform = 'rotate(5deg)';
                        dragImage.style.width = dragEl.offsetWidth + 'px';
                        dragImage.style.height = dragEl.offsetHeight + 'px';
                        return dragImage;
                    }
                });

                console.log('✅ Sortable initialized for column:', columnId);
            }
        }, 300);
    });
},




// Di dalam kanbanApp() - tambahkan methods ini
onDragStart(event, taskId) {
    console.log('Drag start for task:', taskId);
    
    // Tambahkan class untuk styling drag
    event.currentTarget.classList.add('dragging');
    
    // Set data transfer
    event.dataTransfer.setData('text/plain', taskId.toString());
    event.dataTransfer.effectAllowed = 'move';
    
    // Optional: Set custom drag image
    setTimeout(() => {
        event.currentTarget.style.opacity = '0.4';
    }, 0);
},

onDragEnd(event) {
    console.log('Drag end');
    event.currentTarget.classList.remove('dragging');
    event.currentTarget.style.opacity = '1';
},

onDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    event.currentTarget.classList.add('drag-over');
},

onDragLeave(event) {
    event.currentTarget.classList.remove('drag-over');
},

onDrop(event, columnId) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
    
    const taskId = event.dataTransfer.getData('text/plain');
    console.log('Drop task:', taskId, 'to column:', columnId);
    
    // Handle task move
    if (taskId) {
        this.moveTaskToColumn(taskId, columnId);
    }
},


// Di dalam kanbanApp() - tambahkan method ini
async moveTaskToColumn(taskId, columnId) {
    try {
        console.log('Moving task:', taskId, 'to column:', columnId);

        const response = await fetch('/tasks/update-column', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                task_id: taskId,
                board_column_id: columnId
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP Error:', response.status, errorText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
            // Update local state
            const taskIndex = this.tasks.findIndex(t => t.id == taskId);
            if (taskIndex !== -1) {
                this.tasks[taskIndex].board_column_id = columnId;
                this.tasks[taskIndex].status = data.new_status;

                this.showNotification(`Tugas dipindahkan ke ${data.new_column_name}`, 'success');
                console.log('Task moved successfully:', data);
            }
        } else {
            console.error('Failed to move task:', data.message);
            this.showNotification(`Gagal memindahkan tugas: ${data.message}`, 'error');
            
            // Reload tasks untuk sync ulang
            this.$nextTick(() => {
                this.loadKanbanTasks();
            });
        }
    } catch (error) {
        console.error('Error moving task:', error);
        this.showNotification('Gagal memindahkan tugas: ' + error.message, 'error');
        
        // Reload tasks untuk sync ulang
        this.$nextTick(() => {
            this.loadKanbanTasks();
        });
    }
},

                            // Update method handleTaskMove
                            // Di kanbanApp() - perbaiki method handleTaskMove
                            async handleTaskMove(evt, columnId) {
    const taskId = evt.item.dataset.taskId;
    const fromColumnId = evt.from.id.replace('column-', '');
    const toColumnId = evt.to.id.replace('column-', '');

    console.log('🔄 Drag & Drop Event:', {
        taskId,
        fromColumnId,
        toColumnId,
        oldIndex: evt.oldIndex,
        newIndex: evt.newIndex
    });

    if (fromColumnId === toColumnId) {
        console.log('⚠️ Task dipindahkan dalam kolom yang sama');
        return;
    }

                                try {
                                    console.log('Memindahkan task:', taskId, 'dari:', fromColumnId, 'ke:', toColumnId);

                                    // Update task column di database
                                    const response = await fetch('/tasks/update-column', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        },
                                        body: JSON.stringify({
                                            task_id: taskId,
                                            board_column_id: toColumnId
                                        })
                                    });

                                    // Handle response yang bukan JSON (misal error 404/500)
                                    if (!response.ok) {
                                        const errorText = await response.text();
                                        console.error('HTTP Error:', response.status, errorText);
                                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                                    }

                                    const data = await response.json();

                                    if (data.success) {
                                        // Update local state
                                        const taskIndex = this.tasks.findIndex(t => t.id == taskId);
                                        if (taskIndex !== -1) {
                                            this.tasks[taskIndex].board_column_id = toColumnId;
                                            this.tasks[taskIndex].status = data.new_status;

                                            this.showNotification(`Tugas dipindahkan ke ${data.new_column_name}`, 'success');
                                            console.log('Task berhasil dipindahkan:', data);
                                        }
                                    } else {
                                        console.error('Gagal update task column:', data.message);
                                        this.showNotification(`Gagal memindahkan tugas: ${data.message}`, 'error');

                                        // Revert visual move dengan reload data
                                        this.$nextTick(() => {
                                            this.loadKanbanTasks();
                                        });
                                    }
                                } catch (error) {
                                    console.error('Error updating task column:', error);
                                    this.showNotification('Gagal memindahkan tugas: ' + error.message, 'error');

                                    // Revert visual move dengan reload data
                                    this.$nextTick(() => {
                                        this.loadKanbanTasks();
                                    });
                                }
                            },


                            getStatusText(status) {
                                const statusMap = {
                                    'todo': 'To Do',
                                    'inprogress': 'Dikerjakan',
                                    'done': 'Selesai',
                                    'cancel': 'Batal'
                                };

                                // Jika status ada di mapping, gunakan yang ada
                                if (statusMap[status]) {
                                    return statusMap[status];
                                }

                                // Untuk status custom, format dari snake_case ke Title Case
                                // Contoh: 'review_klien' menjadi 'Review Klien'
                                return status.split('_')
                                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                                    .join(' ');
                            },

                            // ✅ NEW: Method untuk mendapatkan tasks berdasarkan kolom
                            getTasksByColumn(columnId) {
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
                            // Update method init() untuk inisialisasi yang lebih baik
init() {
    try {
        // Inisialisasi state dengan nilai default
        this.currentTask = {
            is_secret: false,
            labels: [],
            startDate: '',
            startTime: '',
            dueDate: '',
            dueTime: '',
            members: []
        };

        this.taskForm = {
            title: '',
            phase: '',
            members: [],
            is_secret: false,
            notes: '',
            description: '',
            attachments: [],
            checklists: [],
            labels: [],
            startDate: '',
            startTime: '',
            dueDate: '',
            dueTime: ''
        };

        this.labelData = {
            labels: [],
            colors: [],
            selectedLabelIds: [],
            newLabelName: '',
            newLabelColor: null,
            searchLabel: ''
        };

        // Load data
        this.loadBoardColumns();
        this.loadKanbanTasks();
        this.loadTimelineData();
        this.loadWorkspaceMembers();
        this.loadLabels();
        this.loadColors();
        this.uploadingDetail = false;
        this.uploadProgressDetail = 0;

        console.log('✅ Aplikasi initialized dengan semua state');
        
        // Inisialisasi Sortable setelah semua data dimuat
        setTimeout(() => {
            this.initializeSortable();
        }, 1000);
        
    } catch (error) {
        console.error('❌ Error initializing app:', error);
    }
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
                                // Try meta tag first
                                const metaTag = document.querySelector('meta[name="csrf-token"]');
                                if (metaTag) {
                                    return metaTag.getAttribute('content');
                                }

                                // Try input field
                                const inputTag = document.querySelector('input[name="_token"]');
                                if (inputTag) {
                                    return inputTag.value;
                                }

                                // Try script data attribute
                                const scriptTag = document.querySelector('script[data-csrf]');
                                if (scriptTag) {
                                    return scriptTag.dataset.csrf;
                                }

                                console.error('❌ CSRF Token not found!');
                                return '';
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
                            // Di Alpine.js - perbaiki method saveTaskLabels untuk edit mode
                            // Di dalam kanbanApp() - GANTI method saveTaskLabels
                            async saveTaskLabels(taskId = null) {
                                try {
                                    const selectedLabelIds = this.labelData.labels
                                        .filter(label => label.selected)
                                        .map(label => label.id);

                                    console.log('Menyimpan labels:', selectedLabelIds, 'untuk task:', taskId);

                                    // ✅ UNTUK TASK BARU (belum ada ID)
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

                                    // ✅ UNTUK TASK YANG SUDAH ADA (EDIT MODE)
                                    // HANYA UPDATE STATE LOKAL, TIDAK LANGSUNG SAVE KE DATABASE
                                    if (this.isEditMode && this.currentTask) {
                                        const selectedLabels = this.labelData.labels
                                            .filter(label => label.selected)
                                            .map(label => ({
                                                id: label.id,
                                                name: label.name,
                                                color: label.color.rgb
                                            }));

                                        // Update currentTask.labels (state lokal)
                                        this.currentTask.labels = selectedLabels;

                                        // Reset selection
                                        this.labelData.labels.forEach(label => label.selected = false);
                                        this.openLabelModal = false;

                                        console.log('✅ Label di-stage (belum tersimpan ke database):', selectedLabels);
                                        this.showNotification('Label berhasil dipilih. Klik "Simpan Perubahan" untuk menyimpan.',
                                            'info');
                                        return;
                                    }

                                    // ✅ FALLBACK: Jika bukan edit mode, langsung save
                                    const response = await fetch(`/tasks/${taskId}/labels/update`, {
                                        method: 'PUT',
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
                                        if (this.currentTask) {
                                            this.currentTask.labels = data.labels;
                                        }

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
                                        // Update selected state dengan benar
                                        this.labelData.labels.forEach(label => {
                                            label.selected = data.labels.some(taskLabel =>
                                                taskLabel.id === label.id
                                            );
                                        });

                                        console.log('Loaded task labels:', data.labels);
                                    }
                                } catch (error) {
                                    console.error('Error loading task labels:', error);
                                }
                            },




                            // ✅ NEW: Method untuk menghapus label yang sudah dipilih
                            removeSelectedLabel(labelId) {
                                this.taskForm.labels = this.taskForm.labels.filter(label => label.id !== labelId);

                                // Juga update selected state di labelData
                                const label = this.labelData.labels.find(l => l.id === labelId);
                                if (label) {
                                    label.selected = false;
                                }

                                console.log('Label dihapus:', labelId, 'Labels tersisa:', this.taskForm.labels);
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
                            // ✅ PERBAIKI: Method untuk membuka modal label
                            // Di dalam kanbanApp() - UPDATE method openLabelModalForTask
                            openLabelModalForTask(task = null) {
                                this.openLabelModal = true;
                                this.labelData.searchLabel = '';

                                if (task && task.id) {
                                    // ✅ UNTUK EDIT MODE: Sync selected state dari currentTask.labels
                                    if (this.isEditMode && this.currentTask) {
                                        this.labelData.labels.forEach(label => {
                                            // Cek apakah label ini ada di currentTask.labels
                                            const isSelected = this.currentTask.labels.some(selectedLabel =>
                                                selectedLabel.id === label.id
                                            );
                                            label.selected = isSelected;
                                        });

                                        console.log('✅ Synced label selection from currentTask');
                                    } else {
                                        // Load dari database jika bukan edit mode
                                        this.loadTaskLabels(task.id);
                                    }
                                } else {
                                    // Untuk task baru - sync dengan taskForm.labels
                                    this.labelData.labels.forEach(label => {
                                        const isSelected = this.taskForm.labels.some(selectedLabel =>
                                            selectedLabel.id === label.id
                                        );
                                        label.selected = isSelected;
                                    });
                                }
                            },




                            // ✅ NEW: Checklist Methods
                            addChecklistItem() {
                                const newItem = {
                                    id: 'temp-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                                    title: '',
                                    is_done: false,
                                    position: this.taskForm.checklists.length
                                };

                                this.taskForm.checklists.push(newItem);

                                // Focus ke input baru
                                this.$nextTick(() => {
                                    const inputs = document.querySelectorAll('#checklist-container input[type="text"]');
                                    if (inputs.length > 0) {
                                        inputs[inputs.length - 1].focus();
                                    }
                                });
                            },

                            removeChecklistItem(index) {
                                if (confirm('Hapus item checklist ini?')) {
                                    const item = this.taskForm.checklists[index];

                                    // Jika item sudah ada di database, hapus via API
                                    if (item.id && !item.id.startsWith('temp-')) {
                                        this.deleteChecklistFromAPI(item.id);
                                    }

                                    this.taskForm.checklists.splice(index, 1);
                                    this.updateChecklistPositions();
                                }
                            },

                            async updateChecklistItem(item) {
                                // Jika item sudah ada di database, update via API
                                if (item.id && !item.id.startsWith('temp-')) {
                                    await this.updateChecklistInAPI(item);
                                }
                            },

                            getChecklistProgress() {
                                if (this.taskForm.checklists.length === 0) return 0;
                                const completed = this.taskForm.checklists.filter(item => item.is_done).length;
                                return Math.round((completed / this.taskForm.checklists.length) * 100);
                            },

                            getCompletedChecklists() {
                                return this.taskForm.checklists.filter(item => item.is_done).length;
                            },

                            updateChecklistPositions() {
                                this.taskForm.checklists.forEach((item, index) => {
                                    item.position = index;
                                });
                            },

                            // ✅ NEW: API Methods untuk Checklist
                            async createChecklistInAPI(taskId, checklistData) {
                                try {
                                    const response = await fetch('/tasks/checklists', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        },
                                        body: JSON.stringify({
                                            task_id: taskId,
                                            title: checklistData.title
                                        })
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        // Update ID temporary ke ID real
                                        const index = this.taskForm.checklists.findIndex(item => item.id === checklistData.id);
                                        if (index !== -1) {
                                            this.taskForm.checklists[index].id = data.checklist.id;
                                        }
                                        return data.checklist;
                                    }
                                } catch (error) {
                                    console.error('Error creating checklist:', error);
                                }
                                return null;
                            },

                            async updateChecklistInAPI(checklistItem) {
                                try {
                                    const response = await fetch(`/tasks/checklists/${checklistItem.id}`, {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        },
                                        body: JSON.stringify({
                                            title: checklistItem.title,
                                            is_done: checklistItem.is_done
                                        })
                                    });

                                    const data = await response.json();
                                    return data.success;
                                } catch (error) {
                                    console.error('Error updating checklist:', error);
                                    return false;
                                }
                            },

                            async deleteChecklistFromAPI(checklistId) {
                                try {
                                    const response = await fetch(`/tasks/checklists/${checklistId}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        }
                                    });

                                    const data = await response.json();
                                    return data.success;
                                } catch (error) {
                                    console.error('Error deleting checklist:', error);
                                    return false;
                                }
                            },



                            // Methods untuk handle file upload
                            async handleFileSelect(event) {
                                const files = Array.from(event.target.files);
                                console.log('📁 Files selected:', files.length);

                                if (files.length === 0) {
                                    console.warn('⚠️ No files selected');
                                    return;
                                }

                                await this.processFiles(files);
                                event.target.value = ''; // Reset input
                            },

                            async handleFileDrop(event) {
                                const files = Array.from(event.dataTransfer.files);
                                console.log('📁 Files dropped:', files.length);

                                if (files.length === 0) {
                                    console.warn('⚠️ No files dropped');
                                    return;
                                }

                                await this.processFiles(files);
                            },

                            async processFiles(files) {
                                console.log('🔄 Processing', files.length, 'files...');

                                for (const file of files) {
                                    console.log('📤 Processing file:', file.name);
                                    await this.uploadFile(file);
                                }

                                console.log('✅ All files processed');
                            },

                            // Di dalam kanbanApp() - perbaiki method uploadFile
                            // Di dalam kanbanApp() - perbaiki method uploadFile
                            async uploadFile(file) {
                                this.uploading = true;
                                this.uploadProgress = 0;

                                try {
                                    // Validasi
                                    const allowedTypes = [
                                        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.ms-powerpoint',
                                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                        'text/plain',
                                        'application/zip',
                                        'application/x-rar-compressed'
                                    ];

                                    if (!allowedTypes.includes(file.type)) {
                                        throw new Error('Tipe file tidak didukung');
                                    }

                                    if (file.size > 10 * 1024 * 1024) {
                                        throw new Error('File terlalu besar. Maksimal 10MB');
                                    }

                                    const formData = new FormData();
                                    formData.append('file', file);
                                    formData.append('attachable_type', 'App\\Models\\Task');

                                    console.log('📤 Uploading file:', file.name, file.size, 'bytes');

                                    const response = await fetch('/tasks/attachments/upload', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        },
                                        body: formData
                                    });

                                    if (!response.ok) {
                                        const errorData = await response.json();
                                        console.error('❌ Upload error:', response.status, errorData);
                                        throw new Error(errorData.message || `Upload gagal: ${response.status}`);
                                    }

                                    const data = await response.json();
                                    console.log('✅ Upload response:', data);

                                    if (data.success && data.attachment) {
                                        // ✅ Gunakan data dari backend response
                                        const uploadedFile = {
                                            id: data.attachment.id,
                                            name: data.attachment.file_name || file.name,
                                            size: data.attachment.file_size || file.size,
                                            type: this.getFileTypeFromMime(data.attachment.mime_type || file.type),
                                            url: data.attachment.file_url.startsWith('http') ?
                                                data.attachment.file_url : '/storage/' + data.attachment.file_url,
                                            serverId: data.attachment.id,
                                            uploaded_at: data.attachment.uploaded_at || new Date().toISOString()
                                        };

                                        this.taskForm.attachments.push(uploadedFile);

                                        console.log('✅ File added:', uploadedFile);
                                        console.log('📊 Total attachments:', this.taskForm.attachments.length);

                                        // this.showNotification(`File ${file.name} berhasil diupload`, 'success');
                                        return uploadedFile;
                                    } else {
                                        throw new Error(data.message || 'Upload gagal');
                                    }
                                } catch (error) {
                                    console.error('❌ Error uploading file:', error);
                                    this.showNotification(`Gagal upload file: ${error.message}`, 'error');
                                    return null;
                                } finally {
                                    this.uploading = false;
                                    this.uploadProgress = 0;
                                }
                            },

                            // ✅ TAMBAHKAN helper method ini jika belum ada
                            getFileTypeFromMime(mimeType) {
                                if (!mimeType) return 'other';

                                if (mimeType.startsWith('image/')) return 'image';
                                if (mimeType === 'application/pdf') return 'pdf';
                                if (mimeType.includes('word') || mimeType.includes('document')) return 'doc';
                                if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'xls';
                                if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'ppt';
                                if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('compressed'))
                                    return 'archive';

                                return 'other';
                            },

                            // ✅ TAMBAHKAN: Helper untuk extract nama file dari URL
                            getFileNameFromUrl(fileUrl) {
                                return fileUrl.split('/').pop() || 'unknown';
                            },
                            getFileType(mimeType) {
                                if (mimeType.startsWith('image/')) return 'image';
                                if (mimeType === 'application/pdf') return 'pdf';
                                return 'other';
                            },

                            // Di kanbanApp() - tambahkan method ini
                            formatFileSize(bytes) {
                                if (!bytes || bytes === 0) return '0 Bytes';

                                const k = 1024;
                                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                                const i = Math.floor(Math.log(bytes) / Math.log(k));

                                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                            },

                            removeAttachment(index) {
                                const file = this.taskForm.attachments[index];
                                if (confirm(`Hapus file ${file.name}?`)) {
                                    // Jika file sudah diupload ke server, hapus dari server juga
                                    if (file.id && !file.id.toString().startsWith('temp-')) {
                                        this.deleteAttachmentFromServer(file.id);
                                    }
                                    this.taskForm.attachments.splice(index, 1);
                                }
                            },

                            async deleteAttachmentFromServer(attachmentId) {
                                try {
                                    const response = await fetch(`/tasks/attachments/${attachmentId}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken()
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        console.log('✅ Attachment deleted from server:', attachmentId);
                                    } else {
                                        console.error('❌ Failed to delete from server:', data.message);
                                    }
                                } catch (error) {
                                    console.error('❌ Error deleting attachment:', error);
                                }
                            },

                            previewFile(file) {
                                console.log('👁️ Preview file:', file);

                                if (file.type === 'image' || (file.url && /\.(jpg|jpeg|png|gif|webp)$/i.test(file.url))) {
                                    this.previewModal = {
                                        open: true,
                                        url: file.url,
                                        file: file
                                    };
                                } else {
                                    // Untuk file non-gambar, buka di tab baru
                                    window.open(file.url, '_blank');
                                }
                            },



                            async loadKanbanTasks() {
                                try {
                                    const workspaceId = this.getCurrentWorkspaceId();
                                    if (!workspaceId) return;

                                    const response = await fetch(`/tasks/workspace/${workspaceId}/kanban-tasks`);
                                    const data = await response.json();

                                    if (data.success) {
                                        // Transform data untuk kompatibilitas dengan frontend
                                        this.tasks = data.tasks.map(task => ({
                                            id: task.id,
                                            title: task.title,
                                            phase: task.phase,
                                            status: task.board_column_id, // Gunakan board_column_id sebagai status
                                            board_column_id: task.board_column_id,
                                            members: task.assignees, // Assignees sebagai members
                                            secret: task.is_secret,
                                            is_secret: task.is_secret,
                                            notes: task.description,
                                            description: task.description,
                                            attachments: [], // Bisa di-load terpisah jika perlu
                                            labels: task.labels,
                                            checklist: task.checklists.map(cl => ({
                                                name: cl.title,
                                                done: cl.is_done,
                                                is_done: cl.is_done,
                                                position: cl.position
                                            })),
                                            startDate: task.start_datetime ? task.start_datetime.split('T')[0] : '',
                                            startTime: task.start_datetime ? task.start_datetime.split('T')[1]
                                                .substring(0, 5) : '',
                                            dueDate: task.due_datetime ? task.due_datetime.split('T')[0] : '',
                                            dueTime: task.due_datetime ? task.due_datetime.split('T')[1].substring(0,
                                                5) : '',
                                            priority: task.priority,
                                            progress_percentage: task.progress_percentage,
                                            is_overdue: task.is_overdue,
                                            created_at: task.created_at,
                                            updated_at: task.updated_at
                                        }));

                                        console.log('✅ Tasks loaded from database:', this.tasks.length);
                                    } else {
                                        console.error('Gagal memuat tasks:', data.message);
                                    }
                                } catch (error) {
                                    console.error('Error loading kanban tasks:', error);
                                }
                            },


                            async loadTimelineData() {
                                this.loadingTimeline = true;
                                try {
                                    const workspaceId = this.getCurrentWorkspaceId();
                                    if (!workspaceId) return;

                                    const response = await fetch(`/tasks/workspace/${workspaceId}/timeline`);
                                    const data = await response.json();

                                    if (data.success) {
                                        this.timelineData = data.timeline_data;
                                        console.log('Timeline data loaded:', this.timelineData);
                                    } else {
                                        console.error('Gagal memuat timeline data:', data.message);
                                    }
                                } catch (error) {
                                    console.error('Error loading timeline data:', error);
                                } finally {
                                    this.loadingTimeline = false;
                                }
                            },

                            // Update method getProjectPhases() untuk menggunakan data real
                            getProjectPhases() {
                                if (this.timelineData && this.timelineData.length > 0) {
                                    return this.timelineData.map(phase => ({
                                        id: phase.id,
                                        name: phase.name,
                                        normalized_name: phase.normalized_name,
                                        total_tasks: phase.total_tasks,
                                        completed_tasks: phase.completed_tasks,
                                        progress_percentage: phase.progress_percentage,
                                        start_date: phase.start_date,
                                        end_date: phase.end_date,
                                        duration: phase.duration,
                                        duration_percentage: phase.duration_percentage || 10, // Fallback 10% jika tidak ada
                                        description: `${phase.completed_tasks} dari ${phase.total_tasks} tugas selesai`
                                    }));
                                }

                                // Fallback dummy data jika tidak ada data real
                                return [{
                                        id: 1,
                                        name: 'Perencanaan',
                                        description: '0 dari 0 tugas selesai',
                                        total_tasks: 0,
                                        completed_tasks: 0,
                                        progress_percentage: 0
                                    },
                                    {
                                        id: 2,
                                        name: 'Analisis',
                                        description: '0 dari 0 tugas selesai',
                                        total_tasks: 0,
                                        completed_tasks: 0,
                                        progress_percentage: 0
                                    }
                                    // ... tambahkan phase lainnya sesuai kebutuhan
                                ];
                            },

                            // 🔧 PERBAIKI: Method getTasksByPhaseId
                            // Di dalam kanbanApp() - UPDATE method ini
                            getTasksByPhaseId(phaseId) {
                                // ✅ GUNAKAN DATA DARI BACKEND
                                if (this.timelineData && this.timelineData.length > 0) {
                                    const phase = this.timelineData.find(p => p.id === phaseId);
                                    return phase ? phase.tasks : [];
                                }

                                // ✅ FALLBACK: Filter dengan normalisasi case-insensitive
                                // Cari phase dengan ID yang cocok
                                const targetPhase = this.getProjectPhases().find(p => p.id === phaseId);
                                if (!targetPhase) return [];

                                // Normalisasi nama phase target
                                const normalizedTarget = targetPhase.normalized_name ||
                                    targetPhase.name.toLowerCase().trim().replace(/\s+/g, ' ');

                                // Filter tasks dengan normalisasi yang sama
                                return this.tasks.filter(task => {
                                    if (!task.phase) return false;

                                    // Normalisasi phase name dari task
                                    const taskPhaseNormalized = task.phase.toLowerCase().trim().replace(/\s+/g, ' ');

                                    return taskPhaseNormalized === normalizedTarget;
                                });
                            },

                            // Update method showPhaseTasks() untuk menggunakan data real
                            // Di dalam kanbanApp() - tambahkan method ini

                            // Method untuk format tanggal
                            formatDate(dateString) {
                                if (!dateString) return 'Tidak ada tanggal';

                                try {
                                    const date = new Date(dateString);

                                    // Format: 12 Nov 2025
                                    return date.toLocaleDateString('id-ID', {
                                        day: 'numeric',
                                        month: 'short',
                                        year: 'numeric'
                                    });
                                } catch (error) {
                                    console.error('Error formatting date:', error);
                                    return 'Tanggal tidak valid';
                                }
                            },

                            // Update method showPhaseTasks untuk include date range
                            showPhaseTasks(phaseId) {
                                let phase;
                                let phaseTasks = [];

                                if (this.timelineData && this.timelineData.length > 0) {
                                    phase = this.timelineData.find(p => p.id === phaseId);
                                    if (phase) {
                                        phaseTasks = phase.tasks;
                                    }
                                } else {
                                    // Fallback logic
                                    const phaseMap = {
                                        1: 'Perencanaan',
                                        2: 'Analisis',
                                        3: 'Desain',
                                        4: 'Development',
                                        5: 'Testing',
                                        6: 'Deployment'
                                    };
                                    const phaseName = phaseMap[phaseId];
                                    phaseTasks = this.tasks.filter(task => {
                                        const taskPhase = task.phase ? task.phase.toLowerCase().trim().replace(/\s+/g, ' ') :
                                            '';
                                        const targetPhase = phaseName.toLowerCase().trim().replace(/\s+/g, ' ');
                                        return taskPhase === targetPhase;
                                    });

                                    phase = {
                                        name: phaseName,
                                        description: `Phase ${phaseName}`,
                                        total_tasks: phaseTasks.length,
                                        completed_tasks: phaseTasks.filter(task => task.status === 'done').length,
                                        progress_percentage: phaseTasks.length > 0 ?
                                            Math.round((phaseTasks.filter(task => task.status === 'done').length / phaseTasks
                                                .length) * 100) : 0
                                    };
                                }

                                if (!phase) return;

                                this.selectedPhase = phaseId;
                                this.phaseModal = {
                                    open: true,
                                    title: phase.name,
                                    description: phase.description ||
                                        `${phase.completed_tasks} dari ${phase.total_tasks} tugas selesai`,
                                    tasks: phaseTasks,
                                    stats: {
                                        total: phase.total_tasks,
                                        completed: phase.completed_tasks,
                                        in_progress: phaseTasks.filter(task => task.status === 'inprogress').length,
                                        todo: phaseTasks.filter(task => task.status === 'todo').length,
                                        progress: phase.progress_percentage
                                    },
                                    // Tambahkan date range information
                                    start_date: phase.start_date,
                                    end_date: phase.end_date,
                                    duration: phase.duration || 0,
                                    progress: phase.progress_percentage,
                                    totalTasks: phase.total_tasks,
                                    completedTasks: phase.completed_tasks
                                };
                            },


                            // Tambahkan method ini di dalam kanbanApp() di Alpine.js

                            // Method untuk menghitung progress keseluruhan
                            getOverallProgress() {
                                if (!this.timelineData || this.timelineData.length === 0) return 0;

                                const totalTasks = this.timelineData.reduce((sum, phase) => sum + phase.total_tasks, 0);
                                const completedTasks = this.timelineData.reduce((sum, phase) => sum + phase.completed_tasks, 0);

                                return totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
                            },

                            // Method untuk mendapatkan jumlah tugas in progress
                            getInProgressTasks(phaseId) {
                                const phase = this.timelineData.find(p => p.id === phaseId);
                                if (!phase || !phase.tasks) return 0;

                                return phase.tasks.filter(task => task.status === 'inprogress').length;
                            },

                            // Method untuk mendapatkan jumlah tugas todo
                            getTodoTasks(phaseId) {
                                const phase = this.timelineData.find(p => p.id === phaseId);
                                if (!phase || !phase.tasks) return 0;

                                return phase.tasks.filter(task => task.status === 'todo').length;
                            },

                            // Method untuk mendapatkan deskripsi phase
                            getPhaseDescription(phase) {
                                const completed = phase.completed_tasks || 0;
                                const total = phase.total_tasks || 0;
                                const inProgress = this.getInProgressTasks(phase.id);
                                const todo = this.getTodoTasks(phase.id);

                                let description = `${completed} selesai`;

                                if (inProgress > 0) {
                                    description += `, ${inProgress} dalam progress`;
                                }

                                if (todo > 0) {
                                    description += `, ${todo} belum mulai`;
                                }

                                return description;
                            },


                            async initializeTaskFormEditor() {
                                const editorId = 'editor-catatan';
                                const el = document.getElementById(editorId);

                                if (!el) {
                                    console.warn('❌ Task form editor element not found');
                                    return;
                                }

                                // ✅ CRITICAL: Prevent duplicate initialization
                                if (el._editor || window.taskEditors?.[editorId]) {
                                    console.log('⚠️ Task form editor already exists');
                                    return;
                                }

                                // Clean existing CKEditor DOM
                                const existingCKEditor = el.querySelector('.ck-editor');
                                if (existingCKEditor) {
                                    existingCKEditor.remove();
                                }

                                el.innerHTML = '';

                                try {
                                    const editor = await ClassicEditor.create(el, {
                                        toolbar: {
                                            items: [
                                                'undo', 'redo', '|',
                                                'heading', '|',
                                                'bold', 'italic', 'underline', 'strikethrough', '|',
                                                'fontColor', 'fontBackgroundColor', '|',
                                                'link', 'blockQuote', 'code', '|',
                                                'bulletedList', 'numberedList', '|',
                                                'insertTable',
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
                                            toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block',
                                                'imageStyle:side'
                                            ]
                                        },
                                        table: {
                                            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                                        },
                                        mediaEmbed: {
                                            previewsInData: true
                                        },
                                        placeholder: 'Tulis catatan tugas di sini...'
                                    });

                                    // ✅ Store reference
                                    el._editor = editor;
                                    if (!window.taskEditors) {
                                        window.taskEditors = {};
                                    }
                                    window.taskEditors[editorId] = editor;

                                    console.log('✅ Task form editor initialized successfully');
                                    return editor;

                                } catch (error) {
                                    console.error('❌ Failed to initialize task form editor:', error);

                                    // Fallback to textarea
                                    el.innerHTML = `
            <textarea id="${editorId}-fallback" 
                      class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none"
                      placeholder="Tulis catatan tugas di sini..."></textarea>
        `;
                                }
                            },


                            // Di dalam kanbanApp() - tambahkan method ini
                            async deleteCustomColumn(columnId) {
                                if (!confirm('Hapus kolom ini? Semua tugas harus dipindahkan terlebih dahulu.')) {
                                    return;
                                }

                                try {
                                    console.log('🔄 Deleting custom column:', columnId);

                                    const response = await fetch(`/tasks/custom-columns/${columnId}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': this.getCsrfToken(),
                                            'Accept': 'application/json'
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        // Hapus dari array boardColumns
                                        this.boardColumns = this.boardColumns.filter(col => col.id !== columnId);

                                        // Tutup menu jika terbuka
                                        this.openListMenu = null;

                                        this.showNotification('Kolom berhasil dihapus', 'success');

                                        console.log('✅ Custom column deleted successfully');
                                    } else {
                                        throw new Error(data.message || 'Gagal menghapus kolom');
                                    }
                                } catch (error) {
                                    console.error('❌ Error deleting custom column:', error);
                                    this.showNotification('Gagal menghapus kolom: ' + error.message, 'error');
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








                    // Alpine.js component untuk komentar tugas
                    function taskCommentSection() {
                        return {
                            comments: [],
                            replyView: {
                                active: false,
                                parentComment: null
                            },
                            currentUserAvatar: '{{ Auth::user()->avatar ?? 'https://i.pravatar.cc/40?img=11' }}',
                            loading: false,
                            error: null,
                            taskId: null,
                            editorInstances: {}, // ✅ TAMBAHKAN: Track semua editor instances

                            init() {
                                console.log('🔄 Initializing comment section...');

                                const parentEl = this.$el.closest('[x-data*="kanbanApp"]');
                                const parentData = parentEl ? Alpine.$data(parentEl) : null;

                                if (parentData && parentData.currentTask) {
                                    this.taskId = parentData.currentTask.id;
                                    console.log('✅ Task ID initialized:', this.taskId);

                                    if (parentData.currentTask.comments) {
                                        this.comments = this.formatComments(parentData.currentTask.comments);
                                        console.log('✅ Loaded', this.comments.length, 'comments from currentTask');
                                    }
                                }

                                // ✅ PERBAIKI: Delay initialization untuk memastikan DOM ready
                                this.$nextTick(() => {
                                    setTimeout(() => {
                                        this.initializeMainEditor();
                                    }, 300);
                                });

                                // Watch for task changes
                                let lastTaskId = this.taskId;
                                const watchInterval = setInterval(() => {
                                    const parentEl = this.$el.closest('[x-data*="kanbanApp"]');
                                    const parentData = parentEl ? Alpine.$data(parentEl) : null;
                                    const newTaskId = parentData?.currentTask?.id;

                                    if (newTaskId && newTaskId !== lastTaskId) {
                                        console.log('📝 Task changed from', lastTaskId, 'to', newTaskId);
                                        lastTaskId = newTaskId;
                                        this.taskId = newTaskId;
                                        this.error = null;

                                        // ✅ PERBAIKI: Destroy old editors before loading new task
                                        this.destroyAllEditors();

                                        if (parentData.currentTask.comments) {
                                            this.comments = this.formatComments(parentData.currentTask.comments);
                                        }

                                        // ✅ PERBAIKI: Re-initialize main editor with delay
                                        this.$nextTick(() => {
                                            setTimeout(() => {
                                                this.initializeMainEditor();
                                            }, 300);
                                        });
                                    }
                                }, 500);

                                this.$watch('$el', (value) => {
                                    if (!value) {
                                        clearInterval(watchInterval);
                                        this.destroyAllEditors(); // ✅ TAMBAHKAN: Cleanup on component destroy
                                    }
                                });
                            },

                            formatComments(comments) {
                                return comments.map(c => ({
                                    ...c,
                                    replies: c.replies || [],
                                    author: c.author || {
                                        id: c.user?.id,
                                        name: c.user?.full_name || c.user?.name || 'Unknown User',
                                        avatar: c.user?.avatar || 'https://i.pravatar.cc/40?img=0'
                                    }
                                }));
                            },

                            async initializeMainEditor() {
                                const editorId = 'task-main-comment-editor';
                                const el = document.getElementById(editorId);

                                if (!el) {
                                    console.warn('❌ Editor element not found:', editorId);
                                    return;
                                }

                                // ✅ CRITICAL: Check if editor already exists
                                if (el._editor || this.editorInstances[editorId]) {
                                    console.log('⚠️ Editor already exists for:', editorId);
                                    return; // Prevent duplicate initialization
                                }

                                // ✅ CRITICAL: Check for existing CKEditor instances
                                const existingCKEditor = el.querySelector('.ck-editor');
                                if (existingCKEditor) {
                                    console.log('⚠️ Found existing CKEditor DOM, cleaning up...');
                                    existingCKEditor.remove();
                                }

                                // Clear element content
                                el.innerHTML = '';

                                const commentId = this.generateUUID();
                                window.currentMainCommentId = commentId;

                                try {
                                    const editor = await ClassicEditor.create(el, {
                                        toolbar: {
                                            items: [
                                                'undo', 'redo', '|',
                                                'heading', '|',
                                                'bold', 'italic', '|',
                                                'link', 'blockQuote', '|',
                                                'bulletedList', 'numberedList', '|',
                                                'insertTable'
                                            ],
                                            shouldNotGroupWhenFull: true
                                        },
                                        placeholder: 'Tulis komentar Anda...'
                                    });

                                    // ✅ CRITICAL: Store reference to prevent duplicates
                                    el._editor = editor;
                                    this.editorInstances[editorId] = editor;

                                    console.log('✅ Main editor initialized successfully');

                                    // Tambahkan tombol upload
                                    this.insertUploadImageButton(editor, commentId);
                                    this.insertUploadFileButton(editor, commentId);

                                } catch (err) {
                                    console.error('❌ Failed to init main editor:', err);

                                    // Fallback to textarea
                                    el.innerHTML = `
                    <textarea id="${editorId}-fallback" 
                              class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none"
                              placeholder="Tulis komentar Anda..."></textarea>
                `;
                                }
                            },

                            async initializeReplyEditor(commentId) {
                                const editorId = `task-reply-editor-${commentId}`;
                                const el = document.getElementById(editorId);

                                if (!el) {
                                    console.warn('❌ Reply editor element not found:', editorId);
                                    return;
                                }

                                // ✅ CRITICAL: Prevent duplicate initialization
                                if (el._editor || this.editorInstances[editorId]) {
                                    console.log('⚠️ Reply editor already exists for:', editorId);
                                    return;
                                }

                                // ✅ CRITICAL: Clean up existing CKEditor DOM
                                const existingCKEditor = el.querySelector('.ck-editor');
                                if (existingCKEditor) {
                                    console.log('⚠️ Found existing reply CKEditor DOM, cleaning up...');
                                    existingCKEditor.remove();
                                }

                                el.innerHTML = '';

                                const replyId = this.generateUUID();
                                window[`currentReplyId_${commentId}`] = replyId;

                                try {
                                    const editor = await ClassicEditor.create(el, {
                                        toolbar: {
                                            items: [
                                                'undo', 'redo', '|',
                                                'bold', 'italic', '|',
                                                'link', 'blockQuote', '|',
                                                'bulletedList', 'numberedList'
                                            ],
                                            shouldNotGroupWhenFull: true
                                        },
                                        placeholder: 'Tulis balasan Anda...'
                                    });

                                    // ✅ CRITICAL: Store reference
                                    el._editor = editor;
                                    this.editorInstances[editorId] = editor;

                                    console.log('✅ Reply editor initialized:', editorId);

                                    // Tambahkan tombol upload
                                    this.insertUploadImageButton(editor, replyId);
                                    this.insertUploadFileButton(editor, replyId);

                                } catch (err) {
                                    console.error('❌ Failed to init reply editor:', err);

                                    // Fallback to textarea
                                    el.innerHTML = `
                    <textarea id="${editorId}-fallback" 
                              class="w-full min-h-[100px] p-3 border border-gray-300 rounded-lg bg-white resize-none"
                              placeholder="Tulis balasan Anda..."></textarea>
                `;
                                }
                            },

                            destroyAllEditors() {
                                console.log('🔄 Destroying all editors...');

                                // Destroy tracked instances
                                Object.keys(this.editorInstances).forEach(editorId => {
                                    this.destroyEditor(editorId);
                                });

                                // Clean up any orphaned CKEditor instances
                                document.querySelectorAll('.ck-editor').forEach(ckEditor => {
                                    const parent = ckEditor.parentElement;
                                    if (parent) {
                                        console.log('🧹 Cleaning up orphaned CKEditor:', parent.id);
                                        ckEditor.remove();
                                        parent.innerHTML = '';
                                    }
                                });

                                this.editorInstances = {};
                            },

                            insertUploadImageButton(editor, commentId) {
                                const toolbarEl = editor.ui.view.toolbar.element;
                                const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'ck ck-button ck-off';
                                btn.title = 'Upload Image';
                                btn.innerHTML = `
                <span class="ck-button__label" style="display:flex;align-items:center;gap:4px;padding:2px 4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM5 19l4.5-6 3.5 4.5 2.5-3L19 19H5z"/>
                    </svg>
                    <span style="font-size:11px;">Image</span>
                </span>
            `;
                                btn.style.cursor = 'pointer';

                                btn.addEventListener('click', () => {
                                    const input = document.createElement('input');
                                    input.type = 'file';
                                    input.accept = 'image/*';
                                    input.click();

                                    input.addEventListener('change', async (e) => {
                                        const file = e.target.files[0];
                                        if (!file) return;

                                        // Show loading state
                                        btn.classList.add('ck-disabled');
                                        const originalHTML = btn.innerHTML;
                                        btn.innerHTML = '<span class="ck-button__label">Uploading...</span>';

                                        const fd = new FormData();
                                        fd.append('upload', file);
                                        fd.append('attachable_id', commentId || '');
                                        fd.append('attachable_type', 'App\\Models\\Comment');

                                        try {
                                            const res = await fetch('/tasks/comments/upload', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                                },
                                                body: fd
                                            });

                                            const data = await res.json();

                                            if (res.ok && data.url) {
                                                editor.model.change(writer => {
                                                    const insertPos = editor.model.document.selection
                                                        .getFirstPosition();
                                                    const imageElement = writer.createElement(
                                                        'imageBlock', {
                                                            src: data.url
                                                        });
                                                    editor.model.insertContent(imageElement, insertPos);
                                                });

                                                console.log('✅ Image uploaded:', data.url);
                                                this.showNotification('Image berhasil diupload', 'success');
                                            } else {
                                                throw new Error(data.error || 'Upload gagal');
                                            }
                                        } catch (err) {
                                            console.error('❌ Upload error:', err);
                                            this.showNotification('Gagal upload image: ' + err.message, 'error');
                                        } finally {
                                            // Restore button state
                                            btn.classList.remove('ck-disabled');
                                            btn.innerHTML = originalHTML;
                                        }
                                    }, {
                                        once: true
                                    });
                                });

                                itemsContainer.appendChild(btn);
                            },

                            insertUploadFileButton(editor, commentId) {
                                const toolbarEl = editor.ui.view.toolbar.element;
                                const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'ck ck-button ck-off';
                                btn.title = 'Upload File';
                                btn.innerHTML = `
                <span class="ck-button__label" style="display:flex;align-items:center;gap:4px;padding:2px 4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="16" height="16" fill="currentColor">
                        <path d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2l4 4H10V4z"/>
                    </svg>
                    <span style="font-size:11px;">File</span>
                </span>
            `;
                                btn.style.cursor = 'pointer';

                                btn.addEventListener('click', () => {
                                    const input = document.createElement('input');
                                    input.type = 'file';
                                    input.accept = '.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.ppt,.pptx';
                                    input.click();

                                    input.addEventListener('change', async (e) => {
                                        const file = e.target.files[0];
                                        if (!file) return;

                                        // Show loading state
                                        btn.classList.add('ck-disabled');
                                        const originalHTML = btn.innerHTML;
                                        btn.innerHTML = '<span class="ck-button__label">Uploading...</span>';

                                        const fd = new FormData();
                                        fd.append('upload', file);
                                        fd.append('attachable_id', commentId || '');
                                        fd.append('attachable_type', 'App\\Models\\Comment');

                                        try {
                                            const res = await fetch('/tasks/comments/upload', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': this.getCsrfToken()
                                                },
                                                body: fd
                                            });

                                            const data = await res.json();

                                            if (res.ok && data.url) {
                                                editor.model.change(writer => {
                                                    const insertPos = editor.model.document.selection
                                                        .getFirstPosition();
                                                    const paragraph = writer.createElement('paragraph');
                                                    const textNode = writer.createText(`📎 ${file.name}`, {
                                                        linkHref: data.url
                                                    });
                                                    writer.append(textNode, paragraph);
                                                    editor.model.insertContent(paragraph, insertPos);
                                                });

                                                console.log('✅ File uploaded:', data.url);
                                                this.showNotification('File berhasil diupload', 'success');
                                            } else {
                                                throw new Error(data.error || 'Upload gagal');
                                            }
                                        } catch (err) {
                                            console.error('❌ Upload error:', err);
                                            this.showNotification('Gagal upload file: ' + err.message, 'error');
                                        } finally {
                                            // Restore button state
                                            btn.classList.remove('ck-disabled');
                                            btn.innerHTML = originalHTML;
                                        }
                                    }, {
                                        once: true
                                    });
                                });

                                itemsContainer.appendChild(btn);
                            },

                            getEditorData(editorId) {
                                const el = document.getElementById(editorId);
                                if (!el) return '';
                                if (el._editor) return el._editor.getData();
                                return '';
                            },

                            resetEditor(editorId) {
                                const el = document.getElementById(editorId);
                                if (!el) return;
                                if (el._editor) el._editor.setData('');
                            },

                            destroyEditor(editorId) {
                                const el = document.getElementById(editorId);

                                // Destroy from tracked instances
                                if (this.editorInstances[editorId]) {
                                    try {
                                        this.editorInstances[editorId].destroy()
                                            .then(() => {
                                                delete this.editorInstances[editorId];
                                                console.log('✅ Destroyed tracked editor:', editorId);
                                            })
                                            .catch(err => {
                                                console.warn('⚠️ Error destroying tracked editor:', err);
                                                delete this.editorInstances[editorId];
                                            });
                                    } catch (err) {
                                        console.warn('⚠️ Error in destroy:', err);
                                        delete this.editorInstances[editorId];
                                    }
                                }

                                // Destroy from element reference
                                if (el && el._editor) {
                                    try {
                                        el._editor.destroy()
                                            .then(() => {
                                                el._editor = null;
                                                el.innerHTML = '';
                                                console.log('✅ Destroyed element editor:', editorId);
                                            })
                                            .catch(err => {
                                                console.warn('⚠️ Error destroying element editor:', err);
                                                el._editor = null;
                                                el.innerHTML = '';
                                            });
                                    } catch (err) {
                                        console.warn('⚠️ Error in element destroy:', err);
                                        el._editor = null;
                                        if (el) el.innerHTML = '';
                                    }
                                }
                            },

                            async submitMainComment() {
                                if (!this.taskId) {
                                    this.showNotification('Task ID tidak ditemukan', 'error');
                                    return;
                                }

                                const content = this.getEditorData('task-main-comment-editor').trim();
                                if (!content) {
                                    this.showNotification('Komentar tidak boleh kosong', 'error');
                                    return;
                                }

                                try {
                                    const preId = window.currentMainCommentId || this.generateUUID();

                                    const res = await fetch(`/tasks/${this.taskId}/comments`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken(),
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            id: preId,
                                            content: content
                                        })
                                    });

                                    if (!res.ok) throw new Error('Server error ' + res.status);

                                    const data = await res.json();

                                    if (data.success) {
                                        const newComment = {
                                            ...data.comment,
                                            replies: data.comment.replies || [],
                                            author: data.comment.author || {
                                                name: data.comment.user?.full_name || 'You',
                                                avatar: data.comment.user?.avatar || this.currentUserAvatar
                                            }
                                        };

                                        this.comments.unshift(newComment);
                                        this.resetEditor('task-main-comment-editor');
                                        window.currentMainCommentId = null;

                                        this.$nextTick(() => {
                                            this.initializeMainEditor();
                                        });

                                        this.showNotification('Komentar berhasil dikirim', 'success');
                                    } else {
                                        throw new Error(data.message || 'Gagal mengirim komentar');
                                    }
                                } catch (err) {
                                    console.error(err);
                                    this.showNotification('Gagal mengirim komentar: ' + err.message, 'error');
                                }
                            },

                            async submitReplyFromEditor() {
                                if (!this.replyView.parentComment || !this.taskId) {
                                    this.showNotification('Data tidak lengkap', 'error');
                                    return;
                                }

                                const parent = this.replyView.parentComment;
                                const editorId = `task-reply-editor-${parent.id}`;
                                const content = this.getEditorData(editorId).trim();

                                if (!content) {
                                    this.showNotification('Balasan tidak boleh kosong', 'error');
                                    return;
                                }

                                try {
                                    const preId = window[`currentReplyId_${parent.id}`] || this.generateUUID();

                                    const res = await fetch(`/tasks/${this.taskId}/comments`, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': this.getCsrfToken(),
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            id: preId,
                                            content: content,
                                            parent_comment_id: parent.id
                                        })
                                    });

                                    if (!res.ok) throw new Error(`Server error ${res.status}`);

                                    const data = await res.json();

                                    if (data.success) {
                                        if (!parent.replies) parent.replies = [];

                                        const newReply = {
                                            ...data.comment,
                                            author: data.comment.author || {
                                                name: data.comment.user?.full_name || 'You',
                                                avatar: data.comment.user?.avatar || this.currentUserAvatar
                                            }
                                        };

                                        parent.replies.push(newReply);
                                        this.closeReplyView();
                                        this.showNotification('Balasan berhasil dikirim', 'success');
                                    } else {
                                        throw new Error(data.message || 'Gagal mengirim balasan');
                                    }
                                } catch (err) {
                                    console.error(err);
                                    this.showNotification('Gagal mengirim balasan: ' + err.message, 'error');
                                }
                            },

                            toggleReply(comment) {
                                if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                                    this.closeReplyView();
                                } else {
                                    if (this.replyView.parentComment) {
                                        this.destroyEditor(`task-reply-editor-${this.replyView.parentComment.id}`);
                                    }

                                    this.replyView.active = true;
                                    this.replyView.parentComment = comment;

                                    this.$nextTick(() => this.initializeReplyEditor(comment.id));
                                }
                            },

                            closeReplyView() {
                                if (this.replyView.parentComment) {
                                    this.destroyEditor(`task-reply-editor-${this.replyView.parentComment.id}`);
                                    delete window[`currentReplyId_${this.replyView.parentComment.id}`];
                                }

                                this.replyView.active = false;
                                this.replyView.parentComment = null;
                            },

                            formatCommentDate(dateString) {
                                if (!dateString) return '';

                                const d = new Date(dateString);
                                const now = new Date();
                                const diffMs = now - d;
                                const minutes = Math.floor(diffMs / (1000 * 60));

                                if (minutes < 1) return 'beberapa detik yang lalu';
                                if (minutes < 60) return `${minutes} menit yang lalu`;

                                const hours = Math.floor(minutes / 60);
                                if (hours < 24) return `${hours} jam yang lalu`;

                                const days = Math.floor(hours / 24);
                                if (days < 7) return `${days} hari yang lalu`;

                                return d.toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                });
                            },

                            getCsrfToken() {
                                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                            },

                            generateUUID() {
                                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                                    const r = Math.random() * 16 | 0;
                                    const v = c === 'x' ? r : (r & 0x3 | 0x8);
                                    return v.toString(16);
                                });
                            },

                            showNotification(msg, type = 'info') {
                                console.log(`[${type}] ${msg}`);
                                alert(msg);
                            }
                        };
                    }


                    // -------------------------
                    // CKEditor toolbar custom upload functions (reuse from earlier)
                    // -------------------------
                    function insertUploadImageButtonToToolbar(editor, commentId) {
                        const toolbarEl = editor.ui.view.toolbar.element;
                        const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'ck ck-button';
                        btn.title = 'Upload Image';
                        btn.innerHTML = `<span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
            <path d="M21 19V5a2 2 0 0 0-2-2H5 a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14 a2 2 0 0 0 2-2zM8.5 11 a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM5 19 l4.5-6 3.5 4.5 2.5-3L19 19H5z"/></svg>
    </span>`;
                        btn.style.marginLeft = '6px';
                        btn.style.cursor = 'pointer';
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        btn.addEventListener('click', () => {
                            const input = document.createElement('input');
                            input.type = 'file';
                            input.accept = 'image/*';
                            input.click();
                            input.addEventListener('change', async (e) => {
                                const file = e.target.files[0];
                                if (!file) return;
                                const fd = new FormData();
                                fd.append('upload', file);
                                fd.append('attachable_id', commentId || '');
                                fd.append('attachable_type', 'App\\Models\\Comment');

                                try {
                                    const res = await fetch('/tasks/comments/upload', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken
                                        },
                                        body: fd
                                    });
                                    const data = await res.json();
                                    if (res.ok && data.url) {
                                        editor.model.change(writer => {
                                            const insertPos = editor.model.document.selection
                                                .getFirstPosition();
                                            const imageElement = writer.createElement('imageBlock', {
                                                src: data.url
                                            });
                                            editor.model.insertContent(imageElement, insertPos);
                                        });
                                    } else {
                                        alert('Upload image gagal.');
                                        console.error(data);
                                    }
                                } catch (err) {
                                    console.error(err);
                                    alert('Terjadi kesalahan upload image.');
                                }
                            }, {
                                once: true
                            });
                        });

                        itemsContainer.appendChild(btn);
                    }

                    function insertUploadFileButtonToToolbar(editor, commentId) {
                        const toolbarEl = editor.ui.view.toolbar.element;
                        const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'ck ck-button';
                        btn.title = 'Upload File';
                        btn.innerHTML = `<span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="18" height="18" fill="currentColor">
            <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/></svg>
    </span>`;
                        btn.style.marginLeft = '6px';
                        btn.style.cursor = 'pointer';
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        btn.addEventListener('click', () => {
                            const input = document.createElement('input');
                            input.type = 'file';
                            input.accept = ".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar,.ppt,.pptx";
                            input.click();

                            input.addEventListener('change', async (e) => {
                                const file = e.target.files[0];
                                if (!file) return;
                                const fd = new FormData();
                                fd.append('upload', file);
                                fd.append('attachable_id', commentId || '');
                                fd.append('attachable_type', 'App\\Models\\Comment');

                                try {
                                    const res = await fetch('/tasks/comments/upload', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken
                                        },
                                        body: fd
                                    });
                                    const data = await res.json();
                                    if (res.ok && data.url) {
                                        editor.model.change(writer => {
                                            const insertPos = editor.model.document.selection
                                                .getFirstPosition();
                                            const paragraph = writer.createElement('paragraph');
                                            const textNode = writer.createText(file.name, {
                                                linkHref: data.url
                                            });
                                            writer.append(textNode, paragraph);
                                            editor.model.insertContent(paragraph, insertPos);
                                        });
                                    } else {
                                        alert('Upload file gagal.');
                                        console.error(data);
                                    }
                                } catch (err) {
                                    console.error(err);
                                    alert('Terjadi kesalahan upload file.');
                                }
                            }, {
                                once: true
                            });
                        });

                        itemsContainer.appendChild(btn);
                    }
                </script>
            @endsection
