@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <div x-data x-init="$store.workspace = { selectedMenu: '' }" class="bg-[#f3f6fc] min-h-screen">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav')

        {{-- Grid Workspace --}}
        <div class="p-8 grid grid-cols-3 gap-6 max-w-6xl mx-auto">

            {{-- Card Tugas --}}
            <a href="{{ url('/kanban-tugas') }}" @click="$store.workspace.selectedMenu = 'tugas'"
                class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                    <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Tugas Icon" class="w-full h-full">
                </div>
                <span class="text-gray-700 font-medium">Tugas</span>
            </a>

            {{-- Card Pengumuman --}}
            <a href="{{ route('workspace.pengumuman', $workspace->id) }}" @click="$store.workspace.selectedMenu = 'pengumuman'"
                class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                    <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}" alt="Pengumuman Icon"
                        class="w-full h-full">
                </div>
                <span class="text-gray-700 font-medium">Pengumuman</span>
            </a>

            {{-- Card Jadwal --}}
            <a href="{{ url('/jadwal') }}" @click="$store.workspace.selectedMenu = 'jadwal'"
                class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                    <img src="{{ asset('images/icons/workspace_kalender.svg') }}" alt="Jadwal Icon" class="w-full h-full">
                </div>
                <span class="text-gray-700 font-medium">Jadwal</span>
            </a>

            {{-- Card Chat --}}
            <a href="{{ url('/chat') }}" @click="$store.workspace.selectedMenu = 'chat'"
                class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                    <img src="{{ asset('images/icons/workspace_chat.svg') }}" alt="Chat Icon" class="w-full h-full">
                </div>
                <span class="text-gray-700 font-medium">Chat</span>
            </a>

            {{-- Card Insight --}}
            <a href="{{ url('/mindmap') }}" @click="$store.workspace.selectedMenu = 'insight'"
                class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                    <img src="{{ asset('images/icons/workspace_insight.svg') }}" alt="Insight Icon" class="w-full h-full">
                </div>
                <span class="text-gray-700 font-medium">Mindmap</span>
            </a>

            {{-- Card Dokumen --}}
            <a href="{{ url('/dokumen-dan-file') }}" @click="$store.workspace.selectedMenu = 'dokumen'"
                class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                    <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen Icon"
                        class="w-full h-full">
                </div>
                <span class="text-gray-700 font-medium">Dokumen</span>
            </a>

        </div>
    </div>
@endsection
