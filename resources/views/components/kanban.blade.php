{{-- 🎯 KANBAN BOARD --}}
<div x-show="viewMode === 'kanban'" x-cloak class="h-full">

    {{-- Loading (Bouncing Wave Dots) --}}
    <div x-show="loadingColumns" class="flex flex-col justify-center items-center h-96 select-none">
        <div class="flex space-x-2.5 justify-center items-center mb-4">
            <div class="h-3.5 w-3.5 bg-blue-600 rounded-full bounce-dot-1"></div>
            <div class="h-3.5 w-3.5 bg-blue-600 rounded-full bounce-dot-2"></div>
            <div class="h-3.5 w-3.5 bg-blue-600 rounded-full bounce-dot-3"></div>
        </div>
        <p class="text-sm font-semibold text-gray-500 tracking-wide animate-pulse">Sedang memuat tugas Anda...</p>
    </div>

    <style>
        @keyframes bounceWave {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .bounce-dot-1 {
            animation: bounceWave 0.6s infinite ease-in-out;
            animation-delay: 0s;
        }

        .bounce-dot-2 {
            animation: bounceWave 0.6s infinite ease-in-out;
            animation-delay: 0.15s;
        }

        .bounce-dot-3 {
            animation: bounceWave 0.6s infinite ease-in-out;
            animation-delay: 0.3s;
        }
    </style>

    <div x-show="!loadingColumns" class="flex-1 overflow-x-auto" @click.outside="openListMenu = null">
        <div id="kanban-board" class="flex kanban-gap-medium p-5 xs:p-4 min-w-max">

            {{-- 🔵 DYNAMIC COLUMN --}}
            <template x-for="column in boardColumns" :key="column.id">
                <div
                    class="bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col">

                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-1 xs:mb-2">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base"
                                x-text="column.name"></h2>
                            {{-- <span class="bg-blue-500 text-white text-xs rounded-full px-2 py-1"
                                x-text="column.tasks_count || 0"></span> --}}
                        </div>

                        {{-- Menu hanya untuk kolom custom --}}
                        <button x-show="!['To Do List', 'Dikerjakan', 'Selesai', 'Batal'].includes(column.name)"
                            @click="openListMenu = (openListMenu === column.id ? null : column.id)"
                            class="text-gray-500 hover:text-gray-700 text-xs p-1">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Menu kolom --}}
                    <div x-show="openListMenu === column.id"
                        class="absolute right-0 mt-8 bg-white rounded-lg shadow-lg border z-10 p-2">
                        <button @click="deleteCustomColumn(column.id)"
                            class="flex items-center gap-2 text-red-600 hover:bg-red-50 w-full px-3 py-2 rounded text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.8 12A2 2 0 0116 21H8a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-11 0h16" />
                            </svg>
                            Hapus Kolom
                        </button>
                    </div>

                    {{-- LIST TASKS --}}
                    <div class="flex flex-col flex-1">
                        <div :id="`column-${column.id}`" class="space-y-2 flex-1 overflow-y-auto max-h-[60vh] pr-1"
                            x-ref="`column-${column.id}`">

                            {{-- TASK CARD --}}
                            <template x-for="task in getFilteredTasksByColumn(column.id)" :key="task.id">
                                <div @click="openDetail(task.id)" :data-task-id="task.id"
                                    class="task-card bg-white p-2 xs:p-3 rounded shadow hover:shadow-md cursor-move border transition-all duration-200 text-xs xs:text-sm select-none"
                                    :class="{
                                        'border-2 border-red-500 ring-2 ring-red-200 bg-red-50/20': task.phase === 'Klarifikasi' || task.phase === 'Clarification',
                                        'border-gray-200': !(task.phase === 'Klarifikasi' || task.phase === 'Clarification'),
                                        'task-card-secret border-l-4 border-purple-500 bg-purple-50': task.is_secret,
                                        'border-l-4 border-red-500': task.is_overdue && !(task.phase === 'Klarifikasi' || task.phase === 'Clarification'),
                                        'border-l-4 border-red-600': task.edf_urgency_level === 'overdue' && !(task.phase === 'Klarifikasi' || task.phase === 'Clarification'),
                                        'border-l-4 border-orange-500': task.edf_urgency_level === 'critical' && !(task.phase === 'Klarifikasi' || task.phase === 'Clarification'),
                                        'border-l-4 border-yellow-400': task.edf_urgency_level === 'warning' && !(task.phase === 'Klarifikasi' || task.phase === 'Clarification'),
                                        'border-2 border-indigo-500 ring-4 ring-indigo-100 animate-pulse': newCreatedTaskIds.includes(task.id) && !(task.phase === 'Klarifikasi' || task.phase === 'Clarification')
                                    }" draggable="true" @dragstart="onDragStart($event, task.id)"
                                    @dragend="onDragEnd($event)">

                                    {{-- HEADER: Phase + Badges --}}
                                    <div class="flex justify-between items-start mb-1 xs:mb-2">

                                        {{-- PHASE (baru, di paling atas) --}}
                                        <div class="flex flex-wrap gap-1 flex-1">
                                            <template x-if="task.phase === 'Klarifikasi' || task.phase === 'Clarification'">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-sm">
                                                    ⏱️ 🔴 Klarifikasi
                                                </span>
                                            </template>
                                            <template x-if="task.phase && task.phase !== 'Klarifikasi' && task.phase !== 'Clarification'">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                                    x-text="task.phase">
                                                </span>
                                            </template>
                                        </div>

                                        {{-- Badge Rahasia --}}
                                        <div class="flex items-center gap-1">
                                            <span x-show="task.is_secret"
                                                class="secret-task-badge ml-2 flex-shrink-0 inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" />
                                                </svg>
                                                Rahasia
                                            </span>
                                        </div>
                                        {{-- EDF Priority Badge --}}
                                        <div x-show="task.edf_priority" class="mt-1">
                                            <span :class="{
                                                    'bg-red-100 text-red-700 border border-red-300': task
                                                        .edf_urgency_level === 'overdue',
                                                    'bg-orange-100 text-orange-700 border border-orange-300': task
                                                        .edf_urgency_level === 'critical',
                                                    'bg-yellow-100 text-yellow-700 border border-yellow-300': task
                                                        .edf_urgency_level === 'warning',
                                                    'bg-green-100 text-green-700 border border-green-300': task
                                                        .edf_urgency_level === 'normal'
                                                }"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span x-text="
                                                task.edf_urgency_level === 'overdue'  ? '⚠ Overdue' :
                                                task.edf_urgency_level === 'critical' ? '🔴 Critical' :
                                                task.edf_urgency_level === 'warning'  ? '🟡 Warning' : 'EDF'
                                            "></span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- TITLE --}}
                                    <p class="font-medium text-gray-800 mb-1 xs:mb-2 line-clamp-2" x-text="task.title">
                                    </p>

                                    {{-- DUE DATE + LABELS --}}
                                    <div class="flex items-center justify-between mb-1 xs:mb-2">

                                        {{-- Due --}}
                                        <span x-show="task.dueDate"
                                            class="font-semibold px-1.5 py-0.5 bg-yellow-100 text-gray-700 rounded text-xs xs:text-sm"
                                            x-text="formatDate(task.dueDate)">
                                        </span>

                                        {{-- Labels (posisi baru) --}}
                                        <div x-show="task.labels?.length > 0" class="flex flex-wrap gap-1 justify-end">
                                            <template x-for="label in task.labels.slice(0, 2)" :key="label.id">
                                                <span class="font-semibold px-1.5 py-0.5 rounded text-xs"
                                                    :style="`background: ${label.color}20; color: ${label.color}`"
                                                    x-text="label.name"></span>
                                            </template>
                                            <span x-show="task.labels.length > 2" class="text-xs text-gray-500 ml-1"
                                                x-text="`+${task.labels.length - 2}`">
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Progress --}}
                                    <div x-show="task.checklist?.length > 0" class="mt-1 xs:mt-2">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="w-full bg-gray-200 h-1 xs:h-1.5 rounded-full mr-2">
                                                <div class="bg-blue-500 h-1 xs:h-1.5 rounded-full transition-all"
                                                    :style="`width: ${calculateProgress(task)}%`"></div>
                                            </div>
                                            <span class="font-medium text-gray-700 text-xs xs:text-sm"
                                                x-text="`${calculateProgress(task)}%`"></span>
                                        </div>
                                    </div>

                                    {{-- PARTICIPANTS (paling bawah) --}}
                                    <div x-show="task.members?.length > 0" class="mt-1">
                                        <div class="flex items-center gap-1">
                                            <template x-for="member in task.members.slice(0, 3)" :key="member.id">
                                                <img :src="member.avatar"
                                                    class="w-5 h-5 rounded-full border border-gray-300"
                                                    :alt="member.name" :title="member.name">
                                            </template>

                                            <span x-show="task.members.length > 3" class="text-xs text-gray-500 ml-1"
                                                x-text="`+${task.members.length - 3}`">
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </template>

                            {{-- EMPTY --}}
                            <div x-show="getFilteredTasksByColumn(column.id).length === 0"
                                class="text-center text-gray-500 text-xs xs:text-sm py-4">
                                Tidak ada tugas
                            </div>
                        </div>

                        {{-- ADD TASK --}}
                        <button @click="openTaskModalForColumn(column.id)"
                            class="w-full mt-3 py-2 text-sm font-medium text-gray-600 bg-white rounded-lg border border-gray-300 hover:bg-blue-500 hover:text-white transition shadow-sm">
                            + Buat Tugas
                        </button>
                    </div>
                </div>
            </template>

            {{-- ➕ ADD COLUMN --}}
            <div class="flex items-start justify-center pr-2 xs:pr-3">
                <button @click="openModal = true"
                    class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1.5 xs:px-3 xs:py-2 rounded-lg text-xs xs:text-sm shadow-md">
                    <svg class="h-3 w-3 xs:h-4 xs:w-4" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden xs:inline">Tambah Kolom</span>
                    <span class="xs:hidden">Tambah</span>
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    /* ===== Scrollbar Modern for Kanban ===== */
    #kanban-board::-webkit-scrollbar,
    [id^="column-"]::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    #kanban-board::-webkit-scrollbar-track,
    [id^="column-"]::-webkit-scrollbar-track {
        background: transparent;
    }

    #kanban-board::-webkit-scrollbar-thumb,
    [id^="column-"]::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #3b82f6, #2563eb);
        border-radius: 10px;
    }

    #kanban-board::-webkit-scrollbar-thumb:hover,
    [id^="column-"]::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #2563eb, #1d4ed8);
    }

    /* Firefox Support */
    #kanban-board,
    [id^="column-"] {
        scrollbar-width: thin;
        scrollbar-color: #3b82f6 transparent;
    }



    /* Tambahkan di style section */
    .drag-ghost {
        opacity: 0.5;
        transform: rotate(5deg);
    }

    .drag-chosen {
        background-color: #dbeafe !important;
        border-color: #3b82f6 !important;
    }

    .drag-over {
        background-color: #eff6ff !important;
        border: 2px dashed #3b82f6 !important;
    }

    /* Style untuk placeholder drag & drop */
    .sortable-ghost {
        opacity: 0.4;
        background-color: #93c5fd !important;
    }

    .sortable-chosen {
        background-color: #dbeafe !important;
    }

    .sortable-drag {
        transform: rotate(5deg);
        z-index: 9999 !important;
    }


    /* Tambahkan di style section */
    .task-card {
        user-select: none;
        -webkit-user-drag: element;
        backface-visibility: hidden;
        transform: translateZ(0);
        will-change: transform;
    }

    .task-card.dragging {
        opacity: 0.5 !important;
        transform: scale(0.95) rotate(2deg);
        transition: all 0.2s ease;
    }

    .task-card.dragging-active {
        z-index: 9999 !important;
        position: relative;
    }

    /* Sortable.js styles */
    .sortable-ghost {
        opacity: 0.3;
        background: linear-gradient(135deg, #667eea, #764ba2) !important;
    }

    .sortable-chosen {
        background-color: #dbeafe !important;
        border: 2px solid #3b82f6 !important;
        transform: rotate(3deg);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .sortable-drag {
        opacity: 0.8;
        transform: rotate(5deg) scale(1.02);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        z-index: 10000 !important;
    }

    .drag-over {
        background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
        border: 2px dashed #3b82f6 !important;
        border-radius: 8px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            border-color: #3b82f6;
        }

        50% {
            border-color: #60a5fa;
        }
    }

    /* Fix untuk text blur saat drag */
    .sortable-drag *,
    .sortable-chosen *,
    .sortable-ghost * {
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    /* Firefox specific fix */
    @@supports (-moz-appearance: none) {

        .sortable-drag,
        .sortable-chosen {
            filter: none !important;
        }
    }

    /* Fix untuk Chrome blur issue */
    @media (-webkit-min-device-pixel-ratio: 0) {
        .sortable-drag {
            -webkit-backface-visibility: hidden;
            -webkit-transform: translate3d(0, 0, 0);
        }
    }

    /* Scrollbar Modern for Kanban */
    #kanban-board::-webkit-scrollbar,
    [id^="column-"]::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    #kanban-board::-webkit-scrollbar-track,
    [id^="column-"]::-webkit-scrollbar-track {
        background: transparent;
    }

    #kanban-board::-webkit-scrollbar-thumb,
    [id^="column-"]::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #3b82f6, #2563eb);
        border-radius: 10px;
    }

    #kanban-board::-webkit-scrollbar-thumb:hover,
    [id^="column-"]::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #2563eb, #1d4ed8);
    }

    /* Firefox Support */
    #kanban-board,
    [id^="column-"] {
        scrollbar-width: thin;
        scrollbar-color: #3b82f6 transparent;
    }
</style>