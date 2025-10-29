{{-- Default View (ketika tidak ada pencarian dan tidak di dalam folder) --}}
<template x-if="filteredDocuments.length === 0 && searchQuery.length === 0 && !currentFolder && !currentFile">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 overflow-y-auto flex-1 pb-4">
        {{-- Folder --}}
        <template x-for="folder in folders" :key="folder.id">
            <div @click="selectMode ? toggleDocumentSelection(folder) : openFolder(folder)"
                :class="{
                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(folder.id),
                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(folder.id),
                    'cursor-pointer': true
                }"
                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                <!-- Checkbox untuk select mode -->
                <div x-show="selectMode" class="absolute top-2 right-2">
                    <div :class="isDocumentSelected(folder.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'"
                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                        <svg x-show="isDocumentSelected(folder.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <img src="{{ asset('images/icons/folder.svg') }}" alt="Folder" class="w-14 h-14 mb-3">
                <div class="flex items-center gap-1">
                    <span class="text-sm font-medium text-gray-700" x-text="folder.name"></span>
                    <template x-if="folder.isSecret">
                        <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </template>
                </div>
            </div>
        </template>

        {{-- File PDF --}}
        <template x-for="(pdf, index) in pdfFiles" :key="pdf.id">
            <div @click="selectMode ? toggleDocumentSelection(pdf) : openFile(pdf)"
                :class="{
                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(pdf.id),
                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(pdf.id),
                    'cursor-pointer': true
                }"
                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                <!-- Checkbox untuk select mode -->
                <div x-show="selectMode" class="absolute top-2 right-2">
                    <div :class="isDocumentSelected(pdf.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'"
                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                        <svg x-show="isDocumentSelected(pdf.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <img src="{{ asset('images/icons/pdf.svg') }}" alt="PDF" class="w-14 h-14 mb-3">
                <span class="text-xs text-gray-600 truncate w-full" x-text="pdf.name"></span>
            </div>
        </template>

        {{-- File Word --}}
        <template x-for="(word, index) in wordFiles" :key="word.id">
            <div @click="selectMode ? toggleDocumentSelection(word) : openFile(word)"
                :class="{
                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(word.id),
                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(word.id),
                    'cursor-pointer': true
                }"
                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                <!-- Checkbox untuk select mode -->
                <div x-show="selectMode" class="absolute top-2 right-2">
                    <div :class="isDocumentSelected(word.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'"
                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                        <svg x-show="isDocumentSelected(word.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <img src="{{ asset('images/icons/microsoft-word.svg') }}" alt="File" class="w-14 h-14 mb-3">
                <span class="text-xs text-gray-600 truncate w-full" x-text="word.name"></span>
            </div>
        </template>

        {{-- File Excel --}}
        <template x-for="(excel, index) in excelFiles" :key="excel.id">
            <div @click="selectMode ? toggleDocumentSelection(excel) : openFile(excel)"
                :class="{
                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(excel.id),
                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(excel.id),
                    'cursor-pointer': true
                }"
                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                <!-- Checkbox untuk select mode -->
                <div x-show="selectMode" class="absolute top-2 right-2">
                    <div :class="isDocumentSelected(excel.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'"
                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                        <svg x-show="isDocumentSelected(excel.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <img src="{{ asset('images/icons/excel.svg') }}" alt="Excel" class="w-14 h-14 mb-3">
                <span class="text-xs text-gray-600 truncate w-full" x-text="excel.name"></span>
            </div>
        </template>
    </div>
</template>