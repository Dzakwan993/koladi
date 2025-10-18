<!-- Modal Hapus Anggota -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<div id="deleteModal" class="fixed inset-0 z-50 hidden font-[Inter,sans-serif]" onclick="closeDeleteModal(event)">
    <!-- Modal Container -->
    <div id="deleteModalContent" class="absolute bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.25)] border border-gray-100" style="width: 90%; max-width: 320px; left: 50%; transform: translateX(-50%); top: 100px;" onclick="event.stopPropagation()">
        
        <!-- Header dengan Close Button -->
        <div class="flex justify-end p-2 sm:p-3 pb-0">
            <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="px-4 sm:px-6 pb-5 sm:pb-6 pt-1">
            <!-- Title -->
            <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-2 sm:mb-3">Hapus Anggota?</h2>
            
            <!-- Description -->
            <p class="text-gray-600 text-xs sm:text-sm mb-4 sm:mb-5 leading-relaxed">
                Anggota ini ga akan punya akses lagi ke perusahaan ini.
            </p>

            <!-- Button Hapus -->
            <button onclick="confirmDelete()" class="w-full bg-[#E26767] hover:bg-red-400 text-white py-2.5 sm:py-3 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-2">
                <img src="{{ asset('images/icons/trash-can.svg') }}" alt="Hapus" class="w-4 h-4 sm:w-5 sm:h-5 brightness-0 invert" />
                Hapus
            </button>
        </div>

    </div>
</div>

<script>
function closeDeleteModal(event) {
    if (event && event.target.id !== 'deleteModal') return;
    document.getElementById('deleteModal').classList.add('hidden');
}

function openDeleteModal(event) {
    const modal = document.getElementById('deleteModal');
    const modalContent = document.getElementById('deleteModalContent');
    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();
    
    const modalWidth = 320;
    const modalHeight = 220; // Perkiraan tinggi modal
    const rightSpace = window.innerWidth - rect.right;
    const bottomSpace = window.innerHeight - rect.bottom;
    const gap = 10; // Jarak antara button dan modal
    
    // Cek apakah ada cukup ruang di bawah
    if (bottomSpace >= (modalHeight + gap)) {
        // Tampilkan di bawah button
        modalContent.style.top = (rect.bottom + gap) + 'px';
    } else {
        // Tampilkan di atas button
        modalContent.style.top = (rect.top - modalHeight - gap) + 'px';
    }
    
    // Cek apakah ada cukup ruang di kanan
    if (rightSpace >= modalWidth) {
        // Align kanan dengan button kanan
        modalContent.style.left = (rect.right - modalWidth) + 'px';
    } else {
        // Align dengan ujung kanan layar (dengan padding 16px)
        modalContent.style.left = (window.innerWidth - modalWidth - 16) + 'px';
    }
    
    modalContent.style.transform = 'none';
    
    modal.classList.remove('hidden');
}

function confirmDelete() {
    console.log('Anggota dihapus');
    closeDeleteModal();
}
</script>