<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman buat perusahaan - Koladi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #a8c5ff 0%, #7ea5f5 100%);
            background-image:
                linear-gradient(135deg, #a8c5ff 0%, #7ea5f5 100%),
                url('{{ asset('images/poltek.svg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-blend-mode: overlay;
        }

        /* Pattern overlay untuk efek diagonal */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                repeating-linear-gradient(45deg,
                    transparent,
                    transparent 35px,
                    rgba(255, 255, 255, 0.03) 35px,
                    rgba(255, 255, 255, 0.03) 70px);
            pointer-events: none;
        }

        /* Shadow untuk card yang lebih soft */
        .card-shadow {
            box-shadow:
                0 10px 25px -5px rgba(0, 0, 0, 0.1),
                0 8px 10px -6px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body class="min-h-screen relative">
    {{-- Main Content --}}
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div
            class="bg-white/30 backdrop-blur-xl rounded-3xl card-shadow p-8 sm:p-12 w-full max-w-lg relative border-2 border-blue-500/90">

            {{-- Heading --}}
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-6">
                Ayo buat perusahaanmu!
            </h1>

            {{-- Illustration --}}
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <img src="{{ asset('images/icons/buat-perusahaan.svg') }}" alt="Ilustrasi Perusahaan">
                </div>
            </div>

            {{-- Input Nama Perusahaan --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama perusahaan
                </label>
                <input type="text" placeholder="Masukkan nama perusahaanmu..."
                    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-xl text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
            </div>

            {{-- Tombol Buat Perusahaan --}}
            <a href="{{ url('/dashboard-awal') }}"
                class="block text-center w-full bg-blue-600 text-white py-3.5 rounded-xl font-semibold text-base hover:bg-blue-700 active:bg-blue-800 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                Buat perusahaan
            </a>

        </div>
    </div>
</body>

</html>
