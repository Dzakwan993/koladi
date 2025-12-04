@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    @php
        // ✅ CEK ROLE USER DI COMPANY
        $activeCompanyId = session('active_company_id');
        $user = auth()->user();
        $userCompany = $user->userCompanies()->where('company_id', $activeCompanyId)->with('role')->first();
        $userRole = $userCompany?->role?->name ?? 'Member';

        // ✅ CEK APAKAH USER ADALAH SUPERADMIN/ADMIN/MANAGER DI COMPANY
        $isCompanyAdmin = in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);

        // Ambil workspace yang sedang aktif dari session, atau workspace pertama yang bisa diakses user
        $currentWorkspaceId = session('current_workspace_id');

        // ✅ MODIFIKASI: Ambil workspace hanya yang bisa diakses user
        if ($currentWorkspaceId) {
            $currentWorkspace = \App\Models\Workspace::find($currentWorkspaceId);

            // ✅ VALIDASI: Cek apakah user boleh akses workspace ini
            if ($currentWorkspace) {
                $canAccess = false;

                // Jika SuperAdmin/Admin/Manager di company, boleh akses semua workspace
                if ($isCompanyAdmin) {
                    $canAccess = true;
                }
                // Jika bukan, cek apakah user adalah anggota workspace
                else {
                    $userWorkspace = \App\Models\UserWorkspace::where('user_id', $user->id)
                        ->where('workspace_id', $currentWorkspace->id)
                        ->where('status_active', true)
                        ->first();
                    $canAccess = !is_null($userWorkspace);
                }

                // Jika tidak boleh akses, reset current workspace
                if (!$canAccess) {
                    $currentWorkspace = null;
                    session()->forget('current_workspace_id');
                    session()->forget('current_workspace_name');
                }
            }
        } else {
            // ✅ MODIFIKASI: Ambil workspace pertama yang bisa diakses user
            if ($isCompanyAdmin) {
                // SuperAdmin/Admin/Manager bisa akses semua workspace di company
                $currentWorkspace = \App\Models\Workspace::where('company_id', $activeCompanyId)->active()->first();
            } else {
                // User biasa hanya bisa akses workspace yang mereka ikuti
                $currentWorkspace = $user->userWorkspaces()->where('status_active', true)->with('workspace')->first()
                    ?->workspace;
            }

            // Simpan ke session jika ada workspace yang bisa diakses
            if ($currentWorkspace) {
                session([
                    'current_workspace_id' => $currentWorkspace->id,
                    'current_workspace_name' => $currentWorkspace->name,
                ]);
            }
        }
    @endphp

    {{-- Background biru seperti semula --}}
    <div x-data x-init="$store.workspace = { selectedMenu: '' }" class="bg-[#f3f6fc] min-h-screen">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav', ['workspace' => $workspace, 'active' => ''])

        {{-- Warning jika belum memilih workspace - DIPINDAH KE ATAS --}}
        @if (!$currentWorkspace)
            <div class="max-w-6xl mx-auto px-8 pt-8">
                <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-lg p-5 shadow-sm">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800 mb-1">
                                Belum Ada Workspace Aktif
                            </p>
                            <p class="text-sm text-amber-700">
                                @if ($isCompanyAdmin)
                                    Silakan buat atau pilih workspace dari halaman
                                    <a href="{{ route('kelola-workspace') }}" class="underline font-medium hover:text-amber-900">
                                        Kelola Workspace
                                    </a> terlebih dahulu.
                                @else
                                    Silakan pilih workspace dari halaman
                                    <a href="{{ route('kelola-workspace') }}" class="underline font-medium hover:text-amber-900">
                                        Kelola Workspace
                                    </a> terlebih dahulu.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 🎨 UPDATED: Grid dengan spacing lebih baik --}}
        <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 max-w-6xl mx-auto">

            {{-- Card Tugas --}}
            @if ($currentWorkspace)
                <a href="{{ route('kanban-tugas', $currentWorkspace->id) }}"
                   @click="$store.workspace.selectedMenu = 'tugas'"
                   class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-lg hover:-translate-y-1 transition-all duration-200 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-100 rounded-lg group-hover:bg-blue-600 transition-colors duration-200">
                            <img src="{{ asset('images/icons/workspace_tugas.svg') }}"
                                 alt="Tugas Icon"
                                 class="w-6 h-6 group-hover:brightness-0 group-hover:invert transition-all">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Tugas</h3>
                    <p class="text-sm text-gray-500">Kelola tugas dan prioritas</p>
                </a>
            @else
                <div class="bg-gray-100 rounded-xl border border-gray-200 p-6 flex flex-col opacity-60 cursor-not-allowed">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-200 rounded-lg">
                            <img src="{{ asset('images/icons/workspace_tugas.svg') }}"
                                 alt="Tugas Icon"
                                 class="w-6 h-6 opacity-50">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-500 mb-1">Tugas</h3>
                    <p class="text-sm text-gray-400">Kelola tugas dan prioritas</p>

                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-xs text-gray-500">Pilih workspace terlebih dahulu</p>
                    </div>
                </div>
            @endif

            {{-- Card Pengumuman --}}
            @if ($currentWorkspace)
                <a href="{{ route('workspace.pengumuman', ['workspace' => $currentWorkspace->id]) }}"
                   @click="$store.workspace.selectedMenu = 'pengumuman'"
                   class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-lg hover:-translate-y-1 transition-all duration-200 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-100 rounded-lg group-hover:bg-blue-600 transition-colors duration-200">
                            <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}"
                                 alt="Pengumuman Icon"
                                 class="w-6 h-6 group-hover:brightness-0 group-hover:invert transition-all">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Pengumuman</h3>
                    <p class="text-sm text-gray-500">Informasi dan berita terkini</p>
                </a>
            @else
                <div class="bg-gray-100 rounded-xl border border-gray-200 p-6 flex flex-col opacity-60 cursor-not-allowed">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-200 rounded-lg">
                            <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}"
                                 alt="Pengumuman Icon"
                                 class="w-6 h-6 opacity-50">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-500 mb-1">Pengumuman</h3>
                    <p class="text-sm text-gray-400">Informasi dan berita terkini</p>

                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-xs text-gray-500">Pilih workspace terlebih dahulu</p>
                    </div>
                </div>
            @endif

            {{-- Card Jadwal --}}
            @if ($currentWorkspace)
                <a href="{{ route('jadwal', ['workspaceId' => $currentWorkspace->id]) }}"
                   @click="$store.workspace.selectedMenu = 'jadwal'"
                   class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-lg hover:-translate-y-1 transition-all duration-200 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-100 rounded-lg group-hover:bg-blue-600 transition-colors duration-200">
                            <img src="{{ asset('images/icons/workspace_kalender.svg') }}"
                                 alt="Jadwal Icon"
                                 class="w-6 h-6 group-hover:brightness-0 group-hover:invert transition-all">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Jadwal</h3>
                    <p class="text-sm text-gray-500">Kalender dan timeline proyek</p>
                </a>
            @else
                <div class="bg-gray-100 rounded-xl border border-gray-200 p-6 flex flex-col opacity-60 cursor-not-allowed">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-200 rounded-lg">
                            <img src="{{ asset('images/icons/workspace_kalender.svg') }}"
                                 alt="Jadwal Icon"
                                 class="w-6 h-6 opacity-50">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-500 mb-1">Jadwal</h3>
                    <p class="text-sm text-gray-400">Kalender dan timeline proyek</p>

                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-xs text-gray-500">Pilih workspace terlebih dahulu</p>
                    </div>
                </div>
            @endif

            {{-- Card Chat --}}
            @if ($currentWorkspace)
                <a href="{{ route('chat', $currentWorkspace->id) }}"
                   @click="$store.workspace.selectedMenu = 'chat'"
                   class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-lg hover:-translate-y-1 transition-all duration-200 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-100 rounded-lg group-hover:bg-blue-600 transition-colors duration-200">
                            <img src="{{ asset('images/icons/workspace_chat.svg') }}"
                                 alt="Chat Icon"
                                 class="w-6 h-6 group-hover:brightness-0 group-hover:invert transition-all">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Chat</h3>
                    <p class="text-sm text-gray-500">Komunikasi tim real-time</p>
                </a>
            @else
                <div class="bg-gray-100 rounded-xl border border-gray-200 p-6 flex flex-col opacity-60 cursor-not-allowed">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-200 rounded-lg">
                            <img src="{{ asset('images/icons/workspace_chat.svg') }}"
                                 alt="Chat Icon"
                                 class="w-6 h-6 opacity-50">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-500 mb-1">Chat</h3>
                    <p class="text-sm text-gray-400">Komunikasi tim real-time</p>

                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-xs text-gray-500">Pilih workspace terlebih dahulu</p>
                    </div>
                </div>
            @endif

            {{-- Card Mindmap --}}
            @if ($currentWorkspace)
                <a href="{{ url('/workspace/' . $currentWorkspace->id . '/mindmap') }}"
                   @click="$store.workspace.selectedMenu = 'insight'"
                   class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-lg hover:-translate-y-1 transition-all duration-200 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-100 rounded-lg group-hover:bg-blue-600 transition-colors duration-200">
                            <img src="{{ asset('images/icons/workspace_insight.svg') }}"
                                 alt="Mindmap Icon"
                                 class="w-6 h-6 group-hover:brightness-0 group-hover:invert transition-all">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Mindmap</h3>
                    <p class="text-sm text-gray-500">Visualisasi ide dan konsep</p>
                </a>
            @else
                <div class="bg-gray-100 rounded-xl border border-gray-200 p-6 flex flex-col opacity-60 cursor-not-allowed">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-200 rounded-lg">
                            <img src="{{ asset('images/icons/workspace_insight.svg') }}"
                                 alt="Mindmap Icon"
                                 class="w-6 h-6 opacity-50">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-500 mb-1">Mindmap</h3>
                    <p class="text-sm text-gray-400">Visualisasi ide dan konsep</p>

                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-xs text-gray-500">Pilih workspace terlebih dahulu</p>
                    </div>
                </div>
            @endif

            {{-- Card Dokumen --}}
            @if ($currentWorkspace)
                <a href="{{ route('dokumen-dan-file', $currentWorkspace->id) }}"
                   @click="$store.workspace.selectedMenu = 'dokumen'"
                   class="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-lg hover:-translate-y-1 transition-all duration-200 cursor-pointer">

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-100 rounded-lg group-hover:bg-blue-600 transition-colors duration-200">
                            <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}"
                                 alt="Dokumen Icon"
                                 class="w-6 h-6 group-hover:brightness-0 group-hover:invert transition-all">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Dokumen</h3>
                    <p class="text-sm text-gray-500">File dan dokumentasi</p>
                </a>
            @else
                <div class="bg-gray-100 rounded-xl border border-gray-200 p-6 flex flex-col opacity-60 cursor-not-allowed">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-gray-200 rounded-lg">
                            <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}"
                                 alt="Dokumen Icon"
                                 class="w-6 h-6 opacity-50">
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-500 mb-1">Dokumen</h3>
                    <p class="text-sm text-gray-400">File dan dokumentasi</p>

                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-xs text-gray-500">Pilih workspace terlebih dahulu</p>
                    </div>
                </div>
            @endif

        </div>

        {{-- Footer Info --}}
        @if ($currentWorkspace)
            <div class="max-w-6xl mx-auto px-8 pb-8">
                <p class="text-center text-sm text-gray-500">
                    Pilih salah satu menu untuk memulai bekerja
                </p>
            </div>
        @endif
    </div>
@endsection
