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
  <img
    src="{{ asset('images/icons/Calendar.svg') }}"
    alt="Calendar Icon"
    class="h-8 w-8">
</div>
          <div>
            <h1 class="font-bold text-xl text-black">Rapat Darurat</h1>
            <p class="text-sm font-semibold text-[16px] text-[#6B7280]">Dibuat oleh Rendi pada kamis, 12 Sep 2025</p>
          </div>
        </div>
        <button class="text-[#6B7280] hover:text-gray-800">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
          </svg>
        </button>
      </header>

      <hr class="border-black border-1" />

      <div class="flex flex-col gap-4 text-sm">
        <div class="flex items-start gap-4">
          <img src="images/icons/jampasir.svg" alt="">
          <div>
            <h2 class="font-semibold text-black text-[16px]">Kapan</h2>
            <p class="font-medium text-[14px] text-[#6B7280]">Selasa 18 Sep 2025, 08:00 PM - Tue Sep 30, 09:00 PM (5 days)</p>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <img src="images/icons/bj1.svg" alt="">
          <div>
            <h2 class="font-semibold text-black text-[16px]">Peserta</h2>
            <div class="flex items-center mt-2">
              <img src="https://i.pravatar.cc/150?img=1" alt="Peserta 1" class="h-8 w-8 rounded-full border-2 border-white">
              <img src="https://i.pravatar.cc/150?img=2" alt="Peserta 2" class="h-8 w-8 rounded-full border-2 border-white -ml-3">
              <img src="https://i.pravatar.cc/150?img=3" alt="Peserta 3" class="h-8 w-8 rounded-full border-2 border-white -ml-3">
            </div>
          </div>
        </div>

        <div class="flex items-start gap-4">
          <img src="images/icons/hbj1.svg" alt="">
          <div>
            <h2 class="font-semibold text-black text-[16px]">Rapat dilakukan dengan online</h2>
            <button @click="openPopup = true" class="mt-2 bg-[#2563eb] text-white font-semibold py-1 px-2 rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-1 ">
            <img src="/images/icons/ZoomPutih.svg" alt="Zoom Icon" class="w-[28px] h-[28px]">
            <span>Gabung rapat</span>
        </button>
          </div>
        </div>

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

      <div class="flex flex-col gap-4">
        <h2 class="font-semibold text-black text-[16px]">Komentar</h2>
        <div class="flex items-center gap-3">
          <img src="https://i.pravatar.cc/150?img=4" alt="User Avatar" class="h-9 w-9 rounded-full">
          <input type="text" placeholder="Tambahkan komentar baru....." class="w-full border border-[#6B7280] rounded-lg py-2 px-4 text-sm focus:outline-none focus:ring-2 placeholder-[#6B7280] focus:ring-[#2563eb]">
        </div>
        <p class="text-center font-semibold text-[#000000] text-sm mt-2">Belum ada komentar disini...</p>
      </div>

      </div>
      {{-- KODE POP UP --}}
    {{-- Perbaikan: Menghapus style="display: none;" --}}
    <div x-show="openPopup" x-transition class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
      <div @click.away="openPopup = false" class="bg-[#f3f6fc] rounded-2xl shadow-lg p-8 w-full max-w-sm text-center">
        <img src="images/icons/teamimage.svg" alt="Ilustrasi rapat" class="w-48 mx-auto mb-6">
        <h2 class="text-xl font-medium text-black text-[16px] ">
          Apakah anda ingin bergabung dengan rapat?
        </h2>
        <div class="flex justify-center gap-4 mt-5">
          <button class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-8 rounded-lg transition-colors">
            Batal
          </button>
          <button class="bg-blue-500 hover:bg-blue-800 text-white font-semibold py-2 px-10 rounded-lg transition-colors">
            Ya
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
