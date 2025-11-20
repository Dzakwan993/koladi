{{-- Grid Dokumen di Dalam Folder --}}
                <template x-if="currentFolder">
                    <div
                         class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-6 gap-y-3 items-start pb-4">
                        {{-- Tampilkan hasil pencarian atau dokumen biasa --}}
                        <template x-for="document in getDisplayedDocuments()" :key="document.id">
                            <div @click="selectMode ? toggleDocumentSelection(document) : (document.type === 'Folder' ? openFolder(document) : openFile(document))"
                                :class="{
                                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(
                                        document.id),
                                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(
                                        document.id),
                                    'cursor-pointer': true
                                }"
                                class="flex flex-col items-center text-center p-2 sm:p-3 border rounded-lg transition relative">

                                <!-- Checkbox untuk select mode -->
                                <div x-show="selectMode" class="absolute top-1 right-1 sm:top-2 sm:right-2">
                                    <div :class="isDocumentSelected(document.id) ? 'bg-blue-600 border-blue-600' :
                                        'bg-white border-gray-300'"
                                        class="w-4 h-4 sm:w-5 sm:h-5 border-2 rounded flex items-center justify-center">
                                        <svg x-show="isDocumentSelected(document.id)"
                                            class="w-2 h-2 sm:w-3 sm:h-3 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- PREVIEW -->
                                <div class="w-8 h-8 sm:w-10 sm:h-10 mb-1 sm:mb-2 flex items-center justify-center overflow-hidden rounded">

                                    <!-- IMAGE PREVIEW -->
                                    <template x-if="document.type === 'Image'">
                                        <img 
                                            :src="document.file_url"
                                            class="w-full h-full object-cover"
                                            alt="image preview">
                                    </template>

                                    <!-- VIDEO PREVIEW -->
                                    <template x-if="document.type === 'Video'">
                                        <video 
                                            :src="document.file_url"
                                            class="w-full h-full object-cover"
                                            muted
                                        ></video>
                                    </template>

                                    <!-- DEFAULT ICON (folder, pdf, docx, dll) -->
                                    <template x-if="document.type !== 'Image' && document.type !== 'Video'">
                                        <img 
                                            :src="document.icon"
                                            :alt="document.type"
                                            class="w-8 h-8 sm:w-10 sm:h-10">
                                    </template>

                                </div>
                                <span class="text-xs font-medium text-gray-700 truncate w-full"
                                    x-text="document.name"></span>

                                <span x-show="document.type !== 'Folder'" class="text-xs text-gray-400 mt-0.5"
                                    x-text="document.type"></span>
                            </div>
                        </template>

                        {{-- Empty State --}}
                        <div x-show="getDisplayedDocuments().length === 0 && searchQuery.length > 0"
                            class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-5 xl:col-span-6
            flex flex-col items-center justify-center py-6 sm:py-8 text-gray-500">
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 rounded-full flex items-center justify-center mb-2 sm:mb-3">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium mb-1">Tidak ada hasil ditemukan</p>
                            <p class="text-xs">Coba gunakan kata kunci lain</p>
                        </div>

                        <div x-show="getDisplayedDocuments().length === 0 && searchQuery.length === 0"
                            class="col-span-6 flex flex-col items-center justify-center py-8 text-gray-500">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium mb-1">Folder ini kosong</p>
                            <p class="text-xs">Tambahkan file atau folder baru</p>
                        </div>
                    </div>
                    
                </template>