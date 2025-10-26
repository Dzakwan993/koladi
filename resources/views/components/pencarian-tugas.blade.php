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