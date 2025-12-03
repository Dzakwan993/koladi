@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative">
        @include('components.workspace-nav')

        <div class="justify-center max-w-7xl pt-4 sm:pt-6 mx-4 sm:mx-6 md:mx-12 lg:mx-16 xl:mx-24">
            <!-- Tombol Buat Pengumuman -->
            <div class="flex justify-start mb-1">
                <button id="btnPopup"
                    class="bg-blue-700 text-white px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-1.5 sm:gap-2">
                    <img src="images/icons/tambah.svg" alt="Plus" class="w-5 h-5 sm:w-6 sm:h-6">
                    Buat Insight
                </button>
            </div>

            <div class="border-t border-black my-3 sm:my-4"></div>


            <!-- Daftar Pengumuman -->
            <div class="mt-3 sm:mt-4">
                <div class="bg-white rounded-xl sm:rounded-2xl shadow-md p-4 sm:p-5 md:p-6 h-[400px] sm:h-[450px] md:h-[500px] overflow-hidden">
                    <div class="space-y-3 sm:space-y-4 h-full overflow-y-auto pr-1 sm:pr-2">

                        <!-- Card Pengumuman 1 -->
                        <a href="{{ route('isi-insight') }}" class="block">
                            <div class="bg-[#dde5f4] rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5 flex justify-between items-start hover:bg-[#cfd8ec] transition">
                                <div class="flex flex-col gap-2 sm:gap-3">
                                    <div>
                                        <p class="text-sm sm:text-base text-[#102A63] font-semibold mb-0.5 sm:mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                        <p class="text-[#102A63] font-bold text-lg sm:text-xl">Lapor Progres!</p>
                                    </div>
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9">
                                        <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9 -ml-2 sm:-ml-3">
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-[#FBD644] text-black text-xs sm:text-sm font-bold w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center rounded-full">2</span>
                                </div>
                            </div>
                        </a>

                        <!-- Card Pengumuman 2 -->
                        <a href="{{ route('isi-insight') }}" class="block">
                            <div class="bg-[#dde5f4] rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5 flex justify-between items-start hover:bg-[#cfd8ec] transition">
                                <div class="flex flex-col gap-2 sm:gap-3">
                                    <div>
                                        <p class="text-sm sm:text-base text-[#102A63] font-semibold mb-0.5 sm:mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                        <p class="text-[#102A63] font-bold text-lg sm:text-xl">Lapor Progres!</p>
                                    </div>
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9">
                                        <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9 -ml-2 sm:-ml-3">
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-[#FBD644] text-black text-xs sm:text-sm font-bold w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center rounded-full">2</span>
                                </div>
                            </div>
                        </a>

                        <!-- Card Pengumuman 3 -->
                        <a href="{{ route('isi-insight') }}" class="block">
                            <div class="bg-[#dde5f4] rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5 flex justify-between items-start hover:bg-[#cfd8ec] transition">
                                <div class="flex flex-col gap-2 sm:gap-3">
                                    <div>
                                        <p class="text-sm sm:text-base text-[#102A63] font-semibold mb-0.5 sm:mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                        <p class="text-[#102A63] font-bold text-lg sm:text-xl">Lapor Progres!</p>
                                    </div>
                                    <div class="flex items-center">
                                        <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9">
                                        <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9 -ml-2 sm:-ml-3">
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-[#FBD644] text-black text-xs sm:text-sm font-bold w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center rounded-full">2</span>
                                </div>
                            </div>
                        </a>

                    </div>
                </div>
            </div>
        </div>
        @include('components.insight-modal')
    </div>
@endsection
