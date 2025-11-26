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
                $currentWorkspace = $user->userWorkspaces()
                    ->where('status_active', true)
                    ->with('workspace')
                    ->first()?->workspace;
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

    <div x-data x-init="$store.workspace = { selectedMenu: '' }" class="bg-[#f3f6fc] min-h-screen">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav', ['workspace' => $workspace, 'active' => ''])

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
                </a>
            @else
                <div
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center opacity-50 cursor-not-allowed">
                    <div class="w-16 h-16 mb-4 text-gray-400">
                        <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Tugas Icon" class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Tugas</span>
                    <p class="text-xs text-gray-500 mt-2 text-center">Pilih workspace terlebih dahulu</p>
                </div>
            @endif

            {{-- Card Pengumuman --}}
            <a href="{{ route('workspace.pengumuman', $workspace->id) }}"
                @click="$store.workspace.selectedMenu = 'pengumuman'"
                class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                    <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}" alt="Pengumuman Icon"
                        class="w-full h-full">
                </div>
                <span class="text-gray-700 font-medium">Pengumuman</span>
            </a>

            {{-- Card Jadwal --}}
            @if($currentWorkspace)
                <a href="{{ route('jadwal', ['workspaceId' => $currentWorkspace->id]) }}"
                   @click="$store.workspace.selectedMenu = 'jadwal'"
                   class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                    <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                        <img src="{{ asset('images/icons/workspace_kalender.svg') }}" alt="Jadwal Icon"
                            class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Jadwal</span>
                </a>
            @else
                <div
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center opacity-50 cursor-not-allowed">
                    <div class="w-16 h-16 mb-4 text-gray-400">
                        <img src="{{ asset('images/icons/workspace_kalender.svg') }}" alt="Jadwal Icon"
                            class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Jadwal</span>
                    <p class="text-xs text-gray-500 mt-2 text-center">Pilih workspace terlebih dahulu</p>
                </div>
            @endif

            {{-- Card Chat - 🔥 FIXED: Gunakan route dengan workspace ID --}}
            @if($currentWorkspace)
                <a href="{{ route('chat', $currentWorkspace->id) }}" @click="$store.workspace.selectedMenu = 'chat'"
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                    <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                        <img src="{{ asset('images/icons/workspace_chat.svg') }}" alt="Chat Icon" class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Chat</span>
                </a>
            @else
                <div
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center opacity-50 cursor-not-allowed">
                    <div class="w-16 h-16 mb-4 text-gray-400">
                        <img src="{{ asset('images/icons/workspace_chat.svg') }}" alt="Chat Icon" class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Chat</span>
                    <p class="text-xs text-gray-500 mt-2 text-center">Pilih workspace terlebih dahulu</p>
                </div>
            @endif

            {{-- Card Mindmap --}}
            @if ($currentWorkspace)
                <a href="{{ url('/mindmap') }}" @click="$store.workspace.selectedMenu = 'insight'"
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                    <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                        <img src="{{ asset('images/icons/workspace_insight.svg') }}" alt="Insight Icon"
                            class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Mindmap</span>
                </a>
            @else
                <div
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center opacity-50 cursor-not-allowed">
                    <div class="w-16 h-16 mb-4 text-gray-400">
                        <img src="{{ asset('images/icons/workspace_insight.svg') }}" alt="Insight Icon"
                            class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Mindmap</span>
                    <p class="text-xs text-gray-500 mt-2 text-center">Pilih workspace terlebih dahulu</p>
                </div>
            @endif

            {{-- Card Dokumen --}}
            @if($currentWorkspace)
                <a href="{{ route('dokumen-dan-file', $currentWorkspace->id) }}" @click="$store.workspace.selectedMenu = 'dokumen'"
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center hover:shadow-md transition group cursor-pointer">
                    <div class="w-16 h-16 mb-4 text-gray-400 group-hover:text-blue-500 transition">
                        <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen Icon"
                            class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Dokumen</span>
                </a>
            @else
                <div
                    class="bg-white rounded-2xl shadow-sm p-8 flex flex-col items-center justify-center opacity-50 cursor-not-allowed">
                    <div class="w-16 h-16 mb-4 text-gray-400">
                        <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen Icon"
                            class="w-full h-full">
                    </div>
                    <span class="text-gray-700 font-medium">Dokumen</span>
                    <p class="text-xs text-gray-500 mt-2 text-center">Pilih workspace terlebih dahulu</p>
                </div>
            @endif

        </div>

        {{-- Warning jika belum memilih workspace atau tidak memiliki akses --}}
        @if (!$currentWorkspace)
            <div class="max-w-6xl mx-auto px-8">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-yellow-700">
                            <strong>Peringatan:</strong>
                            @if($isCompanyAdmin)
                                Belum ada workspace yang tersedia atau Anda belum memilih workspace. Silakan buat atau pilih workspace dari halaman <a href="{{ route('kelola-workspace') }}" class="underline font-medium">Kelola Workspace</a> terlebih dahulu.
                            @else
                                Anda belum tergabung dalam workspace manapun atau belum memilih workspace. Silakan pilih
                                workspace dari halaman <a href="{{ route('kelola-workspace') }}"
                                    class="underline font-medium">Kelola Workspace</a> terlebih dahulu.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
