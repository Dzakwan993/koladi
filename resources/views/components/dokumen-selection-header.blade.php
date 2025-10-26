{{-- Header Pilihan (muncul saat select mode) --}}
                <div x-show="selectMode && selectedDocuments.length > 0"
                    class="mb-3 sm:mb-4 flex-shrink-0 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                        <div class="flex items-center gap-2 sm:gap-4">
                            <button @click="cancelSelection()"
                                class="flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="font-medium">Batal</span>
                            </button>
                            <div class="h-6 w-px bg-gray-300"></div>
                            <span class="text-sm text-gray-600">
                                <span x-text="selectedDocuments.length"></span> berkas dipilih
                            </span>
                        </div>
                        <button @click="showMoveDocumentsModal = true"
                            class="bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 transition text-xs sm:text-sm mt-2 sm:mt-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span class="font-medium text-sm">Pindahkan</span>
                        </button>
                    </div>
                </div>