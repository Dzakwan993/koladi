@extends('layouts.app')

@section('title','Workspace')

@section('content')
<div class="bg-[#f3f6fc]">
    {{-- Workspace Nav (menempel ke topbar) --}}
    <div class="h-12 bg-white shadow-sm flex items-center px-6 space-x-6 text-sm border-b border-gray-200">
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500 uppercase tracking-wide">Ruang Kerja</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <a href="#" class="text-gray-700 hover:text-blue-600 font-medium transition">Div. Marketing</a>
        </div>

        <div class="flex-1"></div>

        <div class="flex items-center gap-4">
            <a href="#" class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <img src="/images/icons/insight.png" alt="Insight" class="w-4 h-4">
                <span>Insight</span>
            </a>
            <a href="#" class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <img src="/images/icons/tugas.png" alt="Tugas" class="w-4 h-4">
                <span>Tugas</span>
            </a>
            <a href="#" class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <img src="/images/icons/chat.png" alt="Chat" class="w-4 h-4">
                <span>Chat</span>
            </a>
            <a href="#" class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <img src="/images/icons/dokumen.png" alt="Dokumen" class="w-4 h-4">
                <span>Dokumen</span>
            </a>
            <a href="{{ url('/jadwal')}}" class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <img src="/images/icons/jadwal.png" alt="Jadwal" class="w-4 h-4">
                <span>Jadwal</span>
            </a>
            <a href="#" class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <img src="/images/icons/pengumuman.png" alt="Pengumuman" class="w-4 h-4">
                <span>Pengumuman</span>
            </a>
        </div>
    </div>

    {{-- Grid Workspace --}}
    <div class="p-8 grid grid-cols-3 gap-6 max-w-6xl mx-auto">
        {{-- Card Tugas --}}
        <a href="#" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group">
            <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="text-gray-700 font-medium">Tugas</span>
        </a>

        {{-- Card Pengumuman --}}
        <a href="#" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group">
            <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15"></path>
                </svg>
            </div>
            <span class="text-gray-700 font-medium">Pengumuman</span>
        </a>

        {{-- Card Jadwal --}}
        <a href="{{ url('/jadwal')}}" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group">
            <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10"></path>
                </svg>
            </div>
            <span class="text-gray-700 font-medium">Jadwal</span>
        </a>

        {{-- Card Chat --}}
        <a href="#" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group">
            <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01"></path>
                </svg>
            </div>
            <span class="text-gray-700 font-medium">Chat</span>
        </a>

        {{-- Card Insight --}}
        <a href="#" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group">
            <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1"></path>
                </svg>
            </div>
            <span class="text-gray-700 font-medium">Insight</span>
        </a>

        {{-- Card Dokumen --}}
        <a href="#" class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group">
            <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9"></path>
                </svg>
            </div>
            <span class="text-gray-700 font-medium">Dokumen</span>
        </a>
    </div>
</div>
@endsection
