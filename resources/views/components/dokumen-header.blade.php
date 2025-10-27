 {{-- Header dengan Pencarian dan Tombol Aksi --}}
 <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6 flex-shrink-0" x-show="!currentFile">
     {{-- Tombol Aksi --}}
     <div class="flex flex-wrap gap-2 sm:gap-3" x-show="!selectMode">
         <button @click="showCreateFolderModal = true"
             class="bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg hover:bg-blue-700 flex items-center gap-1 sm:gap-2 transition text-xs sm:text-sm">
             <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
             </svg>
             <span class="font-medium text-sm" x-text="currentFolder ? 'Buat Sub Folder' : 'Buat Folder'"></span>
         </button>

         <label
             class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition cursor-pointer">
             <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round"
                     d="M12 16v-8m0 0l-4 4m4-4l4 4m4 4a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h3l2-2h6a2 2 0 012 2v12z" />
             </svg>
             <span class="font-medium text-sm">Unggah File</span>
             <input type="file" class="hidden" @change="uploadFileToFolder($event)">
         </label>

         <button @click="toggleSelectMode()"
             class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition">
             <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
             </svg>
             <span class="font-medium text-sm">Pilih Berkas</span>
         </button>

     </div>

     {{-- Tombol Batalkan Pilihan (muncul saat select mode) --}}
     <div class="flex gap-3" x-show="selectMode">
         <button @click="cancelSelection()"
             class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
             </svg>
             <span class="font-medium text-sm">Batalkan Pilihan</span>
         </button>
     </div>

     {{-- Search Bar --}}
     <div class="relative w-full sm:w-64 lg:w-80" x-show="!selectMode">
         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
             <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
             </svg>
         </div>
         <input type="text" x-model="searchQuery" @input="filterDocuments()"
             :placeholder="currentFolder ? 'Cari dalam ' + currentFolder.name : 'Cari dokumen atau folder...'"
             class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
     </div>

     {{-- Spacer untuk menjaga layout saat Select Mode --}}
     <div class="w-80" x-show="selectMode"></div>
 </div>
