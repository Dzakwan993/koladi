@extends('layouts.app')

@section('title', 'Pengajuan Cuti Manajer')

@section('content')
    <div class="bg-[#f3f6fc] min-h-screen">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav')

        <div class="container mx-auto py-8 px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 max-w-5xl mx-auto">
                {{-- Header atas --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2 w-1/3">
                        <input type="text" placeholder="Cari pegawai..."
                            class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none">
                        <button class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200">
                            🔍
                        </button>
                    </div>
                    <div class="flex gap-3">
                        <button
                            class="flex items-center gap-2 px-4 py-2 bg-[#10b981] text-white rounded-lg hover:bg-green-600">
                            <img src="/images/icons/approval.svg" alt="approval icon" class="w-5 h-5">
                            Approval
                        </button>
                        <button onclick="openModalCuti()"
                            class="bg-[#2563eb] text-white px-5 py-2.5 rounded-lg flex items-center gap-2 hover:bg-[#1d4ed8] transition-all duration-200 shadow-sm hover:shadow-md">
                            <img src="/images/icons/cuti.svg" alt="cuti icon" class="w-5 h-5">
                            Ajukan Cuti
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto -mx-6 px-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="py-3.5 px-4 text-left font-bold">Pegawai</th>
                                <th class="py-3.5 px-4 text-left font-bold">Tanggal Pengajuan</th>
                                <th class="py-3.5 px-4 text-left font-bold">Status</th>
                                <th class="py-3.5 px-4 text-left font-bold">Tinjau</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">Sahr0ni</td>
                                <td class="px-4 py-3">15 Sep 2025</td>
                                <td class="px-4 py-3">
                                    <span class="bg-[#fbbf24] text-white px-3 py-1.5 rounded-full text-xs">Pending</span>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="openTinjauModal()" class="text-blue-600 hover:text-blue-800">
                                        <img src="/images/icons/tinjau.svg" alt="tinjau">
                                    </button>
                                </td>
                            </tr>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">Risi</td>
                                <td class="px-4 py-3">14 Sep 2025</td>
                                <td class="px-4 py-3">
                                    <span class="bg-[#10b981] text-white px-3 py-1.5 rounded-full text-xs">Diterima</span>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="openTinjauModal()" class="text-blue-600 hover:text-blue-800">
                                        <img src="/images/icons/tinjau.svg" alt="tinjau">
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">Rendi</td>
                                <td class="px-4 py-3">7 Sep 2025</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-[#f87171] text-white px-3 py-1.5 rounded-full text-xs">
                                            Ditolak
                                        </span>
                                        <button
                                            onclick="openModalAlasan('Bapaknya Sahr0ni', 'Kamu bau saya gak terima pengajuan kamu', '/images/avatar.jpg')"
                                            class="w-7 h-7 rounded bg-[#f87171] flex items-center justify-center hover:bg-[#fca5a5] transition-colors"
                                            title="Lihat alasan penolakan">
                                            <img src="/images/icons/tolakdokumen.svg" alt="tolak icon" class="w-4 h-4">
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <button onclick="openTinjauModal()" class="text-blue-600 hover:text-blue-800">
                                        <img src="/images/icons/tinjau.svg" alt="tinjau">
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Form Ajukan Cuti --}}
    <div id="modalCuti" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">

        <!-- Konten Modal -->
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
            onclick="event.stopPropagation()">

            {{-- Header Modal --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-center gap-2">
                    <img src="/images/icons/cuti.svg" alt="cuti icon" class="w-5 h-5">
                    <h3 class="text-lg font-semibold text-gray-900">Ajukan Cuti</h3>
                </div>
            </div>

            {{-- Form Content --}}
            <form class="p-6 space-y-5">
                {{-- Tanggal Cuti --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Cuti</label>
                    <div class="flex items-center gap-3">
                        <input type="date"
                            class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        <span class="text-gray-500 text-sm font-medium">Sampai</span>
                        <input type="date"
                            class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                </div>

                {{-- Jenis Cuti --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Cuti</label>
                    <select
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Pilih jenis cuti</option>
                        <option value="tahunan">Cuti Tahunan</option>
                        <option value="sakit">Cuti Sakit</option>
                        <option value="penting">Cuti Penting</option>
                        <option value="melahirkan">Cuti Melahirkan</option>
                    </select>
                </div>

                {{-- Alasan Cuti --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Cuti</label>
                    <textarea rows="4"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm resize-none"
                        placeholder="Tulis alasan cuti Anda..."></textarea>
                </div>

                {{-- Lampiran (Bukti) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (Bukti)</label>
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                        <input type="file" class="hidden" id="fileUpload" accept=".pdf,.jpg,.jpeg,.png"
                            onchange="showFileName(event)">
                        <label for="fileUpload" class="cursor-pointer">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-sm text-gray-600 mb-1">You can drag and drop files here to add them.</p>
                            <p class="text-xs text-gray-500">atau klik untuk memilih file</p>
                        </label>
                    </div>
                    <p id="fileName" class="mt-2 text-sm text-gray-600"></p>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModalCuti()"
                        class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-[#6b7280] text-white rounded-lg hover:bg-[#4b5563] font-medium">Kirim</button>
                </div>
            </form>
        </div>
    </div>


    {{-- Modal Alasan Ditolak --}}
    <div id="modalAlasan" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-[#f0f5ff] rounded-2xl shadow-2xl max-w-xl w-full max-h-[90vh] overflow-y-auto">
            {{-- Header --}}
            <div
                class="sticky top-0 bg-[#f0f5ff] border-b border-gray-300 px-6 py-4 rounded-t-2xl flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Alasan Ditolak</h3>
            </div>

            {{-- Isi Modal --}}
            <div class="p-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <img id="alasanFoto" src="https://ui-avatars.com/api/?name=User"
                            class="w-10 h-10 rounded-full border">
                        <p id="alasanNama" class="font-semibold text-gray-900">Nama Penanggung Jawab</p>
                    </div>
                    <p id="alasanText" class="text-gray-700 text-sm leading-relaxed">
                        Alasan penolakan akan tampil di sini...
                    </p>
                </div>
            </div>

            {{-- Tombol Tutup --}}
            <div class="px-6 pb-6 flex justify-end">
                <button onclick="closeModalAlasan()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Approval/Tinjau --}}
    <div id="tinjauModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

        <!-- Konten Modal -->
        <div class="bg-white rounded-xl shadow-lg w-full max-w-xl p-6 overflow-y-auto max-h-[90vh]"
            onclick="event.stopPropagation()">

            <!-- Judul Center -->
            <h2 class="text-xl font-bold mb-6 text-center flex items-center justify-center gap-2">
                📝 Approval Cuti
            </h2>

            <form>
                <!-- Nama Pengaju -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Nama Pengaju</label>
                    <input type="text" class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                        value="Sahr0ni Bin Sahr0ni" readonly>
                </div>

                <!-- Tanggal -->
                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Cuti</label>
                        <input type="text" class="w-full border rounded-lg px-3 py-2 bg-gray-100" value="15 Sep 2025"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Sampai</label>
                        <input type="text" class="w-full border rounded-lg px-3 py-2 bg-gray-100" value="15 Sep 2025"
                            readonly>
                    </div>
                </div>

                <!-- Jenis Cuti -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Jenis Cuti</label>
                    <input type="text" class="w-full border rounded-lg px-3 py-2 bg-gray-100" value="Sakit" readonly>
                </div>

                <!-- Alasan Cuti -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Alasan Cuti</label>
                    <textarea class="w-full border rounded-lg px-3 py-2 bg-gray-100" rows="3" readonly>Jenguk orang rumah sakit</textarea>
                </div>

                <!-- Lampiran -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Lampiran</label>
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Ukuran</th>
                                    <th class="px-3 py-2">Jenis</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t">
                                    <td class="px-3 py-2 text-blue-600 underline cursor-pointer">TOR_ProyekA.pdf</td>
                                    <td class="px-3 py-2">9.6 MB</td>
                                    <td class="px-3 py-2">PDF</td>
                                </tr>
                                <tr class="border-t">
                                    <td class="px-3 py-2 text-blue-600 underline cursor-pointer">TOR_ProyekB.pdf</td>
                                    <td class="px-3 py-2">9.6 MB</td>
                                    <td class="px-3 py-2">PDF</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Alasan Ditolak -->
                <div class="mb-4">
                    <label class="block text-sm font-medium">Alasan Ditolak</label>
                    <textarea class="w-full border rounded-lg px-3 py-2" rows="3"></textarea>
                </div>

                <!-- Tombol -->
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeTinjauModal()"
                        class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">Batal</button>
                    <button type="button"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Tolak</button>
                    <button type="button"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Terima</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script Modal Approval Cuti --}}
    @push('scripts')
        <script>
            function openTinjauModal() {
                const modal = document.getElementById('tinjauModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeTinjauModal() {
                const modal = document.getElementById('tinjauModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }

            // Tutup modal kalau klik di luar konten
            document.getElementById('tinjauModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeTinjauModal();
                }
            });

            // Tutup modal dengan tombol ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeTinjauModal();
                }
            });
        </script>
    @endpush

    {{-- Script Modal Cuti --}}
    @push('scripts')
        <script>
            function openModalCuti() {
                document.getElementById('modalCuti').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeModalCuti() {
                document.getElementById('modalCuti').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Close modal when clicking outside
            document.getElementById('modalCuti').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModalCuti();
                }
            });

            // Close modal with ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModalCuti();
                }
            });

            function showFileName(event) {
                const file = event.target.files[0];
                if (file) {
                    document.getElementById('fileName').innerText = "📄 " + file.name;
                }
            }
        </script>
    @endpush

    {{-- Script Modal Alasan Ditolak --}}
    @push('scripts')
        <script>
            function openModalAlasan(nama, alasan, foto = null) {
                document.getElementById('modalAlasan').classList.remove('hidden');
                document.getElementById('modalAlasan').classList.add('flex');
                document.body.style.overflow = 'hidden';

                document.getElementById('alasanNama').innerText = nama;
                document.getElementById('alasanText').innerText = alasan;

                if (foto) {
                    document.getElementById('alasanFoto').src = foto;
                }
            }

            function closeModalAlasan() {
                document.getElementById('modalAlasan').classList.add('hidden');
                document.getElementById('modalAlasan').classList.remove('flex');
                document.body.style.overflow = 'auto';
            }

            // Tutup modal jika klik di luar
            document.getElementById('modalAlasan').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModalAlasan();
                }
            });

            // Tutup modal dengan ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModalAlasan();
                }
            });
        </script>
    @endpush

@endsection
