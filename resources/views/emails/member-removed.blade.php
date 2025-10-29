<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }

        .info-box {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #dc3545;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>🚫 Akses Dicabut</h2>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $user->full_name }}</strong>,</p>

            <p>Kami informasikan bahwa akses Anda ke perusahaan berikut telah dicabut:</p>

            <div class="info-box">
                <strong>Nama Perusahaan:</strong> {{ $company->name }}<br>
                <strong>Dicabut oleh:</strong> {{ $removedBy->full_name }}<br>
                <strong>Tanggal:</strong> {{ now()->format('d M Y, H:i') }} WIB
            </div>

            <p>Mulai saat ini, Anda tidak dapat lagi mengakses data dan fitur dari perusahaan tersebut.</p>

            <p>Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator perusahaan.</p>

            <p>Terima kasih,<br>
                <strong>Tim Koladi</strong>
            </p>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
        </div>
    </div>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                confirmButtonColor: '#3085d6',
            });
        </script>
    @endif
</body>

</html>
