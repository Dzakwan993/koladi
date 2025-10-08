@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
<div class="bg-[#e9effd] min-h-screen">
    @include('components.workspace-nav')

<div class="min-h-screen flex justify-center pt-10 bg-[#f3f6fc]">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-3xl flex flex-col gap-6">

    <!-- Judul -->
    <h2 class="text-xl font-inter font-bold text-[20px] text-[#102a63] border-b pb-2">Buat Jadwal</h2>

    <!-- Nama Jadwal -->
<div class="flex flex-col font-inter">
  <label class="mb-1 font-medium text-[16px] text-black">Nama Jadwal <span class="text-red-500">*</span></label>
  <input
    type="text"
    placeholder="Masukkan nama jadwal..."
    class="border rounded-md pl-5 py-2 focus:outline-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280]"
  />
</div>

<!-- Tanggal & Waktu Mulai dan Akhir -->
<div class="grid grid-cols-2 gap-4">
  <!-- Mulai -->
  <div class="flex flex-col font-inter">
    <label class="mb-1 font-medium text-[16px] text-black">Mulai <span class="text-red-500">*</span></label>
    <div class="flex gap-2">
      <input
        type="date"
        class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
      />
      <input
        type="time"
        class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
      />
    </div>
  </div>

  <!-- Akhir -->
  <div class="flex flex-col font-inter">
    <label class="mb-1 font-medium text-[16px] text-black">Akhir <span class="text-red-500">*</span></label>
    <div class="flex gap-2">
      <input
        type="date"
        class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
      />
      <input
        type="time"
        class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
      />
    </div>
  </div>
</div>



    <!-- Pengulangan -->
    <div class="flex flex-col font-sans">
  <label for="pengulangan-btn" class="mb-1 font-medium text-base text-black">
    Pengulangan<span class="text-red-500">*</span>
  </label>

  <div x-data="{
      open: false,
      options: ['Jangan Ulangi', 'Setiap hari', 'Setiap minggu', 'Setiap kuartal', 'Setiap tahun', 'Setiap hari kerja (Sen - Jum)'],
      selected: 'Jangan Ulangi'
    }"
    class="relative w-full md:w-1/3"
  >
    <button
      @click="open = !open"
      @click.away="open = false"
      id="pengulangan-btn"
      class="w-full flex items-center justify-between text-left bg-white border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
      <span class="flex items-center bg-gray-100 text-gray-700 text-sm font-medium px-2 py-1 rounded">
        <span x-text="selected"></span>
        <svg @click.stop="selected = 'Jangan Ulangi'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2 text-gray-500 hover:text-gray-800 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </span>

      <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </button>

    <div
      x-show="open"
      x-transition
      class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200"
    >
      <ul class="py-1">
        <template x-for="option in options" :key="option">
          <li
            @click="selected = option; open = false"
            class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
            x-text="option">
          </li>
        </template>
      </ul>
    </div>
  </div>
</div>

    <!-- Peserta -->
<div class="flex flex-col gap-2">
  <label class="mb-1 font-medium text-[16px] text-black">Peserta <span class="text-red-500">*</span></label>

  <div x-data="{ openPopup: false }">
  <!-- Foto peserta -->
  <div class="flex items-center gap-2">
    <img src="images/dk.jpg" class="rounded-full border w-10 h-10"/>
    <img src="images/dk.jpg" class="rounded-full border w-10 h-10"/>
    <!-- Tombol tambah peserta -->
    <button @click="openPopup = true" type="button">
      <img src="images/icons/Plus.png" alt="" class="w-10 h-10"/>
    </button>
  </div>

  <!-- POPUP TAMBAH PESERTA -->
  <div
    x-show="openPopup"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <!-- Card Popup -->
    <div
      class="bg-white rounded-xl shadow-lg p-5 w-[350px]"
      @click.away="openPopup = false"
    >
      <!-- Header -->
      <h2 class="text-center font-bold text-[18px] mb-3">Tambah Peserta</h2>

      <!-- Input cari -->
      <input
        type="text"
        placeholder="Cari anggota..."
        class="border w-full rounded-md px-3 py-2 mb-3 text-sm"/>

      <!-- Pilih semua -->
<div class="flex items-center justify-between border-b-2 border-black px-2 pb-3">
    <span class="font-medium">Pilih Semua</span>
    <input
      type="checkbox"
      class="w-5 h-5 rounded-md accent-blue"
    />
</div>

<div class="flex flex-col gap-2 max-h-40 overflow-y-auto px-2 pt-3">
    @for ($i = 0; $i < 4; $i++)
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <img src="images/dk.jpg" class="w-6 h-6 rounded-full" />
            <span>Naufal</span>
        </div>
        <input
          type="checkbox"
          class="w-5 h-5 rounded-md accent-blue"/>
    </div>
    @endfor
</div>

      <!-- Tombol Simpan -->
      <div class="flex justify-end mt-4">
        <button
          @click="openPopup = false"
          class="bg-[#102a63] text-white px-4 py-1 rounded-md font-inter text-inter">
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>


<!--end pop up-->



<!-- Privasi -->
<div class="flex flex-col gap-4">
  <!-- Privasi -->
  <div class="flex flex-col gap-2">
    <label class="mb-1 font-medium text-[16px] text-black font-inter">Privasi</label>
    <div class="flex items-center gap-3">
      <!-- Toggle switch Rahasia -->
      <button class="relative inline-flex items-center h-6 rounded-full w-11 bg-[#102a63]">
        <span class="inline-block w-4 h-4 transform translate-x-6 bg-white rounded-full"></span>
      </button>
      <span class="text-sm font-medium text-[#102a63] font-inter">Rahasia</span>
    </div>
    <span class="text-sm text-gray-500 font-inter">Hanya peserta yang diundang bisa melihat</span>
  </div>

  <!-- Rapat -->
  <div class="flex flex-col gap-2">
    <span class="font-medium text-[16px] text-black font-inter">Apakah anda akan mengadakan rapat?</span>
    <div class="flex items-center gap-3">
      <!-- Toggle switch Rapat -->
      <button class="relative inline-flex items-center h-6 rounded-full w-11 bg-[#102a63]">
        <span class="sr-only">Rapat</span>
        <span class="inline-block w-4 h-4 transform translate-x-6 bg-white rounded-full"></span>
      </button>
      <span class="text-sm font-medium text-[#102a63] font-inter">Rapat</span>
    </div>
  </div>
</div>


    <!-- Link Rapat -->
    <div class="flex flex-col">
      <label class="mb-1 font-medium font-inter text-[16px]">Link Rapat (Opsional)</label>
      <input
    type="text"
    placeholder="Masukkan link rapat anda..."
    class="border rounded-md pl-5 py-2 focus:outline-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280]"
  />
      <span class="text-xs text-gray-400 font-inter">Opsional - isi jika rapat dilakukan online</span>
    </div>

    <!-- Catatan -->
    <div class="flex flex-col">
      <label class="mb-1 font-medium text-[16px]">Catatan <span class="text-red-500">*</span></label>

      <!-- Toolbar Icon Catatan -->
<div class="flex items-center gap-1 border border-b-0 rounded-t-md bg-gray-50 px-1 py-1 text-sm overflow-x-auto">
  <!-- Template icon toolbar -->
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/1a.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/2a.png" alt="" class="w-6 h-6"></button>

  <select class="border rounded text-sm py-1 flex-shrink-0 pr-9">
    <option>Normal text</option>
    <option>Heading 1</option>
    <option>Heading 2</option>
  </select>

  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/1.png" alt="" class="w-10 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/2.png" alt="" class="w-10 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/3.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/4.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/5.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/6.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/7.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/8.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/9.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/10.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/11.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/12.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/13.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/14.png" alt="" class="w-6 h-6"></button>
  <button class="hover:bg-gray-200 rounded flex-shrink-0"><img src="images/icons/15.png" alt="" class="w-6 h-6"></button>
</div>

      <!-- Textarea Catatan -->
      <textarea placeholder="Masukkan catatan anda disini..." class="border rounded-b-md p-2 h-32 resize-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280] pl-5"></textarea>
    </div>

    <!-- Tombol Kirim & Batal -->
   <div class="flex gap-2">
  <button class="bg-blue-600 text-white w-[80px] h-[30px] rounded-md hover:bg-blue-700 transition">
    Kirim
  </button>
  <button class="border border-blue-600 text-blue-600 w-[80px] h-[30px] rounded-md hover:bg-blue-50 transition">
    Batal
  </button>
</div>
  </div>
</div>

<!--pop up-->

@endsection
