<!-- Modal Popup Atur Akses -->

<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden font-[Inter,sans-serif]"
    id="accessModal">
    <div
        class="bg-white rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-[95%] sm:max-w-4xl md:max-w-5xl lg:max-w-6xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">

        <!-- Header dengan Gradient Background -->
        <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 p-6 sm:p-8">
            <button onclick="closeAccessModal()"
                class="absolute top-4 right-4 text-white hover:bg-white/20 rounded-lg p-2 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h2 data-title class="text-xl sm:text-2xl md:text-3xl text-white font-bold mb-6">Akses Anda di</h2>

            @php
                // Ambil role user di company
                $activeCompanyId = session('active_company_id');
                $currentUser = auth()->user();
                $userCompany = $currentUser
                    ->userCompanies()
                    ->where('company_id', $activeCompanyId)
                    ->with('role')
                    ->first();

                $currentUserRole = $userCompany?->role?->name ?? 'Member';

                // Mapping warna
                $colorMapping = [
                    'SuperAdmin' => '#102A63',
                    'Super Admin' => '#102A63',
                    'Administrator' => '#DC2626',
                    'Admin' => '#225AD6',
                    'Manager' => '#0FA875',
                    'Member' => '#E4BA13',
                ];

                $roleColor = $colorMapping[$currentUserRole] ?? '#E4BA13';

                // ✅ AMBIL AVATAR SEPERTI DI HALAMAN MEMBER
                if ($currentUser->avatar && Str::startsWith($currentUser->avatar, ['http://', 'https://'])) {
                    $currentUserAvatar = $currentUser->avatar;
                } elseif ($currentUser->avatar) {
                    $currentUserAvatar = asset('storage/' . $currentUser->avatar);
                } else {
                    $currentUserAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($currentUser->full_name ?? 'User') . '&background=4F46E5&color=fff&bold=true';
                }
            @endphp

            <!-- Profile Card -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 sm:p-5 border border-white/20">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                    <!-- Avatar dengan Ring Effect -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-white rounded-full blur-md opacity-50"></div>
                        <img src="{{ $currentUserAvatar }}"
                             alt="{{ $currentUser->full_name }}"
                             class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover ring-4 ring-white shadow-xl">
                    </div>

                    <!-- User Info -->
                    <div class="flex-1 text-center sm:text-left">
                        <h3 class="text-xl sm:text-2xl text-white font-bold mb-2">
                            {{ $currentUser->full_name ?? 'User' }}
                        </h3>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-3">
                            <span class="text-white/80 text-sm">{{ $currentUser->email }}</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white shadow-lg"
                                  id="accessModalUserRole"
                                  style="background-color: {{ $roleColor }}">
                                {{ $currentUserRole }}
                            </span>
                        </div>
                    </div>

                    <!-- Button Atur Akses -->
                    <button onclick="openRoleModal();"
                        class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2.5 rounded-lg font-semibold transition flex items-center gap-2 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span class="hidden sm:inline">Atur Akses Pengguna</span>
                        <span class="sm:hidden">Atur Akses</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Content dengan Scroll -->
        <div class="flex-1 overflow-y-auto p-6 sm:p-8 bg-gray-50">
            <!-- Access Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                <!-- Super Admin Card -->
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-t-4 border-blue-900">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-blue-900 text-white rounded-full p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl text-center text-gray-900 font-bold mb-4">Super Admin</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Akses penuh ke seluruh sistem tanpa batasan</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengelola seluruh data perusahaan, workspace, dan pengguna</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Kontrol penuh atas semua fitur dan pengaturan</span>
                        </div>
                    </div>
                </div>

                <!-- Admin Card -->
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-t-4 border-red-600">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-red-600 text-white rounded-full p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl text-center text-gray-900 font-bold mb-4">Administrator</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengelola perusahaan dan akses pengguna</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Membuat dan mengatur workspace serta tim</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengelola Tugas, dokumen, dan pengumuman</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Tidak dapat mengatur workspace eksternal</span>
                        </div>
                    </div>
                </div>

                <!-- Manager Card -->
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-t-4 border-green-600">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-green-600 text-white rounded-full p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl text-center text-gray-900 font-bold mb-4">Manager</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengatur workspace yang dipimpin</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengelola tugas dan progres tim</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Tidak bisa mengubah data perusahaan</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Tidak bisa memodifikasi workspace lain</span>
                        </div>
                    </div>
                </div>

                <!-- Member Card -->
                <div class="bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border-t-4 border-yellow-500">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-yellow-500 text-white rounded-full p-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl text-center text-gray-900 font-bold mb-4">Member</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengerjakan tugas dalam workspace</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Memperbarui progres dan dokumen</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Tidak bisa membuat workspace baru</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Tidak bisa mengatur role akses</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Note dengan Icon -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4 flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm text-gray-700 font-medium">
                        <strong>Catatan:</strong> Ini adalah akses default di tingkat perusahaan.
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        Untuk hak akses di setiap workspace akan disesuaikan kembali berdasarkan peranmu di dalam workspace tersebut.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.currentCompanyUserRole = '{{ $currentUserRole }}';
    window.currentCompanyRoleColor = '{{ $roleColor }}';

    function openAccessModal(ctx) {
        if (ctx) {
            window.setRoleContext(ctx);
            console.log('Setting workspace context:', ctx.workspaceName);

            const modal = document.getElementById('accessModal');
            const titleElement = document.querySelector('[data-title]');
            const userRoleElement = document.getElementById('accessModalUserRole');

            // Tampilkan title berdasarkan konteks
            if (titleElement) {
                if (ctx.type === 'workspace' && ctx.workspaceName) {
                    titleElement.textContent = `Akses Anda di Workspace ${ctx.workspaceName}`;
                } else {
                    titleElement.textContent = `Akses Anda di {{ $activeCompany->name ?? 'Perusahaan' }}`;
                }
            }

            // ✅ MODAL WORKSPACE: Fetch role dengan logika baru
            if (ctx.type === 'workspace' && ctx.workspaceId) {
                fetch(`/workspace/${ctx.workspaceId}/user-role`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('Network response was not ok');
                        return r.json();
                    })
                    .then(data => {
                        console.log('Role data received:', data);

                        if (userRoleElement) {
                            userRoleElement.textContent = data.role;

                            const colorMap = {
                                'Super Admin': '#102A63',
                                'SuperAdmin': '#102A63',
                                'Administrator': '#DC2626',
                                'Admin': '#225AD6',
                                'Manager': '#0FA875',
                                'Member': '#E4BA13'
                            };

                            userRoleElement.style.backgroundColor = colorMap[data.role] || '#E4BA13';
                        }

                        modal.classList.remove('hidden');
                    })
                    .catch(err => {
                        console.error('Error fetching role:', err);
                        // ✅ FALLBACK KE COMPANY ROLE
                        if (userRoleElement) {
                            userRoleElement.textContent = window.currentCompanyUserRole;
                            userRoleElement.style.backgroundColor = window.currentCompanyRoleColor;
                        }
                        modal.classList.remove('hidden');
                    });
            } else {
                // ✅ MODAL COMPANY - pakai role dari Blade
                if (userRoleElement) {
                    const role = ctx.userRole || window.currentCompanyUserRole || '{{ $currentUserRole }}';
                    const colorMap = {
                        'Super Admin': '#102A63',
                        'SuperAdmin': '#102A63',
                        'Administrator': '#DC2626',
                        'Admin': '#225AD6',
                        'Manager': '#0FA875',
                        'Member': '#E4BA13'
                    };

                    userRoleElement.textContent = role;
                    userRoleElement.style.backgroundColor = colorMap[role] || window.currentCompanyRoleColor ||
                        '{{ $roleColor }}';
                }
                modal.classList.remove('hidden');
            }
        }
    }

    function closeAccessModal() {
        const modal = document.getElementById('accessModal');
        modal.classList.add('hidden');
    }

    // Event listener untuk klik luar modal
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('accessModal');
        if (modal && !modal.classList.contains('hidden') && event.target === modal) {
            closeAccessModal();
        }
    });
</script>

<style>
    /* Smooth Scrollbar */
    #accessModal > div {
        scrollbar-width: thin;
        scrollbar-color: rgba(59, 130, 246, 0.5) rgba(229, 231, 235, 0.5);
    }

    #accessModal > div::-webkit-scrollbar {
        width: 8px;
    }

    #accessModal > div::-webkit-scrollbar-track {
        background: rgba(229, 231, 235, 0.5);
        border-radius: 10px;
    }

    #accessModal > div::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.5);
        border-radius: 10px;
    }

    #accessModal > div::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.7);
    }
</style>
