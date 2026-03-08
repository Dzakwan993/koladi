{{-- Grid Dokumen Utama (Scrollable) --}}
<template x-if="filteredDocuments.length > 0 && !currentFolder && !currentFile">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-x-6 gap-y-3 items-start">
        <template x-for="document in filteredDocuments" :key="document.id">
            <div @click="selectMode ? toggleDocumentSelection(document) : (document.type === 'Folder' ? openFolder(document) : (document.type === 'Link' ? window.open(document.file_url, '_blank') : openFile(document)))"
                :class="{
                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(document.id),
                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(document.id),
                    'cursor-pointer': true
                }"
                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                
                <!-- Checkbox untuk select mode -->
                <div x-show="selectMode" class="absolute top-2 right-2 z-10">
                    <div :class="isDocumentSelected(document.id) ? 'bg-blue-600 border-blue-600' : 'bg-white border-gray-300'"
                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                        <svg x-show="isDocumentSelected(document.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <!-- =========================== -->
                <!-- UI UNTUK FILE BIASA/FOLDER  -->
                <!-- =========================== -->
                <template x-if="document.type !== 'Link'">
                    <div class="flex flex-col items-center w-full">
                    <div class="w-14 h-14 mb-3 flex items-center justify-center overflow-hidden">
                        <!-- IMAGE THUMBNAIL -->
                        <template x-if="document.type === 'Image'">
                            <img :src="document.file_url" alt="Image" class="w-full h-full object-cover rounded">
                        </template>
                        <!-- VIDEO THUMBNAIL -->
                        <template x-if="document.type === 'Video'">
                            <video :src="document.file_url" class="w-full h-full object-cover rounded" muted></video>
                        </template>
                        <!-- DEFAULT ICON (Folder, PDF, dll) -->
                        <template x-if="document.type !== 'Image' && document.type !== 'Video'">
                            <img :src="document.icon" :alt="document.type" class="w-14 h-14">
                        </template>
                    </div>
                    <span class="text-xs text-gray-600 w-full break-words line-clamp-2 text-center" x-text="document.name" :title="document.name"></span>
                    <div class="flex items-center gap-1 mt-1 justify-center">
                        <span class="text-[10px] text-gray-400" x-text="document.type"></span>
                        <template x-if="document.isSecret">
                            <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </template>
                    </div>
                    </div>
                </template>

                <!-- =========================== -->
                <!-- UI KHUSUS UNTUK TIPE LINK   -->
                <!-- =========================== -->
                <template x-if="document.type === 'Link'">
                    <div class="flex flex-col w-full text-left">
                        <!-- Preview Cover -->
                        <div class="w-full h-24 bg-gray-200 rounded mb-2 overflow-hidden flex border border-gray-100 shadow-inner">
                            <template x-if="document.preview_image_url">
                                <img :src="document.preview_image_url" class="w-full h-full object-cover">
                            </template>
                            <!-- Fallback jika website tidak punya gambar -->
                            <template x-if="!document.preview_image_url">
                                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Judul dan Link Asli -->
                        <span class="text-xs font-semibold text-gray-800 w-full truncate" x-text="document.name" :title="document.name"></span>
                        <span class="text-[10px] text-blue-500 w-full truncate mt-0.5" x-text="document.file_url" :title="document.file_url"></span>
                        
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-medium">Web Link</span>
                            <template x-if="document.isSecret">
                                <svg class="w-3 h-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>