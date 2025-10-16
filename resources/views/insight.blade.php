@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <!-- Tambahkan font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative">
        @include('components.workspace-nav')

        <div class="justify-center max-w-7xl pt-6 mx-24 f">
            <!-- Tombol Buat Pengumuman -->
            <div class="flex justify-start mb-1">
                <button id="btnPopup"
                    class="bg-blue-700 text-white px-3 py-2 text-sm rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
                    <img src="images/icons/tambah.svg" alt="Plus" class="w-6 h-6">
                    Buat Insight
                </button>
            </div>

            <div class="border-t border-black my-4"></div>


            <!-- Daftar Pengumuman -->
            <div class="  mt-4">
                <div class="bg-white rounded-2xl shadow-md p-6 h-[500px] overflow-hidden">
                    <div class="space-y-4 h-full overflow-y-auto pr-2">

                        <!-- Card Pengumuman 1 -->
                        <a href="{{ route('isi-insight') }}" class="block">
                            <div class="bg-[#dde5f4] rounded-xl shadow-sm p-5 flex justify-between items-start hover:bg-[#cfd8ec] transition">
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <p class="text-base text-[#1e3a8a] font-medium mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                        <p class="text-[#1e3a8a] font-bold text-xl">Lapor Progres!</p>
                                    </div>
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-9 h-9 ">
                                        <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-9 h-9 -ml-3 ">
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-[#60a5fa] text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">2</span>
                                </div>
                            </div>
                        </a>

                        <!-- Card Pengumuman 2 -->
                        <a href="{{ route('isi-insight') }}" class="block">
                            <div class="bg-[#dde5f4] rounded-xl shadow-sm p-5 flex justify-between items-start hover:bg-[#cfd8ec] transition">
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <p class="text-base text-[#1e3a8a] font-medium mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                        <p class="text-[#1e3a8a] font-bold text-xl">Lapor Progres!</p>
                                    </div>
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-9 h-9 ">
                                        <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-9 h-9 -ml-3 ">
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-[#60a5fa] text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">2</span>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('isi-insight') }}" class="block">
                            <div class="bg-[#dde5f4] rounded-xl shadow-sm p-5 flex justify-between items-start hover:bg-[#cfd8ec] transition">
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <p class="text-base text-[#1e3a8a] font-medium mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                        <p class="text-[#1e3a8a] font-bold text-xl">Lapor Progres!</p>
                                    </div>
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-9 h-9 ">
                                        <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-9 h-9 -ml-3 ">
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-[#60a5fa] text-white text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full">2</span>
                                </div>
                            </div>
                        </a>

                       

                    </div>
                </div>
            </div>
        </div>
        @include('components.insight-modal')

@endsection
