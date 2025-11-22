{{-- 🎯 Gantt Chart Timeline View --}}
<div x-show="viewMode === 'timeline'" class="h-full p-4">
    <div class="max-w-7xl mx-auto bg-white rounded-xl border shadow-sm">

        <!-- Header -->
        <div class="p-5 border-b bg-white">
            <div class="flex items-center justify-between">

                <!-- Title -->
                <h1 class="text-xl font-semibold text-gray-800 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Timeline Pekerjaan
                </h1>

                <!-- Loading -->
                <div x-show="loadingTimeline" class="flex items-center gap-2 text-gray-600 text-sm">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0
                                0 5.373 0 12h4zm2 5.291A7.962 7.962
                                0 014 12H0c0 3.042 1.135 5.824
                                3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat...
                </div>

                <!-- Stats -->
                <div x-show="!loadingTimeline && timelineData.length > 0"
                     class="text-gray-700 text-sm bg-gray-100 rounded-lg px-3 py-1">
                    <span
                        x-text="`${timelineData.length} Phase • 
                        ${timelineData.reduce((sum, p) => sum + p.total_tasks, 0)} Tugas`">
                    </span>
                </div>
            </div>
        </div>

        <!-- Overall Progress -->
        <div x-show="!loadingTimeline && timelineData.length > 0" class="p-5 border-b bg-gray-50">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-medium text-gray-800">Progress Keseluruhan</h3>
                <span class="text-lg font-semibold text-gray-700" x-text="`${getOverallProgress()}%`"></span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all duration-700"
                     :class="{
                        'bg-green-500': getOverallProgress() >= 80,
                        'bg-blue-500': getOverallProgress() >= 50 && getOverallProgress() < 80,
                        'bg-orange-500': getOverallProgress() >= 20 && getOverallProgress() < 50,
                        'bg-red-500': getOverallProgress() < 20
                     }"
                     :style="`width: ${getOverallProgress()}%`">
                </div>
            </div>

            <div class="flex justify-between text-xs text-gray-600 mt-2">
                <span x-text="`${timelineData.reduce((s, p) => s + p.completed_tasks, 0)} selesai`"></span>
                <span x-text="`${timelineData.reduce((s, p) => s + p.total_tasks, 0)} total tugas`"></span>
            </div>
        </div>

        <!-- Table Header -->
        <div class="flex bg-gray-100 border-b text-gray-600 text-xs font-medium">
            <div class="w-72 px-4 py-3 border-r">Phase / Progress</div>
            <div class="flex-1 px-4 py-3">Timeline</div>
        </div>

        <!-- Loop Phase -->
        <template x-for="phase in getProjectPhases()" :key="phase.id">
            <div class="flex border-b hover:bg-gray-50 transition">
                <!-- Phase Info -->
                <div class="w-72 px-4 py-4 border-r bg-white cursor-pointer" @click="showPhaseTasks(phase.id)">

                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-800" x-text="phase.name"></h3>
                        <span class="text-xs px-2 py-1 rounded-md font-medium"
                              :class="{
                                'bg-green-100 text-green-700': phase.progress_percentage >= 80,
                                'bg-blue-100 text-blue-700': phase.progress_percentage >= 50 && phase.progress_percentage < 80,
                                'bg-orange-100 text-orange-700': phase.progress_percentage >= 20 && phase.progress_percentage < 50,
                                'bg-red-100 text-red-700': phase.progress_percentage < 20
                              }"
                              x-text="`${phase.progress_percentage}%`">
                        </span>
                    </div>

                    <!-- Date -->
                    <div class="text-xs text-gray-500 mb-2" x-show="phase.start_date && phase.end_date">
                        <span x-text="formatDate(phase.start_date) + ' - ' + formatDate(phase.end_date)"></span>
                    </div>

                    <!-- Small progress bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all"
                             :class="{
                                'bg-green-500': phase.progress_percentage >= 80,
                                'bg-blue-500': phase.progress_percentage >= 50 && phase.progress_percentage < 80,
                                'bg-orange-500': phase.progress_percentage >= 20 && phase.progress_percentage < 50,
                                'bg-red-500': phase.progress_percentage < 20
                             }"
                             :style="`width: ${phase.progress_percentage}%`">
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-gray-500"
                         x-text="`${phase.completed_tasks}/${phase.total_tasks} tugas selesai`"></div>
                </div>

                <!-- Timeline Bar -->
                <div class="flex-1 relative bg-white h-20">
                    <div class="absolute inset-0 bg-gray-50"></div>

                    <div class="absolute top-1/2 -translate-y-1/2 h-8 rounded-lg transition-all duration-500 border overflow-hidden cursor-pointer"
                         :style="`width: ${phase.duration_percentage}%; min-width: 60px; max-width: 95%;`"
                         @click="showPhaseTasks(phase.id)">

                        <!-- Background color -->
                        <div class="absolute inset-0"
                             :class="{
                                'bg-green-400': phase.progress_percentage >= 80,
                                'bg-blue-400': phase.progress_percentage >= 50 && phase.progress_percentage < 80,
                                'bg-orange-400': phase.progress_percentage >= 20 && phase.progress_percentage < 50,
                                'bg-red-400': phase.progress_percentage < 20
                             }">
                        </div>

                        <!-- WHITE progress (moves from RIGHT → LEFT) -->
                        <div class="absolute inset-y-0 bg-white bg-opacity-30"
                             :style="`width: ${100 - phase.progress_percentage}%; right: 0;`">
                        </div>

                        <!-- Text -->
                        <div class="relative h-full flex items-center justify-end px-3 text-xs text-white font-medium">
                            <span x-text="`${phase.progress_percentage}%`"
                                  class="bg-black bg-opacity-30 px-2 py-1 rounded-full text-xs">
                            </span>
                        </div>
                    </div>

                    <!-- Date labels -->
                    <div class="absolute top-2 left-0 right-0 flex justify-between text-xs text-gray-600 px-2">
                        <span x-text="formatDate(phase.start_date)" x-show="phase.start_date"></span>
                        <span x-text="formatDate(phase.end_date)" x-show="phase.end_date"></span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty -->
        <div x-show="!loadingTimeline && getProjectPhases().length === 0"
             class="text-center py-12 text-gray-600">
            <h3 class="text-sm font-medium text-gray-800">Belum ada phase</h3>
            <p class="text-xs text-gray-500 mb-3">Mulai dengan membuat tugas baru</p>
            <button @click="openTaskModal = true"
                    class="px-5 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-black">
                Buat Tugas
            </button>
        </div>

    </div>
</div>
