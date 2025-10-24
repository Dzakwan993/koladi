<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Dicabut - Koladi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Icon & Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Akses Dicabut</h1>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="mb-4">
                    <p class="text-gray-700 mb-2">Akses Anda ke perusahaan</p>
                    <p class="text-xl font-semibold text-gray-900 mb-4">{{ $removedCompanyName }}</p>
                    <p class="text-gray-600">telah dicabut oleh administrator.</p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                    <p class="text-sm text-blue-700">
                        📧 Notifikasi telah dikirim ke email Anda dengan detail lengkap.
                    </p>
                </div>

                @if ($companies->isNotEmpty())
                    <div class="border-t pt-4 mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Pilih perusahaan lain:</p>
                        <ul class="space-y-2 mb-4">
                            @foreach ($companies as $company)
                                <li>
                                    <a href="{{ route('company.switch', $company->id) }}"
                                        class="flex items-center justify-between bg-blue-50 hover:bg-blue-100 p-3 rounded-lg transition group">
                                        <span class="text-gray-800 font-medium">{{ $company->name }}</span>
                                        <span class="text-blue-600 group-hover:text-blue-700 font-medium">
                                            Pilih →
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-3">Atau buat perusahaan baru:</p>
                        <a href="{{ route('buat-perusahaan.create') }}"
                            class="block w-full border-2 border-blue-600 hover:bg-blue-50 text-blue-600 font-medium py-3 px-4 rounded-lg text-center transition">
                            + Buat Perusahaan Baru
                        </a>
                    </div>
                @else
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-3">Anda belum memiliki perusahaan lain</p>
                        <a href="{{ route('buat-perusahaan.create') }}"
                            class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg text-center transition">
                            Buat Perusahaan Baru
                        </a>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <p class="text-center text-sm text-gray-500">
                Jika ini kesalahan, hubungi administrator perusahaan
            </p>
        </div>
    </div>
</body>

</html>
