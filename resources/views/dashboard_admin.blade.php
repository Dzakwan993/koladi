<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - Koladi</title>
    @vite('resources/css/app.css')
</head>

@php
    use App\Models\Subscription;
@endphp


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
                <button
                    class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"
                            clip-rule="evenodd" />
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
            <div
                class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-blue-600 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Perusahaan</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-blue-700 mt-1">{{ $totalCompanies }}</h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    <span class="text-green-600 font-medium">+5</span> bulan ini
                </div>
            </div>

            <!-- Total Member -->
            <div
                class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-green-600 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Member</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-green-700 mt-1">{{ $totalMembers }}</h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0h-15" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Rata-rata <span
                        class="font-medium">{{ $totalCompanies > 0 ? round($totalMembers / $totalCompanies) : 0 }}/user</span>
                    perusahaan
                </div>
            </div>

            <!-- Perusahaan Aktif -->
            <div
                class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-purple-600 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Perusahaan Aktif</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-purple-700 mt-1">{{ $activeCompanies }}</h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    <span
                        class="text-green-600 font-medium">{{ $totalCompanies > 0 ? round(($activeCompanies / $totalCompanies) * 100) : 0 }}%</span>
                    dari total
                </div>
            </div>

            <!-- Perusahaan Trial -->
            <div
                class="bg-white shadow-md rounded-xl p-4 md:p-6 border-l-4 border-orange-500 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Perusahaan Trial</p>
                        <h3 class="text-2xl md:text-3xl font-bold text-orange-600 mt-1">{{ $trialCompanies }}</h3>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    <span
                        class="text-orange-600 font-medium">{{ $totalCompanies > 0 ? round(($trialCompanies / $totalCompanies) * 100) : 0 }}%</span>
                    dari total
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

                @php
                    $order = ['Basic', 'Standard', 'Business']; // hapus tanda '='

                    // Urutkan $plans sesuai urutan di $order
                    $plans = $plans->sortBy(function ($plan) use ($order) {
                        return array_search($plan->plan_name, $order);
                    });
                @endphp


                @foreach ($plans as $index => $plan)
                    @php
                        $borderColors = ['border-blue-600', 'border-green-600', 'border-purple-600'];
                        $bgColors = ['bg-blue-100', 'bg-green-100', 'bg-purple-100'];
                        $textColors = ['text-blue-600', 'text-green-600', 'text-purple-600'];

                        $borderColor = $borderColors[$index] ?? $borderColors[0];
                        $bgColor = $bgColors[$index] ?? $bgColors[0];
                        $textColor = $textColors[$index] ?? $textColors[0];

                        $packageName = $plan->plan_name;
                        $description = $plan->description ?? '';
                    @endphp

                    <div class="border {{ $borderColor }} rounded-lg p-5 hover:shadow-md transition-shadow relative package-card"
                        data-package="{{ strtolower($packageName) }}" data-plan-id="{{ $plan->id }}">
                        <button class="absolute top-3 right-3 {{ $textColor }} hover:text-blue-700 edit-package"
                            data-package="{{ strtolower($packageName) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>

                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-lg text-gray-800">{{ $packageName }}</h3>
                                <p class="text-sm text-gray-500">{{ $description }}</p>
                            </div>
                            <span
                                class="{{ $bgColor }} text-gray-800 px-3 py-1 rounded-full text-xs font-medium">
                                @php
                                    $companyCount = Subscription::where('plan_id', $plan->id)
                                        ->where('status', 'active')
                                        ->count();
                                @endphp
                                {{ $companyCount }} perusahaan
                            </span>
                        </div>

                        <div class="mb-4">
                            <p class="text-2xl font-bold {{ $textColor }} package-price">
                                Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}<span
                                    class="text-sm text-gray-500">/bulan</span>
                            </p>
                        </div>

                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>User limit:</span>
                                </div>
                                <span class="font-medium package-user-limit">{{ $plan->base_user_limit }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Storage limit:</span>
                                </div>
                                {{-- DUMMY DATA: Storage limit tetap static seperti permintaan --}}
                                <span class="font-medium package-storage-limit">
                                    @if ($packageName == 'Basic')
                                        10 GB
                                    @elseif($packageName == 'Standard')
                                        50 GB
                                    @elseif($packageName == 'Business')
                                        200 GB
                                    @else
                                        10 GB
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
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
                @foreach ($addons as $addon)
                    <div class="border rounded-lg p-5 hover:shadow-md transition-shadow relative addon-card"
                        data-addon="{{ strtolower(str_replace(' ', '-', $addon->addon_name)) }}"
                        data-addon-id="{{ $addon->id }}">
                        <button class="absolute top-3 right-3 text-gray-400 hover:text-purple-600 edit-addon"
                            data-addon="{{ strtolower(str_replace(' ', '-', $addon->addon_name)) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>

                        <div class="flex items-center gap-4 mb-4">
                            @if (strpos(strtolower($addon->addon_name), 'user') !== false)
                                <div class="bg-purple-100 p-3 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0h-15" />
                                    </svg>
                                </div>
                            @else
                                <div class="bg-blue-100 p-3 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h3 class="font-bold text-lg text-gray-800">{{ $addon->addon_name }}</h3>
                                <p class="text-sm text-gray-500">{{ $addon->description }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Harga per user:</span>
                                <span class="font-bold text-lg text-purple-600 addon-price">Rp
                                    {{ number_format($addon->price_per_user, 0, ',', '.') }}<span
                                        class="text-sm font-normal">/bulan</span></span>
                            </div>

                            {{-- DUMMY DATA: Data statis seperti permintaan --}}
                            @if (strpos(strtolower($addon->addon_name), 'user') !== false)
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>Perusahaan menggunakan:</span>
                                    <span class="font-medium">45 perusahaan</span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>Total add-ons user:</span>
                                    <span class="font-medium">324 user</span>
                                </div>
                            @else
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>Perusahaan menggunakan:</span>
                                    <span class="font-medium">32 perusahaan</span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span>Total add-ons storage:</span>
                                    <span class="font-medium">480 GB</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- FILTER & SEARCH -->
        <div class="mb-6 bg-white p-4 md:p-6 rounded-xl shadow-md">
            <div class="flex flex-col md:flex-row justify-between gap-4">
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <div class="flex-1">
                        <input type="text" placeholder="Cari perusahaan..."
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <select
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="trial">Trial</option>
                        <option value="active">Aktif</option>
                        <option value="expired">Expired</option>
                        <option value="canceled">Canceled</option>
                    </select>
                    <button
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                        Filter
                    </button>
                </div>

                <button
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
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
                    <button
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 refresh-btn"
                        onclick="window.location.reload()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                clip-rule="evenodd" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>



            @if ($allCompanies->isEmpty() || $allCompanies->count() === 0)
                <!-- Tampilkan pesan jika tidak ada data -->
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Data Perusahaan</h3>
                    <p class="text-gray-500">Belum ada perusahaan yang terdaftar di sistem.</p>
                </div>
            @else
                <!-- Tampilkan tabel jika ada data -->
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
                            @foreach ($allCompanies as $company)
                                <tr class="border-b hover:bg-blue-50 transition">
                                    <td class="p-4 font-medium">{{ $company->name }}</td>
                                    <td class="p-4 text-blue-600">{{ $company->email ?? 'N/A' }}</td>
                                    <td class="p-4">{{ $company->created_at->format('d M Y') }}</td>
                                    <td class="p-4 font-medium">{{ $company->member_count }}</td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-1">
                                            @if ($company->addons_user > 0)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="font-medium">{{ $company->addons_user }}</span>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="font-medium text-gray-500">0</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-1">
                                            @if ($company->addons_storage > 0)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="font-medium">{{ $company->addons_storage }} GB</span>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="font-medium text-gray-500">0 GB</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        @php
                                            $badgeColor = 'bg-gray-100 text-gray-700';
                                            if ($company->package_type == 'Standard') {
                                                $badgeColor = 'bg-blue-100 text-blue-700';
                                            } elseif ($company->package_type == 'Business') {
                                                $badgeColor = 'bg-purple-100 text-purple-700';
                                            } elseif ($company->package_type == 'Trial') {
                                                $badgeColor = 'bg-yellow-100 text-yellow-700';
                                            } elseif ($company->package_type == 'Basic') {
                                                $badgeColor = 'bg-green-100 text-green-700';
                                            }
                                        @endphp
                                        <span
                                            class="px-3 py-1 rounded-full {{ $badgeColor }} font-semibold text-xs">
                                            {{ $company->package_type }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        @php
                                            $statusColor = 'bg-gray-100 text-gray-700';
                                            $statusDot = 'bg-gray-500';
                                            if ($company->status == 'active') {
                                                $statusColor = 'bg-blue-100 text-blue-700';
                                                $statusDot = 'bg-blue-600';
                                            } elseif ($company->status == 'trial') {
                                                $statusColor = 'bg-yellow-100 text-yellow-700';
                                                $statusDot = 'bg-yellow-500';
                                            } elseif ($company->status == 'expired') {
                                                $statusColor = 'bg-red-100 text-red-700';
                                                $statusDot = 'bg-red-500';
                                            }
                                        @endphp
                                        <span
                                            class="px-3 py-1 rounded-full {{ $statusColor }} font-semibold text-xs inline-flex items-center gap-1">
                                            <span class="w-2 h-2 {{ $statusDot }} rounded-full"></span>
                                            {{ ucfirst($company->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="flex flex-col md:flex-row justify-between items-center mt-6 text-sm text-gray-600 gap-4">
                    <div>
                        Menampilkan 1-{{ $companies->count() }} dari {{ $totalCompanies }} perusahaan
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            ← Prev
                        </button>
                        <button
                            class="px-3 py-2 border border-blue-600 bg-blue-50 text-blue-700 rounded-lg font-medium">
                            1
                        </button>
                        <button class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50" disabled>
                            Next →
                        </button>
                    </div>
                </div>
            @endif
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
    <div id="editPackageModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800" id="modalPackageTitle">Edit Paket Berlangganan</h3>
                <button class="text-gray-400 hover:text-gray-600 close-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="packageForm">
                @csrf
                <input type="hidden" id="planId" name="plan_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket</label>
                        <input type="text" id="packageName" name="plan_name"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Paket Basic" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga per bulan (Rp)</label>
                        <input type="number" id="packagePrice" name="price_monthly"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="199000" required min="0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User Limit (Jumlah Pengguna
                            Maksimum)</label>
                        <input type="number" id="packageUserLimit" name="base_user_limit"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="10" required min="1">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 close-modal">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        <span class="btn-text">Simpan Perubahan</span>
                        <span class="btn-loading hidden">
                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT ADDON MODAL -->
    <div id="editAddonModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800" id="modalAddonTitle">Edit Add-ons</h3>
                <button class="text-gray-400 hover:text-gray-600 close-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="addonForm">
                @csrf
                <input type="hidden" id="addonId" name="addon_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga per user per bulan
                            (Rp)</label>
                        <input type="number" id="addonPrice" name="price_per_user"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="50000" required min="0">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 close-modal">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                        <span class="btn-text">Simpan Perubahan</span>
                        <span class="btn-loading hidden">
                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 bg-white shadow-lg rounded-lg p-4 hidden z-50 border-l-4">
        <div class="flex items-center gap-3">
            <div id="toastIcon"></div>
            <div>
                <p id="toastMessage" class="font-medium"></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF Token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;

            // Toast notification function
            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toastMessage');
                const toastIcon = document.getElementById('toastIcon');

                toast.classList.remove('border-green-500', 'border-red-500', 'border-blue-500');

                if (type === 'success') {
                    toast.classList.add('border-green-500');
                    toastIcon.innerHTML = `
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
                } else if (type === 'error') {
                    toast.classList.add('border-red-500');
                    toastIcon.innerHTML = `
                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
                }

                toastMessage.textContent = message;
                toast.classList.remove('hidden');

                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 3000);
            }

            // Package Edit Modal
            const editPackageModal = document.getElementById('editPackageModal');
            const modalPackageTitle = document.getElementById('modalPackageTitle');
            const planIdInput = document.getElementById('planId');
            const packageNameInput = document.getElementById('packageName');
            const packagePriceInput = document.getElementById('packagePrice');
            const packageUserLimitInput = document.getElementById('packageUserLimit');

            // Addon Edit Modal
            const editAddonModal = document.getElementById('editAddonModal');
            const modalAddonTitle = document.getElementById('modalAddonTitle');
            const addonIdInput = document.getElementById('addonId');
            const addonPriceInput = document.getElementById('addonPrice');

            // Package edit buttons
            document.querySelectorAll('.edit-package').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const packageCard = this.closest('.package-card');
                    const planId = packageCard.getAttribute('data-plan-id');

                    // Get current values
                    const currentName = packageCard.querySelector('h3').textContent.trim();
                    const currentPrice = packageCard.querySelector('.package-price').textContent
                        .replace(/[^0-9]/g, '');
                    const currentUserLimit = packageCard.querySelector('.package-user-limit')
                        .textContent.trim();

                    // Set modal values
                    modalPackageTitle.textContent = `Edit ${currentName}`;
                    planIdInput.value = planId;
                    packageNameInput.value = currentName;
                    packagePriceInput.value = currentPrice;
                    packageUserLimitInput.value = currentUserLimit;

                    editPackageModal.classList.remove('hidden');
                });
            });

            // Addon edit buttons
            document.querySelectorAll('.edit-addon').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const addonCard = this.closest('.addon-card');
                    const addonId = addonCard.getAttribute('data-addon-id');

                    // Get current values
                    const currentName = addonCard.querySelector('h3').textContent.trim();
                    const currentPrice = addonCard.querySelector('.addon-price').textContent
                        .replace(/[^0-9]/g, '');

                    // Set modal values
                    modalAddonTitle.textContent = `Edit ${currentName}`;
                    addonIdInput.value = addonId;
                    addonPriceInput.value = currentPrice;

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

            // Close modals when clicking outside
            [editPackageModal, editAddonModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        modal.classList.add('hidden');
                    }
                });
            });

            // Package form submission
            document.getElementById('packageForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                // Disable button and show loading
                submitBtn.disabled = true;
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');

                const planId = planIdInput.value;
                const formData = new FormData(this);

                try {
                    const response = await fetch(`/admin/plans/${planId}/update`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update UI
                        const packageCard = document.querySelector(
                            `.package-card[data-plan-id="${planId}"]`);
                        packageCard.querySelector('h3').textContent = formData.get('plan_name');
                        packageCard.querySelector('.package-price').innerHTML =
                            `Rp ${parseInt(formData.get('price_monthly')).toLocaleString('id-ID')}<span class="text-sm text-gray-500">/bulan</span>`;
                        packageCard.querySelector('.package-user-limit').textContent = formData.get(
                            'base_user_limit');

                        showToast(result.message, 'success');
                        editPackageModal.classList.add('hidden');
                    } else {
                        showToast(result.message || 'Gagal memperbarui paket', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat memperbarui paket', 'error');
                } finally {
                    // Re-enable button
                    submitBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                }
            });

            // Addon form submission
            document.getElementById('addonForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');

                // Disable button and show loading
                submitBtn.disabled = true;
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');

                const addonId = addonIdInput.value;
                const formData = new FormData(this);

                try {
                    const response = await fetch(`/admin/addons/${addonId}/update`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Update UI
                        const addonCard = document.querySelector(
                            `.addon-card[data-addon-id="${addonId}"]`);
                        addonCard.querySelector('.addon-price').innerHTML =
                            `Rp ${parseInt(formData.get('price_per_user')).toLocaleString('id-ID')}<span class="text-sm font-normal">/bulan</span>`;

                        showToast(result.message, 'success');
                        editAddonModal.classList.add('hidden');
                    } else {
                        showToast(result.message || 'Gagal memperbarui add-ons', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat memperbarui add-ons', 'error');
                } finally {
                    // Re-enable button
                    submitBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                }
            });

            // Existing refresh and filter code...
            const refreshBtn = document.querySelector('.refresh-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    window.location.reload();
                });
            }
        });
    </script>

</body>

</html>
