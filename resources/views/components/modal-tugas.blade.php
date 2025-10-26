 <!-- Modal Detail Phase -->
        <div x-show="phaseModal.open" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
            <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-800" x-text="phaseModal.title"></h2>
                    <p class="text-gray-600 mt-1" x-text="phaseModal.description"></p>

                    <!-- Progress Overview -->
                    <div class="mt-4 bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-700">Progress Phase</span>
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
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span x-text="`Mulai: ${formatDate(task.startDate)}`"></span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span x-text="`Tenggat: ${formatDate(task.dueDate)}`"></span>
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
        <div x-show="openListMenu && !replyView.active" x-cloak
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
        </div>


         <!-- Modal Tambah List -->
        <div x-show="openModal && !replyView.active"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-6 rounded-xl w-80 shadow-lg">
                <h2 class="text-center font-bold text-lg mb-4">Kanban List</h2>
                <input type="text" x-model="newListName" placeholder="Masukkan nama list"
                    class="w-full border rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring focus:ring-blue-300" />
                <div class="flex justify-end gap-3">
                    <button @click="openModal = false" class="px-4 py-2 rounded-lg bg-red-400 text-white">Batal</button>
                    <button @click="addList()" class="px-4 py-2 rounded-lg bg-blue-800 text-white">
                        Simpan
                    </button>

                </div>
            </div>
        </div>


        <!-- Modal Tambah Tugas -->
        <div x-show="openTaskModal && !replyView.active" x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition
            @click.self="openTaskModal = false"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition>

            <div class="bg-white rounded-xl w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <!-- Header Modal -->
                <div class="bg-white px-6 py-5 border-b">
                    <h2 class="text-center font-bold text-xl text-gray-800">Buat Tugas Baru</h2>
                    <p class="text-center text-sm text-gray-500 mt-1">Didalam To do list di HQ</p>
                </div>

                <!-- Form Content -->
                <form class="p-6 space-y-4">
                    <!-- Nama Tugas -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Nama Tugas <span
                                class="text-red-500">*</span></label>
                        <input type="text" placeholder="Masukkan nama tugas..."
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                    </div>

                    <!-- PHASE INPUT - TEMPATKAN DI SINI -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Phase <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="taskForm.phase"
                            placeholder="Masukkan nama phase (contoh: Inisiasi, Perencanaan, Eksekusi)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Anggota & Tugas Rahasia -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Anggota <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <button type="button" @click="openAddMemberModal = true"
                                    class="text-gray-500 text-xl hover:text-gray-700 font-light">+</button>

                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-600">Rahasia hanya untuk yang terlibat?</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div
                                        class="w-12 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                                    </div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Tugas Rahasia</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tambah Peserta -->
                    <div x-show="openAddMemberModal" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                        x-transition>
                        <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b">
                                <h2 class="text-center font-bold text-lg text-gray-800">Tambah Peserta</h2>
                            </div>

                            <!-- Isi Modal -->
                            <div class="p-6 space-y-4">
                                <!-- Input Cari -->
                                <div class="relative">
                                    <input type="text" placeholder="Cari anggota..."
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                        x-model="searchMember">
                                </div>

                                <!-- Pilih Semua -->
                                <div class="flex items-center justify-between border-b pb-2">
                                    <span class="font-medium text-gray-700 text-sm">Pilih Semua</span>
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll">
                                </div>

                                <!-- List Anggota -->
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    <template x-for="(member, index) in filteredMembers()" :key="index">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <img :src="member.avatar" class="w-8 h-8 rounded-full" alt="">
                                                <span class="text-sm font-medium text-gray-700"
                                                    x-text="member.name"></span>
                                            </div>
                                            <input type="checkbox" x-model="member.selected">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-3 p-4 border-t">
                                <button type="button" @click="openAddMemberModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedMembers()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 mb-2 block">
                            Catatan <span class="text-red-500">*</span>
                        </label>
                        <div class="border rounded-lg overflow-hidden">
                            <textarea id="editor-catatan" name="catatan"></textarea>
                        </div>
                    </div>

                    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            ClassicEditor
                                .create(document.querySelector('#editor-catatan'), {
                                    toolbar: {
                                        items: [
                                            'undo', 'redo', '|',
                                            'heading', '|',
                                            'bold', 'italic', 'underline', 'strikethrough', '|',
                                            'fontColor', 'fontBackgroundColor', '|', // 🎨 warna teks & background
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
                                    }
                                })
                                .then(editor => {
                                    console.log('CKEditor siap dipakai:', editor);

                                    // Simpan value ke textarea saat submit form
                                    const form = document.querySelector("form");
                                    form?.addEventListener("submit", () => {
                                        document.querySelector("#editor-catatan").value = editor.getData();
                                    });
                                })
                                .catch(error => console.error(error));
                        });
                    </script>



                    <!-- Lampiran -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Lampiran</label>
                        <label
                            class="border border-gray-300 rounded-md px-4 py-2.5 flex items-center justify-between hover:border-gray-400 cursor-pointer bg-white">
                            <span class="text-sm text-gray-500">Unggah File</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <input type="file" class="hidden" />
                        </label>
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

                    <!-- Modal Pilih Label -->
                    <div x-show="openLabelModal" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Pilih Label</h2>

                            <!-- Search -->
                            <input type="text" x-model="searchLabel" placeholder="Cari label..."
                                class="w-full border rounded-lg p-2 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Tombol Tambah Label -->
                            <button type="button" @click="openAddLabelModal = true; openLabelModal = false"
                                class="text-blue-600 text-sm hover:underline font-medium mb-3">
                                + Tambah Label
                            </button>

                            <!-- List Label -->
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                <template x-for="label in filteredLabels()" :key="label.name">
                                    <label
                                        class="flex items-center cursor-pointer border rounded-lg px-3 py-2 hover:bg-gray-50 transition">
                                        <input type="checkbox" x-model="label.selected"
                                            class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <div class="flex-1">
                                            <span
                                                class="block w-full text-center px-3 py-1 rounded-md text-white text-sm font-medium shadow-sm"
                                                :style="`background:${label.color}`" x-text="label.name">
                                            </span>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end items-center mt-6 space-x-2">
                                <button type="button" @click="openLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedLabels"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>


                    <!-- Modal Tambah Label -->
                    <div x-show="openAddLabelModal" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Label Baru</h2>

                            <!-- Input nama -->
                            <input type="text" x-model="newLabelName" placeholder="Nama Label"
                                class="w-full border rounded-lg p-2 text-sm mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Pilihan Warna -->
                            <div class="grid grid-cols-6 gap-2 mb-4 max-h-40 overflow-y-auto pr-1">
                                <template x-for="color in colorPalette" :key="color">
                                    <div class="w-8 h-8 rounded-lg cursor-pointer border shadow-sm"
                                        :style="`background:${color}`" @click="newLabelColor = color"
                                        :class="{ 'ring-2 ring-offset-2 ring-blue-600': newLabelColor === color }">
                                    </div>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="openAddLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="addNewLabel"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>


                    <!-- Ceklis -->
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Ceklis</label>
                        <button type="button" @click="openCeklisModal = true"
                            class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm text-left text-gray-500 hover:bg-gray-50 flex items-center justify-between bg-white">
                            <span>Buat ceklis</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Tambah Ceklis -->
                    <div x-show="openCeklisModal"
                        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" x-cloak>
                        <div class="bg-blue-50 rounded-xl shadow-lg w-96 p-6 text-center">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Ceklis</h2>
                            <input type="text" x-model="newCeklisName" placeholder="Masukkan nama ceklis"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

                            <div class="flex justify-center gap-3">
                                <button type="button" @click="openCeklisModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 text-sm font-medium">
                                    Batal
                                </button>
                                <button type="button" @click="saveCeklis()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>


                    <!-- Tanggal & Jam -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam</label>
                            <input type="time"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tenggat</label>
                            <input type="date"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam</label>
                            <input type="time"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-center gap-3 pt-4">
                        <button type="button" @click="openTaskModal = false"
                            class="px-10 py-2 rounded-md bg-white  hover:bg-gray-50 text-blue-600 border border-blue-600 font-medium text-sm">
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





         <!-- Modal Detail Tugas -->
        <div x-show="openTaskDetail && !replyView.active" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-transition
            @click.self="openTaskDetail = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-transition>
            <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

                <!-- Header -->
                <div class="bg-white px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-800 text-center mb-1">
                        MENYELESAIKAN LAPORAN KEUANGANnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn
                    </h2>
                    <p class="text-xs text-gray-500 text-center">
                        Ditambahkan ke To-Do List di HQ pada 27 September.
                    </p>
                </div>

                <!-- Scrollable Content -->
                <div class="overflow-y-auto flex-1 px-6 py-4">
                    <!-- Tombol Pindahkan -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pindahkan</label>
                        <button
                            class="border border-gray-300 rounded-md px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                            @click="openMoveModal = true">
                            <span>Pindahkan Tugas</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>


                    <!-- PHASE INFORMATION - TEMPATKAN DI SINI -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Phase</label>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium"
                                x-text="currentTask?.phase || 'Tidak ada phase'"></span>
                            <template x-if="isEditMode">
                                <input type="text" x-model="currentTask.phase" placeholder="Masukkan phase"
                                    class="border border-gray-300 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500">
                            </template>
                        </div>
                    </div>

                    <!-- Modal Pindahkan -->
                    <div x-show="openMoveModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                        x-transition>
                        <div class="bg-blue-50 rounded-xl shadow-xl w-full max-w-xs p-6">
                            <h2 class="text-center font-semibold text-gray-800 text-lg mb-4">Pindahkan</h2>

                            <!-- Tujuan List -->
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan list</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm">
                                        <option>To do list</option>
                                        <option>Dikerjakan</option>
                                        <option>Selesai</option>
                                        <option>Bata;</option>
                                    </select>
                                    <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm">
                                        <option>1</option>
                                        <option>2</option>
                                        <option>3</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Tujuan Tim -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan tim</label>
                                <select class="w-full border border-gray-300 rounded-md px-2 py-1.5 text-sm">
                                    <option>HQ</option>
                                    <option>Finance</option>
                                    <option>Marketing</option>
                                    <option>Proyek jalan</option>
                                </select>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" @click="openMoveModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 text-sm ">
                                    Batal
                                </button>
                                <button type="button"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>



                    <!-- Anggota & Tugas Rahasia -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Anggota <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <img src="https://i.pravatar.cc/40?img=1"
                                    class="w-9 h-9 rounded-full border-2 border-gray-300" alt="avatar" />
                                <button type="button" @click="openAddMemberModal = true"
                                    class="text-gray-500 text-xl hover:text-gray-700 font-light">+</button>

                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-600">Rahasia hanya untuk yang terlibat?</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div
                                        class="w-12 h-6 bg-gray-300 rounded-full peer peer-checked:after:translate-x-6 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500">
                                    </div>
                                </label>
                                <span class="text-sm font-medium text-gray-700">Tugas Rahasia</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Tambah Peserta -->
                    <div x-show="openAddMemberModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                        x-transition>
                        <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b">
                                <h2 class="text-center font-bold text-lg text-gray-800">Tambah Peserta</h2>
                            </div>

                            <!-- Isi Modal -->
                            <div class="p-6 space-y-4">
                                <!-- Input Cari -->
                                <div class="relative">
                                    <input type="text" placeholder="Cari anggota..."
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                        x-model="searchMember">
                                </div>

                                <!-- Pilih Semua -->
                                <div class="flex items-center justify-between border-b pb-2">
                                    <span class="font-medium text-gray-700 text-sm">Pilih Semua</span>
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll">
                                </div>

                                <!-- List Anggota -->
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    <template x-for="(member, index) in filteredMembers()" :key="index">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <img :src="member.avatar" class="w-8 h-8 rounded-full" alt="">
                                                <span class="text-sm font-medium text-gray-700"
                                                    x-text="member.name"></span>
                                            </div>
                                            <input type="checkbox" x-model="member.selected">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-3 p-4 border-t">
                                <button type="button" @click="openAddMemberModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedMembers()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4" x-data="{ editing: false, editor: null }">
                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                            Catatan
                            <button type="button" class="p-1 rounded hover:bg-green-100"
                                @click="
                if (!editing) {
                    editing = true;
                    ClassicEditor.create($refs.catatanEditor).then(ed => editor = ed);
                }
            ">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </label>

                        <!-- Textarea biasa -->
                        <textarea x-show="!editing"
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            rows="3" x-ref="catatanText" readonly>Laporan keuangan Q4 harus diselesaikan sebelum tanggal 30 September. Data transaksi sudah ada di sistem, tinggal verifikasi dan penyusunan format final PDF.</textarea>

                        <!-- Textarea CKEditor -->
                        <div x-show="editing" class="border rounded-lg overflow-hidden">
                            <textarea id="editor-catatan" x-ref="catatanEditor">
Laporan keuangan Q4 harus diselesaikan sebelum tanggal 30 September. Data transaksi sudah ada di sistem, tinggal verifikasi dan penyusunan format final PDF.
        </textarea>
                        </div>

                        <!-- Tombol Simpan & Batal -->
                        <div x-show="editing" class="flex justify-end gap-2 mt-2">
                            <button type="button"
                                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                @click="
                $refs.catatanText.value = editor.getData();
                editor.destroy();
                editor = null;
                editing = false;
            ">
                                Simpan
                            </button>
                            <button type="button"
                                class="px-3 py-1.5 text-sm bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
                                @click="
                editor.destroy();
                editor = null;
                editing = false;
            ">
                                Batal
                            </button>
                        </div>
                    </div>

                    <!-- CKEditor -->
                    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

                    <!-- Alpine.js -->
                    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

                    <script>
                        document.addEventListener("alpine:init", () => {
                            console.log("Alpine siap digunakan!");
                        });
                    </script>

                    <!-- Lampiran -->
                    <div class="mb-4" x-data="lampiranHandler()">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran</label>

                        <!-- Daftar Lampiran -->
                        <div class="space-y-2">
                            <template x-for="(file, index) in files" :key="index">
                                <div class="flex items-center justify-between border border-gray-300 rounded-lg p-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                                        </svg>
                                        <span class="text-sm" x-text="file.name"></span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs">
                                        <button class="text-blue-600 hover:underline"
                                            @click="downloadFile(file)">Unduh</button>
                                        <button class="text-red-600 hover:underline"
                                            @click="removeFile(index)">Hapus</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Tombol Tambah -->
                        <button type="button" @click="$refs.fileInput.click()"
                            class="mt-3 px-2 py-1 text-xs text-white bg-blue-600 rounded-md hover:bg-blue-700 flex items-center gap-1">
                            <span>Tambah</span>
                            <span class="text-base font-light">+</span>
                        </button>

                        <!-- Input File Tersembunyi -->
                        <input type="file" x-ref="fileInput" class="hidden" @change="addFile">
                    </div>

                    <!-- Alpine.js -->
                    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

                    <script>
                        function lampiranHandler() {
                            return {
                                files: [{
                                        name: 'Draft_Laporan_Q4.docx'
                                    },
                                    {
                                        name: 'Data_Transaksi_Q4.xlsx'
                                    }
                                ],

                                addFile(event) {
                                    const file = event.target.files[0];
                                    if (file) {
                                        this.files.push({
                                            name: file.name
                                        });
                                    }
                                    event.target.value = ''; // reset input file
                                },

                                removeFile(index) {
                                    this.files.splice(index, 1);
                                },

                                downloadFile(file) {
                                    alert(`Mengunduh: ${file.name}`);
                                    // di sini bisa disesuaikan kalau ingin fungsi unduh nyata
                                }
                            }
                        }
                    </script>


                    <!-- Label -->
                    <div class="mb-4">
                        <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-2">
                            Label
                            <button type="button" @click="openLabelModal = true">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </label>
                        <!-- Tampilkan semua label tugas -->
                        <div class="flex flex-wrap gap-2">
                            <template x-for="label in currentTask.labels" :key="label.name">
                                <span class="inline-block px-3 py-1 rounded-md text-white text-sm font-medium shadow-sm"
                                    :style="`background:${label.color}`" x-text="label.name">
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Modal Pilih Label -->
                    <div x-show="openLabelModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Pilih Label</h2>

                            <!-- Search -->
                            <input type="text" x-model="searchLabel" placeholder="Cari label..."
                                class="w-full border rounded-lg p-2 text-sm mb-3 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Tombol Tambah Label -->
                            <button type="button" @click="openAddLabelModal = true; openLabelModal = false"
                                class="text-blue-600 text-sm hover:underline font-medium mb-3">
                                + Tambah Label
                            </button>

                            <!-- List Label -->
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                <template x-for="label in filteredLabels()" :key="label.name">
                                    <label
                                        class="flex items-center cursor-pointer border rounded-lg px-3 py-2 hover:bg-gray-50 transition">
                                        <input type="checkbox" x-model="label.selected"
                                            class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <div class="flex-1">
                                            <span
                                                class="block w-full text-center px-3 py-1 rounded-md text-white text-sm font-medium shadow-sm"
                                                :style="`background:${label.color}`" x-text="label.name">
                                            </span>
                                        </div>
                                    </label>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end items-center mt-6 space-x-2">
                                <button type="button" @click="openLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="saveSelectedLabels"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>


                    <!-- Modal Tambah Label -->
                    <div x-show="openAddLabelModal && !replyView.active" x-cloak
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Label Baru</h2>

                            <!-- Input nama -->
                            <input type="text" x-model="newLabelName" placeholder="Nama Label"
                                class="w-full border rounded-lg p-2 text-sm mb-4 focus:ring-2 focus:ring-blue-500 focus:outline-none" />

                            <!-- Pilihan Warna -->
                            <div class="grid grid-cols-6 gap-2 mb-4 max-h-40 overflow-y-auto pr-1">
                                <template x-for="color in colorPalette" :key="color">
                                    <div class="w-8 h-8 rounded-lg cursor-pointer border shadow-sm"
                                        :style="`background:${color}`" @click="newLabelColor = color"
                                        :class="{ 'ring-2 ring-offset-2 ring-blue-600': newLabelColor === color }">
                                    </div>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end space-x-2 mt-4">
                                <button type="button" @click="openAddLabelModal=false"
                                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">Batal</button>
                                <button type="button" @click="addNewLabel"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">Simpan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Ceklis -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ceklis</label>
                        <div class="space-y-2 border border-gray-300 rounded-lg p-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" checked class="rounded border-gray-300 text-blue-600">
                                <span class="line-through text-gray-500">Kumpulkan data transaksi</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span>Verifikasi data dengan tim Finance</span>
                            </label>
                        </div>
                        <button type="button" @click="openCeklisModal = true"
                            class="mt-3 px-2 py-1 text-xs text-white bg-blue-600 rounded-md hover:bg-blue-700 flex items-center gap-1">
                            <span>Tambah</span>
                            <span class="text-base font-light">+</span>
                        </button>
                    </div>

                    <!-- Modal Tambah Ceklis -->
                    <div x-show="openCeklisModal && !replyView.active"
                        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" x-cloak>
                        <div class="bg-blue-50 rounded-xl shadow-lg w-96 p-6 text-center">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Ceklis</h2>
                            <input type="text" x-model="newCeklisName" placeholder="Masukkan nama ceklis"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

                            <div class="flex justify-center gap-3">
                                <button type="button" @click="openCeklisModal = false"
                                    class="px-4 py-2 rounded-lg text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 text-sm font-medium">
                                    Batal
                                </button>
                                <button type="button" @click="saveCeklis()"
                                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal & Jam -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" value="2025-10-04"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                            <input type="time" value="14:30"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tenggat</label>
                            <input type="date" value="2025-10-05"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Tenggat</label>
                            <input type="time" value="16:00"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>


                    <!-- Komentar Section -->

                    <!-- Komentar Section -->
                    <div class="border-t pt-4 mt-6">
                        <div class="space-y-4">

                            <!-- Tambah Komentar -->
                            <div class="mb-6">
                                <label class="text-sm font-medium text-gray-700 mb-2 block">Tulis Komentar</label>
                                <div class="border rounded-lg overflow-hidden">
                                    <textarea id="editor-komentar" name="komentar"></textarea>
                                </div>
                                <div class="flex justify-end gap-2 mt-2">
                                    <button type="button"
                                        class="mt-3 px-4 py-2 text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 text-sm rounded-lg">
                                        Batal
                                    </button>
                                    <button type="button"
                                        class="mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                                        Kirim
                                    </button>
                                </div>
                            </div>

                            <!-- Komentar 1 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-semibold text-gray-800">Risi Gustiar</p>
                                    <span class="text-xs text-gray-500">Sabtu, 27 Sep 2025</span>
                                </div>
                                <p class="text-sm text-gray-700">
                                    Data transaksi sudah saya update di file Excel.
                                </p>
                                <button
                                    @click="openReplyFromModal({
                    id: 1,
                    author: { name: 'Risi Gustiar', avatar: 'https://i.pravatar.cc/40?img=3' },
                    content: 'Data transaksi sudah saya update di file Excel.',
                    createdAt: '2025-09-27T10:00:00',
                    replies: []
                })"
                                    class="mt-2 flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    <span>balas</span>
                                    <span class="ml-1">2 balasan</span>
                                </button>
                            </div>

                            <!-- Komentar 2 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-semibold text-gray-800">Rendi Sinaga</p>
                                    <span class="text-xs text-gray-500">Minggu, 28 Sep 2025</span>
                                </div>
                                <p class="text-sm text-gray-700">
                                    Draft laporan hampir selesai, tinggal verifikasi
                                </p>
                                <button
                                    @click="openReplyFromModal({
                    id: 2,
                    author: { name: 'Rendi Sinaga', avatar: 'https://i.pravatar.cc/40?img=4' },
                    content: 'Draft laporan hampir selesai, tinggal verifikasi',
                    createdAt: '2025-09-28T14:30:00',
                    replies: []
                })"
                                    class="mt-2 flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                    <span>balas</span>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- CKEditor Script -->
                    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            ClassicEditor
                                .create(document.querySelector('#editor-komentar'), {
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
                                            'imageTextAlternative',
                                            'imageStyle:inline',
                                            'imageStyle:block',
                                            'imageStyle:side'
                                        ]
                                    },
                                    table: {
                                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                                    },
                                    mediaEmbed: {
                                        previewsInData: true
                                    }
                                })
                                .then(editor => {
                                    console.log('CKEditor siap dipakai untuk komentar:', editor);

                                    // Simpan value ke textarea saat submit form
                                    const form = document.querySelector("form");
                                    form?.addEventListener("submit", () => {
                                        document.querySelector("#editor-komentar").value = editor.getData();
                                    });
                                })
                                .catch(error => console.error(error));
                        });
                    </script>



                </div>
            </div>
        </div>