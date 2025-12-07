<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Koladi</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- TOPBAR -->
    <header class="bg-white shadow-md py-4 px-6 flex justify-between items-center border-b sticky top-0 z-10">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold text-blue-700 tracking-wide">
                Dashboard Admin Koladi
            </h1>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-right hidden md:block">
                <p class="text-sm font-medium">{{ now()->format('d F Y') }}</p>
                <p class="text-xs text-gray-500">{{ now()->format('H:i') }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-4 md:p-8">

        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
            <!-- Total Perusahaan -->
            <div class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-blue-600 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Perusahaan</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-blue-700 mt-1">113</h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    <span class="text-green-600 font-medium">+5</span> bulan ini
                </div>
            </div>

            <!-- Total Member -->
            <div class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-green-600 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Member</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-green-700 mt-1">1,624</h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0h-15" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Rata-rata <span class="font-medium">14/user</span> perusahaan
                </div>
            </div>

            <!-- Perusahaan Aktif -->
            <div class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-purple-600 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Perusahaan Aktif</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-purple-700 mt-1">68</h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    <span class="text-green-600 font-medium">60%</span> dari total
                </div>
            </div>

            <!-- Perusahaan Trial -->
            <div class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-orange-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Perusahaan Trial</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-orange-600 mt-1">45</h3>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    <span class="text-orange-600 font-medium">40%</span> dari total
                </div>
            </div>
        </div>

        <!-- PAKET BERLANGGANAN -->
        <div class="bg-white p-6 rounded-xl shadow-md border mb-6 md:mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-blue-700">Paket Berlangganan</h2>
                <span class="text-xs text-gray-500">Click untuk edit</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Paket Basic -->
                <div class="border rounded-lg p-5 hover:shadow-md transition-shadow relative package-card" data-package="basic">
                    <button class="absolute top-3 right-3 text-gray-400 hover:text-blue-600 edit-package" data-package="basic">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                    
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">Basic</h3>
                            <p class="text-sm text-gray-500">Paket Entry Level</p>
                        </div>
                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-medium">
                            32 perusahaan
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-2xl font-bold text-blue-600 package-price">Rp 199,000<span class="text-sm text-gray-500">/bulan</span></p>
                    </div>
                    
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>User limit:</span>
                            </div>
                            <span class="font-medium package-user-limit">10</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>Storage limit:</span>
                            </div>
                            <span class="font-medium package-storage-limit">10 GB</span>
                        </div>
                    </div>
                </div>

                <!-- Paket Standard -->
                <div class="border border-blue-200 rounded-lg p-5 hover:shadow-md transition-shadow bg-blue-50 relative package-card" data-package="standard">
                    <button class="absolute top-3 right-3 text-blue-400 hover:text-blue-700 edit-package" data-package="standard">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                    
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold text-lg text-blue-800">Standard</h3>
                            <p class="text-sm text-blue-600">Most Popular</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">
                            56 perusahaan
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-2xl font-bold text-blue-700 package-price">Rp 399,000<span class="text-sm text-blue-600">/bulan</span></p>
                    </div>
                    
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>User limit:</span>
                            </div>
                            <span class="font-medium package-user-limit">50</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>Storage limit:</span>
                            </div>
                            <span class="font-medium package-storage-limit">50 GB</span>
                        </div>
                    </div>
                </div>

                <!-- Paket Business -->
                <div class="border rounded-lg p-5 hover:shadow-md transition-shadow relative package-card" data-package="business">
                    <button class="absolute top-3 right-3 text-gray-400 hover:text-purple-600 edit-package" data-package="business">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                    
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">Business</h3>
                            <p class="text-sm text-gray-500">Enterprise Grade</p>
                        </div>
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-medium">
                            25 perusahaan
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-2xl font-bold text-purple-600 package-price">Rp 799,000<span class="text-sm text-gray-500">/bulan</span></p>
                    </div>
                    
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>User limit:</span>
                            </div>
                            <span class="font-medium package-user-limit">200</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>Storage limit:</span>
                            </div>
                            <span class="font-medium package-storage-limit">200 GB</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ADD-ONS -->
        <div class="bg-white p-6 rounded-xl shadow-md border mb-6 md:mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-purple-700">Add-ons</h2>
                <span class="text-xs text-gray-500">Click untuk edit harga</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Add-ons User -->
                <div class="border rounded-lg p-5 hover:shadow-md transition-shadow relative addon-card" data-addon="user">
                    <button class="absolute top-3 right-3 text-gray-400 hover:text-purple-600 edit-addon" data-addon="user">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0h-15" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">Add-ons User</h3>
                            <p class="text-sm text-gray-500">Tambahan user di luar paket</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Harga per user:</span>
                            <span class="font-bold text-lg text-purple-600 addon-price">Rp 50,000<span class="text-sm font-normal">/bulan</span></span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Perusahaan menggunakan:</span>
                            <span class="font-medium">45 perusahaan</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Total add-ons user:</span>
                            <span class="font-medium">324 user</span>
                        </div>
                    </div>
                </div>

                <!-- Add-ons Storage -->
                <div class="border rounded-lg p-5 hover:shadow-md transition-shadow relative addon-card" data-addon="storage">
                    <button class="absolute top-3 right-3 text-gray-400 hover:text-blue-600 edit-addon" data-addon="storage">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">Add-ons Storage</h3>
                            <p class="text-sm text-gray-500">Tambahan storage di luar paket</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Harga per 10 GB:</span>
                            <span class="font-bold text-lg text-blue-600 addon-price">Rp 25,000<span class="text-sm font-normal">/bulan</span></span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Perusahaan menggunakan:</span>
                            <span class="font-medium">32 perusahaan</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span>Total add-ons storage:</span>
                            <span class="font-medium">480 GB</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER & SEARCH -->
        <div class="mb-6 bg-white p-4 md:p-6 rounded-xl shadow-md">
            <div class="flex flex-col md:flex-row justify-between gap-4">
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="flex-1">
                        <input type="text" 
                               placeholder="Cari perusahaan..." 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <select class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="trial">Trial</option>
                        <option value="active">Aktif</option>
                        <option value="expired">Expired</option>
                        <option value="canceled">Canceled</option>
                    </select>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                        Filter
                    </button>
                </div>
                
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Export Excel
                </button>
            </div>
        </div>

        <!-- DATA PERUSAHAAN -->
        <div class="bg-white p-6 md:p-8 rounded-xl shadow-md border mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-blue-700">Daftar Perusahaan</h2>
                    <p class="text-sm text-gray-500 mt-1">Manajemen seluruh perusahaan terdaftar</p>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">
                        Terakhir diperbarui: {{ now()->format('d M Y, H:i') }}
                    </span>
                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 refresh-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-300 shadow-sm">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-blue-600 text-white text-sm">
                            <th class="p-3 font-medium">Nama Perusahaan</th>
                            <th class="p-3 font-medium">Email</th>
                            <th class="p-3 font-medium">Tanggal Daftar</th>
                            <th class="p-3 font-medium">Total Member</th>
                            <th class="p-3 font-medium">Add-ons User</th>
                            <th class="p-3 font-medium">Add-ons Storage</th>
                            <th class="p-3 font-medium">Jenis Paket</th>
                            <th class="p-3 font-medium">Status</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white text-sm">
                        <!-- Row 1 - Aktif dengan Standard -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Sukses Makmur</td>
                            <td class="p-4 text-blue-600">admin@suksesmakmur.com</td>
                            <td class="p-4">15 Jan 2024</td>
                            <td class="p-4 font-medium">42</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">5</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">20 GB</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">
                                    Standard
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    Active
                                </span>
                            </td>
                        </tr>

                        <!-- Row 2 - Trial (tidak ada paket) -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">CV. Jaya Sentosa</td>
                            <td class="p-4 text-blue-600">info@jayasentosa.co.id</td>
                            <td class="p-4">28 Feb 2024</td>
                            <td class="p-4 font-medium">12</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium text-gray-500">0</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium text-gray-500">0 GB</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs">
                                    -
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-semibold text-xs inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                    Trial
                                </span>
                            </td>
                        </tr>

                        <!-- Row 3 - Expired dengan Business -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Media Karya</td>
                            <td class="p-4 text-blue-600">contact@mediakarya.com</td>
                            <td class="p-4">10 Des 2023</td>
                            <td class="p-4 font-medium">58</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">8</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">50 GB</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 font-semibold text-xs">
                                    Business
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 font-semibold text-xs inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    Expired
                                </span>
                            </td>
                        </tr>

                        <!-- Row 4 - Aktif dengan Business -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Teknologi Nusantara</td>
                            <td class="p-4 text-blue-600">admin@teknusantara.com</td>
                            <td class="p-4">22 Jan 2024</td>
                            <td class="p-4 font-medium">87</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">12</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">100 GB</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 font-semibold text-xs">
                                    Business
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    Active
                                </span>
                            </td>
                        </tr>

                        <!-- Row 5 - Canceled dengan Basic -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Digital Kreatif</td>
                            <td class="p-4 text-blue-600">hello@digitalkreatif.id</td>
                            <td class="p-4">05 Mar 2024</td>
                            <td class="p-4 font-medium">23</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">3</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">15 GB</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold text-xs">
                                    Basic
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold text-xs inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                    Canceled
                                </span>
                            </td>
                        </tr>

                        <!-- Row 6 - Aktif dengan Basic -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Prima Mandiri</td>
                            <td class="p-4 text-blue-600">contact@primamandiri.co.id</td>
                            <td class="p-4">18 Feb 2024</td>
                            <td class="p-4 font-medium">18</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">2</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">5 GB</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold text-xs">
                                    Basic
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    Active
                                </span>
                            </td>
                        </tr>

                        <!-- Row 7 - Active dengan Standard -->
                        <tr class="border-b hover:bg-blue-50 transition">
                            <td class="p-4 font-medium">PT. Global Solution</td>
                            <td class="p-4 text-blue-600">info@globalsolution.com</td>
                            <td class="p-4">30 Jan 2024</td>
                            <td class="p-4 font-medium">65</td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">10</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">30 GB</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">
                                    Standard
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs inline-flex items-center gap-1">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                    Active
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-6 text-sm text-gray-600 gap-4">
                <div>
                    Menampilkan 1-7 dari 113 perusahaan
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        ← Prev
                    </button>
                    <button class="px-3 py-2 border border-blue-600 bg-blue-50 text-blue-700 rounded-lg font-medium">
                        1
                    </button>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        2
                    </button>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        3
                    </button>
                    <span class="px-2">...</span>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        12
                    </button>
                    <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Next →
                    </button>
                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t py-4 px-6 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500 gap-4">
            <div>
                &copy; {{ date('Y') }} Koladi. All rights reserved.
            </div>
            <div class="flex items-center gap-6">
                <a href="#" class="hover:text-blue-600 transition">Privacy Policy</a>
                <a href="#" class="hover:text-blue-600 transition">Terms of Service</a>
                <a href="#" class="hover:text-blue-600 transition">Help Center</a>
            </div>
        </div>
    </footer>

    <!-- EDIT PAKET MODAL -->
    <div id="editPackageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800" id="modalPackageTitle">Edit Paket Basic</h3>
                <button class="text-gray-400 hover:text-gray-600 close-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="packageForm">
                <input type="hidden" id="packageType" name="package_type">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga per bulan</label>
                        <input type="text" id="packagePrice" name="price" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Rp 199,000">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User limit</label>
                        <input type="number" id="packageUserLimit" name="user_limit" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="10">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Storage limit (GB)</label>
                        <input type="number" id="packageStorageLimit" name="storage_limit" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="10">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 close-modal">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT ADDON MODAL -->
    <div id="editAddonModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800" id="modalAddonTitle">Edit Add-ons User</h3>
                <button class="text-gray-400 hover:text-gray-600 close-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="addonForm">
                <input type="hidden" id="addonType" name="addon_type">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" id="addonPriceLabel">Harga per user per bulan</label>
                        <input type="text" id="addonPrice" name="price" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Rp 50,000">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 close-modal">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh button
            const refreshBtn = document.querySelector('.refresh-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<span class="animate-spin">⟳</span> Refreshing...';
                    this.disabled = true;
                    
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                        alert('Data diperbarui!');
                    }, 1500);
                });
            }

            // Filter functionality
            const filterBtn = document.querySelector('button:contains("Filter")');
            const searchInput = document.querySelector('input[placeholder="Cari perusahaan..."]');
            const statusSelect = document.querySelector('select');
            
            if (filterBtn) {
                filterBtn.addEventListener('click', function() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const status = statusSelect.value;
                    
                    const rows = document.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const companyName = row.cells[0].textContent.toLowerCase();
                        const statusBadge = row.cells[7].textContent.toLowerCase();
                        
                        const matchesSearch = !searchTerm || companyName.includes(searchTerm);
                        const matchesStatus = !status || statusBadge.includes(status);
                        
                        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                    });
                });
            }

            // Package Edit Modal
            const editPackageModal = document.getElementById('editPackageModal');
            const modalPackageTitle = document.getElementById('modalPackageTitle');
            const packageTypeInput = document.getElementById('packageType');
            const packagePriceInput = document.getElementById('packagePrice');
            const packageUserLimitInput = document.getElementById('packageUserLimit');
            const packageStorageLimitInput = document.getElementById('packageStorageLimit');

            // Addon Edit Modal
            const editAddonModal = document.getElementById('editAddonModal');
            const modalAddonTitle = document.getElementById('modalAddonTitle');
            const addonPriceLabel = document.getElementById('addonPriceLabel');
            const addonTypeInput = document.getElementById('addonType');
            const addonPriceInput = document.getElementById('addonPrice');

            // Package edit buttons
            document.querySelectorAll('.edit-package').forEach(button => {
                button.addEventListener('click', function() {
                    const packageType = this.getAttribute('data-package');
                    const packageCard = this.closest('.package-card');
                    
                    // Get current values
                    const currentPrice = packageCard.querySelector('.package-price').textContent.split('Rp')[1].split(',')[0].trim();
                    const currentUserLimit = packageCard.querySelector('.package-user-limit').textContent;
                    const currentStorageLimit = packageCard.querySelector('.package-storage-limit').textContent.split(' ')[0];
                    
                    // Set modal values
                    modalPackageTitle.textContent = `Edit Paket ${packageType.charAt(0).toUpperCase() + packageType.slice(1)}`;
                    packageTypeInput.value = packageType;
                    packagePriceInput.value = `Rp ${currentPrice},000`;
                    packageUserLimitInput.value = currentUserLimit;
                    packageStorageLimitInput.value = currentStorageLimit;
                    
                    editPackageModal.classList.remove('hidden');
                });
            });

            // Addon edit buttons
            document.querySelectorAll('.edit-addon').forEach(button => {
                button.addEventListener('click', function() {
                    const addonType = this.getAttribute('data-addon');
                    const addonCard = this.closest('.addon-card');
                    
                    // Get current value
                    const currentPrice = addonCard.querySelector('.addon-price').textContent;
                    
                    // Set modal values
                    const addonName = addonType === 'user' ? 'User' : 'Storage';
                    modalAddonTitle.textContent = `Edit Add-ons ${addonName}`;
                    addonPriceLabel.textContent = addonType === 'user' 
                        ? 'Harga per user per bulan' 
                        : 'Harga per 10 GB per bulan';
                    addonTypeInput.value = addonType;
                    addonPriceInput.value = currentPrice.split('/')[0].trim();
                    
                    editAddonModal.classList.remove('hidden');
                });
            });

            // Close modals
            document.querySelectorAll('.close-modal').forEach(button => {
                button.addEventListener('click', function() {
                    editPackageModal.classList.add('hidden');
                    editAddonModal.classList.add('hidden');
                });
            });

            // Package form submission
            document.getElementById('packageForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const packageType = packageTypeInput.value;
                const newPrice = packagePriceInput.value;
                const newUserLimit = packageUserLimitInput.value;
                const newStorageLimit = packageStorageLimitInput.value;
                
                // Update the card in UI
                const packageCard = document.querySelector(`.package-card[data-package="${packageType}"]`);
                packageCard.querySelector('.package-price').innerHTML = `${newPrice}<span class="text-sm text-gray-500">/bulan</span>`;
                packageCard.querySelector('.package-user-limit').textContent = newUserLimit;
                packageCard.querySelector('.package-storage-limit').textContent = `${newStorageLimit} GB`;
                
                // In real app, send to server
                alert(`Paket ${packageType} berhasil diperbarui!`);
                
                editPackageModal.classList.add('hidden');
            });

            // Addon form submission
            document.getElementById('addonForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const addonType = addonTypeInput.value;
                const newPrice = addonPriceInput.value;
                
                // Update the card in UI
                const addonCard = document.querySelector(`.addon-card[data-addon="${addonType}"]`);
                addonCard.querySelector('.addon-price').innerHTML = `${newPrice}<span class="text-sm font-normal">/bulan</span>`;
                
                // In real app, send to server
                const addonName = addonType === 'user' ? 'User' : 'Storage';
                alert(`Add-ons ${addonName} berhasil diperbarui!`);
                
                editAddonModal.classList.add('hidden');
            });

            // Close modals when clicking outside
            [editPackageModal, editAddonModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        modal.classList.add('hidden');
                    }
                });
            });
        });
    </script>

</body>
</html>