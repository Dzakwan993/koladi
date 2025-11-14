{{-- 🔍 Search & Filter Section --}}
<div class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b px-3 xs:px-4 sm:px-5 md:px-6 py-2 xs:py-3 sm:py-4 shadow-sm">
    <div class="flex flex-col xs:flex-row xs:items-center xs:justify-between gap-2 xs:gap-3 sm:gap-4">
        {{-- 🔎 Search & Filters --}}
        <div class="flex w-full flex-col gap-2 xs:gap-3 sm:flex-row sm:items-center">
            {{-- Search Input --}}
            <div class="relative min-w-0 flex-1 xs:min-w-[200px] sm:min-w-[250px] md:min-w-[300px]">
                <input type="text" x-model="searchQuery" placeholder="Cari tugas..."
                    class="w-full rounded-lg border border-gray-300 py-2 pl-8 pr-3 text-xs transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 xs:py-2.5 xs:pl-9 xs:text-sm sm:py-2 sm:text-base">
                <svg class="absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 transform text-gray-400 xs:left-3 xs:h-4 xs:w-4"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            {{-- Filter Dropdowns --}}
            <div class="flex flex-wrap items-center gap-1 xs:gap-2">
                {{-- Label Filter --}}
                <select x-model="selectedLabel"
                    class="min-w-[100px] rounded border border-gray-300 bg-white px-2 py-1.5 pr-6 text-xs transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 xs:min-w-[110px] xs:px-3 xs:py-2 xs:pr-7 xs:text-sm">
                    <option value="">Semua Label</option>
                    <template x-for="label in availableLabels" :key="label.name">
                        <option :value="label.name" x-text="label.name"></option>
                    </template>
                </select>

                {{-- Member Filter --}}
                <select x-model="selectedMember"
                    class="min-w-[120px] rounded border border-gray-300 bg-white px-2 py-1.5 pr-6 text-xs transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 xs:min-w-[130px] xs:px-3 xs:py-2 xs:pr-7 xs:text-sm">
                    <option value="">Semua Peserta</option>
                    <template x-for="member in availableMembers" :key="member.name">
                        <option :value="member.name" x-text="member.name"></option>
                    </template>
                </select>

                {{-- Deadline Filter --}}
                <select x-model="selectedDeadline"
                    class="min-w-[110px] rounded border border-gray-300 bg-white px-2 py-1.5 pr-6 text-xs transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 xs:min-w-[120px] xs:px-3 xs:py-2 xs:pr-7 xs:text-sm">
                    <option value="">Semua Tenggat</option>
                    <option value="segera">Segera</option>
                    <option value="hari-ini">Hari Ini</option>
                    <option value="terlambat">Terlambat</option>
                </select>

                {{-- Reset Button --}}
                <button @click="resetFilters()"
                    class="whitespace-nowrap rounded bg-red-100 px-2 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-200 xs:px-3 xs:py-2 xs:text-sm">
                    Reset
                </button>

                {{-- 🔄 View Mode Toggle (Kanban & Timeline) --}}
<div class="ml-2 flex items-center space-x-2 border-l border-gray-300 pl-3">
    <div class="flex rounded-lg bg-blue-50 p-1 shadow-inner ring-1 ring-blue-100">
        <button @click="viewMode = 'kanban'"
            :class="{
                'bg-blue-500 text-white shadow-sm': viewMode === 'kanban',
                'text-blue-700 hover:bg-blue-100': viewMode !== 'kanban'
            }"
            class="rounded-md px-3 py-1.5 text-xs font-medium transition-all duration-200">
            Kanban
        </button>
        <button @click="viewMode = 'timeline'"
            :class="{
                'bg-blue-500 text-white shadow-sm': viewMode === 'timeline',
                'text-blue-700 hover:bg-blue-100': viewMode !== 'timeline'
            }"
            class="rounded-md px-3 py-1.5 text-xs font-medium transition-all duration-200">
            Timeline
        </button>
    </div>
</div>
            </div>
        </div>
    </div>

    {{-- 🎯 Active Filters Display --}}
    <div x-show="hasActiveFilters()" class="mt-2 flex flex-wrap gap-1 xs:mt-3 xs:gap-2">
        <template x-if="searchQuery">
            <span class="inline-flex items-center gap-1 rounded bg-blue-100 px-2 py-1 text-xs text-blue-800 xs:px-2.5 xs:py-1.5 xs:text-sm">
                Pencarian: "<span x-text="searchQuery" class="max-w-[100px] truncate xs:max-w-[120px] sm:max-w-none"></span>"
                <button @click="searchQuery = ''" class="ml-0.5 text-xs font-bold text-blue-600 hover:text-blue-800 xs:text-sm">×</button>
            </span>
        </template>

        <template x-if="selectedLabel">
            <span class="inline-flex items-center gap-1 rounded bg-green-100 px-2 py-1 text-xs text-green-800 xs:px-2.5 xs:py-1.5 xs:text-sm">
                Label: <span x-text="selectedLabel" class="max-w-[80px] truncate xs:max-w-[100px] sm:max-w-none"></span>
                <button @click="selectedLabel = ''" class="ml-0.5 text-xs font-bold text-green-600 hover:text-green-800 xs:text-sm">×</button>
            </span>
        </template>

        <template x-if="selectedMember">
            <span class="inline-flex items-center gap-1 rounded bg-purple-100 px-2 py-1 text-xs text-purple-800 xs:px-2.5 xs:py-1.5 xs:text-sm">
                Peserta: <span x-text="selectedMember" class="max-w-[80px] truncate xs:max-w-[100px] sm:max-w-none"></span>
                <button @click="selectedMember = ''" class="ml-0.5 text-xs font-bold text-purple-600 hover:text-purple-800 xs:text-sm">×</button>
            </span>
        </template>

        <template x-if="selectedDeadline">
            <span class="inline-flex items-center gap-1 rounded bg-orange-100 px-2 py-1 text-xs text-orange-800 xs:px-2.5 xs:py-1.5 xs:text-sm">
                <span x-text="getDeadlineFilterText()" class="max-w-[80px] truncate xs:max-w-[100px] sm:max-w-none"></span>
                <button @click="selectedDeadline = ''" class="ml-0.5 text-xs font-bold text-orange-600 hover:text-orange-800 xs:text-sm">×</button>
            </span>
        </template>
    </div>
</div>