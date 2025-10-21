bantu saya terapkandi seluruh bagian workspace(hq,tim, proyek) ini ke kelola-workspace.blade saya tanpa merusah frontend yang sudah ada

<?php
<!-- Inside HQ Section -->
<div x-show="hqOpen" x-collapse>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($workspaces['HQ'] ?? [] as $workspace)
        <div class="bg-white rounded-xl border border-gray-200 p-4 relative group">
            <div class="flex justify-between items-start">
                <h3 class="font-semibold text-gray-800">{{ $workspace->name }}</h3>
                <button @click="openWorkspaceMenu($event, {{ json_encode($workspace) }})"
                    class="p-1 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                </button>
            </div>
            <p class="text-sm text-gray-600 mt-2">{{ $workspace->description ?? 'Tidak ada deskripsi' }}</p>
            <div class="mt-4">
                <div class="flex -space-x-2">
                    @foreach($workspace->userWorkspaces->take(4) as $userWorkspace)
                    <img src="https://i.pravatar.cc/32?img={{ $loop->index + 1 }}"
                        class="w-8 h-8 rounded-full border-2 border-white" 
                        title="{{ $userWorkspace->user->full_name }}">
                    @endforeach
                    @if($workspace->userWorkspaces->count() > 4)
                    <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center">
                        <span class="text-xs text-gray-600">+{{ $workspace->userWorkspaces->count() - 4 }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-4 text-center py-8 text-gray-500">
            Belum ada workspace HQ
        </div>
        @endforelse
    </div>
</div>

<!-- Inside Tim Section -->
<div x-show="timOpen" x-collapse>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($workspaces['Tim'] ?? [] as $workspace)
        <!-- Same card structure as HQ section -->
        @empty
        <div class="col-span-4 text-center py-8 text-gray-500">
            Belum ada workspace Tim
        </div>
        @endforelse
    </div>
</div>

<!-- Inside Proyek Section -->
<div x-show="proyekOpen" x-collapse>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($workspaces['Proyek'] ?? [] as $workspace)
        <!-- Same card structure as HQ section -->
        @empty
        <div class="col-span-4 text-center py-8 text-gray-500">
            Belum ada workspace Proyek
        </div>
        @endforelse
    </div>
</div>

kelola-workpsace.blade:
@extends('layouts.app')

@section('title', 'Kelola Workspace')

@section('content')
    <div class="p-6" x-data="workspaceManager">

        <!-- Modal untuk Buat Workspace -->
        <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
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
                                <label class="flex items-center">
                                    <input type="radio" name="workspace-type" value="HQ"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        x-model="workspaceData.type">
                                    <span class="ml-2 text-gray-700">HQ</span>
                                </label>

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

        <!-- Modal Menu Workspace (titik tiga) -->
        <div x-show="showWorkspaceMenu" class="fixed inset-0 z-50" @click="showWorkspaceMenu = false">
            <div class="fixed bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-64"
                :style="`top: ${workspaceMenuPosition.y}px; left: ${workspaceMenuPosition.x}px`" @click.stop>
                <button class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    @click="showManageMembersModal = true; showWorkspaceMenu = false">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    Kelola Anggota
                </button>

                <button class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    @click="showAccessRightsModal = true; showWorkspaceMenu = false">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Atur Hak Akses
                </button>

                <button class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    @click="openEditWorkspace(activeWorkspace, 'Jualan makanan sehat yang membuat seseorang menjadi lebih berotot', 'HQ')">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Ruang Kerja
                </button>

                <div class="border-t border-gray-200 my-1"></div>

                <button class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Ruang Kerja
                </button>
            </div>
        </div>

        <!-- Modal Kelola Anggota -->
        <div x-show="showManageMembersModal"
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
                            @click="applyMembers" :disabled="selectedMembers.length === 0"
                            :class="selectedMembers.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Ruang Kerja -->
        <div x-show="showEditWorkspaceModal"
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
                            <label class="flex items-center">
                                <input type="radio" name="edit-workspace-type" value="HQ"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    x-model="editWorkspaceData.type">
                                <span class="ml-2 text-gray-700">HQ</span>
                            </label>

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

        <!-- Modal Atur Hak Akses -->
        <div x-show="showAccessRightsModal"
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
        <div x-show="showRoleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
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
                    <!-- User 1 - Super Admin (tanpa dropdown) -->
                    <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-sm">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                                <img src="https://i.pravatar.cc/150?img=5" alt="Member" class="w-12 h-12 rounded-full">

                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Muhammad Dzakwan</p>
                                <span
                                    class="inline-block px-3 py-0.5 text-xs font-medium text-white bg-blue-600 rounded-md mt-1">Super
                                    Admin</span>
                            </div>
                        </div>
                        <button
                            class="px-4 py-1.5 text-sm font-medium text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-blue-50">
                            Super Admin
                        </button>
                    </div>

                    <!-- User 2 - Manager (dengan dropdown) -->
                    <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-sm">
                        <div class="flex items-center gap-3">
                            <img src="https://i.pravatar.cc/150?img=11" alt="Manager" class="w-12 h-12 rounded-full">
                            <div>
                                <p class="font-semibold text-gray-900">Sarah Johnson</p>
                                <span
                                    class="inline-block px-3 py-0.5 text-xs font-medium text-white bg-teal-500 rounded-md mt-1">Manager</span>
                            </div>
                        </div>
                        <div class="relative">
                            <select
                                class="appearance-none bg-white border border-blue-600 text-blue-600 rounded-lg px-4 py-1.5 pr-10 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <option value="admin" selected>Admin</option>
                                <option value="manager">Manager</option>
                                <option value="member">Member</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- User 3 - Admin (dengan dropdown) -->
                    <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-sm">
                        <div class="flex items-center gap-3">
                            <img src="https://i.pravatar.cc/150?img=32" alt="Admin" class="w-12 h-12 rounded-full">
                            <div>
                                <p class="font-semibold text-gray-900">David Chen</p>
                                <span
                                    class="inline-block px-3 py-0.5 text-xs font-medium text-white bg-blue-500 rounded-md mt-1">Admin</span>
                            </div>
                        </div>
                        <div class="relative">
                            <select
                                class="appearance-none bg-white border border-blue-600 text-blue-600 rounded-lg px-4 py-1.5 pr-10 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <option value="admin">Admin</option>
                                <option value="manager" selected>Manager</option>
                                <option value="member">Member</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- User 4 - Member (dengan dropdown) -->
                    <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-sm">
                        <div class="flex items-center gap-3">
                            <img src="https://i.pravatar.cc/150?img=5" alt="Member" class="w-12 h-12 rounded-full">
                            <div>
                                <p class="font-semibold text-gray-900">Maria Garcia</p>
                                <span
                                    class="inline-block px-3 py-0.5 text-xs font-medium text-white bg-yellow-500 rounded-md mt-1">Member</span>
                            </div>
                        </div>
                        <div class="relative">
                            <select
                                class="appearance-none bg-white border border-blue-600 text-blue-600 rounded-lg px-4 py-1.5 pr-10 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="member" selected>Member</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
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

        <!-- HQ Section -->
        <div class="mt-2 mx-8 mb-8">
            <div class="flex items-center justify-between mb-4">
                <button @click="hqOpen = !hqOpen"
                    class="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition">
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': !hqOpen }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <span class="font-medium">HQ</span>
                </button>
                <button
                    class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition shadow-lg"
                    @click="showModal = true; workspaceType = 'HQ'">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>

            <div x-show="hqOpen" x-collapse>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Card Inti (with 3-dots menu) -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">Inti</h3>
                            <button class="text-gray-400 hover:text-gray-600 relative"
                                @click="showWorkspaceMenu = true;
                                    activeWorkspace = 'Inti';
                                    workspaceMenuPosition = { x: $event.clientX - 256, y: $event.clientY + 10 }">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="5" cy="12" r="2"></circle>
                                    <circle cx="12" cy="12" r="2"></circle>
                                    <circle cx="19" cy="12" r="2"></circle>
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2">Jualan makanan sehat yang membuat seseorang
                            menjadi lebih berotot</p>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="text-xs text-gray-500">Anggota</span>
                        </div>
                        <div class="flex items-center gap-1 mt-2">
                            <div class="flex -space-x-2">
                                <img src="https://i.pravatar.cc/32?img=11" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <img src="https://i.pravatar.cc/32?img=12" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <img src="https://i.pravatar.cc/32?img=13" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <div
                                    class="w-7 h-7 rounded-full border-2 border-white bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs font-medium text-blue-600">2+</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Div. IT -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">Div. IT</h3>
                            <button class="text-gray-400 hover:text-gray-600 relative"
                                @click="showWorkspaceMenu = true;
                                    activeWorkspace = 'Div. IT';
                                    workspaceMenuPosition = { x: $event.clientX - 256, y: $event.clientY + 10 }">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="5" cy="12" r="2"></circle>
                                    <circle cx="12" cy="12" r="2"></circle>
                                    <circle cx="19" cy="12" r="2"></circle>
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2">Jualan makanan sehat yang membuat seseorang
                            menjadi lebih berotot</p>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="text-xs text-gray-500">Anggota</span>
                        </div>
                        <div class="flex items-center gap-1 mt-2">
                            <div class="flex -space-x-2">
                                <img src="https://i.pravatar.cc/32?img=14" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <img src="https://i.pravatar.cc/32?img=15" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <img src="https://i.pravatar.cc/32?img=16" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <div
                                    class="w-7 h-7 rounded-full border-2 border-white bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs font-medium text-blue-600">2+</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Div. Marketing -->
                    <a href="{{ url('/workspace') }}" class="block no-underline">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-semibold text-gray-900">Div. Marketing</h3>
                                <button class="text-gray-400 hover:text-gray-600 relative"
                                    @click.stop="showWorkspaceMenu = true;
                    activeWorkspace = 'Div. Marketing';
                    workspaceMenuPosition = { x: $event.clientX - 256, y: $event.clientY + 10 }">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="5" cy="12" r="2"></circle>
                                        <circle cx="12" cy="12" r="2"></circle>
                                        <circle cx="19" cy="12" r="2"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">Jualan makanan sehat yang membuat seseorang
                                menjadi lebih berotot</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <span class="text-xs text-gray-500">Anggota</span>
                            </div>
                            <div class="flex items-center gap-1 mt-2">
                                <div class="flex -space-x-2">
                                    <img src="https://i.pravatar.cc/32?img=17" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <img src="https://i.pravatar.cc/32?img=18" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <img src="https://i.pravatar.cc/32?img=19" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <div
                                        class="w-7 h-7 rounded-full border-2 border-white bg-blue-100 flex items-center justify-center">
                                        <span class="text-xs font-medium text-blue-600">2+</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

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
                <button
                    class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition shadow-lg"
                    @click="showModal = true; workspaceType = 'Tim'">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>

            <div x-show="timOpen" x-collapse>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-semibold text-gray-900">Div. Marketing</h3>
                                <button class="text-gray-400 hover:text-gray-600 relative"
                                    @click="showWorkspaceMenu = true;
                                        activeWorkspace = 'Div. Marketing Tim ' + ({{ $i }} + 1);
                                        workspaceMenuPosition = { x: $event.clientX - 256, y: $event.clientY + 10 }">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="5" cy="12" r="2"></circle>
                                        <circle cx="12" cy="12" r="2"></circle>
                                        <circle cx="19" cy="12" r="2"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">Jualan makanan sehat yang membuat seseorang
                                menjadi lebih berotot</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <span class="text-xs text-gray-500">Anggota</span>
                            </div>
                            <div class="flex items-center gap-1 mt-2">
                                <div class="flex -space-x-2">
                                    <img src="https://i.pravatar.cc/32?img={{ 23 + $i }}" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <img src="https://i.pravatar.cc/32?img={{ 24 + $i }}" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <img src="https://i.pravatar.cc/32?img={{ 25 + $i }}" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <div
                                        class="w-7 h-7 rounded-full border-2 border-white bg-blue-100 flex items-center justify-center">
                                        <span class="text-xs font-medium text-blue-600">2+</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
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
                <button
                    class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition shadow-lg"
                    @click="showModal = true; workspaceType = 'Proyek'">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>

            <div x-show="proyekOpen" x-collapse>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Proyek Kerjasama Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">Proyek Kerjasama</h3>

                            <button class="text-gray-400 hover:text-gray-600 relative"
                                @click="showWorkspaceMenu = true;
                                    activeWorkspace = 'Proyek Kerjasama';
                                    workspaceMenuPosition = { x: $event.clientX - 256, y: $event.clientY + 10 }">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="5" cy="12" r="2"></circle>
                                    <circle cx="12" cy="12" r="2"></circle>
                                    <circle cx="19" cy="12" r="2"></circle>
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2">Jualan makanan sehat yang membuat seseorang
                            menjadi lebih berotot</p>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="text-xs text-gray-500">Anggota</span>
                        </div>
                        <div class="flex items-center gap-1 mt-2">
                            <div class="flex -space-x-2">
                                <img src="https://i.pravatar.cc/32?img=30" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <img src="https://i.pravatar.cc/32?img=31" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <img src="https://i.pravatar.cc/32?img=32" alt="Member"
                                    class="w-7 h-7 rounded-full border-2 border-white">
                                <div
                                    class="w-7 h-7 rounded-full border-2 border-white bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs font-medium text-blue-600">2+</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @for ($i = 0; $i < 3; $i++)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-semibold text-gray-900">Proyek Digital Office</h3>
                                <button class="text-gray-400 hover:text-gray-600 relative"
                                    @click="showWorkspaceMenu = true;
                                        activeWorkspace = 'Proyek Digital Office ' + ({{ $i }} + 1);
                                        workspaceMenuPosition = { x: $event.clientX - 256, y: $event.clientY + 10 }">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="5" cy="12" r="2"></circle>
                                        <circle cx="12" cy="12" r="2"></circle>
                                        <circle cx="19" cy="12" r="2"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">Jualan makanan sehat yang membuat seseorang
                                menjadi lebih berotot</p>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <span class="text-xs text-gray-500">Anggota</span>
                            </div>
                            <div class="flex items-center gap-1 mt-2">
                                <div class="flex -space-x-2">
                                    <img src="https://i.pravatar.cc/32?img={{ 33 + $i }}" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <img src="https://i.pravatar.cc/32?img={{ 34 + $i }}" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <img src="https://i.pravatar.cc/32?img={{ 35 + $i }}" alt="Member"
                                        class="w-7 h-7 rounded-full border-2 border-white">
                                    <div
                                        class="w-7 h-7 rounded-full border-2 border-white bg-blue-100 flex items-center justify-center">
                                        <span class="text-xs font-medium text-blue-600">2+</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
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
        activeWorkspace: null,
        workspaceMenuPosition: { x: 0, y: 0 },
        showManageMembersModal: false,
        showEditWorkspaceModal: false,
        isSubmitting: false,

        // Workspace data
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

        // Methods untuk workspace
        async createWorkspace() {
            this.isSubmitting = true;
            
            try {
                // Dapatkan CSRF token dengan cara yang lebih aman
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

                if (result.success) {
                    this.showModal = false;
                    this.workspaceData = { name: '', description: '', type: 'HQ' };
                    location.reload();
                } else {
                    alert('Gagal membuat workspace: ' + result.message);
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
                
                const response = await fetch(`/workspace/${this.editWorkspaceData.id}`, {
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
            if (!confirm('Apakah Anda yakin ingin menghapus workspace ini?')) {
                return;
            }

            try {
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
                    alert('Gagal menghapus workspace: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus workspace');
            }
        },

        // Methods untuk members
        async loadAvailableMembers() {
    try {
        const response = await fetch('/workspace-available-users', { // GUNAKAN URL BARU
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            this.availableMembers = await response.json();
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
                const response = await fetch(`/workspace/${workspaceId}/members`);
                if (response.ok) {
                    this.currentWorkspaceMembers = await response.json();
                    this.selectedMembers = this.currentWorkspaceMembers.map(member => member.id);
                }
            } catch (error) {
                console.error('Error loading workspace members:', error);
            }
        },

        async saveMembers(workspaceId) {
            try {
                const csrfToken = this.getCsrfToken();
                
                const response = await fetch(`/workspace/${workspaceId}/members`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        user_ids: this.selectedMembers,
                        role_id: this.getDefaultRoleId()
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.showManageMembersModal = false;
                    this.selectedMembers = [];
                    this.searchMember = '';
                    alert('Anggota berhasil disimpan!');
                } else {
                    alert('Gagal menyimpan anggota: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan anggota');
            }
        },

        // Helper method untuk mendapatkan CSRF token dengan aman
        getCsrfToken() {
            // Coba dari meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.getAttribute('content');
            }
            
            // Fallback: coba dari input hidden
            const inputTag = document.querySelector('input[name="_token"]');
            if (inputTag) {
                return inputTag.value;
            }
            
            // Fallback terakhir
            return document.querySelector('script[data-csrf]')?.dataset.csrf || '';
        },

        getDefaultRoleId() {
            // Return default role ID (Member)
            return '{{ \App\Models\Role::where("name", "Member")->first()->id ?? "" }}';
        },

        // Helper methods
        openEditWorkspace(workspace) {
            this.editWorkspaceData = {
                id: workspace.id,
                name: workspace.name,
                description: workspace.description || '',
                type: workspace.type
            };
            this.showEditWorkspaceModal = true;
            this.showWorkspaceMenu = false;
        },

        openManageMembers(workspace) {
            this.activeWorkspace = workspace;
            this.loadWorkspaceMembers(workspace.id);
            this.showManageMembersModal = true;
            this.showWorkspaceMenu = false;
        },

        toggleMember(memberId) {
            const index = this.selectedMembers.indexOf(memberId);
            if (index === -1) {
                this.selectedMembers.push(memberId);
            } else {
                this.selectedMembers.splice(index, 1);
            }
        },

        isMemberSelected(memberId) {
            return this.selectedMembers.includes(memberId);
        },

        get filteredMembers() {
            if (!this.searchMember) {
                return this.availableMembers;
            }
            const searchTerm = this.searchMember.toLowerCase();
            return this.availableMembers.filter(member =>
                member.name.toLowerCase().includes(searchTerm) ||
                member.email.toLowerCase().includes(searchTerm)
            );
        }
    }));
});
</script>
@endsection



workspacecontrolller:

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // <- TAMBAH INI
use Illuminate\Support\Str;
use App\Models\Workspace;
use App\Models\UserWorkspace;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;

class WorkspaceController extends Controller
{
    // Menampilkan halaman kelola workspace

public function index()
{
    $user = Auth::user();
    $activeCompany = session('active_company_id')
        ? Company::find(session('active_company_id'))
        : $user->companies->first();

    if (!$activeCompany) {
        return redirect()->route('buat-perusahaan.create')
            ->with('error', 'Silakan buat perusahaan terlebih dahulu.');
    }

    // Get workspaces grouped by type
    $workspaces = Workspace::with(['creator', 'userWorkspaces.user'])
        ->where('company_id', $activeCompany->id)
        ->active()
        ->get()
        ->groupBy('type');

    return view('kelola-workspace', [
        'workspaces' => $workspaces,
        'activeCompany' => $activeCompany
    ]);
}

    // Menyimpan workspace baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:HQ,Tim,Proyek',
            'description' => 'nullable|string'
        ]);

        $activeCompanyId = session('active_company_id');
        if (!$activeCompanyId) {
            return response()->json(['error' => 'Tidak ada perusahaan yang aktif'], 400);
        }

        try {
            DB::beginTransaction();

            // Buat workspace
            $workspace = Workspace::create([
                'company_id' => $activeCompanyId,
                'type' => $request->type,
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::id()
            ]);

            // Cari role SuperAdmin, jika tidak ada gunakan role pertama atau buat baru
            $superAdminRole = Role::where('name', 'SuperAdmin')->first();

            if (!$superAdminRole) {
                // Jika SuperAdmin tidak ada, cari role apapun yang tersedia
                $superAdminRole = Role::first();

                // Jika masih tidak ada role, buat role default
                if (!$superAdminRole) {
                    $superAdminRole = Role::create([
                        'id' => Str::uuid()->toString(),
                        'name' => 'SuperAdmin'
                    ]);
                }
            }

            // Tambahkan creator sebagai anggota workspace
            UserWorkspace::create([
                'user_id' => Auth::id(),
                'workspace_id' => $workspace->id,
                'roles_id' => $superAdminRole->id,
                'status_active' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workspace berhasil dibuat!',
                'workspace' => $workspace
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Workspace creation error: ' . $e->getMessage()); // <- Ganti \Log menjadi Log
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat workspace: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update workspace
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:HQ,Tim,Proyek',
            'description' => 'nullable|string'
        ]);

        $workspace = Workspace::findOrFail($id);

        // Cek apakah user memiliki akses ke workspace ini
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        $workspace->update([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workspace berhasil diperbarui!',
            'workspace' => $workspace
        ]);
    }

    // Hapus workspace
    public function destroy($id)
    {
        $workspace = Workspace::findOrFail($id);

        // Cek apakah user memiliki akses ke workspace ini
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        $workspace->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workspace berhasil dihapus!'
        ]);
    }

    // Kelola anggota workspace
    public function manageMembers(Request $request, $workspaceId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $workspace = Workspace::findOrFail($workspaceId);

        // Cek apakah user memiliki akses ke workspace ini
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        try {
            DB::beginTransaction();

            // Hapus anggota lama yang tidak dipilih
            UserWorkspace::where('workspace_id', $workspaceId)
                ->whereNotIn('user_id', $request->user_ids)
                ->delete();

            // Tambah/update anggota baru
            foreach ($request->user_ids as $userId) {
                UserWorkspace::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'workspace_id' => $workspaceId
                    ],
                    [
                        'roles_id' => $request->role_id,
                        'status_active' => true
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Anggota workspace berhasil diupdate!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manage members error: ' . $e->getMessage()); // <- Ganti \Log menjadi Log
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate anggota: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get anggota workspace
    public function getMembers($workspaceId)
    {
        $workspace = Workspace::with(['userWorkspaces.user', 'userWorkspaces.role'])->findOrFail($workspaceId);

        // Cek apakah user memiliki akses ke workspace ini
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        $members = $workspace->userWorkspaces->map(function ($userWorkspace) {
            return [
                'id' => $userWorkspace->user->id,
                'name' => $userWorkspace->user->full_name,
                'email' => $userWorkspace->user->email,
                'role' => $userWorkspace->role->name,
                'avatar' => 'https://i.pravatar.cc/32?img=' . (rand(1, 70))
            ];
        });

        return response()->json($members);
    }

    // Get users yang available untuk di-add ke workspace
    public function getAvailableUsers()
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            if (!$activeCompanyId) {
                return response()->json(['error' => 'No active company'], 400);
            }

            // Ambil users dari company yang aktif
            $companyUsers = User::whereHas('userCompanies', function ($query) use ($activeCompanyId) {
                $query->where('company_id', $activeCompanyId);
            })->get();

            $users = $companyUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'avatar' => 'https://i.pravatar.cc/32?img=' . (rand(1, 70))
                ];
            });

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableUsers: ' . $e->getMessage()); // <- Ganti \Log menjadi Log
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Cek akses user ke workspace
    private function checkWorkspaceAccess($workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // Cek apakah workspace milik company yang aktif
        if ($workspace->company_id !== $activeCompanyId) {
            return false;
        }

        // Cek apakah user adalah creator atau memiliki akses ke workspace
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->active()
            ->first();

        return $userWorkspace && in_array($userWorkspace->role->name, ['SuperAdmin', 'Admin']);
    }
}