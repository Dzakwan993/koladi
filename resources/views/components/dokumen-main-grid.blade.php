{{-- Grid Dokumen Utama (Scrollable) --}}
<template x-if="filteredDocuments.length > 0 && !currentFolder && !currentFile">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-x-6 gap-y-3 items-start">
        <template x-for="document in filteredDocuments" :key="document.id">
            <div @click="selectMode ? toggleDocumentSelection(document) : (document.type === 'Folder' ? openFolder(document) : openFile(document))"
                :class="{
                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(document.id),
                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(document.id),
                    'cursor-pointer': true
                }"
                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                <!-- Checkbox untuk select mode -->
                <div x-show="selectMode" class="absolute top-2 right-2">
                    <div :class="isDocumentSelected(document.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'"
                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                        <svg x-show="isDocumentSelected(document.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <img :src="document.icon" :alt="document.type" class="w-14 h-14 mb-3">
                <div class="flex items-center gap-1">
                    <span class="text-xs text-gray-600 truncate w-full" x-text="document.name"></span>
                    <template x-if="document.isSecret">
                        <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </template>
                </div>
                <span class="text-xs text-gray-400 mt-1" x-text="document.type"></span>
            </div>
        </template>
    </div>
</template>

{{-- Empty State untuk Pencarian --}}
<template x-if="filteredDocuments.length === 0 && searchQuery.length > 0 && !currentFolder && !currentFile">
    <div class="flex-1 flex flex-col items-center justify-center text-gray-500">
        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-lg font-medium mb-2">Tidak ada hasil ditemukan</p>
        <p class="text-sm">Coba gunakan kata kunci lain atau <button @click="clearSearch()" class="text-blue-600 hover:text-blue-800">bersihkan pencarian</button></p>
    </div>
</template>