<!-- Modal Popup Atur Akses -->
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" id="accessModal">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="relative flex justify-between items-start p-6">
            <div class="flex-1 pr-4">
                <h2 class="text-2xl font-bold">Akses Anda di PT. Mencari Cinta Sejati</h2>
                
                <div class="relative pl-14 mt-2 pr-68">
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-lg font-semibold">Muhammad Sahroni</span>
                    </div>
                    <div>
                        <span class="bg-[#1e3a8a] text-white text-sm font-semibold px-4 py-1 rounded-bl-2xl rounded-tr-2xl inline-block">
                            Super Admin
                        </span>
                    </div>
                    <!-- Avatar dengan position absolute, center vertikal -->
                    <img src="https://i.pravatar.cc/50?img=1" alt="Avatar" 
                        class="w-10 h-10 rounded-full absolute left-0 top-1/2 -translate-y-1/2">
                    
                    <!-- Button Atur akses pengguna di kanan, center vertikal sama seperti avatar -->
                    <button onclick="openRoleModal();" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-medium transition flex items-center gap-2 shadow-sm absolute right-0 top-1/2 -translate-y-1/2">
                        <img src="{{ asset('images/icons/Plus.svg') }}" alt="Plus" class="w-5 h-5" />
                        Atur akses pengguna
                    </button>
                </div>
            </div>
            <!-- Tombol Close di pojok kanan atas -->
            <!-- <button onclick="closeModal()" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 28 28">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button> -->
        </div>

        <div class="border-b mx-6 border-gray-500"></div>

        <!-- Content -->
        <div class="p-6">
            <!-- Button Atur Akses -->
            
            <!-- <div class="flex justify-end mb-6">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Atur akses pengguna</span>
                </button>
            </div> -->

            <!-- Access Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Super Admin Card -->
                <div class="bg-blue-100 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4 text-center">Super Admin</h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengatur akses pengguna</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Membuat tim dan tugas dan Membuat tim dan tugas Membuat tim dan tugas</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Super admin memiki semua hak akses yang itu</span>
                        </div>
                    </div>
                </div>

                <!-- Admin Card -->
                <div class="bg-blue-100 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4 text-center">Admin</h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengatur akses pengguna</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Membuat tim dan tugas dan Membuat tim dan tugas Membuat tim dan tugas</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Mengundang anggota tim</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm text-gray-700">Super admin memiki semua hak akses yang itu</span>
                        </div>
                    </div>
                </div>

                <!-- Manager Card -->
                <div class="bg-blue-100 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4 text-center">Manager</h3>
                    <div class="space-y-3">
                        <!-- Add manager permissions here -->
                    </div>
                </div>

                <!-- Member Card -->
                <div class="bg-blue-100 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4 text-center">Member</h3>
                    <div class="space-y-3">
                        <!-- Add member permissions here -->
                    </div>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                <p>Ini adalah Akses default perusahaan kamu. Kamu memiliki Akses yang sama di semua tempat berdasarkan ini.</p>
                <p>Hingga Super User/Admin mengubah Akses Kamu secara khusus di suatu Tim atau Tugas/Dokumen lain.</p>
                <p>Kalo Kamu mengalami kendala saat melakukan sesuatu, harap hubungi orang diatas.</p>
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

<style>
/* Tambahan style jika diperlukan */
</style>