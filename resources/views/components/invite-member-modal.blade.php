<!-- Modal Undang Orang -->
<div id="inviteModal" class="fixed inset-0 z-50 hidden" onclick="closeInviteModal(event)">
    <!-- Modal Container -->
    <div id="inviteModalContent" class="absolute bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.25)] border border-gray-100" style="width: 350px; left: 50%; transform: translateX(-50%); top: 100px;" onclick="event.stopPropagation()">
        
        <!-- Content -->
        <div class="p-6">
            <!-- Title -->
            <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Undang Orang</h2>
            
            <!-- Description dan Input Area -->
            <div class="bg-gray-100 rounded-lg p-4 mb-5">
                <p class="text-gray-500 text-sm mb-3">
                    Silahkan tuliskan email yang ingin diundang. 1 baris per email ya...
                </p>
                
                <!-- Textarea -->
                <textarea 
                    id="emailInput" 
                    rows="6" 
                    class="w-full bg-white border border-gray-200 rounded-lg p-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="email1@example.com&#10;email2@example.com&#10;email3@example.com"
                ></textarea>
            </div>

            <!-- Button Undang -->
            <button onclick="sendInvite()" class="w-full bg-blue-900 hover:bg-blue-950 text-white py-3 rounded-lg font-semibold transition">
                Undang
            </button>
        </div>

    </div>
</div>

<script>
function closeInviteModal(event) {
    if (event && event.target.id !== 'inviteModal') return;
    document.getElementById('inviteModal').classList.add('hidden');
}

function openInviteModal(event) {
    const modal = document.getElementById('inviteModal');
    const modalContent = document.getElementById('inviteModalContent');
    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();
    
    const modalWidth = 350; // Lebar modal (dari 440 jadi 380)
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

function sendInvite() {
    const emails = document.getElementById('emailInput').value;
    console.log('Undangan dikirim ke:', emails);
    closeInviteModal();
}
</script>