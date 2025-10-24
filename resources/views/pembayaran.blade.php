@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Daftar Perusahaan -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Daftar perusahaanmu</h3>
                    <div class="space-y-2">
                        <div
                            class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200 cursor-pointer hover:bg-blue-100 transition">
                            <img src="{{ asset('images/icons/bayar-perusahaan.svg') }}" class="w-8 h-8" alt="Company Icon">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">PT. Mencari Cinta Sejati</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                            <img src="{{ asset('images/icons/bayar-perusahaan.svg') }}" class="w-8 h-8" alt="Company Icon">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700 truncate">PT. Anugerah Keramik</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card Paket Langganan dan Pemakaian -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Paket Langganan -->
                    <div class="bg-white rounded-lg shadow-lg p-5">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-sm font-semibold text-gray-700">Paket Langganan</h3>
                            <span class="bg-red-500 text-white text-xs font-semibold px-3 py-1 rounded">Berakhir</span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-1">7 hari trial gratis</h2>
                        <p class="text-xs text-gray-500 mb-4">Terjadi selanjutnya 27 Okt 2025 (5 hari tersisa)</p>
                        <button onclick="openModal()"
                            class="bg-[#5FD0AB] hover:bg-[#4dbf9a] text-white text-sm font-semibold py-2 px-5 rounded-full transition-all shadow-sm hover:shadow-md">
                            Perpanjang
                        </button>
                    </div>

                    <!-- Pemakaian Anggota -->
                    <div class="bg-white rounded-lg shadow-lg p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Pemakaian Anggota</h3>
                        <div class="flex items-end gap-2 mb-2">
                            <h2 class="text-4xl font-bold text-gray-800">5</h2>
                            <span class="text-2xl font-semibold text-gray-400 mb-1">/30</span>
                        </div>
                        <p class="text-xs text-gray-500">Kamu bisa undang 25 orang lagi</p>
                    </div>
                </div>

                <!-- Tabel Kultansi Perpanjangan Paket -->
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-5 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-700">Kuitansi Perpanjangan Paket</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-5 text-xs font-semibold text-gray-600">No</th>
                                    <th class="text-left py-3 px-5 text-xs font-semibold text-gray-600">Status</th>
                                    <th class="text-left py-3 px-5 text-xs font-semibold text-gray-600">Tanggal</th>
                                    <th class="text-left py-3 px-5 text-xs font-semibold text-gray-600">Paket</th>
                                    <th class="text-left py-3 px-5 text-xs font-semibold text-gray-600">Total</th>
                                    <th class="text-left py-3 px-5 text-xs font-semibold text-gray-600">Link Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-5 text-sm text-gray-700">A001</td>
                                    <td class="py-3 px-5">
                                        <span
                                            class="bg-red-500 text-white text-xs font-medium px-3 py-1 rounded-full">Lewat</span>
                                    </td>
                                    <td class="py-3 px-5 text-sm text-gray-700">-</td>
                                    <td class="py-3 px-5 text-sm text-gray-700">-</td>
                                    <td class="py-3 px-5 text-sm text-gray-700">-</td>
                                    <td class="py-3 px-5">
                                        <button
                                            class="bg-white text-[#5FD0AB] text-sm font-medium px-5 py-1.5 rounded-full hover:bg-[#5FD0AB] hover:text-white transition-all border-2 border-[#5FD0AB]">
                                            Buka Link
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilih Paket -->
    @include('components.pilihan-paket')

    <script>
        function openModal() {
            document.getElementById('modalPilihPaket').classList.remove('hidden');
            document.getElementById('modalPilihPaket').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('modalPilihPaket').classList.add('hidden');
            document.getElementById('modalPilihPaket').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('modalPilihPaket').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
@endsection
