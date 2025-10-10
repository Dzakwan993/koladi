@extends('layouts.app')

@section('title', 'Pengajuan Cuti Karyawan')

@section('content')
    <div class="bg-[#f3f6fc] min-h-screen">
        {{-- Workspace Nav --}}
        @include('components.workspace-nav')

        <div class="container mx-auto py-8 px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 max-w-5xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Pengajuan Cuti Karyawan</h2>
                    <button onclick="openModalCuti()"
                        class="bg-[#2563eb] text-white px-5 py-2.5 rounded-lg flex items-center gap-2 hover:bg-[#1d4ed8] transition-all duration-200 shadow-sm hover:shadow-md">
                        <img src="/images/icons/cuti.svg" alt="cuti icon" class="w-5 h-5">
                        Ajukan Cuti
                    </button>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto -mx-6 px-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="py-3.5 px-4 text-left font-bold ">
                                    <div class="flex items-center gap-2">
                                        Tanggal Pengajuan
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </th>
                                <th class="py-3.5 px-4 text-left font-bold">Penanggung Jawab</th>
                                <th class="py-3.5 px-4 text-left font-bold">
                                    <div class="flex items-center gap-2">
                                        Status
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Static data untuk tampilan front-end --}}
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-4">15 Sep 2025</td>
                                <td class="py-4 px-4">-</td>
                                <td class="py-4 px-4">
                                    <span
                                        class="bg-[#fbbf24] text-white px-4 py-1.5 rounded-full text-xs font-semibold inline-block">
                                        Pending
                                    </span>
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-4 text-gray-800">15 Sep 2025</td>
                                <td class="py-4 px-4 text-gray-800">Risky Sapriadi</td>
                                <td class="py-4 px-4">
                                    <span
                                        class="bg-[#10b981] text-white px-4 py-1.5 rounded-full text-xs font-semibold inline-block">
                                        Diterima
                                    </span>
                                </td>
                            </tr>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-4 text-gray-800">15 Sep 2025</td>
                                <td class="py-4 px-4 text-gray-800">Risky Sapriadi</td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="bg-[#f87171] text-white px-4 py-1.5 rounded-full text-xs font-semibold inline-block">
                                            Ditolak
                                        </span>
                                        <button
                                            onclick="openModalAlasan('Bapaknya Sahr0ni', 'Kamu bau saya gak terima pengajuan kamu', '/images/avatar.jpg')"
                                            class="w-6 h-6 rounded bg-[#f87171] flex items-center justify-center hover:bg-[#fca5a5] transition-colors"
                                            title="Lihat alasan penolakan">
                                            <img src="/images/icons/tolakdokumen.svg" alt="tolak icon" class="w-3.5 h-3.5">
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            {{-- Nanti saat ada backend, uncomment code di bawah dan hapus static data di atas --}}
                            {{-- @forelse($leaveRequests ?? [] as $request)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4 text-gray-800">{{ $request->tanggal_pengajuan }}</td>
                                    <td class="py-4 px-4 text-gray-800">{{ $request->penanggung_jawab ?? '-' }}</td>
                                    <td class="py-4 px-4">
                                        @if ($request->status == 'pending')
                                            <span class="bg-[#fbbf24] text-white px-4 py-1.5 rounded-full text-xs font-semibold inline-block">
                                                Pending
                                            </span>
                                        @elseif($request->status == 'diterima')
                                            <span class="bg-[#10b981] text-white px-4 py-1.5 rounded-full text-xs font-semibold inline-block">
                                                Diterima
                                            </span>
                                        @elseif($request->status == 'ditolak')
                                            <div class="flex items-center gap-2">
                                                <span class="bg-[#f87171] text-white px-4 py-1.5 rounded-full text-xs font-semibold inline-block">
                                                    Ditolak
                                                </span>
                                                <button class="w-6 h-6 rounded bg-[#fecaca] flex items-center justify-center hover:bg-[#fca5a5] transition-colors" title="Lihat alasan penolakan">
                                                    <img src="/images/icons/tolakdokumen.svg" alt="tolak icon" class="w-3.5 h-3.5">
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-500">
                                        Belum ada pengajuan cuti
                                    </td>
                                </tr>
                            @endforelse --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Form Ajukan Cuti --}}
    <div id="modalCuti" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
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
                        <div class="flex-1 relative">
                            <input type="date"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="DD/MM/YYYY">

                        </div>
                        <span class="text-gray-500 text-sm font-medium">Sampai</span>
                        <div class="flex-1 relative">
                            <input type="date"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="DD/MM/YYYY">
                        </div>
                    </div>
                </div>

                {{-- Jenis Cuti --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Cuti</label>
                    <div class="relative">
                        <select
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm appearance-none">
                            <option value="">Pilih jenis cuti</option>
                            <option value="tahunan">Cuti Tahunan</option>
                            <option value="sakit">Cuti Sakit</option>
                            <option value="penting">Cuti Penting</option>
                            <option value="melahirkan">Cuti Melahirkan</option>
                        </select>
                    </div>
                </div>

                {{-- Alasan Cuti --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Cuti</label>
                    <textarea rows="4"
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm resize-none"
                        placeholder="Tulis alasan cuti Anda..."></textarea>
                </div>

                {{-- Lampiran (Bukti) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (Bukti)</label>
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer">
                        <input type="file" class="hidden" id="fileUpload" accept=".pdf,.jpg,.jpeg,.png">
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
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModalCuti()"
                        class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-[#6b7280] text-white rounded-lg hover:bg-[#4b5563] transition-colors font-medium">
                        Kirim
                    </button>
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
        </script>
    @endpush

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
