<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .otp-box {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #667eea;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Kode Verifikasi OTP</h1>
        </div>

        <div class="content">
            <p style="font-size: 16px; color: #333;">
                @if($type === 'register')
                    Terima kasih telah mendaftar di <strong>Koladi</strong>!
                @else
                    Anda telah meminta reset password untuk akun <strong>Koladi</strong> Anda.
                @endif
            </p>

            <p style="color: #666;">
                Gunakan kode OTP di bawah ini untuk
                @if($type === 'register')
                    verifikasi email Anda:
                @else
                    mereset password Anda:
                @endif
            </p>

            <div class="otp-box">
                {{ $otp }}
            </div>

            <div class="warning">
                <strong>⚠️ Perhatian:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Kode ini berlaku selama <strong>10 menit</strong></li>
                    <li>Jangan bagikan kode ini kepada siapapun</li>
                    <li>Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Koladi. All rights reserved.</p>
            <p style="margin-top: 10px;">
                Email ini dikirim otomatis, mohon tidak membalas email ini.
            </p>
        </div>
    </div>
</body>
</html>
