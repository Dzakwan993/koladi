@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <div class="bg-[#e9effd] min-h-screen">
        @include('components.workspace-nav')

        <div class="min-h-screen flex justify-center pt-10 bg-[#f3f6fc] responsive-wrapper">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-3xl flex flex-col gap-6 form-container">

                <!-- Judul -->
                <h2 class="text-xl font-inter font-bold text-[20px] text-[#102a63] border-b pb-2">Buat Jadwal</h2>

                <!-- Nama Jadwal -->
                <div class="flex flex-col font-inter">
                    <label class="mb-1 font-medium text-[16px] text-black">Nama Jadwal <span
                            class="text-red-500">*</span></label>
                    <input type="text" placeholder="Masukkan nama jadwal..."
                        class="border rounded-md pl-5 py-2 focus:outline-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280]" />
                </div>

                <!-- Tanggal & Waktu Mulai dan Akhir -->
                <div class="grid grid-cols-2 gap-4 datetime-grid">
                    <!-- Mulai -->
                    <div class="flex flex-col font-inter">
                        <label class="mb-1 font-medium text-[16px] text-black">Mulai <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2 datetime-inputs">
                            <input type="date"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5" />
                            <input type="time"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5" />
                        </div>
                    </div>

                    <!-- Akhir -->
                    <div class="flex flex-col font-inter">
                        <label class="mb-1 font-medium text-[16px] text-black">Akhir <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2 datetime-inputs">
                            <input type="date"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5" />
                            <input type="time"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5" />
                        </div>
                    </div>
                </div>



                <!-- Pengulangan -->
                <div class="flex flex-col font-sans">
                    <label for="pengulangan-btn" class="mb-1 font-medium text-base text-black">
                        Pengulangan<span class="text-red-500">*</span>
                    </label>

                    <div x-data="{
                        open: false,
                        options: ['Jangan Ulangi', 'Setiap hari', 'Setiap minggu', 'Setiap kuartal', 'Setiap tahun', 'Setiap hari kerja (Sen - Jum)'],
                        selected: 'Jangan Ulangi'
                    }" class="relative w-full md:w-1/3 pengulangan-dropdown">
                        <button @click="open = !open" @click.away="open = false" id="pengulangan-btn"
                            class="w-full flex items-center justify-between text-left bg-white border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="flex items-center bg-gray-100 text-gray-700 text-sm font-medium px-2 py-1 rounded">
                                <span x-text="selected"></span>
                                <svg @click.stop="selected = 'Jangan Ulangi'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 ml-2 text-gray-500 hover:text-gray-800 cursor-pointer" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>

                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition
                            class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200">
                            <ul class="py-1">
                                <template x-for="option in options" :key="option">
                                    <li @click="selected = option; open = false"
                                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                        x-text="option">
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Peserta -->
                <div class="flex flex-col gap-2">
                    <label class="mb-1 font-medium text-[16px] text-black">Peserta <span
                            class="text-red-500">*</span></label>

                    <div x-data="{ openPopup: false }">
                        <!-- Foto peserta -->
                        <div class="flex items-center gap-2">
                            <img src="images/dk.jpg" class="rounded-full border w-10 h-10" />
                            <img src="images/dk.jpg" class="rounded-full border w-10 h-10" />
                            <!-- Tombol tambah peserta -->
                            <button @click="openPopup = true" type="button">
                                <img src="images/icons/Plus.png" alt="" class="w-10 h-10" />
                            </button>
                        </div>

                        <!-- POPUP TAMBAH PESERTA -->
                        <div x-show="openPopup"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 popup-wrapper">
                            <!-- Card Popup -->
                            <div class="bg-white rounded-xl shadow-lg p-5 w-[350px] popup-card"
                                @click.away="openPopup = false">
                                <!-- Header -->
                                <h2 class="text-center font-bold text-[18px] mb-3">Tambah Peserta</h2>

                                <!-- Input cari -->
                                <input type="text" placeholder="Cari anggota..."
                                    class="border w-full rounded-md px-3 py-2 mb-3 text-sm" />

                                <!-- Pilih semua -->
                                <div class="flex items-center justify-between border-b-2 border-black px-2 pb-3">
                                    <span class="font-medium">Pilih Semua</span>
                                    <input type="checkbox" class="w-5 h-5 rounded-md accent-blue" />
                                </div>

                                <div class="flex flex-col gap-2 max-h-40 overflow-y-auto px-2 pt-3">
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <img src="images/dk.jpg" class="w-6 h-6 rounded-full" />
                                                <span>Naufal</span>
                                            </div>
                                            <input type="checkbox" class="w-5 h-5 rounded-md accent-blue" />
                                        </div>
                                    @endfor
                                </div>

                                <!-- Tombol Simpan -->
                                <div class="flex justify-end mt-4">
                                    <button @click="openPopup = false"
                                        class="bg-[#102a63] text-white px-4 py-1 rounded-md font-inter text-inter">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!--end pop up-->



                    <!-- Privasi -->
                    <div class="flex flex-col gap-4">
                        <!-- Privasi -->
                        <div class="flex flex-col gap-2">
                            <label class="mb-1 font-medium text-[16px] text-black font-inter">Privasi</label>
                            <div class="flex items-center gap-3">
                                <!-- Toggle switch Rahasia -->
                                <button class="relative inline-flex items-center h-6 rounded-full w-11 bg-[#102a63]">
                                    <span
                                        class="inline-block w-4 h-4 transform translate-x-6 bg-white rounded-full"></span>
                                </button>
                                <span class="text-sm font-medium text-[#102a63] font-inter">Rahasia</span>
                            </div>
                            <span class="text-sm text-gray-500 font-inter">Hanya peserta yang diundang bisa melihat</span>
                        </div>

                        <!-- Rapat -->
                        <div class="flex flex-col gap-2">
                            <span class="font-medium text-[16px] text-black font-inter">Apakah anda akan mengadakan
                                rapat?</span>
                            <div class="flex items-center gap-3">
                                <!-- Toggle switch Rapat -->
                                <button class="relative inline-flex items-center h-6 rounded-full w-11 bg-[#102a63]">
                                    <span class="sr-only">Rapat</span>
                                    <span
                                        class="inline-block w-4 h-4 transform translate-x-6 bg-white rounded-full"></span>
                                </button>
                                <span class="text-sm font-medium text-[#102a63] font-inter">Rapat</span>
                            </div>
                        </div>
                    </div>


                    <!-- Link Rapat -->
                    <div class="flex flex-col">
                        <label class="mb-1 font-medium font-inter text-[16px]">Link Rapat (Opsional)</label>
                        <input type="text" placeholder="Masukkan link rapat anda..."
                            class="border rounded-md pl-5 py-2 focus:outline-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280]" />
                        <span class="text-xs text-gray-400 font-inter">Opsional - isi jika rapat dilakukan online</span>
                    </div>

                    <!-- Catatan -->
                    <div class="flex flex-col">
                        <label class="mb-1 font-medium text-[16px]">Catatan <span class="text-red-500">*</span></label>

                        <!-- CKEditor Container -->
                        <div id="catatan-editor"></div>
                    </div>

                    <!-- Tombol Kirim & Batal -->
                    <div class="flex gap-2 action-buttons">
                        <button class="bg-blue-600 text-white w-[80px] h-[30px] rounded-md hover:bg-blue-700 transition">
                            Kirim
                        </button>
                        <button
                            class="border border-blue-600 text-blue-600 w-[80px] h-[30px] rounded-md hover:bg-blue-50 transition">
                            Batal
                        </button>
                    </div>
                </div>
            </div>

            <!--pop up-->

            {{-- Alpine.js & CKEditor --}}
            <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
            <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

            {{-- CKEditor Initialization Script --}}
            <script>
                // Store editor instance
                let catatanEditor = null;

                // Initialize CKEditor when DOM is loaded
                document.addEventListener('DOMContentLoaded', function() {
                    const editorElement = document.getElementById('catatan-editor');

                    if (editorElement) {
                        ClassicEditor
                            .create(editorElement, {
                                toolbar: {
                                    items: [
                                        'undo', 'redo', '|',
                                        'heading', '|',
                                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                                        'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                                        '|',
                                        'alignment', '|',
                                        'link', 'blockQuote', 'code', 'codeBlock', '|',
                                        'bulletedList', 'numberedList', 'todoList', '|',
                                        'indent', 'outdent', '|',
                                        'insertTable', 'imageUpload', 'mediaEmbed', '|',
                                        'horizontalLine', 'pageBreak', '|',
                                        'removeFormat', 'sourceEditing'
                                    ],
                                    shouldNotGroupWhenFull: true
                                },
                                heading: {
                                    options: [{
                                            model: 'paragraph',
                                            title: 'Paragraph',
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
                                        },
                                        {
                                            model: 'heading4',
                                            view: 'h4',
                                            title: 'Heading 4',
                                            class: 'ck-heading_heading4'
                                        },
                                        {
                                            model: 'heading5',
                                            view: 'h5',
                                            title: 'Heading 5',
                                            class: 'ck-heading_heading5'
                                        },
                                        {
                                            model: 'heading6',
                                            view: 'h6',
                                            title: 'Heading 6',
                                            class: 'ck-heading_heading6'
                                        }
                                    ]
                                },
                                fontSize: {
                                    options: [
                                        'tiny',
                                        'small',
                                        'default',
                                        'big',
                                        'huge'
                                    ]
                                },
                                fontFamily: {
                                    options: [
                                        'default',
                                        'Arial, Helvetica, sans-serif',
                                        'Courier New, Courier, monospace',
                                        'Georgia, serif',
                                        'Lucida Sans Unicode, Lucida Grande, sans-serif',
                                        'Tahoma, Geneva, sans-serif',
                                        'Times New Roman, Times, serif',
                                        'Trebuchet MS, Helvetica, sans-serif',
                                        'Verdana, Geneva, sans-serif'
                                    ]
                                },
                                alignment: {
                                    options: ['left', 'center', 'right', 'justify']
                                },
                                table: {
                                    contentToolbar: [
                                        'tableColumn',
                                        'tableRow',
                                        'mergeTableCells',
                                        'tableCellProperties',
                                        'tableProperties'
                                    ]
                                },
                                image: {
                                    toolbar: [
                                        'imageTextAlternative',
                                        'toggleImageCaption',
                                        'imageStyle:inline',
                                        'imageStyle:block',
                                        'imageStyle:side',
                                        'linkImage'
                                    ]
                                },
                                placeholder: 'Masukkan catatan anda disini...',
                                language: 'id'
                            })
                            .then(editor => {
                                catatanEditor = editor;
                                console.log('CKEditor initialized successfully');

                                // Optional: Listen to changes
                                editor.model.document.on('change:data', () => {
                                    const data = editor.getData();
                                    console.log('Editor content:', data);
                                });
                            })
                            .catch(error => {
                                console.error('CKEditor initialization error:', error);
                                console.error('Error details:', error.message);

                                // Fallback dengan toolbar lebih sederhana
                                ClassicEditor
                                    .create(editorElement, {
                                        toolbar: [
                                            'heading', '|',
                                            'bold', 'italic', 'underline', '|',
                                            'link', 'bulletedList', 'numberedList', '|',
                                            'blockQuote', 'insertTable', '|',
                                            'undo', 'redo'
                                        ],
                                        heading: {
                                            options: [{
                                                    model: 'paragraph',
                                                    title: 'Paragraph',
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
                                        placeholder: 'Masukkan catatan anda disini...'
                                    })
                                    .then(editor => {
                                        catatanEditor = editor;
                                        console.log('CKEditor initialized with basic toolbar');
                                    })
                                    .catch(fallbackError => {
                                        console.error('Fallback editor also failed:', fallbackError);
                                        // Last resort: textarea
                                        editorElement.innerHTML = `
                                <textarea
                                    placeholder="Masukkan catatan anda disini..."
                                    class="border rounded-md p-2 h-32 resize-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280] pl-5 w-full"
                                ></textarea>
                            `;
                                    });
                            });
                    }
                });

                // Function to get editor data (useful for form submission)
                function getCatatanEditorData() {
                    if (catatanEditor) {
                        return catatanEditor.getData();
                    }
                    // Fallback to textarea if editor failed to load
                    const textarea = document.querySelector('#catatan-editor textarea');
                    return textarea ? textarea.value : '';
                }
            </script>

            <style>
                /* ===== RESPONSIVE STYLES ===== */

                /* CKEditor Custom Styles */
                #catatan-editor {
                    min-height: 200px;
                }

                .ck-editor__editable {
                    min-height: 200px;
                    max-height: 400px;
                }

                .ck.ck-editor__main>.ck-editor__editable {
                    background-color: #ffffff;
                    border: 1px solid #6B7280 !important;
                    border-radius: 0 0 0.375rem 0.375rem;
                }

                .ck.ck-editor__top .ck-sticky-panel .ck-toolbar {
                    border: 1px solid #6B7280 !important;
                    border-bottom: none !important;
                    border-radius: 0.375rem 0.375rem 0 0;
                    background-color: #f9fafb;
                }

                /* Make sure all borders match */
                .ck.ck-toolbar {
                    border-color: #6B7280 !important;
                }

                .ck.ck-editor__editable:not(.ck-editor__nested-editable).ck-focused {
                    border-color: #6B7280 !important;
                    box-shadow: none !important;
                }

                /* Heading styles in editor */
                .ck-content h1 {
                    font-size: 2em;
                    font-weight: bold;
                }

                .ck-content h2 {
                    font-size: 1.5em;
                    font-weight: bold;
                }

                .ck-content h3 {
                    font-size: 1.3em;
                    font-weight: bold;
                }

                .ck-content h4 {
                    font-size: 1.1em;
                    font-weight: bold;
                }

                .ck-content h5 {
                    font-size: 1em;
                    font-weight: bold;
                }

                .ck-content h6 {
                    font-size: 0.9em;
                    font-weight: bold;
                }

                /* Tablet - 1024px and below */
                @media (max-width: 1024px) {
                    .responsive-wrapper {
                        padding: 1.5rem !important;
                    }

                    .form-container {
                        padding: 1.5rem !important;
                        margin: 0 1rem !important;
                    }

                    .pengulangan-dropdown {
                        width: 50% !important;
                    }
                }

                /* Mobile - 768px and below */
                @media (max-width: 768px) {
                    .responsive-wrapper {
                        padding: 1rem !important;
                        padding-top: 2rem !important;
                    }

                    .form-container {
                        padding: 1rem !important;
                        margin: 0 0.5rem !important;
                        gap: 1rem !important;
                    }

                    .datetime-grid {
                        grid-columns: 1 !important;
                        display: flex !important;
                        flex-direction: column !important;
                    }

                    .pengulangan-dropdown {
                        width: 100% !important;
                    }

                    .popup-card {
                        width: 90% !important;
                        max-width: 350px !important;
                        margin: 0 1rem !important;
                    }
                }

                /* Small Mobile - 480px and below */
                @media (max-width: 480px) {
                    .responsive-wrapper {
                        padding: 0.5rem !important;
                        padding-top: 1.5rem !important;
                    }

                    .form-container {
                        padding: 0.75rem !important;
                        margin: 0 !important;
                        gap: 0.75rem !important;
                        border-radius: 0.5rem !important;
                    }

                    .form-container h2 {
                        font-size: 18px !important;
                    }

                    .form-container label {
                        font-size: 14px !important;
                    }

                    .form-container input,
                    .form-container textarea,
                    .form-container select {
                        font-size: 13px !important;
                        padding: 0.5rem !important;
                    }

                    .datetime-inputs {
                        flex-direction: column !important;
                        gap: 0.5rem !important;
                    }

                    .datetime-inputs input {
                        width: 100% !important;
                    }

                    .action-buttons {
                        flex-direction: column !important;
                    }

                    .action-buttons button {
                        width: 100% !important;
                        height: 36px !important;
                    }

                    .popup-wrapper {
                        padding: 1rem !important;
                    }

                    .popup-card {
                        width: 100% !important;
                        padding: 1rem !important;
                        margin: 0 !important;
                    }

                    .popup-card h2 {
                        font-size: 16px !important;
                    }

                    /* CKEditor responsive */
                    #catatan-editor {
                        min-height: 150px !important;
                    }

                    .ck-editor__editable {
                        min-height: 150px !important;
                        font-size: 13px !important;
                    }

                    .ck.ck-toolbar {
                        font-size: 12px !important;
                    }

                    .ck.ck-toolbar .ck-toolbar__items {
                        flex-wrap: wrap !important;
                    }
                }

                /* Extra Small Mobile - 375px and below */
                @media (max-width: 375px) {
                    .form-container {
                        padding: 0.5rem !important;
                    }

                    .form-container h2 {
                        font-size: 16px !important;
                    }

                    .form-container label {
                        font-size: 13px !important;
                    }

                    .form-container input,
                    .form-container textarea {
                        font-size: 12px !important;
                    }
                }
            </style>

        @endsection
