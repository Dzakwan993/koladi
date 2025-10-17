@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <div class="bg-[#e9effd] min-h-screen">
        @include('components.workspace-nav')

        <div class="min-h-screen flex justify-center items-start pt-10 bg-[#f3f6fc] ">
            <div class="bg-white rounded-[8px] shadow-xl p-8 w-full max-w-3xl flex flex-col gap-6">

                <header class="flex justify-between items-start">
                    <div class="flex items-center gap-4">
                        <div class="bg-[#2563eb] rounded-lg p-2">
                            <img src="{{ asset('images/icons/Calendar.svg') }}" alt="Calendar Icon" class="h-8 w-8">
                        </div>
                        <div>
                            <h1 class="font-bold text-xl text-black">Rapat Darurat</h1>
                            <p class="text-sm font-semibold text-[16px] text-[#6B7280]">Dibuat oleh Rendi pada kamis, 12 Sep
                                2025</p>
                        </div>
                    </div>
                    <button class="text-[#6B7280] hover:text-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                </header>

                <hr class="border-black border-1" />

                <div class="flex flex-col gap-4 text-sm">
                    <div class="flex items-start gap-4">
                        <img src="images/icons/jampasir.svg" alt="">
                        <div>
                            <h2 class="font-semibold text-black text-[16px]">Kapan</h2>
                            <p class="font-medium text-[14px] text-[#6B7280]">Selasa 18 Sep 2025, 08:00 PM - Tue Sep 30,
                                09:00 PM (5 days)</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <img src="images/icons/bj1.svg" alt="">
                        <div>
                            <h2 class="font-semibold text-black text-[16px]">Peserta</h2>
                            <div class="flex items-center mt-2">
                                <img src="https://i.pravatar.cc/150?img=1" alt="Peserta 1"
                                    class="h-8 w-8 rounded-full border-2 border-white">
                                <img src="https://i.pravatar.cc/150?img=2" alt="Peserta 2"
                                    class="h-8 w-8 rounded-full border-2 border-white -ml-3">
                                <img src="https://i.pravatar.cc/150?img=3" alt="Peserta 3"
                                    class="h-8 w-8 rounded-full border-2 border-white -ml-3">
                            </div>
                        </div>
                    </div>

                    {{-- <div class="flex items-start gap-4">
          <img src="images/icons/hbj1.svg" alt="">
          <div>
            <h2 class="font-semibold text-black text-[16px]">Tidak ada rapat!!!</h2>
          </div>
        </div> --}}

                    <div class="flex items-start gap-4">
                        <img src="images/icons/Edit.svg" alt="">
                        <div>
                            <h2 class="font-semibold text-black text-[16px]">Catatan</h2>
                            <ul class="list-disc pl-5 mt-1 text-[#6B7280] space-y-1 font-medium text-[14px] ">
                                <li>Agenda</li>
                                <li>Dibuat dalam card box dengan background putih + shadow lembut.</li>
                                <li>Bisa berupa poin-poin bullet supaya rapi.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Komentar start --}}
                <!-- Wrapper utama -->
                <h2 class="font-semibold text-black text-[16px] font-inter">Komentar</h2>

                <div class="flex items-start gap-3">
                    <!-- Avatar di kiri -->
                    <img src="https://i.pravatar.cc/150?img=4" alt="User Avatar"
                        class="h-10 w-10 rounded-full flex-shrink-0 mt-1">

                    <!-- Bagian kanan: toolbar + textarea -->
                    <div class="flex flex-col w-full">
                        <!-- Toolbar -->
                        <div
                            class="flex items-center gap-1 border border-b-0 rounded-t-md bg-gray-50 px-2 py-1 text-sm overflow-x-auto">
                            <!-- Tombol ikon -->
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/1a.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/2a.png" alt="" class="w-6 h-6">
                            </button>

                            <select class="border rounded text-sm px-2 py-1 flex-shrink-0 pr-9">
                                <option>Normal text</option>
                                <option>Heading 1</option>
                                <option>Heading 2</option>
                            </select>

                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/1.png" alt="" class="w-10 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/2.png" alt="" class="w-10 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/3.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/4.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/5.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/6.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/7.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/8.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/9.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/10.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/11.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/12.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/13.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/14.png" alt="" class="w-6 h-6">
                            </button>
                            <button class="hover:bg-gray-200 rounded flex-shrink-0">
                                <img src="images/icons/15.png" alt="" class="w-6 h-6">
                            </button>
                        </div>

                        <!-- Textarea -->
                        <textarea placeholder="Tulis komentar anda disini..."
                            class="border rounded-b-md p-2 h-32 resize-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280] pl-4"></textarea>

                        <!-- Tombol -->
                        <div class="flex gap-2 mt-2">
                            <button
                                class="bg-blue-600 text-white w-[80px] h-[30px] rounded-md hover:bg-blue-700 transition">
                                Kirim
                            </button>
                            <button
                                class="border border-blue-600 text-blue-600 w-[80px] h-[30px] rounded-md hover:bg-blue-50 transition">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
                {{-- End komentar --}}


            </div>
        </div>
    </div>
@endsection
