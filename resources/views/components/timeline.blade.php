{{-- 🎯 Gantt Chart Timeline View --}}
            <div x-show="viewMode === 'timeline'" class="h-full p-3">
                <div class="max-w-7xl mx-auto bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">

                    <!-- 🧭 Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-2">
                        <h1 class="text-base font-bold text-white tracking-wide flex items-center gap-2">
                            <span>TIMELINE PEKERJAAN</span>
                        </h1>
                    </div>

                    <!-- 📊 Gantt Chart -->
                    <div class="overflow-x-auto">

                        <!-- Header -->
                        <div class="flex bg-gray-100 border-b border-gray-200 text-gray-700 font-semibold text-xs">
                            <div class="w-56 px-3 py-2 border-r border-gray-200">Phase / Tugas</div>
                            <div class="flex-1 px-3 py-2">Timeline & Progress</div>
                        </div>

                        <!-- Body -->
                        <template x-for="(phase, index) in getProjectPhases()" :key="phase.id">
                            <div class="flex border-b border-gray-100 transition-all duration-200">

                                <!-- 🧩 Kolom Phase -->
                                <div class="w-56 px-3 py-3 border-r border-gray-200 bg-white cursor-pointer"
                                    :class="{ 'bg-blue-50 font-semibold': selectedPhase === phase.id }"
                                    @click="showPhaseTasks(phase.id)">
                                    <div class="text-gray-800 font-medium text-sm" x-text="phase.name"></div>
                                    <div class="text-xs text-gray-500 mt-0.5" x-text="phase.description"></div>
                                </div>

                                <!-- 📈 Kolom Timeline -->
                                <div class="flex-1 relative bg-white h-[70px]" x-data="{
                                    calculatePhasePosition(phase) {
                                            const tasks = getTasksByPhase(phase.id);
                                            if (tasks.length === 0) return { left: '0%', width: '0%' };
                                
                                            let earliestStart = new Date('2024-12-31');
                                            let latestEnd = new Date('2024-01-01');
                                
                                            tasks.forEach(task => {
                                                const startDate = new Date(task.startDate);
                                                const endDate = new Date(task.dueDate);
                                                if (startDate < earliestStart) earliestStart = startDate;
                                                if (endDate > latestEnd) latestEnd = endDate;
                                            });
                                
                                            const timelineStart = new Date('2024-01-01');
                                            const timelineEnd = new Date('2024-06-30');
                                            const totalDays = (timelineEnd - timelineStart) / (1000 * 60 * 60 * 24);
                                            const phaseStart = (earliestStart - timelineStart) / (1000 * 60 * 60 * 24);
                                            const phaseDuration = (latestEnd - earliestStart) / (1000 * 60 * 60 * 24);
                                
                                            const left = Math.max(0, (phaseStart / totalDays) * 100);
                                            const width = Math.min(100 - left, (phaseDuration / totalDays) * 100);
                                
                                            return {
                                                left: left + '%',
                                                width: Math.max(width, 2) + '%'
                                            };
                                        },
                                
                                        getPhaseColor(phaseId) {
                                            const colors = {
                                                1: 'phase-planning',
                                                2: 'phase-analysis',
                                                3: 'phase-design',
                                                4: 'phase-development',
                                                5: 'phase-testing',
                                                6: 'phase-deployment'
                                            };
                                            return colors[phaseId] || 'phase-planning';
                                        }
                                }">

                                    <!-- Progress Bar - FULL WIDTH dengan warna phase -->
                                    <div class="absolute top-1/2 -translate-y-1/2 h-8 rounded-lg shadow-md transition-all duration-500 overflow-hidden"
                                        :class="getPhaseColor(phase.id)"
                                        :style="`left: ${calculatePhasePosition(phase).left}; width: ${calculatePhasePosition(phase).width}`">

                                        <!-- Progress Percentage -->
                                        <div class="relative z-10 text-white text-xs font-semibold flex items-center justify-center h-full w-full"
                                            x-text="`${getPhaseStats(phase.id).progress}%`">
                                        </div>

                                        <!-- Shimmer Effect -->
                                        <div
                                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-20 animate-shimmer">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- 🧩 Legend -->
                    <div class="bg-gray-50 p-3 border-t border-gray-200">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-700">
                            <span class="font-bold">LEGEND:</span>
                            <template
                                x-for="legend in [
                        {color: 'from-blue-500 to-blue-700', label: 'Perencanaan'},
                        {color: 'from-green-500 to-green-700', label: 'Analisis'},
                        {color: 'from-orange-500 to-orange-700', label: 'Desain'},
                        {color: 'from-purple-500 to-purple-700', label: 'Development'},
                        {color: 'from-pink-500 to-pink-700', label: 'Testing'},
                        {color: 'from-indigo-500 to-indigo-700', label: 'Deployment'}
                    ]"
                                :key="legend.label">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-3 h-3 rounded bg-gradient-to-br" :class="legend.color"></div>
                                    <span x-text="legend.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>