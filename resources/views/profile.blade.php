@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<style>
.input-custom {
    font-family: 'Inter', sans-serif;
    font-weight: 600; /* Semi Bold */
}
.input-custom::placeholder {
    font-family: 'Inter', sans-serif;
    font-weight: 500; /* Medium */
}
</style>

<div class="min-h-screen bg-gradient-to-b from-blue-50 to-white flex justify-center pt-4 sm:pt-6 md:pt-8 pb-4 sm:pb-6 md:pb-8 overflow-hidden">

    <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-4xl xl:max-w-5xl px-4 sm:px-6 lg:px-8">
        
        <!-- Profile Card -->
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6 md:p-8">
            
            <!-- Avatar dan Email -->
            <div class="flex flex-col items-center mb-4 sm:mb-6">
                <!-- Avatar -->
                <div class="relative mb-3 sm:mb-4">
                    <img src="https://i.pravatar.cc/150?img=11" alt="Profile" class="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-full object-cover">
                    <!-- Edit Icon -->
                    <button class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white p-1.5 sm:p-2 rounded-full shadow-lg transition">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Email -->
                <p class="text-base sm:text-lg font-semibold text-gray-800 text-center break-all px-2">Syahroni88gmail.com</p>
            </div>

            <!-- Form Fields -->
            <form class="mx-0 sm:mx-4 md:mx-8">
                <!-- Nama Lengkap -->
                <div class="mb-4 sm:mb-5 md:mb-6">
                    <label class="block text-[#0F172A] font-bold mb-2 sm:mb-2.5 md:mb-3 text-base sm:text-lg">Nama Lengkap</label>
                    <input
                        type="text"
                        placeholder="Masukkan nama lengkap"
                        class="input-custom w-full bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl py-3 sm:py-3.5 pl-12 sm:pl-14 pr-3 sm:pr-4 text-sm sm:text-base text-gray-700 placeholder-[#6B7280] placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        style="background: url('/images/icons/User.svg') no-repeat 10px center; background-size: 18px; background-position: 10px center;"
                    >
                </div>

                <!-- Kata Sandi Lama -->
                <div class="mb-4 sm:mb-5 md:mb-6">
                    <label class="block text-[#0F172A] font-bold mb-2 sm:mb-2.5 md:mb-3 text-base sm:text-lg">Kata sandi lama</label>
                    <input
                        type="password"
                        placeholder="Masukkan kata sandi lama"
                        class="input-custom w-full bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl py-3 sm:py-3.5 pl-12 sm:pl-14 pr-3 sm:pr-4 text-sm sm:text-base text-gray-700 placeholder-[#6B7280] placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        style="background: url('/images/icons/Key.svg') no-repeat 10px center; background-size: 18px; background-position: 10px center;"
                    >
                </div>

                <!-- Kata Sandi Baru -->
                <div class="mb-4 sm:mb-5 md:mb-6">
                    <label class="block text-[#0F172A] font-bold mb-2 sm:mb-2.5 md:mb-3 text-base sm:text-lg">Kata sandi baru</label>
                    <input
                        type="password"
                        placeholder="Masukkan kata sandi baru"
                        class="input-custom w-full bg-gray-50 border border-gray-200 rounded-lg sm:rounded-xl py-3 sm:py-3.5 pl-12 sm:pl-14 pr-3 sm:pr-4 text-sm sm:text-base text-gray-700 placeholder-[#6B7280] placeholder-opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        style="background: url('/images/icons/Key.svg') no-repeat 10px center; background-size: 18px; background-position: 10px center;"
                    >
                </div>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 text-sm sm:text-base">
                    <button 
                        type="submit"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 sm:px-8 py-2.5 sm:py-1 rounded-lg sm:rounded-xl font-semibold transition shadow-sm"
                    > 
                        Simpan
                    </button>
                    <button 
                        type="button"
                        class="w-full sm:w-auto border-2 border-blue-700 text-blue-700 hover:bg-blue-50 px-6 sm:px-8 py-2.5 sm:py-1.5 rounded-lg sm:rounded-xl font-semibold transition"
                    >
                        Batal
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection