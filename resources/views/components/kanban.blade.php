

        {{-- 🎯 Kanban Board --}}
            <div x-show="viewMode === 'kanban'" class="h-full">
                <div class="flex-1 overflow-x-auto" @click.outside="openListMenu = null">
                    <div id="kanban-board" class="flex kanban-gap-medium p-5 xs:p-4 min-w-max">
                        {{-- 📋 To Do Column --}}
                        <div
                            class="bg-blue-100 rounded-lg kanban-padding-medium kanban-column-medium flex-shrink-0 flex flex-col">
                            <div class="flex items-center justify-between mb-1 xs:mb-2">
                                <h2 class="font-semibold text-gray-700 text-xs xs:text-sm sm:text-base">To Do List</h2>
                                <button @click="openListMenu = openListMenu === 'todo' ? null : 'todo'"
                                    class="text-gray-500 hover:text-gray-700 text-xs p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4"
                                        fill="currentColor" viewBox="0 0 20 20">
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
                                                        <span
                                                            class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
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
                                            <div
                                                class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                    </svg>
                                                    <span class="text-xs xs:text-sm"
                                                        x-text="task.attachments ? task.attachments.length : 0"></span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4"
                                        fill="currentColor" viewBox="0 0 20 20">
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
                                                        <span
                                                            class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
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
                                            <div
                                                class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                    </svg>
                                                    <span class="text-xs xs:text-sm"
                                                        x-text="task.attachments ? task.attachments.length : 0"></span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4"
                                        fill="currentColor" viewBox="0 0 20 20">
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
                                                        <span
                                                            class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
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
                                            <div
                                                class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                    </svg>
                                                    <span class="text-xs xs:text-sm"
                                                        x-text="task.attachments ? task.attachments.length : 0"></span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 xs:h-4 xs:w-4"
                                        fill="currentColor" viewBox="0 0 20 20">
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
                                                        <span
                                                            class="font-semibold px-1.5 py-0.5 rounded text-xs xs:text-sm"
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
                                            <div
                                                class="flex items-center space-x-2 xs:space-x-3 text-gray-500 mb-1 xs:mb-2">
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L4.929 11.586a6 6 0 108.485 8.485L19 14" />
                                                    </svg>
                                                    <span class="text-xs xs:text-sm"
                                                        x-text="task.attachments ? task.attachments.length : 0"></span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 xs:h-4 xs:w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
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