@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <!-- Tambahkan font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative">
        @include('components.workspace-nav')

        <div class="max-w-5xl mx-auto py-8 px-4">
            <!-- Tombol Buat Pengumuman -->
            <div class="flex justify-start mb-1">
                <button id="btnPopup"
                    class="bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
                    <img src="images/icons/plusWhite.svg" alt="Plus" class="w-7 h-7">
                    Buat Pengumuman
                </button>
            </div>

            <!-- Daftar Pengumuman -->
            <div class="max-w-5xl mx-auto mt-4">
                <div class="bg-white rounded-2xl shadow-md p-6 h-[500px] overflow-hidden">
                    <div class="space-y-4 h-full overflow-y-auto pr-2">

                        <!-- Card Pengumuman 1 -->
                        <a href="{{ url('/isiPengunguman') }}"
                            class="bg-[#e9effd] rounded-xl shadow-sm p-4 flex justify-between items-start cursor-pointer hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-3">
                                <img src="images/dk.jpg" alt="Avatar" class="rounded-full w-10 h-10">
                                <div>
                                    <p class="font-semibold">Sahroni</p>
                                    <p class="font-medium flex items-center gap-1 text-[#000000]/80">Pengumuman</p>
                                    <p class="text-sm text-[#102a63]/80">besok ada thr</p>
                                    <div class="flex items-center space-x-2 mt-2">
                                        <span
                                            class="bg-[#6B7280] text-white text-xs font-medium px-2 py-1 flex rounded-md items-center gap-1">
                                            <img src="{{ asset('images/icons/Check.svg') }}" alt="Jam" class="w-5 h-5">
                                            20 Sep
                                        </span>
                                        <span class="text-xs text-[#102a63]/60 font-medium">Selesai Otomatis</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col space-y-6 items-center">
                                <span class="bg-[#102a63]/10 text-black text-xs px-2 py-1 rounded-md font-medium">10 menit
                                    yang lalu</span>
                                <span
                                    class="bg-[#fbd644] text-[#102a63] text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">2</span>
                            </div>
                        </a>
                        <!-- Card Pengumuman 2 -->
                        <div class="bg-[#e9effd] rounded-xl shadow-sm p-4 flex justify-between items-start">
                            <div class="flex items-start space-x-3">
                                <img src="images/dk.jpg" alt="Avatar" class="rounded-full w-10 h-10">
                                <div>
                                    <p class="font-semibold">Sahroni</p>
                                    <p class="font-medium flex items-center gap-1 text-[#000000]/80">
                                        <img src="images/icons/Lock.svg" alt="Lock" class="w-5 h-5">
                                        Pengumuman
                                    </p>
                                    <p class="text-sm text-[#102a63]/80">besok ada thr</p>
                                    <div class="flex items-center space-x-2 mt-2">
                                        <span
                                            class="bg-[#102a63] text-white text-xs font-medium px-2 py-1 flex rounded-md items-center gap-1">
                                            <img src="{{ asset('images/icons/clock.svg') }}" alt="Jam" class="w-5 h-5">
                                            20 Sep
                                        </span>
                                        <span class="text-xs text-[#102a63]/60 font-medium">Selesai Otomatis</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col space-y-6 items-center">
                                <span class="bg-[#102a63]/10 text-black text-xs px-2 py-1 rounded-md font-medium">10 menit
                                    yang lalu</span>
                                <span
                                    class="bg-[#fbd644] text-[#102a63] text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">2</span>
                            </div>
                        </div>
                        <!-- Card Pengumuman 2 -->
                        <div class="bg-[#e9effd] rounded-xl shadow-sm p-4 flex justify-between items-start">
                            <div class="flex items-start space-x-3">
                                <img src="images/dk.jpg" alt="Avatar" class="rounded-full w-10 h-10">
                                <div>
                                    <p class="font-semibold">Sahroni</p>
                                    <p class="font-medium flex items-center gap-1 text-[#000000]/80">
                                        <img src="images/icons/Lock.svg" alt="Lock" class="w-5 h-5">
                                        Pengumuman
                                    </p>
                                    <p class="text-sm text-[#102a63]/80">besok ada thr</p>
                                    <div class="flex items-center space-x-2 mt-2">
                                        <span
                                            class="bg-[#102a63] text-white text-xs font-medium px-2 py-1 flex rounded-md items-center gap-1">
                                            <img src="{{ asset('images/icons/clock.svg') }}" alt="Jam" class="w-5 h-5">
                                            20 Sep
                                        </span>
                                        <span class="text-xs text-[#102a63]/60 font-medium">Selesai Otomatis</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col space-y-6 items-center">
                                <span class="bg-[#102a63]/10 text-black text-xs px-2 py-1 rounded-md font-medium">10 menit
                                    yang lalu</span>
                                <span
                                    class="bg-[#fbd644] text-[#102a63] text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">2</span>
                            </div>
                        </div>

                        <!-- Card Pengumuman 3 -->
                        <div class="bg-[#e9effd] rounded-xl shadow-sm p-4 flex justify-between items-start">
                            <div class="flex items-start space-x-3">
                                <img src="images/dk.jpg" alt="Avatar" class="rounded-full w-10 h-10">
                                <div>
                                    <p class="font-semibold">Sahroni</p>
                                    <p class="font-medium flex items-center gap-1 text-[#000000]/80">Pengumuman</p>
                                    <p class="text-sm text-[#102a63]/80">besok ada thr</p>
                                    <div class="flex items-center space-x-2 mt-2">
                                        <span
                                            class="bg-[#102a63] text-white text-xs font-medium px-2 py-1 flex rounded-md items-center gap-1">
                                            <img src="{{ asset('images/icons/clock.svg') }}" alt="Jam" class="w-5 h-5">
                                            20 Sep
                                        </span>
                                        <span class="text-xs text-[#102a63]/60 font-medium">Selesai Otomatis</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col space-y-6 items-center">
                                <span class="bg-[#102a63]/10 text-black text-xs px-2 py-1 rounded-md font-medium">10 menit
                                    yang lalu</span>
                                <span
                                    class="bg-[#fbd644] text-[#102a63] text-sm font-bold w-6 h-6 flex items-center justify-center rounded-full">2</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- POPUP -->
        <div id="popupForm" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-3xl">
                <h2 class="text-xl font-bold mb-4 text-[#102a63] border-b pb-2">Buat Pengumuman</h2>

                <form class="space-y-5">
                    <!-- Judul -->
                    <div>
                        <label class="block font-medium mb-1">Judul Pengumuman <span class="text-red-500">*</span></label>
                        <input type="text" placeholder="Masukkan judul pengumuman..."
                            class="w-full border border-[#6B7280] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 font-[Inter] text-[14px] placeholder:text-[#6B7280] pl-5" />

                        <!-- Catatan -->
                        <div class="flex flex-col">
                            <label class="mb-1 mt-2 font-medium text-[16px]">Catatan <span
                                    class="text-red-500">*</span></label>

                            <div
                                class="flex items-center gap-1 border border-b-0 rounded-t-md bg-gray-50 px-1 py-1 text-sm overflow-x-auto">
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/1a.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/2a.png"
                                        alt="" class="w-6 h-6"></button>

                                <select class="border rounded text-sm py-1 flex-shrink-0 pr-9">
                                    <option>Normal text</option>
                                    <option>Heading 1</option>
                                    <option>Heading 2</option>
                                </select>

                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/1.png"
                                        alt="" class="w-10 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/2.png"
                                        alt="" class="w-10 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/3.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/4.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/5.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/6.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/7.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/8.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/9.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/10.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/11.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/12.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/13.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/14.png"
                                        alt="" class="w-6 h-6"></button>
                                <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/15.png"
                                        alt="" class="w-6 h-6"></button>
                            </div>

                            <textarea placeholder="Masukkan catatan anda disini..."
                                class="border rounded-b-md p-2 h-32 resize-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280] pl-5"></textarea>
                        </div>

                        <!-- Tenggat -->
                        <div>
                            <label class="block font-medium text-[14px] mb-2 text-black font-[Inter]">
                                Tenggat Pengumuman hingga selesai <span class="text-red-500">*</span>
                            </label>

                            <div class="flex items-center gap-3 mb-3 mt-3 relative">
                                <!-- Select chip 1 -->
                                <div
                                    class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden chip-container-1">
                                    <div
                                        class="flex items-center bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg chip-text-1">
                                        Selesai otomatis
                                    </div>
                                    <button type="button" class="px-2 text-gray-500 hover:text-gray-700 dropdown-btn-1">
                                        <img src="images/icons/down.svg" alt="down">
                                    </button>
                                </div>

                                <span class="text-[14px] text-black font-[Inter]" id="labelText">Selesai otomatis
                                    pada:</span>

                                <!-- Select chip 2 (Dropdown) -->
                                <div class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden chip-container-2"
                                    id="dropdownChip">
                                    <div
                                        class="flex items-center bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg chip-text-2">
                                        1 hari dari sekarang
                                    </div>
                                    <button type="button" class="px-2 text-gray-500 hover:text-gray-700 dropdown-btn-2">
                                        <img src="images/icons/down.svg" alt="down">
                                    </button>
                                </div>

                                <!-- Date Input (Hidden by default) -->
                                <div class="hidden items-center gap-2" id="dateInputContainer">
                                    <div
                                        class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden relative">
                                        <input type="date" name="custom_deadline" id="customDeadline"
                                            class="bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg focus:outline-none border-0 pr-10">
                                        <button type="button"
                                            class="px-2 bg-white absolute right-0 h-full flex items-center justify-center pointer-events-auto"
                                            id="calendarBtn" style="z-index: 5;">
                                            <img src="images/icons/calendarAbu.svg" alt="calendar" class="w-5 h-5">
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            /* Styling untuk input date */
                            #customDeadline::-webkit-calendar-picker-indicator {
                                opacity: 0;
                                position: absolute;
                                right: 0;
                                width: 100%;
                                height: 100%;
                                cursor: pointer;
                                z-index: 10;
                            }

                            /* Warna placeholder untuk browser yang support */
                            #customDeadline::-webkit-datetime-edit-text {
                                color: white;
                            }

                            #customDeadline::-webkit-datetime-edit-month-field,
                            #customDeadline::-webkit-datetime-edit-day-field,
                            #customDeadline::-webkit-datetime-edit-year-field {
                                color: white;
                            }

                            /* Fix display saat show */
                            #dateInputContainer:not(.hidden) {
                                display: flex;
                            }
                        </style>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                // Dropdown 1
                                const dropdown1 = document.createElement("div");
                                dropdown1.className =
                                    "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 dropdown-menu-1";
                                dropdown1.innerHTML = `
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-t-lg text-black font-[Inter]" data-value="Selesai otomatis">Selesai otomatis</div>
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-b-lg text-black font-[Inter]" data-value="Atur tenggat waktu sendiri">Atur tenggat waktu sendiri</div>
        `;

                                // Dropdown 2
                                const dropdown2 = document.createElement("div");
                                dropdown2.className =
                                    "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 dropdown-menu-2";
                                dropdown2.innerHTML = `
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-t-lg text-black font-[Inter]" data-value="1 hari dari sekarang">1 hari dari sekarang</div>
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer text-black font-[Inter]" data-value="3 hari dari sekarang">3 hari dari sekarang</div>
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-b-lg text-black font-[Inter]" data-value="7 hari dari sekarang">7 hari dari sekarang</div>
        `;

                                document.body.appendChild(dropdown1);
                                document.body.appendChild(dropdown2);

                                // Get elements
                                const btn1 = document.querySelector(".dropdown-btn-1");
                                const btn2 = document.querySelector(".dropdown-btn-2");
                                const chipText1 = document.querySelector(".chip-text-1");
                                const chipText2 = document.querySelector(".chip-text-2");
                                const chipContainer1 = document.querySelector(".chip-container-1");
                                const chipContainer2 = document.querySelector(".chip-container-2");
                                const labelText = document.getElementById("labelText");
                                const dropdownChip = document.getElementById("dropdownChip");
                                const dateInputContainer = document.getElementById("dateInputContainer");
                                const customDeadline = document.getElementById("customDeadline");
                                const calendarBtn = document.getElementById("calendarBtn");

                                // Function to position and toggle dropdown
                                function toggleDropdown(dropdown, container) {
                                    const rect = container.getBoundingClientRect();
                                    dropdown.style.top = `${rect.bottom + window.scrollY + 5}px`;
                                    dropdown.style.left = `${rect.left + window.scrollX}px`;
                                    dropdown.classList.toggle("hidden");
                                }

                                // Click calendar button to trigger date picker
                                if (calendarBtn) {
                                    calendarBtn.addEventListener("click", (e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        if (customDeadline.showPicker) {
                                            customDeadline.showPicker();
                                        } else {
                                            customDeadline.click();
                                        }
                                    });
                                }

                                // Event listeners for buttons
                                btn1.addEventListener("click", (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    dropdown2.classList.add("hidden");
                                    toggleDropdown(dropdown1, chipContainer1);
                                });

                                btn2.addEventListener("click", (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    dropdown1.classList.add("hidden");
                                    toggleDropdown(dropdown2, chipContainer2);
                                });

                                // Event listeners for dropdown 1 options
                                dropdown1.querySelectorAll("div[data-value]").forEach(opt => {
                                    opt.addEventListener("click", (e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        const value = opt.getAttribute("data-value");
                                        chipText1.textContent = value;
                                        dropdown1.classList.add("hidden");

                                        // Toggle between dropdown and date input
                                        if (value === "Atur tenggat waktu sendiri") {
                                            labelText.textContent = "Tenggat :";
                                            dropdownChip.classList.add("hidden");
                                            dateInputContainer.classList.remove("hidden");
                                        } else {
                                            labelText.textContent = "Selesai otomatis pada:";
                                            dropdownChip.classList.remove("hidden");
                                            dateInputContainer.classList.add("hidden");
                                        }
                                    });
                                });

                                // Event listeners for dropdown 2 options
                                dropdown2.querySelectorAll("div[data-value]").forEach(opt => {
                                    opt.addEventListener("click", (e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        chipText2.textContent = opt.getAttribute("data-value");
                                        dropdown2.classList.add("hidden");
                                    });
                                });

                                // Close dropdowns when clicking outside
                                document.addEventListener("click", (e) => {
                                    const isClickInsideDropdown1 = dropdown1.contains(e.target) || chipContainer1.contains(e
                                        .target);
                                    const isClickInsideDropdown2 = dropdown2.contains(e.target) || chipContainer2.contains(e
                                        .target);

                                    if (!isClickInsideDropdown1) {
                                        dropdown1.classList.add("hidden");
                                    }
                                    if (!isClickInsideDropdown2) {
                                        dropdown2.classList.add("hidden");
                                    }
                                });

                                // Prevent dropdown from closing when moving mouse inside it
                                dropdown1.addEventListener("click", (e) => {
                                    e.stopPropagation();
                                });

                                dropdown2.addEventListener("click", (e) => {
                                    e.stopPropagation();
                                });
                            });
                        </script>
                        <!-- Penerima -->
                        <div>
                            <label class="block font-medium mb-1">Penerima pengumuman:</label>
                            <div class="flex items-center gap-3 relative">
                                <img src="images/dk.jpg" class="w-8 h-8 rounded-full" alt="User">
                                <button type="button"
                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-xl">+</button>
                            </div>
                        </div>

                        <!-- Rahasia -->
                        <div class="mt-3">
                            <label class="font-medium text-[15px] text-[#111827] block mb-2">
                                Apakah pengumuman ini rahasia untuk penerima saja?
                            </label>

                            <label class="inline-flex items-center cursor-pointer">
                                <!-- Switch -->
                                <input type="checkbox" name="is_secret" id="switchRahasia" class="sr-only"
                                    value="1">
                                <div id="switchBg"
                                    class="relative w-11 h-6 bg-gray-300 rounded-full transition-colors duration-300">
                                    <span id="switchCircle"
                                        class="absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full transition-transform duration-300"></span>
                                </div>
                                <span class="ml-2 text-[#102a63] font-medium">Rahasia</span>
                            </label>
                        </div>

                        <script>
                            const switchInput = document.getElementById('switchRahasia');
                            const switchBg = document.getElementById('switchBg');
                            const switchCircle = document.getElementById('switchCircle');

                            // Event listener untuk label (karena kita klik labelnya)
                            switchBg.parentElement.addEventListener('click', function(e) {
                                e.preventDefault();

                                // Toggle checkbox
                                switchInput.checked = !switchInput.checked;

                                // Update tampilan
                                if (switchInput.checked) {
                                    switchBg.classList.remove('bg-gray-300');
                                    switchBg.classList.add('bg-[#102a63]');
                                    switchCircle.style.transform = 'translateX(20px)';
                                } else {
                                    switchBg.classList.remove('bg-[#102a63]');
                                    switchBg.classList.add('bg-gray-300');
                                    switchCircle.style.transform = 'translateX(0)';
                                }
                            });
                        </script>

                        <!-- Tombol Lebih Besar -->
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" id="btnBatal"
                                class="border border-blue-700 text-blue-600 bg-white px-8 py-2 text-[16px] rounded-lg hover:bg-red-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="bg-blue-700 text-white px-8 py-2 text-[16px] rounded-lg hover:bg-blue-800 transition">
                                Kirim
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const btnPopup = document.getElementById('btnPopup');
        const popupForm = document.getElementById('popupForm');
        const btnBatal = document.getElementById('btnBatal');

        btnPopup.addEventListener('click', () => {
            popupForm.classList.remove('hidden');
            popupForm.classList.add('flex');
        });

        btnBatal.addEventListener('click', () => {
            popupForm.classList.add('hidden');
            popupForm.classList.remove('flex');
        });
    </script>
@endsection
