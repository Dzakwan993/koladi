{{-- Breadcrumb dan Info File --}}
<div x-show="currentFile && !replyView.active && isLoadingPermission === false" class="mb-6 flex-shrink-0">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <button @click="goToRoot()" class="text-gray-500 hover:text-gray-700 transition">
            Dokumen
        </button>

        {{-- Breadcrumb folder --}}
        <template x-for="(crumb, index) in fileBreadcrumbs" :key="index">
            <div class="flex items-center gap-2">
                <span class="text-gray-400">›</span>
                <button @click="navigateToFolderFromFile(crumb)" class="text-gray-500 hover:text-gray-700 transition"
                    x-text="crumb.name"></button>
            </div>
        </template>

        {{-- Current file --}}
        <div class="flex items-center gap-2">
            <span class="text-gray-400">›</span>
            <span class="text-gray-700 font-medium" x-text="currentFile?.name"></span>
        </div>
    </div>

    {{-- Header File --}}
    <div x-show="currentFile && !replyView.active" class="bg-white border border-gray-200 rounded-lg p-4">
        {{-- Judul File dan Actions --}}
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <img :src="currentFile?.icon || ''" :alt="currentFile?.type || ''" class="w-8 h-8">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800" x-text="currentFile?.name || ''"></h2>
                    <p class="text-xs text-gray-500"
                        x-text="(currentFile?.type || '') + ' • ' + (currentFile?.size || '')"></p>
                </div>
            </div>

            <div class="flex items-center gap-1">
                {{-- Download Button (selalu tampil) --}}
                <button @click="downloadFile(currentFile)"
                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </button>

                {{-- ✅ Edit & Delete HANYA untuk workspace --}}
                <div x-show="memberListAllowed">
                    <button x-show="currentContext === 'workspace'" @click="openEditFile(currentFile)"
                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button @click="openDeleteFile(currentFile)"
                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Info Pembuat dan Diterima Oleh --}}
        <div class="flex items-center justify-between">
            {{-- Info Pembuat --}}
            <div class="flex items-center gap-2">
                <img :src="currentFile?.creatorAvatar || 'https://i.pravatar.cc/32?img=8'" alt="Profile"
                    class="w-6 h-6 rounded-full">
                <div>
                    <p class="text-xs font-medium text-gray-700" x-text="currentFile?.creator?.name || 'Admin'"></p>
                    <p class="text-xs text-gray-500" x-text="formatDate(currentFile?.createdAt)"></p>
                </div>
            </div>

            {{-- ✅ Diterima Oleh - Tampil di workspace (dengan/tanpa akses) --}}
            <div x-show="currentContext === 'workspace'" class="flex items-center gap-2">
                <div class="text-right">
                    <p class="text-xs font-medium text-gray-700">Diterima Oleh :</p>
                </div>

                {{-- Avatar Container - Clickable --}}
                <div @click="console.log('File clicked!'); openRecipientsModal(currentFile)"
                    class="flex items-center cursor-pointer hover:opacity-80 transition">

                    {{-- Show max 3 avatars --}}
                    <template x-for="(recipient, idx) in (currentFile?.recipients || []).slice(0, 3)"
                        :key="recipient.id">
                        <img :src="recipient.avatar" :alt="recipient.name"
                            class="w-6 h-6 rounded-full border-2 border-white object-cover pointer-events-none"
                            :style="{ marginLeft: idx === 0 ? '0' : '-8px', zIndex: 10 - idx }">
                    </template>

                    {{-- +X indicator --}}
                    <div x-show="(currentFile?.recipients || []).length > 3"
                        class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-[10px] font-semibold text-gray-700 pointer-events-none"
                        style="margin-left: -8px;">
                        <span x-text="`+${(currentFile?.recipients || []).length - 3}`"></span>
                    </div>
                </div>

                {{-- Tombol Tambah - HANYA jika memberListAllowed --}}
                <div x-show="memberListAllowed">
                    <button @click.stop="openAddMemberModal = true"
                        class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
