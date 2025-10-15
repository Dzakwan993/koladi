<!-- Modal Hapus Anggota -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden" onclick="closeDeleteModal(event)">
    <!-- Modal Container -->
    <div id="deleteModalContent" class="absolute bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.25)] border border-gray-100" style="width: 320px; left: 50%; transform: translateX(-50%); top: 100px;" onclick="event.stopPropagation()">
        
        <!-- Header dengan Close Button -->
        <div class="flex justify-end p-3 pb-0">
            <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="px-6 pb-6 pt-1">
            <!-- Title -->
            <h2 class="text-lg font-bold text-gray-900 mb-3">Hapus Anggota?</h2>
            
            <!-- Description -->
            <p class="text-gray-600 text-sm mb-5 leading-relaxed">
                Anggota ini ga akan punya akses lagi ke perusahaan ini.
            </p>

            <!-- Button Hapus -->
            <button onclick="confirmDelete()" class="w-full bg-red-400 hover:bg-red-500 text-white py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
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
    
    const modalWidth = 320; // Lebar modal
    const rightSpace = window.innerWidth - rect.right;
    
    // Posisikan modal tepat di bawah button
    modalContent.style.top = (rect.bottom + 10) + 'px';
    
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