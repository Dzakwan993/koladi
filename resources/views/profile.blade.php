@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-blue-50 to-white flex justify-center pt-8 pb-8 overflow-hidden">

<div class="w-full max-w-2xl lg:max-w-4xl xl:max-w-5xl px-4 sm:px-6 lg:px-8">
        
        <!-- Profile Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            
            <!-- Avatar dan Email -->
            <div class="flex flex-col items-center mb-4">
                <!-- Avatar -->
                <div class="relative mb-4">
                    <img src="https://i.pravatar.cc/150?img=11" alt="Profile" class="w-32 h-32 rounded-full object-cover border-4 border-blue-100">
                    <!-- Edit Icon (Optional) -->
                    <button class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full shadow-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Email -->
                <p class="text-lg font-semibold text-gray-800">Syahroni88gmail.com</p>
            </div>

            <!-- Form Fields -->
            <form class="mx-8">
                <!-- Nama Lengkap -->
                <div class="mb-6">
                    <label class="block text-gray-900 font-bold mb-3">Nama Lengkap</label>
                    <input
                        type="text"
                        value="Muhammad Syahroni"
                        placeholder="Masukkan nama lengkap"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3.5 pl-14 pr-4 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        style="background: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' stroke=\'%23a0aec0\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'/></svg>') no-repeat 12px center; background-size: 20px;"
                    >
                </div>


                <!-- Kata Sandi Lama -->
                <div class="mb-6">
                    <label class="block text-gray-900 font-bold mb-3">Kata sandi lama</label>
                    <input
                        type="text"
                        value="Muhammad Syahroni"
                        placeholder="Masukkan nama lengkap"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3.5 pl-14 pr-4 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        style="background: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' stroke=\'%23a0aec0\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'/></svg>') no-repeat 12px center; background-size: 20px;"
                    >
                </div>

                <!-- Kata Sandi Baru -->
                <div class="mb-6">
                    <label class="block text-gray-900 font-bold mb-3">Kata sandi baru</label>
                    <input
                        type="text"
                        value="Muhammad Syahroni"
                        placeholder="Masukkan nama lengkap"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3.5 pl-14 pr-4 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        style="background: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' stroke=\'%23a0aec0\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'/></svg>') no-repeat 12px center; background-size: 20px;"
                    >
                </div>

                <!-- Buttons -->
                <div class="flex gap-3">
                    <button 
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-1 rounded-xl font-semibold transition shadow-sm"
                    >
                        simpan
                    </button>
                    <button 
                        type="button"
                        class="border-2 border-blue-600 text-blue-600 hover:bg-blue-50 px-8 py-2 rounded-xl font-semibold transition"
                    >
                        Batal
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection