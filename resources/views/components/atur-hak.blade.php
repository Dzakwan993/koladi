@php
    $activeCompany = $activeCompany ?? null;
    $usersInCompany = $usersInCompany ?? collect([]);
    $availableRoles = $availableRoles ?? collect([]);
    $currentUserRole = $currentUserRole ?? 'Member';

    $canManageRoles = in_array($currentUserRole, ['SuperAdmin', 'Administrator', 'AdminSistem']);

    // ✅ WARNA MERAH UNTUK ADMINISTRATOR
    $colorMapping = [
        'SuperAdmin' => '#102A63',
        'Administrator' => '#DC2626', // ✅ MERAH
        'AdminSistem' => '#225AD6',
        'Manager' => '#0FA875',
        'Member' => '#E4BA13',
    ];

    $roleColor = $colorMapping[$currentUserRole] ?? '#E4BA13';

    // Fallback manual jika available roles kosong
    if ($availableRoles->count() === 0 && $canManageRoles) {
        if ($currentUserRole === 'SuperAdmin') {
            $availableRoles = collect([
                (object) ['id' => '55555555-5555-5555-5555-555555555555', 'name' => 'Administrator'],
                (object) ['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager'],
                (object) ['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member'],
            ]);
        } elseif ($currentUserRole === 'Administrator') {
            $availableRoles = collect([
                (object) ['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager'],
                (object) ['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member'],
            ]);
        }
    }
@endphp

<!-- Modal Overlay - Atur Role -->

<!-- ✅ SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>

<style>
    /* SweetAlert2 Custom Style */
    .swal2-popup {
        font-family: 'Inter', sans-serif;
        border-radius: 16px;
    }

    .swal2-title {
        color: #0F172A;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .swal2-html-container {
        color: #6B7280;
        font-size: 1rem;
    }

    .swal2-confirm {
        background-color: #2563EB !important;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 600;
    }

    .swal2-cancel {
        background-color: #E5E7EB !important;
        color: #374151 !important;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 600;
    }
</style>

<div id="roleModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden font-[Inter,sans-serif]"
    onclick="closeRoleModalOverlay(event)">

    <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-[95%] sm:max-w-2xl md:max-w-3xl lg:max-w-4xl mx-4 relative max-h-[90vh] overflow-hidden flex flex-col"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="p-4 sm:p-6 md:p-8 pb-3 sm:pb-4 flex-shrink-0">
            <div class="flex justify-between items-start gap-3">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl md:text-2xl text-[#0F172A] font-bold mb-1 sm:mb-2">Atur role</h2>
                    <p class="text-[#6B7280] text-xs sm:text-sm md:text-base">Anda bebas bisa mengatur role rekan atau
                        mengubah rolenya di bawah...</p>
                </div>
                <button onclick="closeRoleModal()" class="text-gray-500 hover:text-gray-700 transition flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        @if ($canManageRoles)
            <div class="px-4 sm:px-6 pb-4 sm:pb-6 flex-1 overflow-hidden flex">
                <div
                    class="bg-[#BBCFF9] rounded-lg sm:rounded-xl p-3 sm:p-4 flex-1 overflow-y-auto always-scrollbar space-y-2 sm:space-y-3">
                    <div id="roleListContainer" class="space-y-2 sm:space-y-3"></div>

                    <div data-company-role-list class="space-y-2 sm:space-y-3">
                        @forelse($usersInCompany as $user)
                            @php
                                $roleName = $user->current_role->name ?? 'Member';

                                $colorMapping = [
                                    'SuperAdmin' => '#102A63',
                                    'Administrator' => '#DC2626',
                                    'AdminSistem' => '#225AD6',
                                    'Manager' => '#0FA875',
                                    'Member' => '#E4BA13',
                                ];

                                $roleColor = $colorMapping[$roleName] ?? '#E4BA13';
                                $isUnchangeableRole = $roleName === 'SuperAdmin';

                                $canChangeThisRole = false;
                                if ($currentUserRole === 'SuperAdmin') {
                                    $canChangeThisRole = in_array($roleName, ['Administrator', 'Manager', 'Member']);
                                } elseif ($currentUserRole === 'Administrator') {
                                    $canChangeThisRole = in_array($roleName, ['Manager', 'Member']);
                                }

                                $isUnchangeableRole = !$canChangeThisRole;

                                // ✅ AMBIL AVATAR SEPERTI DI HALAMAN MEMBER
                                if ($user->avatar && Str::startsWith($user->avatar, ['http://', 'https://'])) {
                                    $avatarUrl = $user->avatar;
                                } elseif ($user->avatar) {
                                    $avatarUrl = asset('storage/' . $user->avatar);
                                } else {
                                    $avatarUrl =
                                        'https://ui-avatars.com/api/?name=' .
                                        urlencode($user->full_name ?? 'User') .
                                        '&background=4F46E5&color=fff&bold=true';
                                }
                            @endphp

                            <div
                                class="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm">
                                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                                    {{-- ✅ GUNAKAN AVATAR URL DARI PHP --}}
                                    <img src="{{ $avatarUrl }}" alt="{{ $user->full_name }}"
                                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0 object-cover ring-2 ring-gray-200">

                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                                        <span
                                            class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">{{ $user->full_name }}</span>
                                        <span
                                            class="text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap"
                                            style="background-color: {{ $roleColor }}">
                                            {{ $roleName }}
                                        </span>
                                    </div>
                                </div>

                                @if ($isUnchangeableRole)
                                    <div
                                        class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold w-full sm:w-36 md:w-40 h-10 sm:h-11 flex items-center justify-center text-sm sm:text-base">
                                        <span>{{ $roleName }}</span>
                                    </div>
                                @else
                                    <div class="relative w-full sm:w-36 md:w-40">
                                        <button onclick="toggleDropdown(this)"
                                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition w-full h-10 sm:h-11 flex items-center justify-center relative text-sm sm:text-base"
                                            data-user-id="{{ $user->id }}" data-current-role="{{ $roleName }}">
                                            <span
                                                class="role-text absolute left-1/2 -translate-x-1/2">{{ $roleName }}</span>
                                            <svg class="w-4 h-4 absolute right-3 sm:right-4 text-blue-600"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <div
                                            class="dropdown-menu absolute mt-2 w-36 sm:w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[70]">
                                            @if ($availableRoles->count() > 0)
                                                @foreach ($availableRoles as $role)
                                                    <button onclick="selectRole(this, '{{ $role->id }}')"
                                                        data-role-name="{{ $role->name }}"
                                                        class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 {{ $loop->first ? 'rounded-t-lg' : '' }} {{ $loop->last ? 'rounded-b-lg' : '' }} font-medium {{ $role->name === $roleName ? 'bg-blue-50 text-blue-600' : '' }}">
                                                        {{ $role->name }}
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="bg-white rounded-lg p-6 text-center text-gray-500">Belum ada user di perusahaan
                                ini</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 px-4 sm:px-6 pb-4 sm:pb-6 flex-shrink-0">
                <button onclick="closeRoleModal()"
                    class="border-2 border-blue-600 shadow-md text-blue-600 px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-blue-50 transition">Batal</button>
                <button onclick="saveRoleChanges()"
                    class="bg-blue-600 shadow-md text-white px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-blue-700 transition">Simpan</button>
            </div>
        @else
            <div class="px-4 sm:px-6 md:px-8 pb-4 sm:pb-6 md:pb-8 flex-1 overflow-hidden flex">
                <div
                    class="bg-red-50 rounded-lg sm:rounded-xl md:rounded-2xl p-4 sm:p-6 md:p-8 flex-1 flex items-center justify-center">
                    <div class="text-center max-w-md mx-auto">
                        <h3 class="text-base sm:text-lg md:text-xl font-semibold text-red-800 mb-1 sm:mb-2">Akses
                            Ditolak</h3>
                        <p class="text-xs sm:text-sm md:text-base text-red-600 font-medium">Hanya User yang memiliki
                            izin
                            yang dapat mengakses fitur ini.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- SCRIPT -->
<script>
    // Object untuk menyimpan perubahan role
    let roleChanges = {};

    // ✅ FUNGSI: Close modal dengan warning jika ada perubahan
    function closeRoleModal() {
        if (Object.keys(roleChanges).length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perubahan Belum Disimpan',
                text: 'Anda memiliki perubahan yang belum disimpan. Tutup modal?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tutup',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    roleChanges = {};
                    document.getElementById('roleModal').classList.add('hidden');
                    document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
                }
            });
        } else {
            document.getElementById('roleModal').classList.add('hidden');
            document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
        }
    }

    // ✅ FUNGSI: Open role modal
    window.openRoleModal = function() {
        const ctx = window.roleContext || {
            type: 'company'
        };
        const modal = document.getElementById('roleModal');
        modal.classList.remove('hidden');

        const listContainer = document.getElementById('roleListContainer');
        const companyLists = document.querySelectorAll('[data-company-role-list]');

        console.log('Opening role modal with context:', ctx);

        // Reset state - SELALU tampilkan company list dan kosongkan workspace list
        companyLists.forEach(el => {
            el.style.display = 'block';
        });

        if (listContainer) {
            listContainer.innerHTML = '';
        }

        // Jika context adalah company, stop di sini
        if (ctx.type === 'company' || !ctx.workspaceId) {
            console.log('Company mode - showing company list');
            return;
        }

        // WORKSPACE MODE: Sembunyikan list company, fetch workspace members
        console.log('Workspace mode - loading workspace members');
        companyLists.forEach(el => {
            el.style.display = 'none';
        });

        if (!listContainer) return;

        // Fetch dan render workspace members
        fetch(`/workspace/${ctx.workspaceId}/members`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(members => {
                console.log('Workspace members loaded:', members);
                listContainer.innerHTML = '';

                const workspaceRoles = window.availableRolesForWorkspace?.filter(r =>
                    r.name === 'Manager' || r.name === 'Member'
                ) || [];

                members.forEach(m => {
                    const roleName = m.role || 'Member';
                    const roleColor = getRoleColor(roleName);
                    const isUnchangeableRole = ['SuperAdmin', 'Administrator', 'AdminSistem'].includes(
                        roleName);

                    // ✅ GANTI BAGIAN AVATAR INI:
                    let avatarUrl = m.avatar;
                    if (!avatarUrl || (!avatarUrl.startsWith('http://') && !avatarUrl.startsWith(
                            'https://'))) {
                        if (avatarUrl && avatarUrl.length > 0) {
                            avatarUrl = `/storage/${avatarUrl}`;
                        } else {
                            avatarUrl =
                                `https://ui-avatars.com/api/?name=${encodeURIComponent(m.name)}&background=4F46E5&color=fff&bold=true`;
                        }
                    }

                    const item = document.createElement('div');
                    item.className =
                        'bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm';

                    item.innerHTML = `
                    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                        <img src="${avatarUrl}" alt="${m.name}"
                             class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0 object-cover ring-2 ring-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                            <span class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">${m.name}</span>
                            <span class="text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap"
                                style="background-color:${roleColor}">${roleName}</span>
                        </div>
                    </div>
                    ${isUnchangeableRole
                        ? `<div class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold w-full sm:w-36 md:w-40 h-10 sm:h-11 flex items-center justify-center text-sm sm:text-base">${roleName}</div>`
                        : `<div class="relative w-full sm:w-36 md:w-40">
                            <button onclick="toggleDropdown(this)" class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition w-full h-10 sm:h-11 flex items-center justify-center relative text-sm sm:text-base" data-user-id="${m.id}" data-current-role="${roleName}">
                                <span class="role-text absolute left-1/2 -translate-x-1/2">${roleName}</span>
                                <svg class="w-4 h-4 absolute right-3 sm:right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu absolute mt-2 w-36 sm:w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[70]">
                                ${workspaceRoles.length > 0
                                    ? workspaceRoles.map(r => `<button onclick="selectRole(this, '${r.id}')" data-role-name="${r.name}" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 font-medium">${r.name}</button>`).join('')
                                    : '<div class="px-3 sm:px-4 py-2 text-sm text-gray-500">No roles available</div>'}
                            </div>
                        </div>`
                    }
                `;
                    listContainer.appendChild(item);
                });
            })
            .catch(err => console.error('Error loading workspace members:', err));
    }

    // ✅ FUNGSI: Close modal overlay
    function closeRoleModalOverlay(event) {
        if (event.target.id === 'roleModal') closeRoleModal();
    }

    // ✅ FUNGSI: Toggle dropdown
    function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;

        // Tutup dropdown lain
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== dropdown) menu.classList.add('hidden');
        });

        // Toggle dropdown ini
        dropdown.classList.toggle('hidden');
    }

    // ✅ FUNGSI: Select role dengan SweetAlert konfirmasi
    function selectRole(option, roleId) {
        const newRoleName = option.getAttribute('data-role-name') || option.textContent.trim();
        const dropdown = option.closest('.dropdown-menu');
        const button = dropdown.previousElementSibling;
        const userId = button.getAttribute('data-user-id');
        const oldRole = button.getAttribute('data-current-role');

        // Tutup dropdown
        dropdown.classList.add('hidden');

        // Skip jika role tidak berubah
        if (oldRole === newRoleName) return;

        // SweetAlert Konfirmasi
        Swal.fire({
            title: 'Konfirmasi Perubahan Role',
            html: `
                <div class="text-left">
                    <p class="text-gray-700 mb-3">Apakah Anda yakin ingin mengubah role?</p>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Role Lama:</span>
                            <span class="font-semibold text-sm px-3 py-1 rounded-lg" style="background-color: ${getRoleColor(oldRole)}; color: white;">${oldRole}</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Role Baru:</span>
                            <span class="font-semibold text-sm px-3 py-1 rounded-lg" style="background-color: ${getRoleColor(newRoleName)}; color: white;">${newRoleName}</span>
                        </div>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah Role',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Update tampilan
                button.querySelector('.role-text').textContent = newRoleName;
                button.setAttribute('data-current-role', newRoleName);

                // Simpan perubahan
                roleChanges[userId] = roleId;
                console.log('✅ Role changes updated:', roleChanges);

                // Toast konfirmasi
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Role telah diubah',
                    text: 'Jangan lupa klik tombol Simpan',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        });
    }

    // ✅ FUNGSI: Get warna role
    function getRoleColor(roleName) {
        const colorMap = {
            'SuperAdmin': '#102A63',
            'Administrator': '#DC2626', // ✅ MERAH
            'AdminSistem': '#225AD6',
            'Manager': '#0FA875',
            'Member': '#E4BA13'
        };
        return colorMap[roleName] || '#E4BA13';
    }

    // ✅ FUNGSI: Save role changes dengan SweetAlert
    function saveRoleChanges() {
        console.group('💾 Saving role changes');
        console.log('Changes to save:', roleChanges);

        if (Object.keys(roleChanges).length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Tidak Ada Perubahan',
                text: 'Tidak ada perubahan role yang perlu disimpan',
                confirmButtonText: 'OK'
            });
            console.groupEnd();
            return;
        }

        const ctx = window.roleContext || {
            type: 'company'
        };
        const payload = {
            changes: roleChanges
        };

        let url = '/update-user-roles';
        if (ctx.type === 'company') {
            payload.company_id = '{{ $activeCompany->id ?? '' }}';
        } else if (ctx.type === 'workspace' && ctx.workspaceId) {
            url = `/workspace/${ctx.workspaceId}/update-user-roles`;
        }

        // SweetAlert Loading
        Swal.fire({
            title: 'Menyimpan Perubahan...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                Swal.close();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Role berhasil diperbarui',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        roleChanges = {};
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message || 'Terjadi kesalahan saat menyimpan',
                        confirmButtonText: 'OK'
                    });
                }
                console.groupEnd();
            })
            .catch(err => {
                console.error('Error saving:', err);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal menyimpan perubahan. Silakan coba lagi.',
                    confirmButtonText: 'OK'
                });
                console.groupEnd();
            });
    }

    // Event listeners
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.relative') || e.target.closest('.dropdown-menu')) {
            if (!e.target.closest('button[onclick*="toggleDropdown"]')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
            }
        }
    });

    document.querySelector('.always-scrollbar')?.addEventListener('scroll', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (!menu.classList.contains('hidden')) {
                const button = menu.previousElementSibling;
                const buttonRect = button.getBoundingClientRect();
                menu.style.left = buttonRect.left + 'px';
                menu.style.top = (buttonRect.bottom + 8) + 'px';
            }
        });
    });

    document.addEventListener('click', function(event) {
        const modal = document.getElementById('roleModal');
        if (modal && !modal.classList.contains('hidden') && event.target === modal) {
            closeRoleModal();
        }
    });
</script>

<style>
    .dropdown-menu {
        position: fixed !important;
        z-index: 9999 !important;
    }

    .always-scrollbar {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(100, 100, 100, 0.4) rgba(200, 200, 200, 0.2);
    }

    .always-scrollbar::-webkit-scrollbar {
        width: 8px;
        display: block;
    }

    .always-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(100, 100, 100, 0.4);
        border-radius: 4px;
    }

    .always-scrollbar::-webkit-scrollbar-track {
        background: rgba(200, 200, 200, 0.2);
    }
</style>
