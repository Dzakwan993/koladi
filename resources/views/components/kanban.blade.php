{{-- 🎯 Kanban Board --}}
<div x-show="viewMode === 'kanban'" class="h-full">
    
    {{-- Loading State --}}
    <div x-show="loadingColumns" class="flex justify-center items-center h-32">
        <div class="text-center">
            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600">Memuat kolom...</p>
        </div>
    </div>

    <div x-show="!loadingColumns" class="flex-1 overflow-x-auto" @click.outside="openListMenu = null">
        <div id="kanban-board" class="flex kanban-gap-medium p-5 xs:p-4 min-w-max">
            
            {{-- Dynamic Columns dari Database --}}
            <template x-for="column in boardColumns" :key="column.id">
                <div class="bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col">
                    <div class="flex items-center justify-between mb-1 xs:mb-2">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base" 
                                x-text="column.name"></h2>
                            <span class="bg-blue-500 text-white text-xs rounded-full px-2 py-1" 
                                  x-text="column.tasks_count || 0"></span>
                        </div>
                        
                        {{-- Tombol menu hanya untuk kolom custom --}}
                        <button x-show="!['To Do List', 'Dikerjakan', 'Selesai', 'Batal'].includes(column.name)"
                            @click="openListMenu = openListMenu === column.id ? null : column.id"
                            class="text-gray-500 hover:text-gray-700 text-xs p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6 10a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Menu untuk kolom custom --}}
                    <div x-show="openListMenu === column.id" 
                         class="absolute right-0 mt-8 bg-white rounded-lg shadow-lg border z-10 p-2">
                        <button @click="deleteColumn(column.id)" 
                                class="flex items-center gap-2 text-red-600 hover:bg-red-50 w-full px-3 py-2 rounded text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Kolom
                        </button>
                    </div>

                    <div class="flex flex-col flex-1">
                        <div :id="`column-${column.id}`"
                            class="space-y-2 flex-1 overflow-y-auto max-h-[50vh] xs:max-h-[55vh] sm:max-h-[60vh] pr-1">
                            
                            <template x-for="task in getFilteredTasksByColumn(column.id)" :key="task.id">
                                <div @click="openDetail(task.id)"
                                    data-task-id="task.id"
                                    class="bg-white p-2 xs:p-3 rounded shadow hover:shadow-md cursor-move border border-gray-200 transition-all duration-200 text-xs xs:text-sm">
                                    {{-- Task card content --}}
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

                                    <p class="font-medium text-gray-800 mb-1 xs:mb-2 line-clamp-2 text-xs xs:text-sm"
                                        x-text="task.title"></p>

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
                                    </div>
                                </div>
                            </template>

                            {{-- No tasks message --}}
                            <div x-show="getFilteredTasksByColumn(column.id).length === 0"
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
            </template>

            {{-- ➕ Tombol Tambah List --}}
            <div class="flex items-start justify-center pr-2 xs:pr-3">
                <button @click="openModal = true"
                    class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1.5 xs:px-3 xs:py-2 rounded-lg text-xs xs:text-sm shadow-md hover:shadow-lg transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden xs:inline">Tambah Kolom</span>
                    <span class="xs:hidden">Tambah</span>
                </button>
            </div>
        </div>
    </div>
</div>