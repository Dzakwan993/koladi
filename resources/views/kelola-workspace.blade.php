@extends('layouts.app')

@section('title', 'Kelola Workspace')

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@section('content')
    <div class="p-6" x-data="workspaceManager">
        @php
            // ✅ CEK ROLE USER UNTUK TOMBOL CREATE
            $activeCompanyId = session('active_company_id');
            $user = auth()->user();
            $userCompany = $user->userCompanies()->where('company_id', $activeCompanyId)->with('role')->first();
            $userRole = $userCompany?->role?->name ?? 'Member';
            $canCreateWorkspace = in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);

            // ✅ CEK JIKA USER ADALAH SUPERADMIN/ADMIN
            $isCompanyAdmin = in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin']);

            // ✅ CEK JIKA USER ADALAH MANAGER DI COMPANY
            $isCompanyManager = $userRole === 'Manager';

            // ✅ CEK JIKA USER BOLEH EDIT/HAPUS WORKSPACE
            $canEditDeleteWorkspace = in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
        @endphp

        <!-- Modal untuk Buat Workspace -->
        <div x-show="showModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md" @click.outside="showModal = false">
                <form id="createWorkspaceForm" @submit.prevent="createWorkspace">
                    <div class="p-6">
                        <h2 class="text-center text-xl font-semibold text-gray-900 mb-4">Buat Workspace</h2>

                        <div class="mb-6">
                            <label for="workspace-name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Workspace
                            </label>
                            <input type="text" id="workspace-name" x-model="workspaceData.name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan nama workspace...">
                        </div>

                        <div class="mb-6">
                            <label for="workspace-description" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi
                            </label>
                            <textarea id="workspace-description" x-model="workspaceData.description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan deskripsi workspace..."></textarea>
                        </div>

                        <div class="mb-6">
                            <p class="block text-sm font-medium text-gray-700 mb-3">Untuk apakah workspace ini?</p>

                            <div class="space-y-3">
                                {{-- <label class="flex items-center">
                                    <input type="radio" name="workspace-type" value="HQ"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        x-model="workspaceData.type">
                                    <span class="ml-2 text-gray-700">HQ</span>
                                </label> --}}

                                <label class="flex items-center">
                                    <input type="radio" name="workspace-type" value="Tim"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        x-model="workspaceData.type">
                                    <span class="ml-2 text-gray-700">Tim</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="radio" name="workspace-type" value="Proyek"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        x-model="workspaceData.type">
                                    <span class="ml-2 text-gray-700">Proyek</span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4 flex justify-end">
                            <button type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mr-3"
                                @click="showModal = false">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                :disabled="isSubmitting">
                                <span x-show="!isSubmitting">Buat</span>
                                <span x-show="isSubmitting">Membuat...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Menu Workspace -->
        <div x-show="showWorkspaceMenu" x-cloak class="fixed inset-0 z-50" @click="showWorkspaceMenu = false">
            <div class="fixed bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-64"
                :style="`top: ${workspaceMenuPosition.y}px; left: ${workspaceMenuPosition.x}px`" @click.stop>

                <!-- ✅ SEMBUNYIKAN "Kelola Anggota" JIKA TIDAK BOLEH -->
                <template x-if="canManageMembers(activeWorkspace)">
                    <button
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                        @click="openManageMembers(activeWorkspace)">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        Kelola Anggota
                    </button>
                </template>

                <!-- memanggil fungsi openAccesModal() di components/hak-akses.blade  dengan context workspace -->
                <button class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    @click="showWorkspaceMenu = false;
                    window.openAccessModal({
                    type: 'workspace',
                    workspaceId: activeWorkspace?.id,
                    workspaceName: activeWorkspace?.name
                    });">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Atur Hak Akses
                </button>

                <!-- ✅ SEMBUNYIKAN "Edit Ruang Kerja" JIKA TIDAK BOLEH -->
                <template x-if="canEditDeleteWorkspace">
                    <button
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                        @click="openEditWorkspace(activeWorkspace)">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Ruang Kerja
                    </button>
                </template>

                <div class="border-t border-gray-200 my-1"></div>

                <!-- ✅ SEMBUNYIKAN "Hapus Ruang Kerja" JIKA TIDAK BOLEH -->
                <template x-if="canEditDeleteWorkspace">
                    <button class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-3"
                        @click="deleteWorkspace(activeWorkspace.id)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Ruang Kerja
                    </button>
                </template>
            </div>
        </div>

        <!-- Modal Kelola Anggota -->
        <div x-show="showManageMembersModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="showManageMembersModal = false; selectedMembers = []; searchMember = ''">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md max-h-[90vh] flex flex-col" @click.stop>
                <!-- Header -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Anggota</h2>
                </div>

                <!-- Search Bar -->
                <div class="p-4 border-b border-gray-200">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-model="searchMember" placeholder="Cari anggota..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Members List -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="space-y-3">
                        <template x-for="member in filteredMembers" :key="member.id">
                            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="flex items-center gap-3">
                                    <img :src="member.avatar" :alt="member.name" class="w-8 h-8 rounded-full">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="member.name"></p>
                                        <p class="text-xs text-gray-500" x-text="member.email"></p>
                                    </div>
                                </div>
                                <input type="checkbox" :checked="isMemberSelected(member.id)"
                                    @change="toggleMember(member.id)"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="filteredMembers.length === 0" class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500 text-sm">Tidak ada anggota yang ditemukan</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <div class="flex justify-end gap-3">
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @click="showManageMembersModal = false; selectedMembers = []; searchMember = ''">
                            Batal
                        </button>
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            @click="applyMembers">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Ruang Kerja -->
        <div x-show="showEditWorkspaceModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="showEditWorkspaceModal = false">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md" @click.stop>
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Ubah Tim</h2>

                    <!-- Nama Tim -->
                    <div class="mb-6">
                        <label for="edit-workspace-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Tim
                        </label>
                        <input type="text" id="edit-workspace-name" x-model="editWorkspaceData.name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan nama tim...">
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-6">
                        <label for="edit-workspace-description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea id="edit-workspace-description" x-model="editWorkspaceData.description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan deskripsi tim..."></textarea>
                    </div>

                    <!-- Pindahkan ke -->
                    <div class="mb-6">
                        <p class="block text-sm font-medium text-gray-700 mb-3">Pindahkan ke</p>

                        <div class="space-y-3">
                            {{-- <label class="flex items-center">
                                <input type="radio" name="edit-workspace-type" value="HQ"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    x-model="editWorkspaceData.type">
                                <span class="ml-2 text-gray-700">HQ</span>
                            </label> --}}

                            <label class="flex items-center">
                                <input type="radio" name="edit-workspace-type" value="Tim"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    x-model="editWorkspaceData.type">
                                <span class="ml-2 text-gray-700">Tim</span>
                            </label>

                            <label class="flex items-center">
                                <input type="radio" name="edit-workspace-type" value="Proyek"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    x-model="editWorkspaceData.type">
                                <span class="ml-2 text-gray-700">Proyek</span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 flex justify-end">
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mr-3"
                            @click="showEditWorkspaceModal = false">
                            Batal
                        </button>
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            @click="saveWorkspaceChanges">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tim Section -->
        <div class="m-8">
            <div class="flex items-center justify-between mb-4">
                <button @click="timOpen = !timOpen"
                    class="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition">
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': !timOpen }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span class="font-medium">Tim</span>
                </button>
                @if ($canCreateWorkspace)
                    <!-- ✅ TAMPILKAN TOMBOL JIKA BOLEH CREATE -->
                    <button
                        class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition shadow-lg"
                        @click="showModal = true; workspaceData.type = 'Tim'">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                @else
                    <!-- ✅ SEMBUNYIKAN TOMBOL JIKA TIDAK BOLEH CREATE -->
                    <button
                        class="w-10 h-10 bg-gray-400 cursor-not-allowed rounded-full flex items-center justify-center transition shadow-lg"
                        title="Hanya SuperAdmin, Admin, dan Manager yang dapat membuat workspace">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                @endif
            </div>

            <div x-show="timOpen" x-collapse>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @forelse($workspaces['Tim'] ?? [] as $workspace)
                        <!-- ✅ PERBAIKAN: Link ke workspace spesifik -->
                        <a href="{{ route('workspace.detail', $workspace->id) }}"
                            class="block bg-white rounded-xl border border-gray-200 p-4 relative group hover:shadow-md transition-shadow duration-200">
                            <div class="flex justify-between items-start">
                                <h3 class="font-semibold text-gray-800">{{ $workspace->name }}</h3>

                                <!-- tombol titik tiga -->
                                <button @click.stop.prevent="openWorkspaceMenu($event, {{ json_encode($workspace) }})"
                                    class="p-1 hover:bg-gray-100 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12v.01M12 12v.01M19 12v.01
                                                                                                                                                        M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </button>
                            </div>

                            <p class="text-sm text-gray-600 mt-2">
                                {{ $workspace->description ?? 'Tidak ada deskripsi' }}
                            </p>

                            <div class="mt-4">
                                <div class="flex -space-x-2">
                                    @foreach ($workspace->userWorkspaces->take(4) as $userWorkspace)
                                        @php
                                            // ✅ TAMBAHKAN LOGIC AVATAR INI
                                            $member = $userWorkspace->user;
                                            if (
                                                $member->avatar &&
                                                Str::startsWith($member->avatar, ['http://', 'https://'])
                                            ) {
                                                $memberAvatar = $member->avatar;
                                            } elseif ($member->avatar) {
                                                $memberAvatar = asset('storage/' . $member->avatar);
                                            } else {
                                                $memberAvatar =
                                                    'https://ui-avatars.com/api/?name=' .
                                                    urlencode($member->full_name ?? 'User') .
                                                    '&background=4F46E5&color=fff&bold=true';
                                            }
                                        @endphp

                                        {{-- ✅ GANTI IMG TAG INI --}}
                                        <img src="{{ $memberAvatar }}" alt="{{ $member->full_name }}"
                                            class="w-8 h-8 rounded-full border-2 border-white object-cover"
                                            title="{{ $member->full_name }}">
                                    @endforeach

                                    @if ($workspace->userWorkspaces->count() > 4)
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center">
                                            <span
                                                class="text-xs text-gray-600">+{{ $workspace->userWorkspaces->count() - 4 }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-4 text-center py-8 text-gray-500">
                            @if ($isCompanyAdmin)
                                Belum ada workspace Tim di perusahaan ini
                            @else
                                Anda belum tergabung dalam workspace Tim manapun
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Proyek Section -->
        <div class="mb-4 mx-8 mt-8">
            <div class="flex items-center justify-between mb-4">
                <button @click="proyekOpen = !proyekOpen"
                    class="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition">
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': !proyekOpen }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <span class="font-medium">Proyek</span>
                </button>

                @if ($canCreateWorkspace)
                    <!-- ✅ TAMPILKAN TOMBOL JIKA BOLEH CREATE -->
                    <button
                        class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition shadow-lg"
                        @click="showModal = true; workspaceData.type = 'Proyek'">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                @else
                    <!-- ✅ SEMBUNYIKAN TOMBOL JIKA TIDAK BOLEH CREATE -->
                    <button
                        class="w-10 h-10 bg-gray-400 cursor-not-allowed rounded-full flex items-center justify-center transition shadow-lg"
                        title="Hanya SuperAdmin, Admin, dan Manager yang dapat membuat workspace">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                @endif
            </div>

            <div x-show="proyekOpen" x-collapse>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @forelse($workspaces['Proyek'] ?? [] as $workspace)
                        <!-- ✅ PERBAIKAN: Link ke workspace spesifik -->
                        <a href="{{ route('workspace.detail', $workspace->id) }}"
                            class="block bg-white rounded-xl border border-gray-200 p-4 relative group hover:shadow-md transition-shadow duration-200">
                            <div class="flex justify-between items-start">
                                <h3 class="font-semibold text-gray-800">{{ $workspace->name }}</h3>

                                <!-- tombol titik tiga -->
                                <button @click.stop.prevent="openWorkspaceMenu($event, {{ json_encode($workspace) }})"
                                    class="p-1 hover:bg-gray-100 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12v.01M12 12v.01M19 12v.01
                                                                                                                                                        M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </button>
                            </div>

                            <p class="text-sm text-gray-600 mt-2">
                                {{ $workspace->description ?? 'Tidak ada deskripsi' }}
                            </p>

                            <div class="mt-4">
                                <div class="flex -space-x-2">
                                    @foreach ($workspace->userWorkspaces->take(4) as $userWorkspace)
                                        @php
                                            // ✅ TAMBAHKAN LOGIC AVATAR INI
                                            $member = $userWorkspace->user;
                                            if (
                                                $member->avatar &&
                                                Str::startsWith($member->avatar, ['http://', 'https://'])
                                            ) {
                                                $memberAvatar = $member->avatar;
                                            } elseif ($member->avatar) {
                                                $memberAvatar = asset('storage/' . $member->avatar);
                                            } else {
                                                $memberAvatar =
                                                    'https://ui-avatars.com/api/?name=' .
                                                    urlencode($member->full_name ?? 'User') .
                                                    '&background=4F46E5&color=fff&bold=true';
                                            }
                                        @endphp

                                        {{-- ✅ GANTI IMG TAG INI --}}
                                        <img src="{{ $memberAvatar }}" alt="{{ $member->full_name }}"
                                            class="w-8 h-8 rounded-full border-2 border-white object-cover"
                                            title="{{ $member->full_name }}">
                                    @endforeach

                                    @if ($workspace->userWorkspaces->count() > 4)
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center">
                                            <span
                                                class="text-xs text-gray-600">+{{ $workspace->userWorkspaces->count() - 4 }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-4 text-center py-8 text-gray-500">
                            @if ($isCompanyAdmin)
                                Belum ada workspace Proyek di perusahaan ini
                            @else
                                Anda belum tergabung dalam workspace Proyek manapun
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>


        <!-- Modal Atur Hak Akses -->
        <div x-show="showAccessRightsModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="showAccessRightsModal = false">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] overflow-hidden" @click.stop>
                <!-- Header -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Akses Anda di PT. Mencari Cinta Sejati</h2>
                    <div class="mt-2 flex justify-between items-center">
                        <div>
                            <p class="text-lg font-medium text-gray-900">Muhammad Sahroni</p>
                            <p class="text-sm text-gray-500">Super Admin</p>
                        </div>
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-700 border border-blue-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            @click="showRoleModal = true; showAccessRightsModal = false">
                            Atur Akses Pengguna
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <!-- Super Admin Section -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Super Admin</h3>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-600 ml-6">
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Mengatur akses pengguna</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Menggundang anggota tim</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Menggundang anggota tim</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Membuat tim dan tugas dan Membuat tim dan tugas Membuat tim dan tugas</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Menggundang anggota tim</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Super admin memiliki semua hak akses yang itu</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Admin Section -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Admin</h3>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-600 ml-6">
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Mengatur akses pengguna</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Menggundang anggota tim</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Menggundang anggota tim</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Membuat tim dan tugas dan Membuat tim dan tugas Membuat tim dan tugas</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Menggundang anggota tim</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1">•</span>
                                <span>Super admin memiliki semua hak akses yang itu</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Manager Section -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Manager</h3>
                        </div>
                        <!-- Kosong sesuai gambar -->
                    </div>

                    <!-- Member Section -->
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-900">Member</h3>
                        </div>
                        <!-- Kosong sesuai gambar -->
                    </div>

                    <!-- Footer Text -->
                    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600">
                            Ini adalah Akses default perusahaan kamu. Kamu memiliki Akses yang sama di semua tempat
                            berdasarkan ini.
                            Hingga Super User/Admin mengubah Akses Kamu secara khusus di suatu Tim atau Tugas/Dokumen lain.
                            Kalo Kamu mengalami kendala saat melakukan sesuatu, harap hubungi orang diatas.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <div class="flex justify-end">
                        <button type="button"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            @click="showAccessRightsModal = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Atur Role -->
        <div x-show="showRoleModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="showRoleModal = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl" @click.stop>
                <!-- Header -->
                <div class="p-6 pb-4 relative">
                    <button @click="showRoleModal = false"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <h2 class="text-2xl font-bold text-gray-900">Atur role</h2>
                    <p class="text-sm text-gray-500 mt-2">
                        Anda bebas bisa mengatur role rekan ada ataupun mengubah rolenya dibawah...
                    </p>
                </div>

                <!-- User List -->
                <div class="px-6 py-4 space-y-3 max-h-[50vh] overflow-y-auto bg-blue-50">
                </div>

                <!-- Footer -->
                <div class="p-6 flex justify-end gap-3 bg-white rounded-b-2xl">
                    <button type="button"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @click="showRoleModal = false">
                        Batal
                    </button>
                    <button type="button"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        @click="showRoleModal = false">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('workspaceManager', () => ({
                // Data
                hqOpen: true,
                timOpen: true,
                proyekOpen: true,
                showModal: false,
                showAccessRightsModal: false,
                showRoleModal: false,
                showWorkspaceMenu: false,
                showManageMembersModal: false,
                showEditWorkspaceModal: false,
                isSubmitting: false,

                // Workspace data
                activeWorkspace: null,
                workspaceMenuPosition: {
                    x: 0,
                    y: 0
                },
                workspaceData: {
                    name: '',
                    description: '',
                    type: 'HQ'
                },
                editWorkspaceData: {
                    id: '',
                    name: '',
                    description: '',
                    type: 'HQ'
                },

                // Members data
                searchMember: '',
                selectedMembers: [],
                availableMembers: [],
                currentWorkspaceMembers: [],

                // Initialize
                async init() {
                    await this.loadAvailableMembers();
                },

                canEditDeleteWorkspace: {{ $canEditDeleteWorkspace ? 'true' : 'false' }},

                canManageMembers(workspace) {
                    if (!workspace) return false;
                    // ✅ CEK ROLE USER DI COMPANY (dari PHP)
                    const userCompanyRole = '{{ $userRole }}';
                    const isCompanyAdmin = ['SuperAdmin', 'Administrator', 'Admin'].includes(
                        userCompanyRole);
                    const isCompanyManager = userCompanyRole === 'Manager';

                    // ✅ JIKA SUPERADMIN/ADMIN/MANAGER DI COMPANY, BOLEH KELOLA ANGGOTA
                    if (isCompanyAdmin || isCompanyManager) {
                        return true;
                    }

                    // ✅ JIKA BUKAN, CEK APAKAH USER ADALAH MANAGER DI WORKSPACE
                    // Ini akan di-check di backend saat membuka modal, tapi kita bisa kasih indikator di frontend
                    // Untuk sekarang, return true dan biarkan backend yang validasi
                    return true;
                },

                // Methods untuk workspace
                openWorkspaceMenu(event, workspace) {
                    this.activeWorkspace = workspace;
                    this.workspaceMenuPosition = {
                        x: event.clientX - 256,
                        y: event.clientY + 10
                    };
                    this.showWorkspaceMenu = true;
                },

                async createWorkspace() {
                    this.isSubmitting = true;

                    try {
                        const csrfToken = this.getCsrfToken();

                        const response = await fetch('/workspace', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.workspaceData)
                        });

                        const result = await response.json();
                        console.log('✅ Workspace creation response:', result);

                        if (result.success) {
                            console.log('🎯 show_onboarding:', result.show_onboarding);
                            console.log('🎯 workspace_name:', result.workspace_name);

                            // Close modal and reset form
                            this.showModal = false;
                            this.workspaceData = {
                                name: '',
                                description: '',
                                type: 'HQ'
                            };

                            // ✅ CEK APAKAH PERLU SHOW ONBOARDING
                            if (result.show_onboarding && result.workspace_name) {
                                // Delay sedikit untuk smooth transition
                                setTimeout(() => {
                                    showOnboardingStep5Modal(result.workspace_name);
                                }, 500);
                            } else {
                                // Normal flow tanpa onboarding
                                location.reload();
                            }
                        } else {
                            // ✅ HANDLE ERROR RESPONSE
                            if (response.status === 403) {
                                alert('Akses Ditolak: ' + result.message);
                            } else {
                                alert('Gagal membuat workspace: ' + result.message);
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat membuat workspace');
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                async updateWorkspace() {
                    try {
                        const csrfToken = this.getCsrfToken();

                        const response = await fetch(
                            `/workspace/${this.editWorkspaceData.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(this.editWorkspaceData)
                            });

                        const result = await response.json();

                        if (result.success) {
                            this.showEditWorkspaceModal = false;
                            location.reload();
                        } else {
                            alert('Gagal mengupdate workspace: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengupdate workspace');
                    }
                },

                async deleteWorkspace(workspaceId) {
                    try {
                        // ✅ CEK PERMISSION SEBELUM MENGHAPUS
                        if (!this.canEditDeleteWorkspace) {
                            alert(
                                'Anda tidak memiliki izin untuk menghapus workspace. Hanya SuperAdmin, Admin, dan Manager yang dapat menghapus workspace.'
                            );
                            this.showWorkspaceMenu = false;
                            return;
                        }

                        if (!confirm(
                                'Apakah Anda yakin ingin menghapus workspace ini?')) {
                            return;
                        }

                        const csrfToken = this.getCsrfToken();

                        const response = await fetch(`/workspace/${workspaceId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.showWorkspaceMenu = false;
                            location.reload();
                        } else {
                            // ✅ TAMPILKAN ERROR MESSAGE YANG DETAIL
                            if (response.status === 403) {
                                alert('Akses Ditolak: ' + result.message);
                            } else {
                                alert('Gagal menghapus workspace: ' + result.message);
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus workspace');
                    }
                },
                // Methods untuk members
                async loadAvailableMembers() {
                    try {
                        const response = await fetch('/workspace-available-users', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            this.availableMembers = await response.json();
                            console.log('Available members loaded:', this
                                .availableMembers);
                        } else {
                            console.error('Failed to load available members');
                            this.availableMembers = [];
                        }
                    } catch (error) {
                        console.error('Error loading available members:', error);
                        this.availableMembers = [];
                    }
                },
                async loadWorkspaceMembers(workspaceId) {
                    try {
                        console.log('Loading members for workspace:',
                            workspaceId);
                        const response = await fetch(
                            `/workspace/${workspaceId}/members`);
                        if (response.ok) {
                            const members = await response.json();
                            console.log('Workspace members loaded:', members);

                            // SET selectedMembers dengan ID user yang sudah terdaftar
                            this.selectedMembers = members.map(member => member
                                .id);
                            console.log('Selected members set to:', this
                                .selectedMembers);

                            this.currentWorkspaceMembers = members;
                        } else {
                            console.error('Failed to load workspace members');
                            this.selectedMembers = [];
                            this.currentWorkspaceMembers = [];
                        }
                    } catch (error) {
                        console.error('Error loading workspace members:',
                            error);
                        this.selectedMembers = [];
                        this.currentWorkspaceMembers = [];
                    }
                },


                async saveMembers(workspaceId) {
                    this.isSubmitting = true;
                    try {
                        const csrfToken = this.getCsrfToken();
                        const payload = {
                            user_ids: this.selectedMembers,
                            role_id: this.getDefaultRoleId()
                        };
                        console.log('Saving members payload:', payload,
                            'workspaceId:',
                            workspaceId);

                        const response = await fetch(
                            `/workspace/${workspaceId}/members`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(payload)
                            });

                        const rawText = await response.text();
                        let result = null;
                        try {
                            result = rawText ? JSON.parse(rawText) : null;
                        } catch (e) {
                            console.warn('Response is not JSON:', rawText);
                        }

                        console.log('Response status:', response.status,
                            'parsed:', result, 'raw:',
                            rawText);

                        if (response.ok) {
                            // sukses
                            this.showManageMembersModal = false;
                            this.selectedMembers = [];
                            this.searchMember = '';
                            location.reload();
                            return {
                                success: true,
                                message: result?.message || 'Berhasil'
                            };
                        } else {
                            // ambil pesan error yang paling bermakna
                            const serverMsg = result?.message ||
                                (result?.errors ? JSON.stringify(result
                                    .errors) : null) ||
                                rawText ||
                                response.statusText;
                            console.error('Failed saving members:', response
                                .status, serverMsg);
                            alert('Gagal menyimpan anggota: ' + serverMsg);
                            return {
                                success: false,
                                message: serverMsg
                            };
                        }
                    } catch (error) {
                        console.error('saveMembers exception:', error);
                        alert('Gagal menyimpan anggota: ' + (error
                            .message || error));
                        return {
                            success: false,
                            message: error.message || String(error)
                        };
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                // Helper methods
                async openEditWorkspace(workspace) {
                    try {
                        // ✅ CEK PERMISSION SEBELUM MEMBUKA MODAL EDIT
                        if (!this.canEditDeleteWorkspace) {
                            alert(
                                'Anda tidak memiliki izin untuk mengedit workspace. Hanya SuperAdmin, Admin, dan Manager yang dapat mengedit workspace.'
                            );
                            this.showWorkspaceMenu = false;
                            return;
                        }

                        this.editWorkspaceData = {
                            id: workspace.id,
                            name: workspace.name,
                            description: workspace.description ||
                                '',
                            type: workspace.type
                        };
                        this.showEditWorkspaceModal = true;
                        this.showWorkspaceMenu = false;
                    } catch (error) {
                        console.error('Error opening edit workspace:',
                            error);
                        alert(
                            'Terjadi kesalahan saat membuka form edit');
                        this.showWorkspaceMenu = false;
                    }
                },

                async openManageMembers(workspace) {
                    this.activeWorkspace = workspace;

                    try {
                        // ✅ CEK PERMISSION SEBELUM MEMBUKA MODAL
                        const response = await fetch(
                            `/workspace/${workspace.id}/members`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                        if (response.ok) {
                            this.showManageMembersModal = true;
                            this.showWorkspaceMenu = false;
                            this.selectedMembers = [];
                            this.searchMember = '';

                            // Load members workspace saat modal dibuka
                            setTimeout(() => {
                                this.loadWorkspaceMembers(
                                    workspace.id);
                            }, 100);
                        } else if (response.status === 403) {
                            const result = await response.json();
                            alert('Akses Ditolak: ' + result.error);
                            this.showWorkspaceMenu = false;
                        } else {
                            alert('Gagal memuat data anggota');
                            this.showWorkspaceMenu = false;
                        }
                    } catch (error) {
                        console.error('Error checking permission:',
                            error);
                        alert(
                            'Terjadi kesalahan saat memeriksa akses');
                        this.showWorkspaceMenu = false;
                    }
                },

                async saveWorkspaceChanges() {
                    try {
                        const csrfToken = this.getCsrfToken();

                        const response = await fetch(
                            `/workspace/${this.editWorkspaceData.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(this
                                    .editWorkspaceData)
                            });

                        const result = await response.json();

                        if (result.success) {
                            this.showEditWorkspaceModal = false;
                            location.reload();
                        } else {
                            // ✅ TAMPILKAN ERROR MESSAGE YANG DETAIL
                            if (response.status === 403) {
                                alert('Akses Ditolak: ' + result
                                    .message);
                            } else {
                                alert('Gagal mengupdate workspace: ' +
                                    result.message);
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert(
                            'Terjadi kesalahan saat mengupdate workspace');
                    }
                },


                applyMembers() {
                    if (this.activeWorkspace) {
                        this.saveMembers(this.activeWorkspace
                            .id);
                    }
                },

                toggleMember(memberId) {
                    console.log('Toggling member:', memberId,
                        'Current selected:', this
                        .selectedMembers);
                    const index = this.selectedMembers.indexOf(
                        memberId);
                    if (index === -1) {
                        this.selectedMembers.push(memberId);
                    } else {
                        this.selectedMembers.splice(index, 1);
                    }
                    console.log('After toggle selected:', this
                        .selectedMembers);
                },

                isMemberSelected(memberId) {
                    const isSelected = this.selectedMembers
                        .includes(memberId);
                    console.log(`Checking member ${memberId}:`,
                        isSelected);
                    return isSelected;
                },

                get filteredMembers() {
                    if (!this.searchMember) {
                        return this.availableMembers;
                    }
                    const searchTerm = this.searchMember
                        .toLowerCase();
                    return this.availableMembers.filter(
                        member =>
                        member.name.toLowerCase().includes(
                            searchTerm) ||
                        member.email.toLowerCase().includes(
                            searchTerm)
                    );
                },

                getCsrfToken() {
                    const metaTag = document.querySelector(
                        'meta[name="csrf-token"]');
                    if (metaTag) {
                        return metaTag.getAttribute('content');
                    }

                    const inputTag = document.querySelector(
                        'input[name="_token"]');
                    if (inputTag) {
                        return inputTag.value;
                    }

                    return document.querySelector(
                            'script[data-csrf]')?.dataset
                        .csrf || '';
                },

                getDefaultRoleId() {
                    return '{{ \App\Models\Role::where('name', 'Member')->first()->id ?? '' }}';
                }
            }));
        });
    </script>

    @php
        // ✅ DEFINISIKAN AVAILABLE ROLES UNTUK WORKSPACE (Manager & Member saja)
        $workspaceRoles = \App\Models\Role::whereIn('id', [
            'a688ef38-3030-45cb-9a4d-0407605bc322', // Manager
            'ed81bd39-9041-43b8-a504-bf743b5c2919', // Member
        ])->get(['id', 'name']);

        // ✅ Fallback manual jika query gagal
        if ($workspaceRoles->count() === 0) {
            $workspaceRoles = collect([
                (object) ['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager'],
                (object) ['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member'],
            ]);
        }
    @endphp

    <script>
        // ✅ SET GLOBAL VARIABLE UNTUK WORKSPACE ROLES
        window.availableRolesForWorkspace = @json($workspaceRoles);

        console.log('✅ Available roles for workspace:', window.availableRolesForWorkspace);
        console.table(window.availableRolesForWorkspace);
    </script>

    {{-- 🎯 ONBOARDING STEP 4 --}}
    <div id="onboarding-step3" class="hidden fixed inset-0 z-[9999]">
        <div class="absolute inset-0 bg-black/50 transition-opacity duration-500"></div>
        <div id="spotlight-step3" class="absolute rounded-full transition-all duration-500"></div>

        <div id="tooltip-step3"
            class="absolute bg-white rounded-3xl shadow-2xl p-8 w-[420px] max-w-[90vw] border-2 border-blue-500/30"
            style="z-index: 10001; opacity: 0;">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg ring-4 ring-blue-100 relative">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <div
                            class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white animate-pulse">
                        </div>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Langkah Terakhir! 🎉</h3>
                    <p class="text-sm text-gray-600 leading-relaxed mb-4">
                        Klik tombol <strong class="text-blue-600">+</strong> untuk membuat workspace pertama Anda!
                    </p>

                    <div class="flex items-center gap-2 mb-5">
                        <div class="flex-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"
                                style="width: 100%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-500">4/4</span>
                    </div>

                    <button onclick="finishOnboarding()"
                        class="w-full px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-bold rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg flex items-center justify-center gap-2">
                        Mengerti! <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Arrow -->
            <div id="arrow-step3" class="absolute pointer-events-none"></div>
        </div>
    </div>

    {{-- 🎯 STEP 5: ONBOARDING MODAL (Hidden by default) --}}
    <div id="onboarding-step5-modal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity duration-500"></div>

        <!-- Modal Card -->
        <div class="relative bg-white rounded-3xl shadow-2xl w-[600px] max-w-[90vw] mx-4 transform transition-all duration-500 scale-95 opacity-0"
            id="modal-content-step5">

            <!-- ✨ Celebration Effects -->
            <div class="absolute -top-10 -left-10 w-24 h-24 bg-yellow-400 rounded-full blur-3xl opacity-50 animate-pulse">
            </div>
            <div
                class="absolute -bottom-10 -right-10 w-32 h-32 bg-blue-400 rounded-full blur-3xl opacity-50 animate-pulse">
            </div>

            <!-- Confetti Animation -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden rounded-3xl">
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
            </div>

            <!-- Content -->
            <div class="relative p-8">
                <!-- Icon Header -->
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div
                            class="w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center shadow-xl transform hover:scale-110 transition-transform duration-300">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <!-- Ping Effects -->
                        <div class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-ping"></div>
                        <div class="absolute -bottom-2 -left-2 w-3 h-3 bg-blue-400 rounded-full animate-ping"
                            style="animation-delay: 0.2s"></div>
                    </div>
                </div>

                <!-- Title -->
                <h2 class="text-3xl font-bold text-center text-gray-900 mb-3">
                    🎉 Workspace Berhasil Dibuat!
                </h2>

                <!-- Subtitle dengan nama workspace -->
                <p class="text-center text-gray-600 mb-8 text-lg">
                    Workspace <strong class="text-blue-600" id="workspace-name-display"></strong> siap digunakan!
                </p>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <!-- Feature 1 -->
                    <div
                        class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm mb-1">Kelola Tim</h3>
                                <p class="text-xs text-gray-600">Tambah anggota & atur role</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                        style="transition-delay: 0.1s">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm mb-1">Kanban Board</h3>
                                <p class="text-xs text-gray-600">Kelola tugas dengan mudah</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                        style="transition-delay: 0.2s">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm mb-1">Kolaborasi</h3>
                                <p class="text-xs text-gray-600">Chat & berbagi file</p>
                            </div>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                        style="transition-delay: 0.3s">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm mb-1">Jadwal & Event</h3>
                                <p class="text-xs text-gray-600">Atur meeting & deadline</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-600">Tutorial Selesai</span>
                        <span class="text-sm font-bold text-green-600">100%</span>
                    </div>
                    <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-400 via-green-500 to-green-600 rounded-full transition-all duration-[2s] ease-out"
                            id="progress-bar-step5" style="width: 0%"></div>
                    </div>
                </div>

                <!-- CTA Button -->
                <button onclick="completeOnboardingStep5()"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3.5 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Siap, Mari Mulai!
                </button>

                <!-- Help Text -->
                <p class="text-center text-xs text-gray-500 mt-4">
                    Klik workspace di sidebar untuk mulai bekerja 🚀
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const step = '{{ Auth::user()->onboarding_step ?? '' }}';
                console.log('🎯 Kelola Workspace - Current step:', step);

                if (step === 'kelola-workspace') {
                    setTimeout(() => showStep4(), 800);
                }
            });

            function showStep4() {
                const overlay = document.getElementById('onboarding-step3');
                const createBtnTim = document.querySelector(
                    'button[\\@click*="showModal = true; workspaceData.type = \'Tim\'"]');
                const spotlight = document.getElementById('spotlight-step3');
                const tooltip = document.getElementById('tooltip-step3');
                const arrow = document.getElementById('arrow-step3');

                if (!createBtnTim) {
                    console.error('❌ Tombol + Tim tidak ditemukan');
                    return;
                }

                console.log('✅ Showing Step 4: Create workspace button');
                overlay.classList.remove('hidden');

                createBtnTim.style.position = 'relative';
                createBtnTim.style.zIndex = '10000';

                // ✅ Fungsi positioning yang bisa dipanggil ulang
                function positionTooltipStep4() {
                    const rect = createBtnTim.getBoundingClientRect();
                    const padding = 15;

                    // Spotlight positioning
                    spotlight.style.left = (rect.left - padding) + 'px';
                    spotlight.style.top = (rect.top - padding) + 'px';
                    spotlight.style.width = (rect.width + padding * 2) + 'px';
                    spotlight.style.height = (rect.height + padding * 2) + 'px';
                    spotlight.style.boxShadow = `
            0 0 0 9999px rgba(0, 0, 0, 0.5),
            0 0 0 ${padding + 4}px rgba(59, 130, 246, 0.6),
            0 0 80px 15px rgba(59, 130, 246, 0.5)
        `;

                    // ✅ Tooltip positioning - RESPONSIVE
                    const tooltipWidth = window.innerWidth < 640 ? 340 : 420;
                    const gap = window.innerWidth < 768 ? 15 : 30;

                    let tooltipLeft, tooltipTop, arrowPos;

                    if (window.innerWidth < 1024) {
                        // Mobile & Tablet: taruh di atas button
                        tooltipTop = rect.top - 320 - gap;
                        tooltipLeft = Math.max(20, Math.min(window.innerWidth - tooltipWidth - 20,
                            rect.left + (rect.width / 2) - (tooltipWidth / 2)));
                        arrowPos = 'bottom';
                    } else {
                        // Desktop: taruh di kiri
                        tooltipLeft = rect.left - tooltipWidth - gap;
                        tooltipTop = rect.top - 50;
                        arrowPos = 'right';
                    }

                    tooltip.style.left = tooltipLeft + 'px';
                    tooltip.style.top = tooltipTop + 'px';
                    tooltip.style.opacity = '1';
                    tooltip.classList.add('onboarding-tooltip');

                    // Arrow styling
                    if (arrowPos === 'bottom') {
                        arrow.style.bottom = '-12px';
                        arrow.style.left = (rect.left + rect.width / 2 - tooltipLeft) + 'px';
                        arrow.style.top = 'auto';
                        arrow.style.right = 'auto';
                        arrow.style.transform = 'translateX(-50%)';
                        arrow.style.width = '0';
                        arrow.style.height = '0';
                        arrow.style.borderTop = '12px solid white';
                        arrow.style.borderLeft = '12px solid transparent';
                        arrow.style.borderRight = '12px solid transparent';
                        arrow.style.borderBottom = 'none';
                        arrow.style.filter = 'drop-shadow(0 2px 4px rgba(0,0,0,0.1))';
                    } else {
                        arrow.style.right = '-12px';
                        arrow.style.top = '35px'; // ⬅️ PERUBAHAN: dari '50%' jadi '35px'
                        arrow.style.left = 'auto';
                        arrow.style.bottom = 'auto';
                        arrow.style.transform = 'none'; // ⬅️ PERUBAHAN: hapus translateY
                        arrow.style.width = '0';
                        arrow.style.height = '0';
                        arrow.style.borderLeft = '12px solid white';
                        arrow.style.borderTop = '12px solid transparent';
                        arrow.style.borderBottom = '12px solid transparent';
                        arrow.style.borderRight = 'none';
                        arrow.style.filter = 'drop-shadow(2px 0 4px rgba(0,0,0,0.1))';
                    }
                }

                // ✅ Panggil pertama kali
                positionTooltipStep4();

                // ✅ TAMBAHKAN SCROLL LISTENER - agar tooltip ikut scroll
                const scrollContainer = document.querySelector('main') || document.querySelector('.overflow-y-auto');
                if (scrollContainer) {
                    scrollContainer.addEventListener('scroll', positionTooltipStep4);
                }
                // ✅ Simpan reference
                window._onboardingStep4 = {
                    overlay,
                    spotlight,
                    tooltip,
                    arrow,
                    button: createBtnTim,
                    positionFunc: positionTooltipStep4
                };

                // ✅ TAMBAHKAN RESIZE LISTENER
                window.addEventListener('resize', positionTooltipStep4);

                const newButton = createBtnTim.cloneNode(true);
                createBtnTim.parentNode.replaceChild(newButton, createBtnTim);

                newButton.style.position = 'relative';
                newButton.style.zIndex = '10000';

                newButton.addEventListener('click', function(e) {
                    console.log('✅ Tombol + diklik!');

                    // ✅ HAPUS RESIZE DAN SCROLL LISTENER
                    if (window._onboardingStep4?.positionFunc) {
                        window.removeEventListener('resize', window._onboardingStep4.positionFunc);
                        const scrollContainer = document.querySelector('main') || document.querySelector(
                            '.overflow-y-auto');
                        if (scrollContainer) {
                            scrollContainer.removeEventListener('scroll', window._onboardingStep4.positionFunc);
                        }
                    }

                    // ✅ JANGAN LANGSUNG MARK AS SEEN, TAPI UPDATE KE STEP BERIKUTNYA
                    fetch('{{ route('update-onboarding-step') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                step: 'workspace-created' // ⬅️ STEP BARU
                            })
                        })
                        .then(() => {
                            console.log('✅ Moving to workspace creation...');
                            overlay.classList.add('hidden');
                            newButton.style.zIndex = '';
                            newButton.style.position = '';

                            const clickAttr = newButton.getAttribute('@click');
                            if (clickAttr && window.Alpine) {
                                try {
                                    Alpine.evaluate(newButton, clickAttr);
                                } catch (err) {
                                    console.error('❌ Error:', err);
                                    newButton.click();
                                }
                            }

                            delete window._onboardingStep4;
                        });
                });
            }

            // ✅ UPDATE fungsi finishOnboarding juga
            function finishOnboarding() {
                console.log('✅ User clicked "Mengerti!"');

                // ✅ HAPUS RESIZE DAN SCROLL LISTENER
                if (window._onboardingStep4?.positionFunc) {
                    window.removeEventListener('resize', window._onboardingStep4.positionFunc);
                    const scrollContainer = document.querySelector('main') || document.querySelector('.overflow-y-auto');
                    if (scrollContainer) {
                        scrollContainer.removeEventListener('scroll', window._onboardingStep4.positionFunc);
                    }
                }

                // ✅ UPDATE KE STEP BERIKUTNYA
                fetch('{{ route('update-onboarding-step') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            step: 'workspace-created'
                        })
                    })
                    .then(() => {
                        document.getElementById('onboarding-step3')?.classList.add('hidden');

                        const createBtn = document.querySelector('button[\\@click*="showModal = true"]');
                        if (createBtn) {
                            console.log('🚀 Opening modal...');
                            createBtn.click();
                        }

                        delete window._onboardingStep4;
                    });
            }
            const style = document.createElement('style');
            style.textContent = `
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.02); opacity: 0.9; }
    }
`;
            document.head.appendChild(style);
            // ========================================
            // 🎯 STEP 5: WORKSPACE CREATED MODAL
            // ========================================
            function showOnboardingStep5Modal(workspaceName) {
                const modal = document.getElementById('onboarding-step5-modal');
                const content = document.getElementById('modal-content-step5');
                const nameDisplay = document.getElementById('workspace-name-display');
                const progressBar = document.getElementById('progress-bar-step5');

                if (!modal || !content) {
                    console.error('❌ Modal Step 5 tidak ditemukan');
                    return;
                }

                console.log('✅ Showing Step 5 modal for:', workspaceName);

                // Set workspace name
                if (nameDisplay) {
                    nameDisplay.textContent = workspaceName;
                }

                // Show modal
                modal.classList.remove('hidden');

                // Animate entrance
                setTimeout(() => {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }, 100);

                // Animate progress bar
                setTimeout(() => {
                    if (progressBar) {
                        progressBar.style.width = '100%';
                    }
                }, 600);

                // Auto-trigger confetti
                triggerConfetti();
            }

            function completeOnboardingStep5() {
                const modal = document.getElementById('onboarding-step5-modal');
                const content = document.getElementById('modal-content-step5');

                console.log('✅ Completing onboarding...');

                // Hide with animation
                if (content) {
                    content.classList.add('scale-95', 'opacity-0');
                    content.classList.remove('scale-100', 'opacity-100');
                }

                setTimeout(() => {
                    if (modal) {
                        modal.classList.add('hidden');
                    }

                    // Complete onboarding
                    fetch('{{ route('complete-onboarding') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('✅ Onboarding completed:', data);

                            // Show success toast if Swal is available
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Selamat! Anda siap mulai bekerja 🎉',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }

                            // Reload page to refresh workspace list
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        })
                        .catch(err => {
                            console.error('❌ Error completing onboarding:', err);
                            location.reload(); // Reload anyway
                        });
                }, 300);
            }

            function triggerConfetti() {
                const confettiElements = document.querySelectorAll('.confetti');
                confettiElements.forEach((el, index) => {
                    setTimeout(() => {
                        el.style.animation = `confetti-fall 3s ease-out forwards`;
                        el.style.animationDelay = `${index * 0.2}s`;
                    }, 500);
                });
            }
        </script>
    @endpush
    <style>
        /* Confetti Animation */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4, #45B7D1, #FFA07A, #98D8C8);
            top: -20px;
            opacity: 0;
        }

        .confetti:nth-child(1) {
            left: 10%;
        }

        .confetti:nth-child(2) {
            left: 30%;
        }

        .confetti:nth-child(3) {
            left: 50%;
        }

        .confetti:nth-child(4) {
            left: 70%;
        }

        .confetti:nth-child(5) {
            left: 90%;
        }

        @keyframes confetti-fall {
            0% {
                top: -20px;
                opacity: 1;
                transform: rotate(0deg);
            }

            100% {
                top: 100%;
                opacity: 0;
                transform: rotate(720deg);
            }
        }

        /* Smooth transitions */
        #modal-content-step5 {
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>
@endsection
