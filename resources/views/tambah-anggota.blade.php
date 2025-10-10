@extends('layouts.app')

@section('title', 'Tambah Anggota')

@section('content')
    <div class="bg-[#f3f6fc] min-h-screen p-8" x-data="{ openModal: false }">
        <div class="max-w-5xl mx-auto">

            {{-- Header: Judul dan Tombol Undang --}}
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold ">Anggota Perusahaan</h2>
                <button @click="openModal = true"
                    class="flex items-center gap-2 bg-[#225ad6] hover:bg-[#1d4ed8] text-white px-4 py-2 rounded-lg shadow transition">
                    <img src="{{ asset('images/icons/undang.svg') }}" alt="Tambah anggota" class="w-4 h-4">
                    Undang
                </button>
            </div>

            {{-- Kartu Anggota --}}
            <div class="bg-white rounded-xl shadow-sm p-4 flex justify-between items-center hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/profile.svg') }}" alt="Profile" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <p class="font-medium text-gray-800">Naufal</p>
                    </div>
                </div>
                <button
                    class="flex items-center gap-2 text-white bg-[#e26767] hover:bg-red-600 px-3 py-1.5 rounded-md text-sm transition">
                    <img src="{{ asset('images/icons/hapus.svg') }}" alt="Hapus" class="w-4 h-4">
                    Hapus
                </button>
            </div>
        </div>

        {{-- Popup Undang --}}
        <div x-show="openModal" x-transition
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50" x-cloak>
            <div class="relative bg-[#edf2ff] rounded-xl shadow-lg w-[340px] p-5 border border-gray-200">

                {{-- Tombol tutup (X) --}}
                <button @click="openModal = false"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Judul --}}
                <h3 class="text-center font-semibold text-lg text-[#0b1e5b] mb-4 mt-2">Undang Orang</h3>

                {{-- Textarea --}}
                <textarea rows="5" placeholder="Silahkan tuliskan email yang ingin diundang. 1 baris per email ya..."
                    class="w-full p-3 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#1d4ed8] text-sm text-gray-700 resize-none bg-white"></textarea>

                {{-- Tombol Undang --}}
                <div class="mt-4 flex justify-center">
                    <button @click="openModal = false"
                        class="w-full bg-[#102a63] hover:bg-[#0a1b52] text-white font-medium py-2 rounded-md transition">
                        Undang
                    </button>
                </div>
            </div>
        </div>

    @endsection
