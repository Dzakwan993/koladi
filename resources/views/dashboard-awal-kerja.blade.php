@extends('layouts.app')

@section('title', 'Dashboard Awal Tambah Ruang Kerja')

@section('content')
    <div x-data="{ showOverlay: true, showTooltip: true, showPopup: false }" x-init="// Tutup overlay & tooltip ketika menekan tombol apa pun
    window.addEventListener('keydown', () => {
        showOverlay = false;
        showTooltip = false;
    });" class="relative bg-[#f3f6fc] min-h-screen">

        {{-- Overlay efek spotlight --}}
        <template x-if="showOverlay">
            <div class="fixed inset-0 z-40 pointer-events-auto" @click="showOverlay = false; showTooltip = false">
                <div class="absolute inset-0 bg-black bg-opacity-40 transition duration-500"></div>
                <div
                    class="absolute right-[18%] bottom-[33%] w-72 h-72 rounded-full bg-transparent blur-2xl drop-shadow-[0_0_60px_rgba(255,255,255,0.3)]">
                </div>
            </div>
        </template>

        {{-- Konten utama --}}
        <div class="relative z-50 h-full flex items-center justify-center px-8 py-16">
            <div class="max-w-7xl w-full">
                <div class="grid grid-cols-2 gap-20 items-center">

                    {{-- Ilustrasi kiri --}}
                    <div class="flex items-center justify-center">
                        <img src="{{ asset('images/icons/dashboard-awal.svg') }}" alt="Dashboard Illustration"
                            class="relative z-50 w-[420px]">
                    </div>

                    {{-- Teks kanan --}}
                    <div class="flex flex-col justify-center relative z-50">
                        <div class="mb-12">
                            <h1 class="text-[32px] font-bold text-gray-900 mb-4">Ayo buat ruang kerjamu sekarang!</h1>
                            <p class="text-gray-600 text-[15px] leading-relaxed max-w-lg">
                                Setelah membuat ruang kerja, kamu bisa langsung menambahkan anggota tim sesuai kebutuhan.
                                Atur peran masing-masing anggota, kelola tugas dengan mudah, dan pastikan alur kerja tetap
                                teratur.
                                Dengan adanya ruang kerja, semua aktivitas tim bisa dipusatkan dalam satu tempat sehingga
                                koordinasi lebih lancar, pekerjaan lebih terorganisir, dan hasil yang dicapai pun lebih
                                maksimal.
                            </p>
                        </div>

                        {{-- Tombol tambah & tooltip --}}
                        <div class="relative flex justify-end pr-16 mt-[-40px]"> {{-- posisi dinaikkan --}}
                            <button @click="showPopup = true"
                                class="relative z-[60] bg-[#2563eb] hover:bg-[#1d4ed8] text-white rounded-full p-3 flex items-center justify-center shadow-2xl transition ring-4 ring-white/50">
                                <img src="{{ asset('images/icons/tambah.svg') }}" alt="Tambah" class="w-7 h-7">
                            </button>

                            {{-- Tooltip --}}
                            <div x-show="showTooltip" x-transition class="absolute right-10 top-full mt-3 z-[70]">
                                <div
                                    class="absolute right-11 -top-2 w-4 h-4 bg-white rotate-45 border-r border-t border-gray-200">
                                </div>
                                <div class="relative bg-white rounded-lg shadow-xl p-4 border border-gray-200 w-72">
                                    <h4 class="font-bold text-gray-900 mb-1 text-[15px]">Selamat datang!</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        Buat ruang kerjamu sekarang dan mulai atur tim serta proyekmu dengan mudah di
                                        KOLADI.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Popup Buat Workspace --}}
        <div x-show="showPopup" x-transition class="fixed inset-0 bg-black/40 z-[80] flex items-center justify-center"
            x-cloak>
            <div @click.outside="showPopup = false"
                class="relative bg-[#eaf1ff] rounded-xl p-8 w-[400px] shadow-2xl border border-gray-200">

                {{-- Tombol tutup (X) --}}
                <button @click="showPopup = false"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Judul --}}
                <h2 class="text-2xl font-bold text-center text-[#0f2167] mb-6">Buat Workspace</h2>

                {{-- Form --}}
                <form action="{{ url('/workspace/create') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <input type="text" name="nama" placeholder="Nama Workspace...."
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium text-sm mb-2">Untuk apakah workspace ini?</label>
                        <div class="space-y-3">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="tipe" value="HQ"
                                    class="text-blue-600 focus:ring-blue-500">
                                <span>HQ</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="tipe" value="Tim"
                                    class="text-blue-600 focus:ring-blue-500">
                                <span>Tim</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="tipe" value="Projek"
                                    class="text-blue-600 focus:ring-blue-500">
                                <span>Projek</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="w-full px-8 py-2.5 rounded-lg bg-[#102a63] text-white font-semibold hover:bg-[#0c1a52] transition">
                            Buat
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
