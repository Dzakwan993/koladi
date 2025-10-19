<!-- Modal Popup Atur Akses -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden font-[Inter,sans-serif]" id="accessModal">
    <div class="bg-white pt-2 px-4 sm:px-6 rounded-lg sm:rounded-xl shadow-xl w-full max-w-[95%] sm:max-w-4xl md:max-w-5xl lg:max-w-6xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="relative flex flex-col sm:flex-row justify-between items-start p-4 sm:p-6 gap-4">
            <div class="flex-1 w-full">
                <h2 class="text-lg sm:text-xl md:text-2xl text-[#0F172A] font-bold mb-3 sm:mb-4">Akses Anda di PT. Mencari Cinta Sejati</h2>
                
                <div class="relative sm:pl-14 mt-2 w-full">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3 mt-1">
                        <!-- Avatar - di kiri di mobile, absolute di desktop -->
                        <img src="https://i.pravatar.cc/50?img=1" alt="Avatar" 
                            class="w-10 h-10 sm:w-10 sm:h-10 rounded-full sm:absolute sm:left-0 sm:top-1/2 sm:-translate-y-1/2">
                        
                        <div class="flex flex-col gap-1">
                            <span class="text-base sm:text-lg md:text-xl text-[#0F172A] font-bold">Muhammad Sahroni</span>
                            <span class="bg-[#102A63] text-white text-xs font-semibold px-2 sm:px-3 py-0.5 rounded-bl-2xl rounded-tr-2xl inline-block w-fit">
                                Super Admin
                            </span>
                        </div>
                    </div>
                    
                    <!-- Button Atur akses pengguna -->
                    <button onclick="openRoleModal();" class="bg-[#225AD6] hover:bg-blue-600 text-white px-3 py-2 sm:py-2.5 text-xs sm:text-sm rounded-lg font-semibold transition flex items-center gap-1.5 sm:gap-2 shadow-lg mt-3 sm:mt-0 sm:absolute sm:right-0 sm:top-1/2 sm:-translate-y-1/2 w-full sm:w-auto justify-center">
                        <img src="{{ asset('images/icons/Protect.svg') }}" alt="Plus" class="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7" />
                        Atur akses pengguna
                    </button>
                </div>
            </div>
        </div>

        <div class="border-b mx-4 sm:mx-8 md:mx-12 border-[#102A63]"></div>

        <!-- Content -->
        <div class="p-4 sm:p-6">
            <!-- Access Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-2">
                <!-- Super Admin Card -->
                <div class="bg-[#BBCFF9] rounded-lg p-4 sm:p-5 md:p-6">
                    <h3 class="text-base sm:text-lg md:text-xl text-[#0F172A] font-bold mb-3 sm:mb-4 text-center">Super Admin</h3>
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengatur akses pengguna</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Membuat tim dan tugas dan Membuat tim dan tugas Membuat tim dan tugas</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Super admin memiki semua hak akses yang itu</span>
                        </div>
                    </div>
                </div>

                <!-- Admin Card -->
                <div class="bg-[#BBCFF9] rounded-lg p-4 sm:p-5 md:p-6">
                    <h3 class="text-base sm:text-lg md:text-xl text-[#0F172A] font-bold mb-3 sm:mb-4 text-center">Admin</h3>
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengatur akses pengguna</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Membuat tim dan tugas dan Membuat tim dan tugas Membuat tim dan tugas</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-xs sm:text-sm text-gray-700 font-medium">Super admin memiki semua hak akses yang itu</span>
                        </div>
                    </div>
                </div>

                <!-- Manager Card -->
                <div class="bg-[#BBCFF9] rounded-lg p-4 sm:p-5 md:p-6">
                    <h3 class="text-base sm:text-lg md:text-xl text-[#0F172A] font-bold mb-3 sm:mb-4 text-center">Manager</h3>
                    <div class="space-y-2 sm:space-y-3">
                        <!-- Add manager permissions here -->
                    </div>
                </div>

                <!-- Member Card -->
                <div class="bg-[#BBCFF9] rounded-lg p-4 sm:p-5 md:p-6">
                    <h3 class="text-base sm:text-lg md:text-xl text-[#0F172A] font-bold mb-3 sm:mb-4 text-center">Member</h3>
                    <div class="space-y-2 sm:space-y-3">
                        <!-- Add member permissions here -->
                    </div>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="rounded-lg p-3 sm:p-4 text-xs sm:text-sm text-[#6B7280]">
                <p class="mb-1 font-medium">Ini adalah Akses default perusahaan kamu. Kamu memiliki Akses yang sama di semua tempat berdasarkan ini.</p>
                <p class="mb-1 font-medium">Hingga Super User/Admin mengubah Akses Kamu secara khusus di suatu Tim atau Tugas/Dokumen lain.</p>
                <p class="font-medium">Kalo Kamu mengalami kendala saat melakukan sesuatu, harap hubungi orang diatas.</p>
            </div>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('accessModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('accessModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('accessModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>