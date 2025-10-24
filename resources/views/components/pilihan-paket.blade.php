<div id="modalPilihPaket"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm font-[Inter,sans-serif]">
    <div class="bg-white rounded-2xl shadow-xl w-[90%] max-w-4xl p-6 relative">
        <!-- Tombol Close -->
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Header -->
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Pilih Paket</h2>

        <!-- Kartu Paket -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
            <!-- Paket Basic -->
            <div
                class="flex flex-col items-center bg-[#EBF1FF] border border-[#D0DAFF] rounded-2xl p-6 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Basic</h3>
                <p class="text-gray-700 text-sm mb-4">Rp. 15.000 / bulan</p>
                <ul class="text-sm text-gray-600 text-left mb-6 space-y-2">
                    <li>✓ Dapat 5 User</li>
                    <li>✓ Penyimpanan 20GB</li>
                    <li>✓ Akses seluruh fitur</li>
                    <li>✓ Tim & Proyek tanpa batas</li>
                </ul>
                <button onclick="openDetailModal(); closeModal();"
                    class="w-full bg-[#4A63E7] text-white font-semibold py-2 rounded-lg hover:bg-[#3a4fc7] transition">
                    Pilih
                </button>
            </div>

            <!-- Paket Standard -->
            <div
                class="flex flex-col items-center bg-[#EBF1FF] border border-[#D0DAFF] rounded-2xl p-6 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Standard</h3>
                <p class="text-gray-700 text-sm mb-4">Rp. 45.000 / bulan</p>
                <ul class="text-sm text-gray-600 text-left mb-6 space-y-2">
                    <li>✓ Dapat 20 User</li>
                    <li>✓ Penyimpanan 15GB</li>
                    <li>✓ Akses seluruh fitur</li>
                    <li>✓ Tim & Proyek tanpa batas</li>
                </ul>
                <button onclick="openDetailModal(); closeModal();"
                    class="w-full bg-[#4A63E7] text-white font-semibold py-2 rounded-lg hover:bg-[#3a4fc7] transition">
                    Pilih
                </button>
            </div>

            <!-- Paket Business -->
            <div
                class="flex flex-col items-center bg-[#EBF1FF] border border-[#D0DAFF] rounded-2xl p-6 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Business</h3>
                <p class="text-gray-700 text-sm mb-4">Rp. 100.000 / bulan</p>
                <ul class="text-sm text-gray-600 text-left mb-6 space-y-2">
                    <li>✓ Dapat 50 User</li>
                    <li>✓ Penyimpanan 30GB</li>
                    <li>✓ Akses seluruh fitur</li>
                    <li>✓ Tim & Proyek tanpa batas</li>
                </ul>
                <button onclick="openDetailModal(); closeModal();"
                    class="w-full bg-[#4A63E7] text-white font-semibold py-2 rounded-lg hover:bg-[#3a4fc7] transition">
                    Pilih
                </button>
            </div>
        </div>

        <!-- Catatan -->
        <div class="mt-6 text-xs text-gray-500">
            <p>*Harga untuk 1 perusahaan</p>
            <p>*Untuk setiap penambahan 1 user dikenakan biaya Rp4.000 / bulan</p>
            <p>*Untuk setiap 1GB penambahan penyimpanan dikenakan biaya Rp3.000 / bulan</p>
        </div>
    </div>
</div>
@include('components.detail-pesanan')   

<script>
    function openModal() {
        const modal = document.getElementById('modalPilihPaket');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('modalPilihPaket');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Tutup modal jika klik di luar kontainer
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('modalPilihPaket');
        if (modal && !modal.classList.contains('hidden') && e.target === modal) {
            closeModal();
        }
    });
</script>
