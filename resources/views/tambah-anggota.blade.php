@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 h-screen overflow-hidden mx-8">
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        {{-- Header - Fixed Height --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 mb-4 flex-shrink-0">
            <div>
                <h1 class="text-2xl sm:text-2xl font-bold text-gray-900">Anggota Perusahaan</h1>
            </div>
            <button onClick="openInviteModal(event)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-lg font-medium transition flex items-center justify-center gap-1 shadow-sm">
                <img src="{{ asset('images/icons/add-user.svg') }}" alt="Schedule" class="w-6 h-6" />
                Undang
            </button>
        </div>

        {{-- Content Area - Scrollable --}}
        <div class="flex-1 overflow-y-auto flex flex-col gap-2">
            
            {{-- User Card 1 --}}
            <div class="border-2 border-gray-200 bg-white rounded-xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/50?img=1" alt="Avatar" class="w-12 h-12 rounded-full">
                    <span class="font-semibold text-lg text-gray-900">Naufal Naufal Naufal</span>
                </div>
                
                <button onclick="openDeleteModal(event)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-5 h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>

            {{-- User Card 2 --}}
            <div class="border-2 border-gray-200 bg-white rounded-xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/50?img=2" alt="Avatar" class="w-12 h-12 rounded-full">
                    <span class="font-semibold text-lg text-gray-900">Muhammad Sahroni</span>
                </div>
                <button onclick="openDeleteModal(event)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-5 h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>

            {{-- User Card 3 --}}
            <div class="border-2 border-gray-200 bg-white rounded-xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-12 h-12 rounded-full">
                    <span class="font-semibold text-lg text-gray-900">John Doe</span>
                </div>
                <button onclick="openDeleteModal(event)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-5 h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>
            <div class="border-2 border-gray-200 bg-white rounded-xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-12 h-12 rounded-full">
                    <span class="font-semibold text-lg text-gray-900">John Doe</span>
                </div>
                <button onclick="openDeleteModal(event)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-5 h-5 brightness-0 invert" />
                    Hapus
                </button>
            </div>
            <div class="border-2 border-gray-200 bg-white rounded-xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-12 h-12 rounded-full">
                    <span class="font-semibold text-lg text-gray-900">John Doe</span>
                </div>
                <button onclick="openDeleteModal(event)" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-5 h-5 brightness-0 invert" />
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