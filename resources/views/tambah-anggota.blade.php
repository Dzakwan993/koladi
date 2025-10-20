@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Tambahkan font Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="p-3 sm:p-4 md:p-6 lg:p-8 h-screen overflow-hidden mx-4 sm:mx-6 md:mx-12 lg:mx-16 xl:mx-24 font-[Inter,sans-serif]">
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        {{-- Header - Fixed Height --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 pb-2 mb-4 sm:mb-5 md:mb-6 flex-shrink-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Anggota Perusahaan</h1>
            </div>
            <button onClick="openInviteModal(event)" class="bg-[#225AD6] hover:bg-blue-600 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-1.5 sm:gap-2 shadow-sm">
                <img src="{{ asset('images/icons/add-user.svg') }}" alt="Schedule" class="w-5 h-5 sm:w-6 sm:h-6" />
                Undang
            </button>
        </div>

        {{-- Content Area - Scrollable --}}
        <div class="flex-1 overflow-y-auto flex flex-col gap-2 sm:gap-2.5 md:gap-3">
            
            {{-- User Card 1 --}}
            <div class="border-2 border-gray-200 bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <img src="https://i.pravatar.cc/50?img=1" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                    <span class="font-semibold text-base sm:text-lg text-gray-900 truncate">Naufal Naufal Naufal</span>
                </div>
                
                <button onclick="openDeleteModal(event)" class="w-full sm:w-auto bg-[#E26767] hover:bg-red-400 text-white px-3 sm:px-4 py-2 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-4 h-4 sm:w-5 sm:h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>

            {{-- User Card 2 --}}
            <div class="border-2 border-gray-200 bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <img src="https://i.pravatar.cc/50?img=2" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                    <span class="font-semibold text-base sm:text-lg text-gray-900 truncate">Muhammad Sahroni</span>
                </div>
                <button onclick="openDeleteModal(event)" class="w-full sm:w-auto bg-[#E26767] hover:bg-red-400 text-white px-3 sm:px-4 py-2 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-4 h-4 sm:w-5 sm:h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>

            {{-- User Card 3 --}}
            <div class="border-2 border-gray-200 bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                    <span class="font-semibold text-base sm:text-lg text-gray-900 truncate">John Doe</span>
                </div>
                <button onclick="openDeleteModal(event)" class="w-full sm:w-auto bg-[#E26767] hover:bg-red-400 text-white px-3 sm:px-4 py-2 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-4 h-4 sm:w-5 sm:h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>

            <div class="border-2 border-gray-200 bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                    <span class="font-semibold text-base sm:text-lg text-gray-900 truncate">John Doe</span>
                </div>
                <button onclick="openDeleteModal(event)" class="w-full sm:w-auto bg-[#E26767] hover:bg-red-400 text-white px-3 sm:px-4 py-2 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-4 h-4 sm:w-5 sm:h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>

            <div class="border-2 border-gray-200 bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                    <span class="font-semibold text-base sm:text-lg text-gray-900 truncate">John Doe</span>
                </div>
                <button onclick="openDeleteModal(event)" class="w-full sm:w-auto bg-[#E26767] hover:bg-red-400 text-white px-3 sm:px-4 py-2 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-4 h-4 sm:w-5 sm:h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>
            

                {{-- Tambahkan lebih banyak user card sesuai kebutuhan --}}

            </div>
        </div>
    </div>
    @include('components.delete-member-modal')
    @include('components.invite-member-modal')
@endsection
