@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    @php
        function getShortName($fullName)
        {
            if (!$fullName) {
                return 'User';
            }
            $words = explode(' ', trim($fullName));
            return implode(' ', array_slice($words, 0, 2));
        }

        // ✅ GUNAKAN VARIABEL DARI CONTROLLER
        $canCreateWorkspace = in_array($userRole ?? 'Member', ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
        $canEditDeleteWorkspace = $canCreateWorkspace;
    @endphp

    <div class="min-h-screen bg-[#E9EFFD]" x-data="dashboardWorkspace">

        <div class="max-w-7xl mx-auto px-8 py-8">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">
                        Selamat Datang, {{ getShortName(Auth::user()->full_name) }} 👋
                    </h1>
                    <p class="text-slate-500 mt-2">Kelola seluruh proyek Anda dalam satu dashboard.</p>
                </div>

                <div class="flex gap-3">

                    <a href="{{ url('/tambah-anggota') }}" role="button" aria-label="Tambah Anggota Baru"
                        id="tambah-anggota-btn"
                        class="inline-flex items-center justify-center gap-2 text-sm sm:text-base bg-gradient-to-r from-[#225AD6] to-[#1e40af] hover:from-[#1e40af] hover:to-[#225AD6] text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#225AD6] focus:ring-offset-2 whitespace-nowrap">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                        <span class="hidden xs:inline">Tambah Anggota</span>
                        <span class="xs:hidden">Tambah Anggota</span>
                    </a>

                    {{-- <a href="{{ route('kelola-workspace') }}"
                        class="h-11 px-5 rounded-xl bg-white shadow hover:shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-building text-blue-600"></i>
                        Workspace
                    </a>

                    @if ($canCreateWorkspace)
                        <button @click="openCreateModal('Proyek')"
                            class="h-11 px-5 rounded-xl bg-[#2B6EF3] text-white shadow hover:bg-blue-700 transition flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            New Project
                        </button>
                    @endif --}}
                </div>
            </div>

            {{-- Grid Project versi 3 card --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-7">


                {{-- Grid Project versi 5 card --}}

                {{-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5"> --}}

                @forelse ($workspaces ?? [] as $workspace)
    <a href="{{ route('kanban-tugas', $workspace->id) }}"
        class="group bg-white rounded-2xl shadow-sm hover:shadow-lg transition p-6 flex flex-col relative">

                        <div class="flex justify-between items-start mb-6">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $workspace->type === 'Tim' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-700' }}">
                                {{ $workspace->type }}
                            </span>

                            <button type="button"
                                @click.stop.prevent="openWorkspaceMenu($event, {{ Illuminate\Support\Js::from($workspace->toArray()) }})"
                                class="text-slate-400 hover:text-blue-600">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>
                        </div>

                        <h3 class="text-xl font-bold text-slate-800">{{ $workspace->name }}</h3>
                        <p class="text-slate-500 mt-1">{{ $workspace->description ?? 'Tidak ada deskripsi' }}</p>

                        <div class="flex justify-between items-center mt-7">
                            <div class="flex -space-x-3">
                                @foreach ($workspace->userWorkspaces->take(4) as $uw)
                                    @php
                                        $member = $uw->user ?? null;
                                        $avatar = $member
                                            ? ($member->avatar
                                                ? (Str::startsWith($member->avatar, ['http://', 'https://'])
                                                    ? $member->avatar
                                                    : asset('storage/' . $member->avatar))
                                                : 'https://ui-avatars.com/api/?name=' .
                                                    urlencode($member->full_name ?? 'User') .
                                                    '&background=4F46E5&color=fff&bold=true')
                                            : 'https://ui-avatars.com/api/?name=User&background=4F46E5&color=fff&bold=true';
                                    @endphp
                                    <img src="{{ $avatar }}" title="{{ $member->full_name ?? 'User' }}"
                                        class="w-10 h-10 rounded-full border-2 border-white object-cover">
                                @endforeach

                                @if ($workspace->userWorkspaces->count() > 4)
                                    <div
                                        class="w-10 h-10 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center">
                                        <span
                                            class="text-xs text-slate-600">+{{ $workspace->userWorkspaces->count() - 4 }}</span>
                                    </div>
                                @endif
                            </div>

                            <div
                                class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:scale-110 transition">
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    {{-- Empty state --}}
                @endforelse

                {{-- New Project Card --}}
                @if ($canCreateWorkspace)
                    <button @click="openCreateModal('Proyek')" class="dashboard-add-card">
                        <div>
                            <div class="dashboard-add-icon">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <h3>Mulai Project Baru</h3>
                            <p>Buat workspace atau project baru untuk tim Anda.</p>
                        </div>
                    </button>
                @endif
            </div>
        </div>

        {{-- Floating Button --}}
        {{-- @if ($canCreateWorkspace)
            <button @click="openCreateModal('Proyek')"
                class="fixed bottom-8 right-8 w-16 h-16 rounded-full bg-[#2B6EF3] text-white shadow-xl hover:scale-110 transition z-40">
                <i class="fa-solid fa-plus text-xl"></i>
            </button>
        @endif --}}

        {{-- ✅ MODAL CREATE WORKSPACE --}}
        <div x-show="showCreateModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.away="showCreateModal = false">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
                <form @submit.prevent="createWorkspace">
                    <div class="p-6">
                        <h2 class="text-center text-xl font-semibold text-gray-900 mb-4">Buat Workspace</h2>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Workspace</label>
                            <input type="text" x-model="workspaceData.name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan nama workspace...">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea x-model="workspaceData.description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan deskripsi workspace..."></textarea>
                        </div>

                        <div class="mb-6">
                            <p class="block text-sm font-medium text-gray-700 mb-3">Untuk apakah workspace ini?</p>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="workspace-type-dashboard" value="Tim"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        x-model="workspaceData.type">
                                    <span class="ml-2 text-gray-700">Tim</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="workspace-type-dashboard" value="Proyek"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        x-model="workspaceData.type">
                                    <span class="ml-2 text-gray-700">Proyek</span>
                                </label>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4 flex justify-end">
                            <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 mr-3">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                :disabled="isSubmitting">
                                <span x-show="!isSubmitting">Buat</span>
                                <span x-show="isSubmitting">Membuat...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ✅ MODAL MENU WORKSPACE --}}
        <div x-show="showMenuModal" x-cloak class="fixed inset-0 z-50" @click="showMenuModal = false">
            <div class="fixed bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-64"
                :style="`top: ${menuPosition.y}px; left: ${menuPosition.x}px`" @click.stop>

                <template x-if="canManageMembers(activeWorkspace)">
                    <button
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                        @click="showMenuModal = false; openManageMembers(activeWorkspace)">
                        <i class="fa-solid fa-users text-gray-500 w-4"></i>
                        Kelola Anggota
                    </button>
                </template>

                <button class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    @click="showMenuModal = false;
                    window.openAccessModal && window.openAccessModal({
                    type: 'workspace',
                    workspaceId: activeWorkspace?.id,
                    workspaceName: activeWorkspace?.name
                    });">
                    <i class="fa-solid fa-gear text-gray-500 w-4"></i>
                    Atur Hak Akses
                </button>

                <template x-if="canEditDelete">
                    <button
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                        @click="openEditWorkspace(activeWorkspace)">
                        <i class="fa-solid fa-pen-to-square text-gray-500 w-4"></i>
                        Edit Ruang Kerja
                    </button>
                </template>

                <div class="border-t border-gray-200 my-1"></div>

                <template x-if="canEditDelete">
                    <button class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-3"
                        @click="deleteWorkspace(activeWorkspace.id)">
                        <i class="fa-solid fa-trash w-4"></i>
                        Hapus Ruang Kerja
                    </button>
                </template>
            </div>
        </div>

        {{-- ✅ MODAL EDIT WORKSPACE --}}
        <div x-show="showEditModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.away="showEditModal = false">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Ubah Workspace</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Workspace</label>
                        <input type="text" x-model="editData.name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea x-model="editData.description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="mb-6">
                        <p class="block text-sm font-medium text-gray-700 mb-3">Pindahkan ke</p>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="radio" name="edit-type-dashboard" value="Tim" x-model="editData.type"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-2 text-gray-700">Tim</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="edit-type-dashboard" value="Proyek" x-model="editData.type"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-2 text-gray-700">Proyek</span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 flex justify-end">
                        <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 mr-3">
                            Batal
                        </button>
                        <button type="button" @click="saveEdit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ MODAL KELOLA ANGGOTA --}}
        <div x-show="showMembersModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.away="closeMembersModal">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md max-h-[90vh] flex flex-col">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Anggota</h2>
                </div>

                <div class="p-4 border-b border-gray-200">
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" x-model="searchMember" placeholder="Cari anggota..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4">
                    <div class="space-y-3">
                        <template x-for="member in filteredMembers" :key="member.id">
                            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <img :src="member.avatar" :alt="member.name" class="w-8 h-8 rounded-full">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="member.name"></p>
                                        <p class="text-xs text-gray-500" x-text="member.email"></p>
                                    </div>
                                </div>
                                <input type="checkbox" :checked="selectedMembers.includes(member.id)"
                                    @change="toggleMember(member.id)"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            </div>
                        </template>
                        <div x-show="filteredMembers.length === 0" class="text-center py-8">
                            <p class="text-gray-500 text-sm">Tidak ada anggota yang ditemukan</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeMembersModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="button" @click="applyMembers"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dashboardWorkspace', () => ({
                    // Modal states
                    showCreateModal: false,
                    showMenuModal: false,
                    showEditModal: false,
                    showMembersModal: false,
                    isSubmitting: false,

                    // Data
                    workspaceData: {
                        name: '',
                        description: '',
                        type: 'Proyek'
                    },
                    editData: {
                        id: '',
                        name: '',
                        description: '',
                        type: ''
                    },
                    activeWorkspace: null,
                    menuPosition: {
                        x: 0,
                        y: 0
                    },

                    // Members
                    searchMember: '',
                    selectedMembers: [],
                    availableMembers: [],
                    currentWorkspaceMembers: [],

                    // Permissions from PHP
                    canEditDelete: {{ $canEditDeleteWorkspace ? 'true' : 'false' }},
                    userRole: '{{ $userRole ?? 'Member' }}',

                    init() {
                        this.loadAvailableMembers();
                    },

                    // ✅ OPEN CREATE MODAL
                    openCreateModal(type = 'Proyek') {
                        this.workspaceData = {
                            name: '',
                            description: '',
                            type: type
                        };
                        this.showCreateModal = true;
                    },

                    // ✅ CREATE WORKSPACE
                    async createWorkspace() {
                        this.isSubmitting = true;
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')
                                ?.content || '';

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
                                this.showCreateModal = false;
                                window.location.reload();
                            } else {
                                alert(result.message || 'Gagal membuat workspace');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat membuat workspace');
                        } finally {
                            this.isSubmitting = false;
                        }
                    },

                    // ✅ OPEN WORKSPACE MENU
                    openWorkspaceMenu(event, workspace) {
                        this.activeWorkspace = workspace;
                        this.menuPosition = {
                            x: event.clientX - 256,
                            y: event.clientY + 10
                        };
                        this.showMenuModal = true;
                    },

                    // ✅ CHECK PERMISSIONS
                    canManageMembers(workspace) {
                        if (!workspace) return false;
                        const adminRoles = ['SuperAdmin', 'Administrator', 'Admin', 'Manager'];
                        return adminRoles.includes(this.userRole);
                    },

                    // ✅ OPEN EDIT WORKSPACE
                    openEditWorkspace(workspace) {
                        if (!this.canEditDelete) {
                            alert('Anda tidak memiliki izin untuk mengedit workspace.');
                            return;
                        }
                        this.editData = {
                            id: workspace.id,
                            name: workspace.name,
                            description: workspace.description || '',
                            type: workspace.type
                        };
                        this.showMenuModal = false;
                        this.showEditModal = true;
                    },

                    // ✅ SAVE EDIT
                    async saveEdit() {
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')
                                ?.content || '';

                            const response = await fetch(`/workspace/${this.editData.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(this.editData)
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.showEditModal = false;
                                window.location.reload();
                            } else {
                                alert(result.message || 'Gagal mengupdate workspace');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mengupdate workspace');
                        }
                    },

                    // ✅ DELETE WORKSPACE
                    async deleteWorkspace(workspaceId) {
                        if (!this.canEditDelete) {
                            alert('Anda tidak memiliki izin untuk menghapus workspace.');
                            this.showMenuModal = false;
                            return;
                        }

                        if (!confirm('Hapus workspace ini? Tindakan ini permanen.')) {
                            this.showMenuModal = false;
                            return;
                        }

                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')
                                ?.content || '';

                            const response = await fetch(`/workspace/${workspaceId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.showMenuModal = false;
                                window.location.reload();
                            } else {
                                alert(result.message || 'Gagal menghapus workspace');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat menghapus workspace');
                        }
                    },

                    // ✅ LOAD AVAILABLE MEMBERS
                    async loadAvailableMembers() {
                        try {
                            const response = await fetch('/workspace-available-users', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (response.ok) {
                                this.availableMembers = await response.json();
                            }
                        } catch (error) {
                            console.error('Error loading members:', error);
                        }
                    },

                    // ✅ LOAD WORKSPACE MEMBERS
                    async loadWorkspaceMembers(workspaceId) {
                        try {
                            const response = await fetch(`/workspace/${workspaceId}/members`);
                            if (response.ok) {
                                const members = await response.json();
                                this.selectedMembers = members.map(m => m.id);
                                this.currentWorkspaceMembers = members;
                            }
                        } catch (error) {
                            console.error('Error loading workspace members:', error);
                        }
                    },

                    // ✅ OPEN MANAGE MEMBERS
                    async openManageMembers(workspace) {
                        this.activeWorkspace = workspace;
                        this.showMembersModal = true;
                        this.searchMember = '';
                        await this.loadWorkspaceMembers(workspace.id);
                    },

                    // ✅ CLOSE MEMBERS MODAL
                    closeMembersModal() {
                        this.showMembersModal = false;
                        this.selectedMembers = [];
                        this.searchMember = '';
                    },

                    // ✅ TOGGLE MEMBER
                    toggleMember(memberId) {
                        const index = this.selectedMembers.indexOf(memberId);
                        if (index === -1) {
                            this.selectedMembers.push(memberId);
                        } else {
                            this.selectedMembers.splice(index, 1);
                        }
                    },

                    // ✅ FILTERED MEMBERS
                    get filteredMembers() {
                        if (!this.searchMember) return this.availableMembers;
                        const term = this.searchMember.toLowerCase();
                        return this.availableMembers.filter(m =>
                            m.name.toLowerCase().includes(term) ||
                            m.email.toLowerCase().includes(term)
                        );
                    },

                    // ✅ APPLY MEMBERS
                    async applyMembers() {
                        if (!this.activeWorkspace) return;

                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')
                                ?.content || '';

                            const response = await fetch(
                                `/workspace/${this.activeWorkspace.id}/members`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({
                                        user_ids: this.selectedMembers,
                                        role_id: '{{ \App\Models\Role::where('name', 'Member')->first()->id ?? '' }}'
                                    })
                                });

                            const result = await response.json();

                            if (result.success) {
                                this.showMembersModal = false;
                                this.selectedMembers = [];
                                this.searchMember = '';
                                window.location.reload();
                            } else {
                                alert(result.message || 'Gagal menyimpan anggota');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat menyimpan anggota');
                        }
                    }
                }));
            });
        </script>
    @endpush

    @vite(['resources/css/dashboard.css'])
@endsection
