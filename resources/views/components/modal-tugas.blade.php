<!-- Modal Detail Phase -->
<div x-show="phaseModal.open" x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-800" x-text="phaseModal.title"></h2>
            <p class="text-gray-600 mt-1" x-text="phaseModal.description"></p>

            <!-- Phase Date Range & Duration -->
            <div class="mt-4 bg-blue-50 rounded-lg p-4" x-show="phaseModal.start_date || phaseModal.end_date">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <!-- Start Date -->
                    <div class="flex items-center gap-2" x-show="phaseModal.start_date">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <div class="font-medium text-gray-700">Mulai Fase</div>
                            <div class="text-gray-600" x-text="formatDate(phaseModal.start_date)"></div>
                        </div>
                    </div>

                    <!-- End Date -->
                    <div class="flex items-center gap-2" x-show="phaseModal.end_date">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <div class="font-medium text-gray-700">Selesai Fase</div>
                            <div class="text-gray-600" x-text="formatDate(phaseModal.end_date)"></div>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="flex items-center gap-2" x-show="phaseModal.duration > 0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <div class="font-medium text-gray-700">Total Durasi</div>
                            <div class="text-gray-600" x-text="phaseModal.duration + ' hari'"></div>
                        </div>
                    </div>
                </div>

                <!-- Date Range Summary -->
                <div class="mt-3 pt-3 border-t border-blue-200" 
                     x-show="phaseModal.start_date && phaseModal.end_date">
                    <div class="text-center text-sm text-blue-700 font-medium">
                        <span x-text="'Phase ini berlangsung dari ' + formatDate(phaseModal.start_date) + ' hingga ' + formatDate(phaseModal.end_date)"></span>
                    </div>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-gray-700">Progress Fase</span>
                    <span class="font-bold text-lg"
                        :class="{
                            'text-green-600': phaseModal.progress === 100,
                            'text-blue-600': phaseModal.progress > 0 && phaseModal.progress < 100,
                            'text-gray-400': phaseModal.progress === 0
                        }"
                        x-text="`${phaseModal.progress}%`">
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all duration-500"
                        :class="{
                            'bg-green-500': phaseModal.progress === 100,
                            'bg-blue-500': phaseModal.progress > 0 && phaseModal.progress < 100,
                            'bg-gray-300': phaseModal.progress === 0
                        }"
                        :style="`width: ${phaseModal.progress}%`">
                    </div>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mt-1">
                    <span x-text="`${phaseModal.completedTasks} tugas selesai`"></span>
                    <span x-text="`${phaseModal.totalTasks} total tugas`"></span>
                </div>
            </div>
        </div>

        <!-- Rest of the modal content remains the same -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Daftar Tugas</h3>
            <div class="space-y-4">
                <template x-for="task in phaseModal.tasks" :key="task.id">
                    <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-all duration-200"
                        :class="{
                            'border-green-200 bg-green-50': task.status === 'done',
                            'border-blue-200 bg-blue-50': task.status === 'inprogress',
                            'border-gray-200': task.status === 'todo'
                        }"
                        @click="openDetail(task.id)">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="font-medium text-gray-800" x-text="task.title"></h4>
                                    <span class="text-xs px-2 py-1 rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800': task.status === 'done',
                                            'bg-blue-100 text-blue-800': task.status === 'inprogress',
                                            'bg-gray-100 text-gray-800': task.status === 'todo'
                                        }"
                                        x-text="task.status === 'done' ? 'Selesai' : (task.status === 'inprogress' ? 'Dikerjakan' : 'To Do')">
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span x-text="`Mulai: ${formatDate(task.start_datetime)}`"></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="`Tenggat: ${formatDate(task.due_datetime)}`"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600 mb-1" x-text="`${calculateProgress(task)}%`">
                                </div>
                                <div class="w-24 h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 rounded-full transition-all duration-300"
                                        :class="{
                                            'bg-green-500': task.status === 'done',
                                            'bg-blue-500': task.status === 'inprogress',
                                            'bg-gray-400': task.status === 'todo'
                                        }"
                                        :style="`width: ${calculateProgress(task)}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-6 border-t flex justify-end">
            <button @click="phaseModal.open = false"
                class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>



 <!-- Modal Aksi List -->
 {{-- <div x-show="openListMenu && !replyView.active" x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition
     @click.self="openListMenu = null">

     <div class="bg-white rounded-2xl shadow-xl w-full max-w-xs mx-auto">
         <!-- Header -->
         <div class="px-6 py-4 border-b">
             <h2 class="text-center font-bold text-lg text-gray-800">Aksi List</h2>
         </div>

         <!-- Menu Options -->
         <div class="p-4 space-y-3">
             <!-- Urutkan tugas dari tenggat waktu terdekat -->
             <button @click="sortTasks('deadline-asc'); openListMenu = null"
                 class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                 <div class="font-medium">Urutkan tugas dari tenggat waktu terdekat</div>
             </button>

             <!-- Urutkan tugas dari tenggat waktu terjauh -->
             <button @click="sortTasks('deadline-desc'); openListMenu = null"
                 class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                 <div class="font-medium">Urutkan tugas dari tenggat waktu terjauh</div>
             </button>

             <!-- Urutkan tugas dari waktu dibuat terdekat -->
             <button @click="sortTasks('created-asc'); openListMenu = null"
                 class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                 <div class="font-medium">Urutkan tugas dari waktu dibuat terdekat</div>
             </button>

             <!-- Urutkan tugas dari waktu dibuat terjauh -->
             <button @click="sortTasks('created-desc'); openListMenu = null"
                 class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 border border-gray-200 hover:border-blue-300">
                 <div class="font-medium">Urutkan tugas dari waktu dibuat terjauh</div>
             </button>
         </div>

         <!-- Footer -->
         <div class="px-6 py-4 border-t bg-gray-50 rounded-b-2xl">
             <button @click="openListMenu = null"
                 class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition-colors duration-200">
                 Tutup
             </button>
         </div>
     </div>
 </div> --}}


 <!-- Modal Tambah List -->
 <div x-show="openModal && !replyView.active" x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
     <div class="bg-white p-6 rounded-xl w-80 shadow-lg">
         <h2 class="text-center font-bold text-lg mb-4">Tambah Kolom Kanban</h2>

         <!-- Loading State -->
         <div x-show="addingColumn" class="text-center py-4">
             <div class="inline-flex items-center">
                 <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24">
                     <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                         stroke-width="4"></circle>
                     <path class="opacity-75" fill="currentColor"
                         d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                     </path>
                 </svg>
                 Menambahkan kolom...
             </div>
         </div>

         <!-- Form -->
         <div x-show="!addingColumn">
             <input type="text" x-model="newListName" placeholder="Masukkan nama kolom"
                 @keydown.enter="addNewColumn()"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                 :disabled="addingColumn">

             <div class="flex justify-end gap-3">
                 <button @click="openModal = false" :disabled="addingColumn"
                     class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white transition-colors disabled:opacity-50">
                     Batal
                 </button>
                 <button @click="addNewColumn()" :disabled="!newListName.trim() || addingColumn"
                     class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                     Simpan
                 </button>
             </div>
         </div>
     </div>
 </div>


 <!-- Modal Tambah Tugas -->
 <div x-show="openTaskModal && !replyView.active" x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition
     @click.self="openTaskModal = false">

     <div class="bg-white rounded-xl w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">
         <!-- Header Modal -->
         <div class="bg-white px-6 py-5 border-b">
             <h2 class="text-center font-bold text-xl text-gray-800">Buat Tugas Baru</h2>
             {{-- <p class="text-center text-sm text-gray-500 mt-1">Didalam To do list di HQ</p> --}}
         </div>

         <form @submit.prevent="createTask()" class="p-6 space-y-4">
             <!-- Nama Tugas -->
             <input type="hidden" x-model="currentColumnId" name="board_column_id">

             <div>
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Nama Tugas <span
                         class="text-red-500">*</span></label>
                 <input type="text" x-model="taskForm.title" placeholder="Masukkan nama tugas..."
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                     required />
             </div>

             <!-- PHASE INPUT -->
             <!-- Di modal tambah tugas -->
             <div>
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Fase <span
                         class="text-red-500">*</span></label>
                 <input type="text" x-model="taskForm.phase" placeholder="Masukkan nama phase" required
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                 {{-- <p x-show="!taskForm.phase" class="text-red-500 text-xs mt-1">Phase wajib diisi</p> --}}
             </div>

             <!-- Anggota & Tugas Rahasia -->
             <!-- Di dalam modal tambah tugas, setelah section Anggota -->
             <div class="mb-4">
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Anggota<span
                         class="text-red-500"> *</span></label>
                 
                 <div class="flex items-center justify-between">
                     <div class="flex items-center gap-2">
                         <template x-for="(member, index) in taskForm.members" :key="member?.id || index">
                             <div class="relative">
                                 <img :src="member.avatar" class="w-9 h-9 rounded-full border-2 border-gray-300"
                                     :alt="member.name" :title="member.name">
                                 <button type="button" @click="removeAssignedMember(member.id)"
                                     class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">
                                     ×
                                 </button>
                             </div>
                         </template>


                         <!-- Tombol tambah anggota -->
                         <button type="button" @click="openAddMemberModalForTask()"
                             class="w-9 h-9 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:border-gray-400 transition">
                             <span class="text-xl font-light">+</span>
                         </button>
                     </div>
                     <!-- ✅ SWITCH BUTTON TUGAS RAHASIA -->
                     <div class="flex items-center gap-3 bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                         <span class="text-xs text-blue-700 font-medium">Tugas Rahasia?</span>
                         <label class="relative inline-flex items-center cursor-pointer">
                             <input type="checkbox" x-model="taskForm.is_secret" class="sr-only peer">
                             <div
                                 class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                             </div>
                         </label>
                         <span class="text-sm font-medium"
                             :class="taskForm.is_secret ? 'text-blue-700' : 'text-gray-500'"
                             x-text="taskForm.is_secret ? 'Ya' : 'Tidak'">
                         </span>
                     </div>
                 </div>

                 <!-- ✅ INFO TEXT UNTUK TUGAS RAHASIA -->
                 <div x-show="taskForm.is_secret" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                     <div class="flex items-start gap-2">
                         <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                         </svg>
                         <div>
                             <p class="text-xs text-blue-800 font-medium">Tugas ini hanya akan terlihat oleh:</p>
                             <ul class="text-xs text-blue-700 mt-1 list-disc list-inside">
                                 <li>Anggota yang ditugaskan</li>
                                 <li>Super Admin, Administrator dan Manajer</li>
                                 <li>Pembuat tugas</li>
                             </ul>
                         </div>
                     </div>
                 </div>
             </div>




             <!-- Catatan -->
             <!-- Di modal tambah tugas -->
<div class="mb-4">
    <label class="text-sm font-medium text-gray-700 mb-2 block">Catatan<span
                         class="text-red-500">*</span></label>
    <div class="border rounded-lg overflow-hidden">
        <!-- ✅ PASTIKAN ID INI ADA -->
        <textarea id="editor-catatan" x-model="taskForm.description"></textarea>
    </div>
</div>

             {{-- <script>
                 document.addEventListener("DOMContentLoaded", () => {
                     // Inisialisasi CKEditor untuk catatan
                     let catatanEditor = null;

                     ClassicEditor
                         .create(document.querySelector('#editor-catatan'), {
                             toolbar: {
                                 items: [
                                     'undo', 'redo', '|',
                                     'heading', '|',
                                     'bold', 'italic', 'underline', 'strikethrough', '|',
                                     'fontColor', 'fontBackgroundColor', '|',
                                     'link', 'blockQuote', 'code', '|',
                                     'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                                     'insertTable', 'imageUpload', 'mediaEmbed'
                                 ],
                                 shouldNotGroupWhenFull: true
                             },
                             heading: {
                                 options: [{
                                         model: 'paragraph',
                                         title: 'Paragraf',
                                         class: 'ck-heading_paragraph'
                                     },
                                     {
                                         model: 'heading1',
                                         view: 'h1',
                                         title: 'Heading 1',
                                         class: 'ck-heading_heading1'
                                     },
                                     {
                                         model: 'heading2',
                                         view: 'h2',
                                         title: 'Heading 2',
                                         class: 'ck-heading_heading2'
                                     },
                                     {
                                         model: 'heading3',
                                         view: 'h3',
                                         title: 'Heading 3',
                                         class: 'ck-heading_heading3'
                                     }
                                 ]
                             },
                             fontColor: {
                                 colors: [{
                                         color: 'black',
                                         label: 'Hitam'
                                     },
                                     {
                                         color: 'red',
                                         label: 'Merah'
                                     },
                                     {
                                         color: 'blue',
                                         label: 'Biru'
                                     },
                                     {
                                         color: 'green',
                                         label: 'Hijau'
                                     },
                                     {
                                         color: 'orange',
                                         label: 'Oranye'
                                     },
                                     {
                                         color: 'purple',
                                         label: 'Ungu'
                                     }
                                 ]
                             },
                             fontBackgroundColor: {
                                 colors: [{
                                         color: 'yellow',
                                         label: 'Kuning'
                                     },
                                     {
                                         color: 'lightgreen',
                                         label: 'Hijau Muda'
                                     },
                                     {
                                         color: 'lightblue',
                                         label: 'Biru Muda'
                                     },
                                     {
                                         color: 'pink',
                                         label: 'Merah Muda'
                                     },
                                     {
                                         color: 'gray',
                                         label: 'Abu-abu'
                                     }
                                 ]
                             },
                             image: {
                                 toolbar: [
                                     'imageTextAlternative', 'imageStyle:inline',
                                     'imageStyle:block', 'imageStyle:side'
                                 ]
                             },
                             table: {
                                 contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                             },
                             mediaEmbed: {
                                 previewsInData: true
                             },
                             placeholder: 'Tulis catatan tugas di sini...'
                         })
                         .then(editor => {
                             catatanEditor = editor;
                             console.log('CKEditor catatan siap dipakai:', editor);

                             // Simpan instance editor ke global variable
                             if (typeof taskEditors === 'undefined') {
                                 window.taskEditors = {};
                             }
                             taskEditors['editor-catatan'] = editor;
                         })
                         .catch(error => {
                             console.error('Error initializing CKEditor:', error);
                             // Fallback: tampilkan textarea biasa
                             const textarea = document.querySelector('#editor-catatan');
                             if (textarea) {
                                 textarea.style.display = 'block';
                                 textarea.style.minHeight = '120px';
                                 textarea.style.padding = '12px';
                                 textarea.placeholder = 'Tulis catatan tugas di sini...';
                             }
                         });
                 });
             </script> --}}



             <!-- Di dalam modal tambah tugas, setelah section Checklist -->
             <!-- Lampiran Section -->
             <div class="mb-4">
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Lampiran</label>

                 <!-- Upload Area -->
                 <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center mb-3 bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer"
                     @click="$refs.fileInput.click()" x-data="{ isDragging: false }"
                     @drop.prevent="isDragging = false; handleFileDrop($event)" @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false" :class="{ 'border-blue-500 bg-blue-50': isDragging }">

                     <input type="file" x-ref="fileInput" class="hidden" multiple
                         @change="handleFileSelect($event)"
                         accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">

                     <div class="flex flex-col items-center justify-center py-4">
                         <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                         </svg>
                         <p class="text-sm text-gray-600 mb-1">
                             <span class="text-blue-600 font-medium">Klik untuk upload</span> atau drag & drop
                         </p>
                         <p class="text-xs text-gray-500">File maksimal 10MB. Format: JPG, PNG, PDF, DOC, XLS, PPT</p>
                     </div>
                 </div>

                 <!-- Upload Progress -->
                 <div x-show="uploading" class="mb-3">
                     <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                         <span>Mengupload...</span>
                         <span x-text="uploadProgress + '%'"></span>
                     </div>
                     <div class="w-full bg-gray-200 rounded-full h-2">
                         <div class="h-2 rounded-full bg-blue-500 transition-all duration-300"
                             :style="`width: ${uploadProgress}%`"></div>
                     </div>
                 </div>

                 <!-- List File yang akan diupload -->
                 <div class="space-y-2 max-h-40 overflow-y-auto" x-show="taskForm.attachments.length > 0">
                     <template x-for="(file, index) in taskForm.attachments" :key="file.id || file.name">
                         <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3 bg-white">
                             <div class="flex items-center gap-3 flex-1 min-w-0">
                                 <!-- File Icon -->
                                 <div class="flex-shrink-0">
                                     <template x-if="file.type === 'pdf'">
                                         <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                             <path fill-rule="evenodd"
                                                 d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                 clip-rule="evenodd" />
                                         </svg>
                                     </template>
                                     <template x-if="file.type === 'image'">
                                         <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                 d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                         </svg>
                                     </template>
                                     <template x-if="!file.type || file.type === 'other'">
                                         <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                             <path fill-rule="evenodd"
                                                 d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                 clip-rule="evenodd" />
                                         </svg>
                                     </template>
                                 </div>

                                 <!-- File Info -->
                                 <div class="flex-1 min-w-0">
                                     <p class="text-sm font-medium text-gray-700 truncate" x-text="file.name"></p>
                                     <p class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></p>
                                 </div>
                             </div>

                             <!-- Actions -->
                             <div class="flex items-center gap-2 flex-shrink-0">
                                 <!-- Preview Button untuk gambar -->
                                 <button x-show="file.type === 'image'" type="button" @click="previewFile(file)"
                                     class="text-blue-600 hover:text-blue-800 p-1" title="Pratinjau">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                     </svg>
                                 </button>

                                 <!-- Remove Button -->
                                 <button type="button" @click="removeAttachment(index)"
                                     class="text-red-600 hover:text-red-800 p-1" title="Hapus">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                     </svg>
                                 </button>
                             </div>
                         </div>
                     </template>
                 </div>

                 <!-- Empty State -->
                 <div x-show="taskForm.attachments.length === 0 && !uploading"
                     class="text-center py-4 text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg">
                     <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                     </svg>
                     Belum ada file yang dipilih
                 </div>
             </div>

             <!-- Modal Preview Gambar -->
             <div x-show="previewModal.open" x-cloak
                 class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-75 z-50 p-4">
                 <div class="bg-white rounded-lg max-w-4xl max-h-full overflow-auto">
                     <div class="flex justify-between items-center p-4 border-b">
                         <h3 class="text-lg font-semibold" x-text="previewModal.file?.name"></h3>
                         <button @click="previewModal.open = false" class="text-gray-500 hover:text-gray-700">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                     d="M6 18L18 6M6 6l12 12" />
                             </svg>
                         </button>
                     </div>
                     <div class="p-4">
                         <img :src="previewModal.url" :alt="previewModal.file?.name"
                             class="max-w-full h-auto mx-auto">
                     </div>
                 </div>
             </div>



             <!-- Tombol Pilih Label -->
             <div>
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Label</label>
                 <button type="button" @click="openLabelModal = true"
                     class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm text-left text-gray-600 hover:bg-gray-50 flex items-center justify-between bg-white shadow-sm">
                     <span>Pilih Label Tugas</span>
                     <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                     </svg>
                 </button>
             </div>

             <!-- Di dalam modal tambah tugas, setelah section "Tombol Pilih Label" -->
             <!-- Tampilkan Label yang Sudah Dipilih -->
             <div x-show="taskForm.labels && taskForm.labels.length > 0" class="mb-4">
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Label Terpilih</label>
                 <div class="flex flex-wrap gap-2">
                     <template x-for="label in taskForm.labels" :key="label.id">
                         <div class="flex items-center gap-2 bg-gray-100 rounded-full px-3 py-1">
                             <span class="inline-block w-3 h-3 rounded-full"
                                 :style="`background:${label.color}`"></span>
                             <span class="text-sm font-medium text-gray-700" x-text="label.name"></span>
                             <button type="button" @click="removeSelectedLabel(label.id)"
                                 class="text-gray-500 hover:text-red-500 text-xs">
                                 ×
                             </button>
                         </div>
                     </template>
                 </div>
             </div>

             <!-- Empty State untuk Label -->
             <div x-show="!taskForm.labels || taskForm.labels.length === 0"
                 class="mb-4 p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                 <p class="text-sm text-gray-500 text-center">Belum ada label yang dipilih</p>
             </div>

             <!-- Modal Pilih Label -->
<div x-show="openLabelModal" x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 modal-layer-3 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pilih Label</h2>

        <!-- ✅ TAMBAHKAN INFO TEXT UNTUK EDIT MODE -->
        <div x-show="isEditMode" class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-700">
                💡 Label akan disimpan setelah Anda klik <strong>"Simpan Perubahan"</strong> di halaman detail tugas.
            </p>
        </div>

        <!-- Search -->
        <input type="text" x-model="labelData.searchLabel" placeholder="Cari label..."
            class="w-full border rounded-lg p-2 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

        <!-- Tombol Tambah Label -->
        <button type="button" @click="openAddLabelModal = true; openLabelModal = false"
            class="text-blue-600 text-sm hover:underline font-medium mb-3">
            + Tambah Label Baru
        </button>

        <!-- List Label -->
        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
            <template x-for="label in filteredLabels()" :key="label.id">
                <label
                    class="flex items-center cursor-pointer border rounded-lg px-3 py-2 hover:bg-gray-50 transition">
                    <input type="checkbox" x-model="label.selected"
                        class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <div class="flex-1">
                        <span
                            class="inline-block px-3 py-1 rounded-md text-white text-sm font-medium shadow-sm"
                            :style="`background:${label.color.rgb}`" x-text="label.name">
                        </span>
                    </div>
                </label>
            </template>

            <!-- Empty State -->
            <div x-show="filteredLabels().length === 0" class="text-center py-4 text-gray-500 text-sm">
                <template x-if="labelData.searchLabel">
                    Tidak ada label yang cocok
                </template>
                <template x-if="!labelData.searchLabel">
                    Tidak ada label tersedia
                </template>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center mt-6 space-x-2">
            <button type="button"
                @click="openLabelModal = false; labelData.labels.forEach(l => l.selected = false)"
                class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
            <button type="button" @click="saveTaskLabels(currentTask ? currentTask.id : null)"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
                Simpan (<span x-text="labelData.labels.filter(l => l.selected).length"></span>)
            </button>
        </div>
    </div>
</div>

             <!-- Modal Tambah Label Baru -->
             <div x-show="openAddLabelModal" x-cloak
                 class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 modal-layer-4 p-4">
                 <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                     <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Label Baru</h2>

                     <!-- Input nama -->
                     <input type="text" x-model="labelData.newLabelName" placeholder="Nama Label"
                         class="w-full border rounded-lg p-2 text-sm mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                     <!-- Pilihan Warna -->
                     <div class="grid grid-cols-6 gap-2 mb-4 max-h-40 overflow-y-auto pr-1">
                         <template x-for="color in labelData.colors" :key="color.id">
                             <div class="w-8 h-8 rounded-lg cursor-pointer border shadow-sm"
                                 :style="`background:${color.rgb}`" @click="labelData.newLabelColor = color.rgb"
                                 :class="{ 'ring-2 ring-offset-2 ring-blue-600': labelData.newLabelColor === color.rgb }">
                             </div>
                         </template>
                     </div>

                     <!-- Debug Info -->
                     <div class="text-xs text-gray-500 mb-2" x-show="labelData.newLabelColor">
                         Warna terpilih: <span x-text="labelData.newLabelColor"></span>
                     </div>

                     <!-- Footer -->
                     <div class="flex justify-end space-x-2 mt-4">
                         <button type="button" @click="openAddLabelModal = false; openLabelModal = true"
                             class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                         <button type="button"
                             @click="createNewLabel().then(id => { 
                    if(id) { 
                        openAddLabelModal = false; 
                        openLabelModal = true; 
                    } 
                })"
                             :disabled="!labelData.newLabelName.trim() || !labelData.newLabelColor"
                             class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                             Simpan
                         </button>
                     </div>
                 </div>
             </div>


             <!-- Checklist Section - FIXED -->
             <div>
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Checklist</label>

                 <!-- Checklist Items -->
                 <div class="space-y-2 mb-3 max-h-40 overflow-y-auto" id="checklist-container">
                     <template x-for="(item, index) in taskForm.checklists" :key="item.id">
                         <div
                             class="flex items-center gap-2 p-2 border rounded-lg bg-white hover:bg-gray-50 transition-colors">
                             <input type="checkbox" x-model="item.is_done"
                                 class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">

                             <input type="text" x-model="item.title" @blur="updateChecklistItem(item)"
                                 @keydown.enter="$event.target.blur()" @keydown.escape="$event.target.blur()"
                                 class="flex-1 border-0 focus:ring-0 p-1 text-sm bg-transparent outline-none"
                                 :class="{ 'line-through text-gray-500': item.is_done }"
                                 placeholder="Ketik item checklist..." x-ref="'checklist-input-' + index">

                             <button type="button" @click="removeChecklistItem(index)"
                                 class="text-red-500 hover:text-red-700 p-1 transition-colors" title="Hapus item">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                 </svg>
                             </button>
                         </div>
                     </template>

                     <!-- Empty State -->
                     <div x-show="!taskForm.checklists || taskForm.checklists.length === 0"
                         class="text-center py-4 text-gray-500 text-sm border-2 border-dashed border-gray-300 rounded-lg">
                         <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                         </svg>
                         Belum ada checklist
                     </div>
                 </div>

                 <!-- Add Checklist Button -->
                 <button type="button" @click="addChecklistItem()"
                     class="w-full border border-dashed border-gray-300 rounded-lg px-4 py-3 text-sm text-gray-500 hover:bg-gray-50 hover:border-gray-400 hover:text-gray-700 flex items-center justify-center gap-2 bg-white transition-all duration-200">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                     </svg>
                     Tambah Item Checklist
                 </button>

                 <!-- Progress Bar -->
                 <div x-show="taskForm.checklists && taskForm.checklists.length > 0"
                     class="mt-3 p-3 bg-gray-50 rounded-lg">
                     <div class="flex justify-between items-center text-xs text-gray-600 mb-1">
                         <span class="font-medium">Progress Checklist</span>
                         <span x-text="`${getChecklistProgress()}%`"
                             :class="{
                                 'text-green-600': getChecklistProgress() ===
                                     100,
                                 'text-blue-600': getChecklistProgress() < 100
                             }">
                         </span>
                     </div>
                     <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                         <div class="h-2 rounded-full transition-all duration-300"
                             :class="{
                                 'bg-green-500': getChecklistProgress() === 100,
                                 'bg-blue-500': getChecklistProgress() <
                                     100
                             }"
                             :style="`width: ${getChecklistProgress()}%`"></div>
                     </div>
                     <div class="text-xs text-gray-500 text-center"
                         x-text="`${getCompletedChecklists()} dari ${taskForm.checklists.length} selesai`">
                     </div>
                 </div>
             </div>

             {{-- <!-- Modal Tambah Ceklis -->
             <div x-show="openCeklisModal" x-cloak
                 class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" x-cloak>
                 <div class="bg-blue-50 rounded-xl shadow-lg w-96 p-6 text-center">
                     <h2 class="text-lg font-semibold text-gray-800 mb-4">Ceklis</h2>
                     <input type="text" x-model="newCeklisName" placeholder="Masukkan nama ceklis"
                         class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

                     <div class="flex justify-center gap-3">
                         <button type="button" @click="openCeklisModal = false"
                             class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 hover:bg-gray-50 text-sm font-medium">
                             Batal
                         </button>
                         <button type="button" @click="saveCeklis()"
                             class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                             Simpan
                         </button>
                     </div>
                 </div>
             </div> --}}


             <!-- Tanggal & Jam - TAMBAHKAN x-model -->
             <div class="grid grid-cols-2 gap-4">
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai<span
                         class="text-red-500">*</span></label>
                     <input type="date" x-model="taskForm.startDate"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai<span
                         class="text-red-500">*</span></label>
                     <!-- ✅ UBAH LABEL -->
                     <input type="time" x-model="taskForm.startTime"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Tenggat<span
                         class="text-red-500">*</span></label>
                     <input type="date" x-model="taskForm.dueDate"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Jam Tenggat<span
                         class="text-red-500">*</span></label>
                     <!-- ✅ UBAH LABEL -->
                     <input type="time" x-model="taskForm.dueTime"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
             </div>

             <!-- Tombol Simpan -->
             <div class="flex justify-center gap-3 pt-4">
                 <button type="button" @click="openTaskModal = false; resetTaskForm()"
                     class="px-10 py-2 rounded-md bg-white hover:bg-gray-50 text-blue-600 border border-blue-600 font-medium text-sm">
                     Batal
                 </button>
                 <button type="submit"
                     class="px-10 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm">
                     Simpan
                 </button>
             </div>
         </form>
     </div>
 </div>


 {{-- Modal Pilih Anggota untuk Tugas --}}
 <div x-show="openAddMemberModal" x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 modal-layer-3 p-4"
     @click.self="openAddMemberModal = false; debugMemberState()" x-transition>
     <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl">
         <div class="px-6 py-4 border-b">
             <h2 class="text-center font-bold text-lg text-gray-800">Pilih Anggota</h2>
         </div>

         <div class="p-6 space-y-4">
             <!-- Search -->
             <input type="text" placeholder="Cari anggota..."
                 class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                 x-model="searchMember">

             <!-- Select All -->
             <div class="flex items-center justify-between border-b pb-2">
                 <span class="font-medium text-gray-700 text-sm">Pilih Semua</span>
                 <input type="checkbox" x-model="selectAll" @change="toggleSelectAllMembers">
             </div>

             <!-- Members List -->
             <div class="space-y-3 max-h-60 overflow-y-auto">
                 <template x-for="(member, index) in filteredWorkspaceMembers" :key="member.id || index">
                     <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition">
                         <div class="flex items-center gap-2">
                             <img :src="member.avatar" class="w-8 h-8 rounded-full" :alt="member.name"
                                 x-show="member.avatar">
                             <div>
                                 <p class="text-sm font-medium text-gray-700" x-text="member.name || 'Unknown'"></p>
                                 <p class="text-xs text-gray-500" x-text="member.email || ''"></p>
                             </div>
                         </div>
                         <input type="checkbox" :checked="selectedMemberIds.includes(member.id)"
                             @change="toggleMember(member.id)"
                             class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                     </div>
                 </template>

                 <!-- Empty State -->
                 <div x-show="filteredWorkspaceMembers.length === 0" class="text-center py-4">
                     <p class="text-gray-500 text-sm" x-show="searchMember">
                         Tidak ada anggota yang cocok
                     </p>
                     <p class="text-gray-500 text-sm" x-show="!searchMember">
                         Tidak ada anggota di workspace
                     </p>
                 </div>
             </div>
         </div>

         <div class="flex justify-end gap-3 p-4 border-t">
             <button type="button" @click="openAddMemberModal = false; selectedMemberIds = []; searchMember = ''"
                 class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 hover:bg-gray-50">
                 Batal
             </button>
             <button type="button" @click="applyMembersToTask()"
                 class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white"
                 :disabled="selectedMemberIds.length === 0">
                 Simpan (<span x-text="selectedMemberIds.length"></span>)
             </button>
         </div>
     </div>
 </div>




 <!-- Modal Detail Tugas -->
 <div x-show="openTaskDetail && !replyView.active" x-cloak
     class="fixed inset-0 modal-layer-2 flex items-center justify-center bg-black bg-opacity-50 p-4"
     @click.self="openTaskDetail = false">

     <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
         <!-- Header -->
         <div class="bg-white px-6 py-4 border-b">
             <!-- Edit Title Section -->
             <div class="flex items-center justify-between mb-1">
                 <template x-if="!isEditMode">
                     <h2 class="text-lg font-bold text-gray-800" x-text="currentTask?.title || 'Loading...'"></h2>
                 </template>
                 <template x-if="isEditMode">
                     <div class="flex-1 mr-4">
                         <input type="text" x-model="currentTask.title"
                             class="w-full text-lg font-bold text-gray-800 bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none py-1"
                             placeholder="Masukkan judul tugas">
                         <p x-show="!currentTask.title" class="text-red-500 text-xs mt-1">Judul wajib diisi</p>
                     </div>
                 </template>

                 <div class="flex items-center gap-2">
    <!-- Edit Toggle Button -->
    <template x-if="!isEditMode">
        <button @click="enableEditMode()"
            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
            title="Edit Tugas">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
        </button>
    </template>
    
    <!-- Tombol Hapus -->
    <button @click="confirmDeleteTask()"
        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
        title="Hapus Tugas">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
    </button>
    
    <!-- Tombol Tutup -->
    <button @click="openTaskDetail = false"
        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
             </div>
             <p class="text-xs text-gray-500 text-center">
                 Ditambahkan ke <span x-text="currentTask?.board_column?.name || 'To-Do List'"></span> pada
                 <span x-text="formatDetailDate(currentTask?.created_at)"></span>.
             </p>
         </div>

         <!-- Scrollable Content -->
         <div class="overflow-y-auto flex-1 px-6 py-4">
             <!-- Tombol Pindahkan -->
             {{-- <div class="mb-4">    
                 <label class="block text-sm font-medium text-gray-700 mb-2">Pindahkan</label>
                 <button
                     class="border border-gray-300 rounded-md px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                     @click="openMoveModal = true">
                     <span>Pindahkan Tugas</span>
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                     </svg>
                 </button>
             </div> --}}

             <!-- PHASE INFORMATION -->
             <div class="mb-4">
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Fase</label>
                 <div class="flex items-center gap-2">
                     <template x-if="!isEditMode">
                         <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium"
                             x-text="currentTask?.phase || 'Tidak ada phase'"></span>
                     </template>
                     <template x-if="isEditMode">
                         <input type="text" x-model="currentTask.phase" placeholder="Masukkan phase"
                             class="border border-gray-300 rounded px-3 py-1 text-sm focus:ring-1 focus:ring-blue-500 w-48">
                     </template>
                 </div>
             </div>

             <!-- Anggota & Tugas Rahasia -->
             <div class="mb-4">
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Anggota</label>
                 <div class="flex items-center justify-between">
                     <div class="flex items-center gap-2">
                         <template x-for="member in assignedMembers" :key="member.id">
                             <div class="relative">
                                 <img :src="member.avatar" class="w-9 h-9 rounded-full border-2 border-gray-300"
                                     :alt="member.name" :title="member.name">
                                 <button x-show="isEditMode" @click="removeAssignedMember(member.id)"
                                     class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">
                                     ×
                                 </button>
                             </div>
                         </template>

                         <button x-show="isEditMode" type="button" @click="openAddMemberModalForTask(currentTask)"
                             class="w-9 h-9 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:border-gray-400 transition">
                             <span class="text-xl font-light">+</span>
                         </button>
                     </div>

                     <!-- Switch Button Tugas Rahasia -->
                     <div class="flex items-center gap-3 bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                         <span class="text-xs text-blue-700 font-medium">Tugas Rahasia?</span>
                         <label class="relative inline-flex items-center cursor-pointer">
                             <input type="checkbox" x-model="currentTask.is_secret" class="sr-only peer"
                                 :disabled="!isEditMode">
                             <div
                                 class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                             </div>
                         </label>
                         <span class="text-sm font-medium"
                             :class="currentTask.is_secret ? 'text-blue-700' : 'text-gray-500'"
                             x-text="currentTask.is_secret ? 'Ya' : 'Tidak'">
                         </span>
                     </div>
                 </div>
             </div>

             <!-- Catatan Section di Modal Detail -->
             <div class="mb-4">
                 <label class="text-sm font-medium text-gray-700 mb-2 block">Catatan</label>
                 <template x-if="!isEditMode">
                     <div class="border border-gray-300 rounded-lg p-3 bg-gray-50 min-h-[100px]">
                         <div x-html="currentTask?.description || 'Tidak ada catatan'"></div>
                     </div>
                 </template>
                 <template x-if="isEditMode">
                     <div class="border rounded-lg overflow-hidden">
                         <textarea id="editor-catatan-edit" x-model="currentTask.description"></textarea>
                     </div>
                 </template>
             </div>

             <!-- Lampiran Section dengan Upload di Edit Mode -->
             <div class="mb-4">
                 <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran</label>

                 <!-- Upload Area (Hanya tampil di Edit Mode) -->
                 <template x-if="isEditMode">
                     <div class="mb-3">
                         <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center mb-3 bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer"
                             @click="$refs.fileInputDetail.click()" x-data="{ isDragging: false }"
                             @drop.prevent="isDragging = false; handleFileDropDetail($event)"
                             @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                             :class="{ 'border-blue-500 bg-blue-50': isDragging }">

                             <input type="file" x-ref="fileInputDetail" class="hidden" multiple
                                 @change="handleFileSelectDetail($event)"
                                 accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">

                             <div class="flex flex-col items-center justify-center py-4">
                                 <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                 </svg>
                                 <p class="text-sm text-gray-600 mb-1">
                                     <span class="text-blue-600 font-medium">Klik untuk upload</span> atau drag & drop
                                 </p>
                                 <p class="text-xs text-gray-500">File maksimal 10MB. Format: JPG, PNG, PDF, DOC, XLS,
                                     PPT</p>
                             </div>
                         </div>

                         <!-- Upload Progress -->
                         <div x-show="uploadingDetail" class="mb-3">
                             <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                 <span>Mengupload...</span>
                                 <span x-text="uploadProgressDetail + '%'"></span>
                             </div>
                             <div class="w-full bg-gray-200 rounded-full h-2">
                                 <div class="h-2 rounded-full bg-blue-500 transition-all duration-300"
                                     :style="`width: ${uploadProgressDetail}%`"></div>
                             </div>
                         </div>
                     </div>
                 </template>

                 <!-- List File Attachments -->
                 <!-- Di modal-tugas.blade.php - bagian lampiran -->
<div class="space-y-2">
    <template x-for="(file, index) in currentTask?.attachments || []" :key="file.id">
        <div class="flex items-center justify-between border border-gray-300 rounded-lg p-3">
            <div class="flex items-center gap-2">
                <!-- File Icon berdasarkan tipe -->
                <template x-if="file.type === 'image'">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                </template>
                <template x-if="file.type === 'pdf'">
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                </template>
                <template x-if="!['image', 'pdf'].includes(file.type)">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                </template>
                
                <div>
                    <!-- ✅ PERBAIKAN: Gunakan file.name yang sudah benar dari backend -->
                    <p class="text-sm font-medium text-gray-700" x-text="file.name || 'Unknown File'"></p>
                    <p class="text-xs text-gray-500" 
                       x-text="file.size ? formatFileSize(file.size) : ''"></p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <a :href="file.url" target="_blank" 
                   class="text-blue-600 hover:underline">Lihat</a>
                <button x-show="isEditMode" @click="removeAttachmentFromDetail(index)"
                        class="text-red-600 hover:underline">Hapus</button>
            </div>
        </div>
    </template>

    <template x-if="!currentTask?.attachments || currentTask.attachments.length === 0">
        <div class="text-center py-4 text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg">
            Tidak ada lampiran
        </div>
    </template>
</div>
             </div>

             <!-- Label Section dengan Edit di Edit Mode -->
             <!-- Di dalam modal Detail Tugas, section Label -->
             <div class="mb-4">
                 <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                     Label
                     <button x-show="isEditMode" type="button" @click="openLabelModalForTask(currentTask)">
                         <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                         </svg>
                     </button>
                 </label>
                 <div class="flex flex-wrap gap-2">
                     <template x-for="label in currentTask?.labels || []" :key="label.id">
                         <div class="flex items-center gap-2 bg-gray-100 rounded-full px-3 py-1">
                             <span class="inline-block w-3 h-3 rounded-full"
                                 :style="`background:${label.color}`"></span>
                             <span class="text-sm font-medium text-gray-700" x-text="label.name"></span>
                             <button x-show="isEditMode" type="button" @click="removeLabelFromTask(label.id)"
                                 class="text-gray-500 hover:text-red-500 text-xs">
                                 ×
                             </button>
                         </div>
                     </template>

                     <template x-if="!currentTask?.labels || currentTask.labels.length === 0">
                         <span class="text-gray-500 text-sm">Tidak ada label</span>
                     </template>
                 </div>
             </div>


             <!-- Modal Pilih Label (Sama seperti di modal Tambah Tugas) -->
             <div x-show="openLabelModal" x-cloak
                 class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 modal-layer-3 p-4">
                 <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                     <h2 class="text-lg font-semibold text-gray-800 mb-4">Pilih Label</h2>

                     <!-- Search -->
                     <input type="text" x-model="labelData.searchLabel" placeholder="Cari label..."
                         class="w-full border rounded-lg p-2 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                     <!-- Tombol Tambah Label -->
                     <button type="button" @click="openAddLabelModal = true; openLabelModal = false"
                         class="text-blue-600 text-sm hover:underline font-medium mb-3">
                         + Tambah Label Baru
                     </button>

                     <!-- List Label -->
                     <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                         <template x-for="label in filteredLabels()" :key="label.id">
                             <label
                                 class="flex items-center cursor-pointer border rounded-lg px-3 py-2 hover:bg-gray-50 transition">
                                 <input type="checkbox" x-model="label.selected"
                                     class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                 <div class="flex-1">
                                     <span
                                         class="inline-block px-3 py-1 rounded-md text-white text-sm font-medium shadow-sm"
                                         :style="`background:${label.color.rgb}`" x-text="label.name">
                                     </span>
                                 </div>
                             </label>
                         </template>

                         <!-- Empty State -->
                         <div x-show="filteredLabels().length === 0" class="text-center py-4 text-gray-500 text-sm">
                             <template x-if="labelData.searchLabel">
                                 Tidak ada label yang cocok
                             </template>
                             <template x-if="!labelData.searchLabel">
                                 Tidak ada label tersedia
                             </template>
                         </div>
                     </div>

                     <!-- Footer -->
                     <div class="flex justify-end items-center mt-6 space-x-2">
                         <button type="button"
                             @click="openLabelModal = false; labelData.labels.forEach(l => l.selected = false)"
                             class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                         <button type="button" @click="saveTaskLabels(currentTask ? currentTask.id : null)"
                             class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
                             Simpan (<span x-text="labelData.labels.filter(l => l.selected).length"></span>)
                         </button>
                     </div>
                 </div>
             </div>

             <!-- Modal Tambah Label Baru -->
             <div x-show="openAddLabelModal" x-cloak
                 class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 modal-layer-4 p-4">
                 <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                     <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Label Baru</h2>

                     <!-- Input nama -->
                     <input type="text" x-model="labelData.newLabelName" placeholder="Nama Label"
                         class="w-full border rounded-lg p-2 text-sm mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                     <!-- Pilihan Warna -->
                     <div class="grid grid-cols-6 gap-2 mb-4 max-h-40 overflow-y-auto pr-1">
                         <template x-for="color in labelData.colors" :key="color.id">
                             <div class="w-8 h-8 rounded-lg cursor-pointer border shadow-sm"
                                 :style="`background:${color.rgb}`" @click="labelData.newLabelColor = color.rgb"
                                 :class="{ 'ring-2 ring-offset-2 ring-blue-600': labelData.newLabelColor === color.rgb }">
                             </div>
                         </template>
                     </div>

                     <!-- Debug Info -->
                     <div class="text-xs text-gray-500 mb-2" x-show="labelData.newLabelColor">
                         Warna terpilih: <span x-text="labelData.newLabelColor"></span>
                     </div>

                     <!-- Footer -->
                     <div class="flex justify-end space-x-2 mt-4">
                         <button type="button" @click="openAddLabelModal = false; openLabelModal = true"
                             class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                         <button type="button"
                             @click="createNewLabel().then(id => { 
                    if(id) { 
                        openAddLabelModal = false; 
                        openLabelModal = true; 
                    } 
                })"
                             :disabled="!labelData.newLabelName.trim() || !labelData.newLabelColor"
                             class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                             Simpan
                         </button>
                     </div>
                 </div>
             </div>

             <!-- Checklist Section -->
             <div class="mb-4">
                 <label class="block text-sm font-medium text-gray-700 mb-2">Checklist</label>

                 <!-- Progress Bar -->
                 <div x-show="currentTask?.checklist && currentTask.checklist.length > 0" class="mb-3">
                     <div class="flex justify-between items-center text-xs text-gray-600 mb-1">
                         <span>Progress Checklist</span>
                         <span x-text="`${calculateTaskProgress(currentTask)}%`"
                             :class="{
                                 'text-green-600': calculateTaskProgress(currentTask) === 100,
                                 'text-blue-600': calculateTaskProgress(currentTask) < 100
                             }">
                         </span>
                     </div>
                     <div class="w-full bg-gray-200 rounded-full h-2">
                         <div class="h-2 rounded-full transition-all duration-300"
                             :class="{
                                 'bg-green-500': calculateTaskProgress(currentTask) === 100,
                                 'bg-blue-500': calculateTaskProgress(currentTask) < 100
                             }"
                             :style="`width: ${calculateTaskProgress(currentTask)}%`"></div>
                     </div>
                 </div>

                 <!-- Checklist Items -->
                 <div class="space-y-2 border border-gray-300 rounded-lg p-3" id="detail-checklist-container">
                     <template x-for="(item, index) in currentTask?.checklist || []" :key="item.id">
                         <div class="flex items-center gap-2">
                             <input type="checkbox" x-model="item.is_done"
                                 @change="updateChecklistItemInDetail(item)"
                                 class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                 :disabled="!isEditMode">

                             <template x-if="!isEditMode">
                                 <span class="text-sm" :class="{ 'line-through text-gray-500': item.is_done }"
                                     x-text="item.title"></span>
                             </template>

                             <template x-if="isEditMode">
                                 <input type="text" x-model="item.title" @blur="updateChecklistItemInDetail(item)"
                                     class="flex-1 border-0 focus:ring-0 p-1 text-sm bg-transparent outline-none"
                                     :class="{ 'line-through text-gray-500': item.is_done }">
                             </template>

                             <button x-show="isEditMode" @click="removeChecklistItemFromDetail(index)"
                                 class="text-red-500 hover:text-red-700 p-1">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                 </svg>
                             </button>
                         </div>
                     </template>

                     <template x-if="!currentTask?.checklist || currentTask.checklist.length === 0">
                         <div class="text-center py-2 text-gray-500 text-sm">
                             Tidak ada checklist
                         </div>
                     </template>
                 </div>

                 <!-- Add Checklist Button -->
                 <button x-show="isEditMode" type="button" @click="addChecklistItemToDetail()"
                     class="w-full mt-2 border border-dashed border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-500 hover:bg-gray-50 hover:border-gray-400 hover:text-gray-700 flex items-center justify-center gap-2 bg-white transition-all duration-200">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                     </svg>
                     Tambah Item Checklist
                 </button>
             </div>

             <!-- Tanggal & Jam -->
             <div class="grid grid-cols-2 gap-4 mb-6">
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                     <input type="date" x-model="currentTask.startDate" :readonly="!isEditMode"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                     <input type="time" x-model="currentTask.startTime" :readonly="!isEditMode"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Tenggat</label>
                     <input type="date" x-model="currentTask.dueDate" :readonly="!isEditMode"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
                 <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Jam Tenggat</label>
                     <input type="time" x-model="currentTask.dueTime" :readonly="!isEditMode"
                         class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                 </div>
             </div>

             <!-- Komentar Section -->
             <!-- Komentar Section -->
<div class="border-t pt-4 mt-6" x-data="taskCommentSection()" x-ref="commentSection" x-init="init()">
    <div class="space-y-4">
        
        <!-- Tambah Komentar -->
        <div class="mb-6">
            <label class="text-sm font-medium text-gray-700 mb-2 block">Tulis Komentar</label>
            <div class="flex items-start gap-3">
                <img :src="currentUserAvatar" alt="Avatar" class="rounded-full w-10 h-10">

                <!-- Container untuk editor komentar utama -->
                <div class="flex-1" x-data="{ active: true }">
                    <div class="bg-white border border-gray-300 rounded-lg p-4">
                        <div id="task-main-comment-editor" class="min-h-[120px] bg-white"></div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button @click="destroyMainEditor()"
                                class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-lg hover:text-gray-800 transition">
                                Batal
                            </button>
                            <button @click="submitMainComment()"
                                class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Kirim
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Komentar yang sudah ada -->
        <template x-if="comments.length > 0">
            <div class="space-y-4">
                <template x-for="comment in comments" :key="comment.id">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-start gap-3">
                            <img :src="comment.author.avatar" alt="" class="w-8 h-8 rounded-full">
                            <div class="flex-1">
                                <div class="flex justify-between items-center">
                                    <p class="text-sm font-semibold text-gray-800" 
                                       x-text="comment.author.name"></p>
                                    <span class="text-xs text-gray-500" 
                                          x-text="formatCommentDate(comment.createdAt)"></span>
                                </div>
                                
                                <div class="text-sm text-gray-700 mt-1" x-html="comment.content"></div>

                                <!-- Tombol Balas -->
                                <div class="flex items-center gap-4 mt-2">
                                    <button @click="toggleReply(comment)"
                                        class="flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        <span>balas</span>
                                        <span x-show="(comment.replies ?? []).length > 0" 
                                              x-text="(comment.replies ?? []).length + ' balasan'"
                                              class="ml-1"></span>
                                    </button>
                                </div>

                                <!-- Form balasan inline -->
                                <template x-if="replyView.active && replyView.parentComment?.id === comment.id">
                                    <div class="mt-4 pl-6 border-l-2 border-gray-200">
                                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                                            <h4 class="text-sm font-semibold text-gray-800 mb-2">
                                                Membalas <span x-text="comment.author.name"></span>
                                            </h4>
                                            <div class="border border-gray-300 rounded-lg overflow-hidden mb-3">
                                                <div :id="'task-reply-editor-' + comment.id" 
                                                     class="min-h-[100px] p-3 bg-white"></div>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button @click="closeReplyView()"
                                                    class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition border border-gray-300 rounded-lg">
                                                    Batal
                                                </button>
                                                <button @click="submitReplyFromEditor()"
                                                    class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                                    Kirim
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Tampilkan balasan yang sudah ada -->
                                <template x-if="comment.replies && (comment.replies ?? []).length > 0">
                                    <div class="mt-3 pl-6 border-l-2 border-gray-200 space-y-3">
                                        <template x-for="reply in comment.replies" :key="reply.id">
                                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                                <div class="flex items-start gap-2">
                                                    <img :src="reply.author.avatar" 
                                                         class="w-6 h-6 rounded-full">
                                                    <div class="flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <p class="text-sm font-semibold text-gray-800" 
                                                               x-text="reply.author.name"></p>
                                                            <span class="text-xs text-gray-500" 
                                                                  x-text="formatCommentDate(reply.createdAt)"></span>
                                                        </div>
                                                        <div class="text-sm text-gray-700 mt-1" 
                                                             x-html="reply.content"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="comments.length === 0">
            <div class="text-center py-4 text-gray-500 text-sm">Belum ada komentar disini...</div>
        </template>
    </div>
</div>

             <!-- Tombol Aksi -->
             <div class="flex justify-end gap-3 pt-4 border-t">
                 <template x-if="!isEditMode">
                     <button @click="enableEditMode()"
                         class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                         Edit Tugas
                     </button>
                 </template>

                 <template x-if="isEditMode">
                     <div class="flex gap-3">
                         <button @click="cancelEdit()"
                             class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                             Batal
                         </button>
                         <button @click="saveTaskEdit()"
                             class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                             Simpan Perubahan
                         </button>
                     </div>
                 </template>

                 <button @click="openTaskDetail = false"
                     class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                     Tutup
                 </button>
             </div>
         </div>
     </div>
 </div>

 <!-- Script untuk CKEditor dan Alpine.js -->
 {{-- <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script> --}}
 {{-- <script>
     document.addEventListener("alpine:init", () => {
         Alpine.data('commentSection', () => ({
             comments: [
                 {
                     id: 1,
                     author: {
                         name: 'John Doe',
                         avatar: 'https://i.pravatar.cc/40?img=1'
                     },
                     content: 'Progressnya sudah berapa persen? Deadline semakin dekat nih.',
                     createdAt: new Date('2024-01-15T10:30:00'),
                     replies: [
                         {
                             id: 2,
                             author: {
                                 name: 'Jane Smith',
                                 avatar: 'https://i.pravatar.cc/40?img=5'
                             },
                             content: 'Sudah 75% complete. Besok bisa selesai.',
                             createdAt: new Date('2024-01-15T11:15:00')
                         }
                     ]
                 },
                 {
                     id: 3,
                     author: {
                         name: 'Mike Johnson',
                         avatar: 'https://i.pravatar.cc/40?img=3'
                     },
                     content: 'Jangan lupa untuk memeriksa data transaksi Q4 sebelum disubmit.',
                     createdAt: new Date('2024-01-14T14:20:00'),
                     replies: []
                 }
             ],
             replyView: {
                 active: false,
                 parentComment: null
             },

             init() {
                 // Inisialisasi CKEditor untuk komentar utama
                 this.$nextTick(() => {
                     this.initializeMainEditor();
                 });
             },

             initializeMainEditor() {
                 const editorElement = document.getElementById('task-main-comment-editor');
                 if (editorElement && !editorElement._editor) {
                     ClassicEditor
                         .create(editorElement, {
                             toolbar: {
                                 items: [
                                     'undo', 'redo', '|',
                                     'heading', '|',
                                     'bold', 'italic', 'underline', 'strikethrough', '|',
                                     'fontColor', 'fontBackgroundColor', '|',
                                     'link', 'blockQuote', 'code', '|',
                                     'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                                     'insertTable', 'imageUpload', 'mediaEmbed'
                                 ],
                                 shouldNotGroupWhenFull: true
                             },
                             heading: {
                                 options: [
                                     {
                                         model: 'paragraph',
                                         title: 'Paragraf',
                                         class: 'ck-heading_paragraph'
                                     },
                                     {
                                         model: 'heading1',
                                         view: 'h1',
                                         title: 'Heading 1',
                                         class: 'ck-heading_heading1'
                                     },
                                     {
                                         model: 'heading2',
                                         view: 'h2',
                                         title: 'Heading 2',
                                         class: 'ck-heading_heading2'
                                     },
                                     {
                                         model: 'heading3',
                                         view: 'h3',
                                         title: 'Heading 3',
                                         class: 'ck-heading_heading3'
                                     }
                                 ]
                             },
                             fontColor: {
                                 colors: [
                                     {
                                         color: 'black',
                                         label: 'Hitam'
                                     },
                                     {
                                         color: 'red',
                                         label: 'Merah'
                                     },
                                     {
                                         color: 'blue',
                                         label: 'Biru'
                                     },
                                     {
                                         color: 'green',
                                         label: 'Hijau'
                                     },
                                     {
                                         color: 'orange',
                                         label: 'Oranye'
                                     },
                                     {
                                         color: 'purple',
                                         label: 'Ungu'
                                     }
                                 ]
                             },
                             fontBackgroundColor: {
                                 colors: [
                                     {
                                         color: 'yellow',
                                         label: 'Kuning'
                                     },
                                     {
                                         color: 'lightgreen',
                                         label: 'Hijau Muda'
                                     },
                                     {
                                         color: 'lightblue',
                                         label: 'Biru Muda'
                                     },
                                     {
                                         color: 'pink',
                                         label: 'Merah Muda'
                                     },
                                     {
                                         color: 'gray',
                                         label: 'Abu-abu'
                                     }
                                 ]
                             },
                             image: {
                                 toolbar: [
                                     'imageTextAlternative', 'imageStyle:inline',
                                     'imageStyle:block', 'imageStyle:side'
                                 ]
                             },
                             table: {
                                 contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                             },
                             mediaEmbed: {
                                 previewsInData: true
                             },
                             placeholder: 'Tulis komentar Anda...'
                         })
                         .then(editor => {
                             editorElement._editor = editor;
                         })
                         .catch(error => {
                             console.error('Error initializing main editor:', error);
                         });
                 }
             },

             initializeReplyEditor(commentId) {
                 const editorId = `task-reply-editor-${commentId}`;
                 const editorElement = document.getElementById(editorId);
                 
                 if (editorElement && !editorElement._editor) {
                     ClassicEditor
                         .create(editorElement, {
                             toolbar: {
                                 items: [
                                     'undo', 'redo', '|',
                                     'heading', '|',
                                     'bold', 'italic', 'underline', 'strikethrough', '|',
                                     'fontColor', 'fontBackgroundColor', '|',
                                     'link', 'blockQuote', 'code', '|',
                                     'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                                     'insertTable', 'imageUpload', 'mediaEmbed'
                                 ],
                                 shouldNotGroupWhenFull: true
                             },
                             heading: {
                                 options: [
                                     {
                                         model: 'paragraph',
                                         title: 'Paragraf',
                                         class: 'ck-heading_paragraph'
                                     },
                                     {
                                         model: 'heading1',
                                         view: 'h1',
                                         title: 'Heading 1',
                                         class: 'ck-heading_heading1'
                                     },
                                     {
                                         model: 'heading2',
                                         view: 'h2',
                                         title: 'Heading 2',
                                         class: 'ck-heading_heading2'
                                     },
                                     {
                                         model: 'heading3',
                                         view: 'h3',
                                         title: 'Heading 3',
                                         class: 'ck-heading_heading3'
                                     }
                                 ]
                             },
                             fontColor: {
                                 colors: [
                                     {
                                         color: 'black',
                                         label: 'Hitam'
                                     },
                                     {
                                         color: 'red',
                                         label: 'Merah'
                                     },
                                     {
                                         color: 'blue',
                                         label: 'Biru'
                                     },
                                     {
                                         color: 'green',
                                         label: 'Hijau'
                                     },
                                     {
                                         color: 'orange',
                                         label: 'Oranye'
                                     },
                                     {
                                         color: 'purple',
                                         label: 'Ungu'
                                     }
                                 ]
                             },
                             fontBackgroundColor: {
                                 colors: [
                                     {
                                         color: 'yellow',
                                         label: 'Kuning'
                                     },
                                     {
                                         color: 'lightgreen',
                                         label: 'Hijau Muda'
                                     },
                                     {
                                         color: 'lightblue',
                                         label: 'Biru Muda'
                                     },
                                     {
                                         color: 'pink',
                                         label: 'Merah Muda'
                                     },
                                     {
                                         color: 'gray',
                                         label: 'Abu-abu'
                                     }
                                 ]
                             },
                             image: {
                                 toolbar: [
                                     'imageTextAlternative', 'imageStyle:inline',
                                     'imageStyle:block', 'imageStyle:side'
                                 ]
                             },
                             table: {
                                 contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                             },
                             mediaEmbed: {
                                 previewsInData: true
                             },
                             placeholder: 'Tulis balasan Anda...'
                         })
                         .then(editor => {
                             editorElement._editor = editor;
                         })
                         .catch(error => {
                             console.error('Error initializing reply editor:', error);
                         });
                 }
             },

             toggleReply(comment) {
                 if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                     this.closeReplyView();
                 } else {
                     this.replyView.active = true;
                     this.replyView.parentComment = comment;
                     
                     this.$nextTick(() => {
                         this.initializeReplyEditor(comment.id);
                     });
                 }
             },

             closeReplyView() {
                 this.replyView.active = false;
                 this.replyView.parentComment = null;
             },

             submitMain() {
                 const editorElement = document.getElementById('task-main-comment-editor');
                 if (editorElement && editorElement._editor) {
                     const content = editorElement._editor.getData();
                     if (content.trim()) {
                         // Simulasikan penambahan komentar baru
                         const newComment = {
                             id: Date.now(),
                             author: {
                                 name: 'Current User',
                                 avatar: 'https://i.pravatar.cc/40?img=11'
                             },
                             content: content,
                             createdAt: new Date(),
                             replies: []
                         };
                         
                         this.comments.unshift(newComment);
                         editorElement._editor.setData('');
                         
                         // Tampilkan notifikasi atau lakukan API call
                         console.log('Komentar utama dikirim:', content);
                     }
                 }
             },

             submitReplyFromEditor() {
                 if (!this.replyView.parentComment) return;

                 const editorId = `task-reply-editor-${this.replyView.parentComment.id}`;
                 const editorElement = document.getElementById(editorId);
                 
                 if (editorElement && editorElement._editor) {
                     const content = editorElement._editor.getData();
                     if (content.trim()) {
                         // Tambahkan balasan ke komentar parent
                         const newReply = {
                             id: Date.now(),
                             author: {
                                 name: 'Current User',
                                 avatar: 'https://i.pravatar.cc/40?img=11'
                             },
                             content: content,
                             createdAt: new Date()
                         };

                         if (!this.replyView.parentComment.replies) {
                             this.replyView.parentComment.replies = [];
                         }
                         
                         this.replyView.parentComment.replies.push(newReply);
                         editorElement._editor.setData('');
                         this.closeReplyView();
                         
                         // Tampilkan notifikasi atau lakukan API call
                         console.log('Balasan dikirim:', content);
                     }
                 }
             },

             formatCommentDate(date) {
                 return new Date(date).toLocaleDateString('id-ID', {
                     day: 'numeric',
                     month: 'long',
                     year: 'numeric',
                     hour: '2-digit',
                     minute: '2-digit'
                 });
             },

             destroyMainEditorForTask(editorId) {
                 const editorElement = document.getElementById(editorId);
                 if (editorElement && editorElement._editor) {
                     editorElement._editor.destroy()
                         .then(() => {
                             editorElement._editor = null;
                             console.log('Editor destroyed successfully');
                         })
                         .catch(error => {
                             console.error('Error destroying editor:', error);
                         });
                 }
             }
         }));
     });
 </script> --}}
