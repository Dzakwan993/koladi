<div id="modalDetailPesanan"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm font-[Inter,sans-serif]">
    <div class="bg-white rounded-2xl shadow-xl w-[90%] max-w-4xl p-6 relative">

        <!-- Tombol Kembali & Close -->
        <div class="absolute top-4 right-4 left-4 flex justify-between items-center">
            <!-- Tombol Kembali -->
            <button onclick="backToPilihPaket()"
                class="flex items-center gap-1 text-gray-600 hover:text-gray-800 transition text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </button>

            <!-- Tombol Close -->
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Header -->
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center mt-6">Tinjau Pesananmu</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Detail & Addon -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Detail Produk -->
                <div>
                    <h3 class="text-xl font-semibold mb-3">Detail</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500">
                                <th class="text-left py-2">Produk</th>
                                <th class="text-right py-2">Harga</th>
                                <th class="text-center py-2">Jml</th>
                                <th class="text-right py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="py-3">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-800">Paket Basic</span>
                                        <span class="text-xs text-gray-500">22 Okt 2025 - 22 Nov 2025</span>
                                    </div>
                                </td>
                                <td class="text-right py-3 text-gray-700">Rp 45.000</td>
                                <td class="text-center py-3 text-gray-700">1</td>
                                <td class="text-right py-3 text-gray-700">Rp 45.000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Addon -->
                <div>
                    <h3 class="text-xl font-semibold  mb-3">Addon</h3>
                    <div class="space-y-4">
                        <!-- Addon User -->
                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Addon User</p>
                                <p class="text-xs text-gray-500">Rp4.000 / user / bulan</p>
                            </div>
                            <input type="number" min="0"
                                class="w-16 border border-gray-300 rounded-lg text-center text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>

                        <!-- Addon Penyimpanan -->
                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Addon Penyimpanan</p>
                                <p class="text-xs text-gray-500">Rp3.000 / GB / bulan</p>
                            </div>
                            <input type="number" min="0"
                                class="w-16 border border-gray-300 rounded-lg text-center text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <h3 class="text-xl font-semibold  mb-2">Catatan</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Durasi perpanjangan langgananmu akan ditambahkan secara otomatis pada tanggal akhir langgananmu
                        saat ini.
                    </p>
                </div>
            </div>

            <!-- Kolom Kanan: Ringkasan -->
            <div>
                <div class="bg-[#F4F7FF] rounded-xl p-5">
                    <h3 class="text-base font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h3>
                    <div class="flex justify-between text-sm text-gray-700 mb-2">
                        <span>Paket</span>
                        <span>Rp45.000</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-700 mb-2">
                        <span>Addon</span>
                        <span>Rp0</span>
                    </div>
                    <hr class="my-3 border-gray-300">
                    <div class="flex justify-between items-center text-base font-bold text-gray-900 mb-4">
                        <span>Total</span>
                        <span>Rp45.000</span>
                    </div>
                    <button
                        class="w-full bg-yellow-400 hover:bg-yellow-500 text-white font-semibold py-2 rounded-lg transition">
                        Selanjutnya
                    </button>
                    <p class="text-xs text-gray-500 mt-3 leading-snug">
                        Setelah klik <strong>Selanjutnya</strong>, kamu akan dialihkan ke halaman pemilihan metode
                        bayar.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openDetailModal() {
        const modal = document.getElementById('modalDetailPesanan');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDetailModal() {
        const modal = document.getElementById('modalDetailPesanan');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function backToPilihPaket() {
        closeDetailModal();
        const modalPilih = document.getElementById('modalPilihPaket');
        modalPilih.classList.remove('hidden');
        modalPilih.classList.add('flex');
    }

    // Tutup modal saat klik di luar
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('modalDetailPesanan');
        if (modal && !modal.classList.contains('hidden') && e.target === modal) {
            closeDetailModal();
        }
    });
</script>
