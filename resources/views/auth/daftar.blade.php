<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Koladi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-50 to-[#F9FAFB] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/logo-koladi1.png') }}" alt="Logo Koladi" class="w-32 md:w-40">
        </div>

        {{-- Heading --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang di Koladi</h1>
            <p class="text-gray-500 text-sm">
                @if (isset($email) && $email)
                    Lengkapi data untuk menerima undangan
                @else
                    Silahkan daftar untuk melanjutkan
                @endif
            </p>
        </div>

        {{-- Info Undangan --}}
        @if (isset($email) && $email)
            <div
                class="bg-blue-50 border border-blue-200 text-blue-700 p-3 rounded-lg mb-4 text-sm flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <span>Anda telah diundang untuk bergabung ke perusahaan. Silakan lengkapi data untuk melanjutkan.</span>
            </div>
        @endif

        {{-- Error Message --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded-lg mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-100 border border-blue-300 text-blue-700 p-3 rounded-lg mb-4 text-sm">
                {{ session('info') }}
            </div>
        @endif

        {{-- Form --}}
        <form class="space-y-4" method="POST" action="{{ route('daftar.store') }}">
            @csrf

            {{-- Nama Lengkap --}}
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Nama Lengkap"
                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    required>
            </div>

            {{-- Email --}}
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <input type="email" name="email" value="{{ old('email', $email ?? '') }}" placeholder="Email"
                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition {{ isset($email) && $email ? 'bg-gray-50' : '' }}"
                    {{ isset($email) && $email ? 'readonly' : '' }} required>
                @if (isset($email) && $email)
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-green-500" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                @endif
            </div>

            {{-- Password --}}
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <input id="password" type="password" name="password" placeholder="Kata sandi (minimal 8 karakter)"
                    class="w-full pl-12 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    required>
                <button type="button" id="togglePassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05
                            10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.957
                            9.957 0 012.125-3.368m2.59-2.591A9.956
                            9.956 0 0112 5c4.477 0 8.268 2.943
                            9.542 7a9.956 9.956 0 01-2.318
                            3.74M15 12a3 3 0 00-4.243-4.243M3
                            3l18 18" />
                    </svg>
                </button>
            </div>

            {{-- Konfirmasi Password --}}
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <input id="password_confirmation" type="password" name="password_confirmation"
                    placeholder="Konfirmasi kata sandi"
                    class="w-full pl-12 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    required>
                <button type="button" id="togglePasswordConfirm"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05
                            10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.957
                            9.957 0 012.125-3.368m2.59-2.591A9.956
                            9.956 0 0112 5c4.477 0 8.268 2.943
                            9.542 7a9.956 9.956 0 01-2.318
                            3.74M15 12a3 3 0 00-4.243-4.243M3
                            3l18 18" />
                    </svg>
                </button>
            </div>

            {{-- Tombol --}}
            <button type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-md hover:shadow-lg">
                @if (isset($email) && $email)
                    Daftar & Terima Undangan
                @else
                    Daftar
                @endif
            </button>

            {{-- Link Masuk --}}
            <div class="text-center text-sm">
                <span class="text-gray-500">Sudah punya akun?</span>
                <a href="{{ url('/masuk') }}" class="text-blue-600 font-semibold hover:underline">Masuk</a>
            </div>

            {{-- Divider & Google hanya muncul jika BUKAN dari undangan --}}
            @if (!isset($email) || !$email)
                {{-- Divider --}}
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Atau</span>
                    </div>
                </div>

                {{-- Google --}}
                <a href="{{ route('google.login') }}" 
                    class="w-full bg-white border border-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-50 transition shadow-sm hover:shadow flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Masuk dengan Google
                </a>
            @endif
        </form>
    </div>

    <script>
        // Fungsi toggle mata password
        function setupToggle(buttonId, inputId, iconId) {
            const toggle = document.querySelector(buttonId);
            const input = document.querySelector(inputId);
            const icon = document.querySelector(iconId);

            toggle.addEventListener("click", () => {
                const isHidden = input.getAttribute("type") === "password";
                input.setAttribute("type", isHidden ? "text" : "password");

                icon.innerHTML = isHidden
                    // 👁️ Saat password TERLIHAT → mata terbuka
                    ?
                    `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5
                        12 5c4.477 0 8.268 2.943 9.542
                        7-1.274 4.057-5.065 7-9.542
                        7-4.477 0-8.268-2.943-9.542-7z" />`
                    // 🙈 Saat password TERSEMBUNYI → mata dicoret
                    :
                    `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112
                        19c-4.477 0-8.268-2.943-9.542-7a9.957
                        9.957 0 012.125-3.368m2.59-2.591A9.956
                        9.956 0 0112 5c4.477 0 8.268 2.943
                        9.542 7a9.956 9.956 0 01-2.318
                        3.74M15 12a3 3 0 00-4.243-4.243M3
                        3l18 18" />`;
            });
        }

        setupToggle("#togglePassword", "#password", "#eyeIcon");
        setupToggle("#togglePasswordConfirm", "#password_confirmation", "#eyeIconConfirm");
    </script>
</body>

</html>
