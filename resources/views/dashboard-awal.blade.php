@extends('layouts.app')

@section('title', 'Dashboard Tambah Anggota')

@section('content')
    <div x-data="{ showOverlay: true, showTooltip: true }" x-init="// Tutup overlay kalau user menekan tombol apa pun
    window.addEventListener('keydown', () => {
        showOverlay = false;
        showTooltip = false;
    });" class="relative bg-[#f3f6fc] min-h-full">

        {{-- Overlay dengan efek spotlight --}}
        <template x-if="showOverlay">
            <div class="fixed inset-0 z-40 pointer-events-auto" @click="showOverlay = false; showTooltip = false">
                <div class="absolute inset-0 bg-black bg-opacity-40 transition duration-500"></div>

                <div
                    class="absolute right-[15%] bottom-[35%] w-64 h-64 rounded-full bg-transparent blur-2xl drop-shadow-[0_0_60px_rgba(255,255,255,0.2)]">
                </div>
            </div>
        </template>

        {{-- Konten utama --}}
        <div class="relative z-50 h-full flex items-center justify-center px-8 py-16">
            <div class="max-w-7xl w-full">
                <div class="grid grid-cols-2 gap-20 items-center">

                    {{-- Ilustrasi --}}
                    <div class="flex items-center justify-center">
                        <img src="{{ asset('images/icons/dashboard-awal.svg') }}" alt="Dashboard Illustration"
                            class="relative z-50">
                    </div>

                    {{-- Konten --}}
                    <div class="flex flex-col justify-center relative z-50">
                        <div class="mb-10">
                            <h1 class="text-4xl font-bold text-gray-900 mb-4">Ayo tambahkan anggotamu sekarang!</h1>
                            <p class="text-gray-600 text-base leading-relaxed">
                                Setelah menambahkan anggota ke dalam tim, Anda dapat dengan mudah mengelola proyek, mengubah
                                tugas, serta mengatur rapat sesuai kebutuhan.
                            </p>
                        </div>

                        {{-- Tombol & Tooltip --}}
                        <div class="relative flex justify-end pr-10">
                            <a href="{{ url('/tambah-anggota') }}"
                                class="relative z-[60] bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-semibold py-3.5 px-8 rounded-lg inline-flex items-center justify-center gap-2 shadow-2xl transition ring-4 ring-white/50">
                                <img src="{{ asset('images/icons/tambah.svg') }}" alt="Tambah anggota">
                                Tambah anggota
                            </a>

                            {{-- Tooltip --}}
                            <div x-show="showTooltip" x-transition class="absolute right-10 top-full mt-4 z-[70]">
                                <div
                                    class="absolute right-12 -top-2 w-4 h-4 bg-white rotate-45 border-r border-t border-gray-200">
                                </div>
                                <div class="relative bg-white rounded-lg shadow-2xl p-5 border border-gray-200 w-72">
                                    <h4 class="font-bold text-gray-900 mb-2 text-base">Selamat datang!</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        Tambah anggotamu dan nikmati semua fitur KOLADI.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
