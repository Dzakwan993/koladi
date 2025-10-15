@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <!-- Tambahkan font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative">
        @include('components.workspace-nav')

        <div class="justify-center max-w-7xl pt-6 mx-24 f">
            <!-- Tombol Buat Pengumuman -->
            <div class="flex justify-start mb-1">
                <button id="btnPopup"
                    class="bg-blue-700 text-white px-3 py-2 text-sm rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
                    <img src="images/icons/tambah.svg" alt="Plus" class="w-6 h-6">
                    Buat Insight
                </button>
            </div>

            <div class="border-t border-black my-4"></div>


            <!-- Daftar Pengumuman -->
            <div class="  mt-4">
                <div class="bg-white rounded-2xl shadow-md p-6 h-[500px] overflow-hidden">
                    <div class="space-y-4 h-full overflow-y-auto pr-2">

                        <!-- Card Pengumuman 1 -->
                        <div class="bg-[#dde5f4] rounded-xl shadow-sm p-5 flex justify-between items-start">
                            <div class="flex flex-col gap-3">
                                <div>
                                    <p class="text-base text-[#1e3a8a] font-medium mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                    <p class="text-[#1e3a8a] font-bold text-xl">Lapor Progres!</p>
                                </div>
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-9 h-9 ">
                                    <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-9 h-9 -ml-3 ">
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-[#60a5fa] text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">2</span>
                            </div>
                        </div>
                        </a>
                        <!-- Card Pengumuman 2 -->
                        <div class="bg-[#dde5f4] rounded-xl shadow-sm p-5 flex justify-between items-start">
                            <div class="flex flex-col gap-3">
                                <div>
                                    <p class="text-base text-[#1e3a8a] font-medium mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                    <p class="text-[#1e3a8a] font-bold text-xl">Lapor Progres!</p>
                                </div>
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-9 h-9 ">
                                    <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-9 h-9 -ml-3 ">
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-[#60a5fa] text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">2</span>
                            </div>
                        </div>
                        <div class="bg-[#dde5f4] rounded-xl shadow-sm p-5 flex justify-between items-start">
                            <div class="flex flex-col gap-3">
                                <div>
                                    <p class="text-base text-[#1e3a8a] font-medium mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                    <p class="text-[#1e3a8a] font-bold text-xl">Lapor Progres!</p>
                                </div>
                                <div class="flex items-center">
                                    <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-9 h-9 ">
                                    <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-9 h-9 -ml-3 ">
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-[#60a5fa] text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">2</span>
                            </div>
                        </div>

                       

                    </div>
                </div>
            </div>
        </div>

       <!-- POPUP -->
<div id="popupForm" class=" fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-4xl">
        
        <!-- Judul Utama -->
        <div class="mb-6 ">
            <h2 class="text-base font-bold text-black mb-2">Insight rutinan apa yang ingin kamu tanyakan?</h2>
            <input type="text" placeholder="Berapa data penjualan hari ini? Apa yang akan dilakukan minggu ini?, dll."
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-600 placeholder:text-gray-400" />
        </div>

        <!-- Pada hari apa -->
        <div class="mb-6">
            <h3 class="text-base font-bold text-black mb-3">Pada hari apa aja insight ini dikirim?</h3>
            <div class="flex gap-2 flex-wrap">
                <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 bg-[#e8eaf6] text-black font-medium hover:bg-[#d1d5f0] transition day-btn active">Senin</button>
                <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 bg-white text-black font-medium hover:bg-gray-50 transition day-btn">Selasa</button>
                <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 bg-white text-black font-medium hover:bg-gray-50 transition day-btn">Rabu</button>
                <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 bg-white text-black font-medium hover:bg-gray-50 transition day-btn">Kamis</button>
                <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 bg-white text-black font-medium hover:bg-gray-50 transition day-btn">Jumat</button>
                <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 bg-white text-black font-medium hover:bg-gray-50 transition day-btn">Sabtu</button>
                <button type="button" class="px-6 py-2 rounded-lg border border-gray-300 bg-white text-black font-medium hover:bg-gray-50 transition day-btn">Minggu</button>
            </div>
        </div>

        <!-- Jam berapa -->
        <div class="mb-6">
            <h3 class="text-base font-bold text-black mb-3">Jam berapa?</h3>
            <div class="relative inline-block">
                <input type="time" value="12:00" 
                    class="w-40 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm appearance-none" />
            </div>
        </div>

        <!-- Penerima -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-black mb-3">Penerima</h3>
            <div class="flex items-center gap-3">
                <img src="https://i.pravatar.cc/40?img=6" class="w-10 h-10 rounded-full border-2 border-gray-200" alt="User">
                <button type="button"
                    class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold text-2xl hover:bg-blue-200 transition">+</button>
            </div>
        </div>

        <!-- Privasi -->
        <div class="mb-8">
            <h3 class="text-base font-bold text-black mb-3">Privasi</h3>
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" id="switchRahasia" class="sr-only">
                <div id="switchBg" class="relative w-12 h-6 bg-blue-600 rounded-full transition-colors duration-300">
                    <span id="switchCircle" class="absolute top-[2px] left-[26px] w-[20px] h-[20px] bg-white rounded-full transition-transform duration-300"></span>
                </div>
                <span class="ml-3 text-black font-semibold text-sm">Rahasia</span>
            </label>
            <p class="text-gray-500 text-sm mt-2 ml-0">Hanya penerima yang bisa melihat</p>
        </div>

        <!-- Tombol (SEPERTI SEMULA) -->
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

    </div>
</div>

<script>
    // Toggle day buttons
    document.querySelectorAll('.day-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.classList.contains('active')) {
                this.classList.remove('active', 'bg-[#e8eaf6]');
                this.classList.add('bg-white');
            } else {
                this.classList.add('active', 'bg-[#e8eaf6]');
                this.classList.remove('bg-white');
            }
        });
    });

    // Toggle switch
    const switchInput = document.getElementById('switchRahasia');
    const switchBg = document.getElementById('switchBg');
    const switchCircle = document.getElementById('switchCircle');

    // Set initial state (ON)
    switchInput.checked = true;

    switchBg.parentElement.addEventListener('click', function(e) {
        e.preventDefault();
        switchInput.checked = !switchInput.checked;

        if (switchInput.checked) {
            switchBg.classList.remove('bg-gray-300');
            switchBg.classList.add('bg-blue-600');
            switchCircle.style.transform = 'translateX(0)';
        } else {
            switchBg.classList.remove('bg-blue-600');
            switchBg.classList.add('bg-gray-300');
            switchCircle.style.transform = 'translateX(-24px)';
        }
    });

    // Popup toggle
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

    // Close popup when clicking outside
    popupForm.addEventListener('click', (e) => {
        if (e.target === popupForm) {
            popupForm.classList.add('hidden');
            popupForm.classList.remove('flex');
        }
    });
</script>
@endsection
