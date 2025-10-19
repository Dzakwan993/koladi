<!-- Modal Overlay - Atur Role -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<div id="roleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden font-[Inter,sans-serif]" onclick="closeRoleModalOverlay(event)">
    <!-- Modal Container -->
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-[95%] sm:max-w-2xl md:max-w-3xl lg:max-w-4xl mx-4 relative max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="p-4 sm:p-6 md:p-8 pb-3 sm:pb-4 flex-shrink-0">
            <div class="flex justify-between items-start gap-3">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl md:text-2xl text-[#0F172A] font-bold mb-1 sm:mb-2">Atur role</h2>
                    <p class="text-[#6B7280] text-xs sm:text-sm md:text-base">Anda bebas bisa mengatur role rekan atau mengubah rolenya di bawah...</p>
                </div>
                
                <!-- Close Button -->
                <button onclick="closeRoleModal()" class="text-gray-500 hover:text-gray-700 transition flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Scrollable Content Area with Blue Background -->
        <div class="px-4 sm:px-6 pb-4 sm:pb-6 flex-1 overflow-hidden">
            <div class="bg-[#BBCFF9] rounded-lg sm:rounded-xl p-3 sm:p-4 h-full overflow-y-scroll always-scrollbar space-y-2 sm:space-y-3">
                
                <!-- User Item 1 - Super Admin (TIDAK ADA DROPDOWN) -->
                <div class="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm">
                    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                        <img src="https://i.pravatar.cc/50?img=1" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                            <span class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">Muhammad Sahroni</span>
                            <span class="bg-[#102A63] text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap">Super Admin</span>
                        </div>
                    </div>
                    <!-- Super Admin - Hanya Text Tanpa Dropdown -->
                    <div class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold w-full sm:w-36 md:w-40 h-10 sm:h-11 flex items-center justify-center text-sm sm:text-base">
                        <span>Super Admin</span>
                    </div>
                </div>

                <!-- User Item 2 - Manager (ADA DROPDOWN) -->
                <div class="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm">
                    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                        <img src="https://i.pravatar.cc/50?img=2" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                            <span class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">Muhammad Sahroni</span>
                            <span class="bg-[#0FA875] text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap">Manager</span>
                        </div>
                    </div>
                    <div class="relative w-full sm:w-36 md:w-40">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition
                                   w-full h-10 sm:h-11 flex items-center justify-center relative text-sm sm:text-base">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Admin</span>
                            <svg class="w-4 h-4 absolute right-3 sm:right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <!-- Dropdown dengan z-index tinggi -->
                        <div class="dropdown-menu fixed mt-2 w-36 sm:w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 rounded-t-lg font-medium">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 font-medium">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 rounded-b-lg font-medium">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 3 - Admin (ADA DROPDOWN) -->
                <div class="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm">
                    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                        <img src="https://i.pravatar.cc/50?img=3" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                            <span class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">Muhammad Sahroni</span>
                            <span class="bg-[#225AD6] text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap">Admin</span>
                        </div>
                    </div>
                    <div class="relative w-full sm:w-36 md:w-40">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition
                                   w-full h-10 sm:h-11 flex items-center justify-center relative text-sm sm:text-base">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Manager</span>
                            <svg class="w-4 h-4 absolute right-3 sm:right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-36 sm:w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 rounded-t-lg font-medium">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 font-medium">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 rounded-b-lg font-medium">Member</button>
                        </div>
                    </div>
                </div>

                <!-- User Item 4 - Member (ADA DROPDOWN) -->
                <div class="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 shadow-sm">
                    <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto min-w-0">
                        <img src="https://i.pravatar.cc/50?img=4" alt="Avatar" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex-shrink-0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                            <span class="font-semibold text-base sm:text-lg text-[#0F172A] truncate">Muhammad Sahroni</span>
                            <span class="bg-[#E4BA13] text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl whitespace-nowrap">Member</span>
                        </div>
                    </div>
                    <div class="relative w-full sm:w-36 md:w-40">
                        <button onclick="toggleDropdown(this)" 
                            class="border-2 border-blue-600 text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition
                                   w-full h-10 sm:h-11 flex items-center justify-center relative text-sm sm:text-base">
                            <span class="role-text absolute left-1/2 -translate-x-1/2">Member</span>
                            <svg class="w-4 h-4 absolute right-3 sm:right-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu fixed mt-2 w-36 sm:w-40 bg-white border border-gray-200 rounded-lg shadow-xl hidden z-[9999]">
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 rounded-t-lg font-medium">Manager</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 font-medium">Admin</button>
                            <button onclick="selectRole(this)" class="block w-full text-left px-3 sm:px-4 py-2 text-sm sm:text-base hover:bg-blue-50 rounded-b-lg font-medium">Member</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 px-4 sm:px-6 pb-4 sm:pb-6 flex-shrink-0">
            <button onclick="closeRoleModal()" class="border-2 border-blue-600 shadow-md text-blue-600 px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-blue-50 transition">
                Batal
            </button>
            <button onclick="closeRoleModal()" class="bg-blue-600 shadow-md text-white px-6 sm:px-8 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold hover:bg-blue-700 transition">
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