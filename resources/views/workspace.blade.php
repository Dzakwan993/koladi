@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    @php
        // Ambil workspace yang sedang aktif dari session, atau workspace pertama yang bisa diakses user
        $currentWorkspaceId = session('current_workspace_id');
        $currentWorkspace = $currentWorkspaceId 
            ? \App\Models\Workspace::find($currentWorkspaceId)
            : Auth::user()->workspaces()->active()->first();
    @endphp

    <div x-data x-init="$store.workspace = { selectedMenu: '' }" class="bg-[#f3f6fc] min-h-screen">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav')

        {{-- Info Workspace yang Aktif
        @if($currentWorkspace)
            <div class="p-4 bg-white border-b">
                <div class="max-w-6xl mx-auto">
                    <h1 class="text-xl font-semibold text-gray-800">{{ $currentWorkspace->name }}</h1>
                    <p class="text-sm text-gray-600">{{ $currentWorkspace->description ?? 'Tidak ada deskripsi' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Type: {{ $currentWorkspace->type }}</p>
                </div>
            </div>
        @endif --}}

        {{-- Grid Workspace --}}
        <div class="p-8 grid grid-cols-3 gap-6 max-w-6xl mx-auto">

            {{-- Card Tugas --}}
            @if($currentWorkspace)
                <a href="{{ route('kanban-tugas', $currentWorkspace->id) }}" 
                   @click="$store.workspace.selectedMenu = 'tugas'"
                   class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                    <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                        <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Tugas Icon" class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Tugas</span>
                    {{-- <span class="text-xs text-gray-500 mt-1">{{ $currentWorkspace->name }}</span> --}}
                </a>
            @else
                <div class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center opacity-50 cursor-not-allowed">
                    <div class="w-16 h-16 mb-4 text-gray-400">
                        <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Tugas Icon" class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Tugas</span>
                    <p class="text-xs text-gray-500 mt-2 text-center">Pilih workspace terlebih dahulu</p>
                </div>
            @endif

            {{-- Card Pengumuman --}}
            <a href="{{ url('/pengumuman') }}" @click="$store.workspace.selectedMenu = 'pengumuman'"
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

        {{-- Warning jika belum memilih workspace --}}
        @if(!$currentWorkspace)
            <div class="max-w-6xl mx-auto px-8">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-yellow-700">
                            <strong>Peringatan:</strong> Anda belum memilih workspace. Silakan pilih workspace dari halaman <a href="{{ route('kelola-workspace') }}" class="underline font-medium">Kelola Workspace</a> terlebih dahulu.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection