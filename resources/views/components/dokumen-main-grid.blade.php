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

                <!-- =========================== -->
                <!-- PREVIEW / ICON              -->
                <!-- =========================== -->
                <div class="w-14 h-14 mb-3 flex items-center justify-center overflow-hidden">

                    <!-- IMAGE THUMBNAIL -->
                    <template x-if="document.type === 'Image'">
                        <img 
                            :src="document.file_url" 
                            alt="Image"
                            class="w-full h-full object-cover rounded">
                    </template>

                    <!-- VIDEO THUMBNAIL -->
                    <template x-if="document.type === 'Video'">
                        <video 
                            :src="document.file_url"
                            class="w-full h-full object-cover rounded"
                            muted
                        ></video>
                    </template>

                    <!-- DEFAULT ICON (Folder, PDF, dll) -->
                    <template x-if="document.type !== 'Image' && document.type !== 'Video'">
                        <img 
                            :src="document.icon" 
                            :alt="document.type" 
                            class="w-14 h-14">
                    </template>

                </div>

                <!-- Nama File/Folder dengan word-break -->
                <span 
                    class="text-xs text-gray-600 w-full break-words line-clamp-2" 
                    x-text="document.name"
                    :title="document.name">
                </span>
                
                <!-- Tipe File dengan icon secret -->
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs text-gray-400" x-text="document.type"></span>
                    <template x-if="document.isSecret">
                        <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </template>
                </div>
            </div>
        </template>
    </div>
</template>