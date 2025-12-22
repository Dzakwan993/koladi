<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/images/LogoAtas.svg">
    <title>Verifikasi OTP - Koladi</title>
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
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Verifikasi Email</h1>
            <p class="text-gray-500 text-sm">
                Masukkan kode OTP 6 digit yang telah dikirim ke<br>
                <strong class="text-gray-700">{{ session('register_data.email') }}</strong>
            </p>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded-lg mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Message --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-700 p-3 rounded-lg mb-4 text-sm">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        {{-- Form OTP --}}
        <form method="POST" action="{{ route('verify-otp.verify') }}" class="space-y-6">
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

            {{-- Hidden input untuk kirim OTP --}}
            <input type="hidden" name="otp" id="otp-value">

            {{-- Timer & Resend --}}
            <div class="text-center text-sm text-gray-600">
                <span>Tidak menerima kode? </span>
                <button type="button" id="resend-btn" class="text-blue-600 font-semibold hover:underline" disabled>
                    Kirim ulang (<span id="timer">60</span>s)
                </button>
            </div>

            {{-- Submit Button --}}
            <button type="submit" id="verify-btn"
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-md hover:shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                disabled>
                Verifikasi
            </button>

            {{-- Back to Register --}}
            <div class="text-center">
                <a href="{{ route('daftar') }}" class="text-sm text-gray-600 hover:text-blue-600">
                    ← Kembali ke halaman pendaftaran
                </a>
            </div>
        </form>
    </div>

    <script>
        const inputs = document.querySelectorAll('#otp-inputs input');
        const hiddenOtp = document.getElementById('otp-value');
        const verifyBtn = document.getElementById('verify-btn');
        const resendBtn = document.getElementById('resend-btn');
        const timerSpan = document.getElementById('timer');
        let timeLeft = 60;

        // Auto-focus & move to next input
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;

                // Hanya angka
                if (!/^\d$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // Pindah ke input berikutnya
                if (value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                // Update hidden input
                updateOtpValue();
            });

            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Auto-select on focus
            input.addEventListener('focus', (e) => {
                e.target.select();
            });
        });

        // Update hidden OTP value
        function updateOtpValue() {
            const otp = Array.from(inputs).map(input => input.value).join('');
            hiddenOtp.value = otp;
            verifyBtn.disabled = otp.length !== 6;
        }

        // Timer countdown
        const countdown = setInterval(() => {
            timeLeft--;
            timerSpan.textContent = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Kirim ulang';
            }
        }, 1000);

        // Resend OTP
        resendBtn.addEventListener('click', async () => {
            resendBtn.disabled = true;
            resendBtn.textContent = 'Mengirim...';

            try {
                const response = await fetch('{{ route('verify-otp.resend') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Kode OTP baru telah dikirim!');
                    timeLeft = 60;
                    const newCountdown = setInterval(() => {
                        timeLeft--;
                        timerSpan.textContent = timeLeft;
                        if (timeLeft <= 0) {
                            clearInterval(newCountdown);
                            resendBtn.disabled = false;
                            resendBtn.innerHTML = 'Kirim ulang';
                        }
                    }, 1000);
                    resendBtn.innerHTML = `Kirim ulang (<span id="timer">${timeLeft}</span>s)`;
                } else {
                    alert('Gagal mengirim ulang OTP. Silakan coba lagi.');
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Kirim ulang';
                }
            } catch (error) {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                resendBtn.disabled = false;
                resendBtn.textContent = 'Kirim ulang';
            }
        });

        // Auto-focus first input
        inputs[0].focus();
    </script>
</body>

</html>
