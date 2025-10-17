<!-- Modal Overlay -->
<div id="roleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden" onclick="closeRoleModalOverlay(event)">
    <!-- Modal Container -->
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 relative" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="p-6 pb-4">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Atur role</h2>
                    <p class="text-gray-600 text-sm">Anda bebas bisa mengatur role rekan atau mengubah rolenya di bawah...</p>
                </div>
                
                <!-- Close Button -->
                <button onclick="closeRoleModal()" class="text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Scrollable Content Area with Blue Background -->
        <div class="px-6 pb-6">
            <div class="bg-blue-100 rounded-xl p-4 max-h-96 overflow-y-scroll always-scrollbar space-y-3">
                
                <!-- User Item 1 - Super Admin (TIDAK ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=1" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-blue-700 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Super Admin</span>
                        </div>
                    </div>
                    <!-- Super Admin - Hanya Text Tanpa Dropdown -->
                    <div class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium w-40 h-11 flex items-center justify-center">
                        <span>Super Admin</span>
                    </div>
                </div>

                <!-- User Item 2 - Manager (ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=2" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-green-500 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Manager</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition
                                   w-40 h-11 flex items-center justify-center relative">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Admin</span>
                            <svg class="w-4 h-4 absolute right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <!-- Dropdown dengan z-index tinggi -->
                        <div class="dropdown-menu fixed mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-t-lg">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-b-lg">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 3 - Admin (ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Admin</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition
                                   w-40 h-11 flex items-center justify-center relative">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Manager</span>
                            <svg class="w-4 h-4 absolute right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-t-lg">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-b-lg">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 4 - Member (ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=4" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-yellow-500 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Member</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition
                                   w-40 h-11 flex items-center justify-center relative">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Member</span>
                            <svg class="w-4 h-4 absolute right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-t-lg">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-b-lg">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 3 - Admin (ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Admin</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition
                                   w-40 h-11 flex items-center justify-center relative">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Manager</span>
                            <svg class="w-4 h-4 absolute right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-t-lg">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-b-lg">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 4 - Member (ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=4" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-yellow-500 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Member</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition
                                   w-40 h-11 flex items-center justify-center relative">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Member</span>
                            <svg class="w-4 h-4 absolute right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-t-lg">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-b-lg">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 3 - Admin (ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Admin</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition
                                   w-40 h-11 flex items-center justify-center relative">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Manager</span>
                            <svg class="w-4 h-4 absolute right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-t-lg">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-b-lg">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 4 - Member (ADA DROPDOWN) -->
                <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/50?img=4" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-lg">Muhammad Sahroni</span>
                            <span class="bg-yellow-500 text-white text-xs font-semibold px-3 py-1 rounded-bl-2xl rounded-tr-2xl">Member</span>
                        </div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition
                                   w-40 h-11 flex items-center justify-center relative">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Member</span>
                            <svg class="w-4 h-4 absolute right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-t-lg">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-4 py-2 hover:bg-blue-50 rounded-b-lg">Member</button>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-end gap-3 px-6 pb-6">
            <button onclick="closeRoleModal()" class="border-2 border-blue-600 text-blue-600 px-8 py-2.5 rounded-lg font-medium hover:bg-blue-50 transition">
                Batal
            </button>
            <button onclick="closeRoleModal()" class="bg-blue-600 text-white px-8 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
                Simpan
            </button>
        </div>

    </div>
</div>

<!-- SCRIPT -->
<script>
function closeRoleModal() {
    document.getElementById('roleModal').classList.add('hidden');
    // Tutup semua dropdown saat modal ditutup
    document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
}

function openRoleModal() {
    document.getElementById('roleModal').classList.remove('hidden');
}

function closeRoleModalOverlay(event) {
    if (event.target.id === 'roleModal') closeRoleModal();
}

// === DROPDOWN FUNCTIONALITY ===
function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    const buttonRect = button.getBoundingClientRect();
    
    // Tutup semua dropdown lain
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu !== dropdown) menu.classList.add('hidden');
    });
    
    // Posisikan dropdown berdasarkan posisi button
    dropdown.style.left = buttonRect.left + 'px';
    dropdown.style.top = (buttonRect.bottom + 8) + 'px';
    
    // Toggle dropdown
    dropdown.classList.toggle('hidden');
}

function selectRole(option) {
    const newRole = option.textContent.trim();
    const dropdown = option.closest('.dropdown-menu');
    const button = dropdown.previousElementSibling;
    button.querySelector('.role-text').textContent = newRole;
    dropdown.classList.add('hidden');
}

// Tutup dropdown jika klik di luar area
window.addEventListener('click', function(e) {
    if (!e.target.closest('.relative') || e.target.closest('.dropdown-menu')) {
        if (!e.target.closest('button[onclick*="toggleDropdown"]')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
        }
    }
});

// Update posisi dropdown saat scroll
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
</script>

<!-- STYLE -->
<style>
/* Scrollbar selalu terlihat */
.always-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(100, 100, 100, 0.4) rgba(200, 200, 200, 0.2);
    overflow-y: scroll !important;
}
.always-scrollbar::-webkit-scrollbar {
    width: 8px;
    display: block; /* Tambahkan ini */
}
.always-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(100, 100, 100, 0.4);
    border-radius: 4px;
}
.always-scrollbar::-webkit-scrollbar-track {
    background: rgba(200, 200, 200, 0.2); /* Ubah dari transparent */
}
</style>