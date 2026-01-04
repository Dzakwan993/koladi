    {{-- Breadcrumb dan Info Folder --}}
    <div x-show="isLoadingPermission" class="p-4">
        <div class="animate-pulse space-y-3">
            <div class="h-4 bg-gray-300 rounded w-1/3"></div>
            <div class="h-3 bg-gray-300 rounded w-1/2"></div>
            <div class="h-3 bg-gray-300 rounded w-2/3"></div>
        </div>
    </div>

    <div x-show="currentFolder && isLoadingPermission === false" class="mb-4 sm:mb-6 flex-shrink-0">
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-1 sm:gap-2 text-xs sm:text-sm text-gray-500 mb-2 flex-wrap">
            <button @click="goToRoot()" class="text-gray-500 hover:text-gray-700 transition">
                Dokumen
            </button>
            
            <template x-for="(crumb, index) in breadcrumbs" :key="index">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">›</span>
                    <button @click="navigateToFolder(crumb)" class="text-gray-500 hover:text-gray-700 transition"
                        x-text="crumb.name"></button>
                </div>
            </template>
            
            <template x-if="currentFolder">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">›</span>
                    <span class="text-gray-700 font-medium" x-text="currentFolder?.name"></span>
                </div>
            </template>
        </div>

        {{-- Header Folder --}}
        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
            {{-- Judul Folder dan Actions --}}
            <div class="flex items-center justify-between mb-2 sm:mb-3">
                <h2 class="text-lg font-semibold text-gray-800" x-text="currentFolder?.name"></h2>
                
                {{-- ✅ Edit & Delete HANYA untuk workspace --}}
                <div x-show="memberListAllowed" class="flex items-center gap-1">
                    <button @click="openEditFolder(currentFolder)"
                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button @click="openDeleteFolder(currentFolder)"
                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Info Pembuat dan Diterima Oleh --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                {{-- Info Pembuat (selalu tampil) --}}
                <div class="flex items-center gap-2">
                    <img :src="currentFolder?.creatorAvatar || 'https://i.pravatar.cc/32?img=8'" alt="Profile"
                        class="w-6 h-6 rounded-full">
                    <div>
                        <p class="text-xs font-medium text-gray-700" x-text="currentFolder?.creator?.name || 'Admin'"></p>
                        <p class="text-xs text-gray-500" x-text="formatDate(currentFolder?.createdAt)"></p>
                    </div>
                </div>

                {{-- ✅ PERBAIKAN: Diterima Oleh - Tampil jika workspace DAN (memberListAllowed ATAU ada recipients) --}}

                    <div x-show="currentContext === 'workspace' " class="flex items-center gap-2">
                        <div class="text-right">
                            <p class="text-xs font-medium text-gray-700">Diterima Oleh :</p>
                        </div>

                        {{-- Avatar Container - Clickable --}}
                        <div @click="console.log('Folder clicked!', currentFolder); openRecipientsModal(currentFolder)"
                            class="flex items-center cursor-pointer hover:opacity-80 transition">
                            
                            {{-- Show max 3 avatars --}}
                            <template x-for="(recipient, idx) in (currentFolder?.recipients || []).slice(0, 3)"
                                :key="recipient.id">
                                <img :src="recipient.avatar" 
                                    :alt="recipient.name"
                                    class="w-6 h-6 rounded-full border-2 border-white object-cover pointer-events-none"
                                    :style="{ marginLeft: idx === 0 ? '0' : '-8px', zIndex: 10 - idx }">
                            </template>

                            {{-- +X indicator --}}
                            <div x-show="(currentFolder?.recipients || []).length > 3"
                                class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-[10px] font-semibold text-gray-700 pointer-events-none"
                                style="margin-left: -8px;">
                                <span x-text="`+${(currentFolder?.recipients || []).length - 3}`"></span>
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