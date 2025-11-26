<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP Reset Password - Koladi</title>
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
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Verifikasi Kode OTP</h1>
            <p class="text-gray-500 text-sm">
                Masukkan kode OTP yang telah dikirim ke<br>
                <strong class="text-gray-700">{{ session('reset_email') }}</strong>
            </p>
        </div>

        {{-- Messages --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded-lg mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-lg mb-4 text-sm">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        {{-- Form OTP --}}
        <form method="POST" action="{{ route('reset-password.verify-otp-submit') }}" class="space-y-6">
            @csrf

            {{-- Input OTP --}}
            <div class="flex justify-center gap-2" id="otp-inputs">
                <input type="text" maxlength="1"
                    class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                    data-index="0" autocomplete="off">
                <input type="text" maxlength="1"
                    class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                    data-index="1" autocomplete="off">
                <input type="text" maxlength="1"
                    class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                    data-index="2" autocomplete="off">
                <input type="text" maxlength="1"
                    class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                    data-index="3" autocomplete="off">
                <input type="text" maxlength="1"
                    class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                    data-index="4" autocomplete="off">
                <input type="text" maxlength="1"
                    class="w-12 h-14 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                    data-index="5" autocomplete="off">
            </div>

            <input type="hidden" name="otp" id="otp-value">

            <button type="submit" id="verify-btn"
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-md hover:shadow-lg disabled:bg-gray-400"
                disabled>
                Verifikasi & Lanjutkan
            </button>

            <div class="text-center">
                <a href="{{ route('forgot-password') }}" class="text-sm text-gray-600 hover:text-blue-600">
                    ← Kembali
                </a>
            </div>
        </form>
    </div>

    <script>
        const inputs = document.querySelectorAll('#otp-inputs input');
        const hiddenOtp = document.getElementById('otp-value');
        const verifyBtn = document.getElementById('verify-btn');

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (!/^\d$/.test(e.target.value)) {
                    e.target.value = '';
                    return;
                }
                if (e.target.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateOtpValue();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('focus', (e) => e.target.select());
        });

        function updateOtpValue() {
            const otp = Array.from(inputs).map(input => input.value).join('');
            hiddenOtp.value = otp;
            verifyBtn.disabled = otp.length !== 6;
        }

        inputs[0].focus();
    </script>
</body>
</html>
