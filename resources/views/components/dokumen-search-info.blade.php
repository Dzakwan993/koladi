{{-- Hasil Pencarian Info di Dalam Folder --}}
                <div x-show="searchQuery.length > 0 && !(selectMode && selectedDocuments.length > 0) && currentFolder"
                    class="mb-4 flex-shrink-0">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span x-text="filteredDocuments.length"></span> hasil dari pencarian "<span
                            x-text="searchQuery"></span>" dalam <span x-text="getCurrentFolderPath()"></span>
                        <button @click="clearSearch()" class="text-blue-600 hover:text-blue-800 ml-2 text-sm">
                            Bersihkan pencarian
                        </button>
                    </p>
                </div>


                {{-- Hasil Pencarian Info --}}
                <div x-show="searchQuery.length > 0 && !(selectMode && selectedDocuments.length > 0) && !currentFolder && !currentFile"
                    class="mb-4 flex-shrink-0">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span x-text="filteredDocuments.length"></span> hasil dari pencarian "<span
                            x-text="searchQuery"></span>"
                        <button @click="clearSearch()" class="text-blue-600 hover:text-blue-800 ml-2 text-sm">
                            Bersihkan pencarian
                        </button>
                    </p>
                </div>