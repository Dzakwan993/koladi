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
    <div class="flex flex-col">
      <label class="mb-1 font-medium text-[16px] text-black ">Perulangan<span class="text-red-500">*</span></label>
      <select class="border rounded-md p-2 w-1/3 text-[#6B7280] font-inter text-[14px] pl-5">
        <optio cl>Jangan Ulangi</optio>
        <option>Setiap Hari</option>
        <option>Setiap Minggu</option>
      </select>
    </div>

    <!-- Peserta -->
<div class="flex flex-col gap-2">
  <label class="mb-1 font-medium text-[16px] text-black">Peserta <span class="text-red-500">*</span></label>

  <!-- Foto peserta -->
  <div class="flex items-center gap-2">
    <img src="images/dk.jpg" class="rounded-full border w-10 h-10"/>
    <img src="images/dk.jpg" class="rounded-full border w-10 h-10"/>
    <!-- Tombol tambah -->
    <button><img src="images/icons/Plus.png" alt="" class=" w-15 h-15"/></button>
  </div>
</div>


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

  <select class="border rounded text-sm px-10 py-1 flex-shrink-0">
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

@endsection
