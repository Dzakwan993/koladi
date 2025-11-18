@php
    // ✅ PERBAIKAN: Definisikan semua variabel dengan nilai default
    $activeCompany = $activeCompany ?? null;
    $usersInCompany = $usersInCompany ?? collect([]);
    $availableRoles = $availableRoles ?? collect([]);
    $currentUserRole = $currentUserRole ?? 'Member';

    // ✅ PERBAIKAN: Hitung ulang canManageRoles dengan logic yang konsisten
    $canManageRoles = in_array($currentUserRole, ['SuperAdmin', 'Administrator', 'AdminSistem']);

    // ✅ PERBAIKAN: Update mapping warna sesuai role di database
    $colorMapping = [
        'SuperAdmin' => '#102A63',
        'Administrator' => '#225AD6',
        'AdminSistem' => '#225AD6',
        'Manager' => '#0FA875',
        'Member' => '#E4BA13',
    ];

    $roleColor = $colorMapping[$currentUserRole] ?? '#E4BA13';

    // ✅ DEBUG: Tambahkan log detail
    \Log::info('=== BLADE TEMPLATE START ===');
    \Log::info('Blade template data', [
        'canManageRoles' => $canManageRoles,
        'currentUserRole' => $currentUserRole,
        'users_count' => $usersInCompany->count(),
        'roles_count' => $availableRoles->count(),
        'available_roles_names' => $availableRoles->pluck('name')->toArray(),
        'all_users_roles' => $usersInCompany
            ->map(function ($user) {
                return [
                    'user_name' => $user->full_name,
                    'user_role' => $user->current_role->name ?? 'No role',
                ];
            })
            ->toArray(),
    ]);

    // ✅ PERBAIKAN: Fallback manual TANPA AdminSistem
    if ($availableRoles->count() === 0 && $canManageRoles) {
        \Log::warning('Available roles is empty in blade, creating manual fallback WITHOUT AdminSistem');
        $availableRoles = collect([
            (object)['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager'],
            (object)['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member']
        ]);
        \Log::info('Manual fallback created in blade (without AdminSistem):', $availableRoles->pluck('name')->toArray());
    }

    // ✅ PERBAIKAN: Script console log yang benar
    echo "<script>";
    echo "console.log('Blade Data:', " . json_encode([
        'availableRoles' => $availableRoles->toArray(),
        'availableRolesCount' => $availableRoles->count(),
        'currentUserRole' => $currentUserRole,
        'usersCount' => $usersInCompany->count()
    ]) . ");";
    echo "</script>";
    
    \Log::info('=== BLADE TEMPLATE END ===');
@endphp


<!-- Modal Overlay - Atur Role -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<script>
    console.log('Available roles from backend:', @json($availableRoles));
    console.log('Available roles count:', @json($availableRoles->count()));
    console.log('Current user role:', '{{ $currentUserRole }}');

    window.availableRolesForWorkspace = @json($availableRoles);
    window.currentUserRole = '{{ $currentUserRole }}';

    // ✅ DEBUG: Log ke console
    console.log('Window availableRoles:', window.availableRolesForWorkspace);
    console.log('Window currentUserRole:', window.currentUserRole);
</script>

<div id="roleModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden font-[Inter,sans-serif]"
    onclick="closeRoleModalOverlay(event)">
    <!-- Modal Container -->
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

                <!-- Close Button -->
                <button onclick="closeRoleModal()" class="text-gray-500 hover:text-gray-700 transition flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- ✅ TAMBAHKAN: Cek akses sebelum menampilkan konten -->
        @if ($canManageRoles)
            <!-- Scrollable Content Area with Blue Background -->
            <div class="px-4 sm:px-6 pb-4 sm:pb-6 flex-1 overflow-hidden flex">
                <div
                    class="bg-[#BBCFF9] rounded-lg sm:rounded-xl p-3 sm:p-4 flex-1 overflow-y-auto always-scrollbar space-y-2 sm:space-y-3 ">
                    <div id="roleListContainer" class="space-y-2 sm:space-y-3"></div>

                    <div data-company-role-list class="space-y-2 sm:space-y-3">
                        @forelse($usersInCompany as $user)
                            @php
                                // ✅ PERBAIKAN: Gunakan current_role yang sudah di-attach
                                $roleName = $user->current_role->name ?? 'Member';

                                // ✅ PERBAIKAN: Update mapping warna
                                $colorMapping = [
                                    'SuperAdmin' => '#102A63',
                                    'Administrator' => '#225AD6',
                                    'AdminSistem' => '#225AD6',
                                    'Manager' => '#0FA875',
                                    'Member' => '#E4BA13',
                                ];

                                $roleColor = $colorMapping[$roleName] ?? '#E4BA13';

                                // ✅ PERBAIKAN: Update logic untuk role yang tidak bisa diubah
                                // SuperAdmin, Administrator, dan AdminSistem tidak bisa diubah dari dropdown
                                $isUnchangeableRole = in_array($roleName, ['SuperAdmin', 'Administrator', 'AdminSistem']);
                            @endphp

                            <!-- User Item -->
                            <div
                                class="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm ">
                                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                                    <!-- Avatar -->
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name) }}&background=random"
                                        alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">

                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                                        <span class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">
                                            {{ $user->full_name }}
                                        </span>
                                        <span
                                            class="text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap"
                                            style="background-color: {{ $roleColor }}">
                                            {{ $roleName }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Dropdown Role atau Text Static untuk role yang tidak bisa diubah -->
                                @if ($isUnchangeableRole)
                                    <!-- SuperAdmin, Administrator, AdminSistem - Hanya Text Tanpa Dropdown -->
                                    <div
                                        class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold w-full sm:w-36 md:w-40 h-10 sm:h-11 flex items-center justify-center text-sm sm:text-base">
                                        <span>{{ $roleName }}</span>
                                    </div>
                                @else
                                    <!-- Role Lainnya (Manager, Member) - Ada Dropdown -->
                                    <div class="relative w-full sm:w-36 md:w-40">
                                        <button onclick="toggleDropdown(this)"
                                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition w-full h-10 sm:h-11 flex items-center justify-center relative text-sm sm:text-base"
                                            data-user-id="{{ $user->id }}">
                                            <span
                                                class="role-text absolute left-1/2 -translate-x-1/2">{{ $roleName }}</span>
                                            <svg class="w-4 h-4 absolute right-3 sm:right-4 text-blue-600"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div
                                            class="dropdown-menu absolute mt-2 w-36 sm:w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[70]">
                                            @foreach ($availableRoles as $index => $role)
                                                <button onclick="selectRole(this, '{{ $role->id }}')"
                                                    class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 
                                    {{ $loop->first ? 'rounded-t-lg' : '' }} 
                                    {{ $loop->last ? 'rounded-b-lg' : '' }} 
                                    font-medium">
                                                    {{ $role->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <!-- Jika tidak ada user -->
                            <div class="bg-white rounded-lg p-6 text-center text-gray-500">
                                Belum ada user di perusahaan ini
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 px-4 sm:px-6 pb-4 sm:pb-6 flex-shrink-0">
                <button onclick="closeRoleModal()"
                    class="border-2 border-blue-600 shadow-md text-blue-600 px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-blue-50 transition">
                    Batal
                </button>
                <button onclick="saveRoleChanges()"
                    class="bg-blue-600 shadow-md text-white px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-blue-700 transition">
                    Simpan
                </button>
            </div>
        @else
            <!-- ✅ Tampilan jika tidak punya akses -->
            <div class="px-4 sm:px-6 md:px-8 pb-4 sm:pb-6 md:pb-8 flex-1 overflow-hidden flex">
                <div
                    class="bg-red-50 rounded-lg sm:rounded-xl md:rounded-2xl p-4 sm:p-6 md:p-8 flex-1 flex items-center justify-center">
                    <div class="text-center max-w-md mx-auto">
                        <img src="{{ asset('images/icons/Error.svg') }}"
                            class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 mx-auto mb-2 sm:mb-3 md:mb-4"
                            alt="Akses Ditolak">
                        <h3 class="text-base sm:text-lg md:text-xl font-semibold text-red-800 mb-1 sm:mb-2">Akses
                            Ditolak</h3>
                        <p class="text-xs sm:text-sm md:text-base text-red-600 font-medium">Hanya Super Admin dan Admin
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

    function closeRoleModal() {
        document.getElementById('roleModal').classList.add('hidden');
        document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
    }

    window.openRoleModal = function() {
        const ctx = window.roleContext || {
            type: 'company'
        };
        const modal = document.getElementById('roleModal');
        modal.classList.remove('hidden');

        const listContainer = document.getElementById('roleListContainer');
        const companyLists = document.querySelectorAll('[data-company-role-list]');

        // ✅ Log untuk debugging
        console.log('Opening role modal with context:', ctx);
        console.log('Available roles:', window.availableRolesForWorkspace);
        console.log('Available roles length:', window.availableRolesForWorkspace ? window.availableRolesForWorkspace
            .length : 0);

        // ✅ Reset state - SELALU tampilkan company list dan kosongkan workspace list
        companyLists.forEach(el => {
            el.style.display = 'block';
        });

        if (listContainer) {
            listContainer.innerHTML = '';
        }

        // ✅ Jika context adalah company, stop di sini
        if (ctx.type === 'company' || !ctx.workspaceId) {
            console.log('Company mode - showing company list');

            // ✅ DEBUG: Cek dropdown menu content
            setTimeout(() => {
                const dropdownMenus = document.querySelectorAll('.dropdown-menu');
                console.log('Dropdown menus found:', dropdownMenus.length);
                dropdownMenus.forEach((menu, index) => {
                    console.log(`Dropdown ${index} content:`, menu.innerHTML);
                });
            }, 100);

            return;
        }

        // ✅ WORKSPACE MODE: Sembunyikan list company, fetch workspace members
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

                // ✅ FILTER: Hanya Manager dan Member untuk workspace
                const workspaceRoles = window.availableRolesForWorkspace?.filter(r =>
                    r.name === 'Manager' || r.name === 'Member'
                ) || [];

                console.log('Workspace roles available:', workspaceRoles);

                members.forEach(m => {
                    const roleName = m.role || 'Member';
                    const colorMap = {
                        'SuperAdmin': '#102A63',
                        'Administrator': '#225AD6',
                        'AdminSistem': '#225AD6',
                        'Manager': '#0FA875',
                        'Member': '#E4BA13'
                    };
                    const roleColor = colorMap[roleName] || '#E4BA13';
                    
                    // ✅ PERBAIKAN: Role yang tidak bisa diubah di workspace
                    const isUnchangeableRole = roleName === 'SuperAdmin' || roleName === 'Administrator' || roleName === 'AdminSistem';

                    const item = document.createElement('div');
                    item.className =
                        'bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm';

                    item.innerHTML = `
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(m.name)}&background=random"
                         class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">

                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                        <span class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">${m.name}</span>
                        <span class="text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap"
                            style="background-color:${roleColor}">
                            ${roleName}
                        </span>
                    </div>
                </div>

                ${
                    isUnchangeableRole
                    ? `<div class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold w-full sm:w-36 md:w-40 h-10 sm:h-11 flex items-center justify-center text-sm sm:text-base">${roleName}</div>`
                    : `<div class="relative w-full sm:w-36 md:w-40">
                        <button onclick="toggleDropdown(this)"
                                class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition w-full h-10 sm:h-11 flex items-center justify-center relative text-sm sm:text-base"
                                data-user-id="${m.id}">

                            <span class="role-text absolute left-1/2 -translate-x-1/2">${roleName}</span>
                            <svg class="w-4 h-4 absolute right-3 sm:right-4 text-blue-600"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div class="dropdown-menu absolute mt-2 w-36 sm:w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[70]">
                            ${
                                workspaceRoles.length > 0 
                                ? workspaceRoles.map(r => `
                                    <button onclick="selectRole(this, '${r.id}')"
                                        class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 font-medium">
                                        ${r.name}
                                    </button>
                                `).join('')
                                : '<div class="px-3 sm:px-4 py-2 text-sm text-gray-500">No roles available</div>'
                            }
                        </div>
                    </div>`
                }
            `;

                    listContainer.appendChild(item);
                });
            })
            .catch(err => {
                console.error('Error loading workspace members:', err);
            });
    }

    function closeRoleModalOverlay(event) {
        if (event.target.id === 'roleModal') closeRoleModal();
    }

    // Dropdown functionality
    function toggleDropdown(button) {
        console.log('Toggle dropdown clicked');
        const dropdown = button.nextElementSibling;

        console.log('Dropdown element:', dropdown);
        console.log('Dropdown HTML:', dropdown.innerHTML);

        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== dropdown) menu.classList.add('hidden');
        });

        dropdown.classList.toggle('hidden');
        console.log('Dropdown visible after toggle:', !dropdown.classList.contains('hidden'));
    }

    // Select role dari dropdown
    function selectRole(option, roleId) {
        const newRoleName = option.textContent.trim();
        const dropdown = option.closest('.dropdown-menu');
        const button = dropdown.previousElementSibling;
        const userId = button.getAttribute('data-user-id');

        // Update tampilan dropdown
        button.querySelector('.role-text').textContent = newRoleName;
        dropdown.classList.add('hidden');

        // Simpan perubahan ke object
        roleChanges[userId] = roleId;

        console.log('Role changes:', roleChanges);
    }

    // Fungsi simpan perubahan ke database
    function saveRoleChanges() {
        if (Object.keys(roleChanges).length === 0) {
            alert('Tidak ada perubahan role');
            closeRoleModal();
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
                if (data.success) {
                    alert('Role berhasil diperbarui!');
                    roleChanges = {};
                    location.reload();
                } else {
                    alert('Gagal memperbarui role: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan');
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

    // Tambahkan event listener biar klik luar modal menutup
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('roleModal');
        if (modal && !modal.classList.contains('hidden') && event.target === modal) {
            closeRoleModal();
        }
    });
</script>

<!-- STYLE -->
<style>
    /* ✅ Pastikan dropdown tampil di atas semua elemen */
    .dropdown-menu {
        position: fixed !important;
        z-index: 9999 !important;
    }

    /* ✅ Kontainer utama bisa discroll */
    .always-scrollbar {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(100, 100, 100, 0.4) rgba(200, 200, 200, 0.2);
    }

    /* ✅ Scrollbar untuk browser berbasis WebKit (Chrome, Edge, Safari) */
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

    /* ✅ Hindari flex container mematikan scroll di dalam */
    .flex-1.overflow-hidden {
        overflow: hidden !important;
    }


</style>
