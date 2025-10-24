<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Koladi</title>
</head>

<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f3f4f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                    style="background-color: #ffffff; border-radius: 12px; max-width: 600px;">

                    <!-- Header -->
                    <tr>
                        <td
                            style="background-color: #225AD6; padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <div
                                            style="background-color: rgba(255, 255, 255, 0.2); width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px; line-height: 80px; text-align: center;">
                                            <span style="font-size: 40px;">👥</span>
                                        </div>
                                        <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;">
                                            Undangan Bergabung</h1>
                                        <p style="margin: 10px 0 0; color: #e0e7ff; font-size: 14px;">Koladi -
                                            Kolaborasi Digital</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 20px; color: #374151; font-size: 16px;">Halo! 👋</p>

                                        <p style="margin: 0 0 25px; color: #374151; font-size: 16px;">
                                            <strong style="color: #1f2937;">{{ $inviterName }}</strong> mengundang Anda
                                            untuk bergabung ke perusahaan:
                                        </p>

                                        <!-- Company Card -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                            style="margin-bottom: 30px;">
                                            <tr>
                                                <td
                                                    style="background-color: #eff6ff; border-left: 4px solid #225AD6; padding: 20px; border-radius: 8px;">
                                                    <table width="100%" cellpadding="0" cellspacing="0"
                                                        border="0">
                                                        <tr>
                                                            <td width="60" valign="top">
                                                                <div
                                                                    style="background-color: #225AD6; width: 48px; height: 48px; border-radius: 8px; text-align: center; line-height: 48px;">
                                                                    <span style="font-size: 24px;">🏢</span>
                                                                </div>
                                                            </td>
                                                            <td valign="middle">
                                                                <p
                                                                    style="margin: 0; color: #1e40af; font-size: 12px; font-weight: bold; text-transform: uppercase;">
                                                                    PERUSAHAAN</p>
                                                                <p
                                                                    style="margin: 5px 0 0; color: #1e3a8a; font-size: 20px; font-weight: bold;">
                                                                    {{ $company }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <p style="margin: 0 0 30px; color: #6b7280; font-size: 15px;">
                                            Bergabunglah dengan tim dan mulai berkolaborasi dalam proyek, jadwal, dan
                                            tugas bersama.
                                        </p>

                                        <!-- CTA Button -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="padding: 10px 0 30px;">
                                                    <a href="{{ $url }}"
                                                        style="display: inline-block; background-color: #225AD6; color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                                        ✓ Terima Undangan
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Warning Box -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td
                                                    style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 6px;">
                                                    <p style="margin: 0; color: #92400e; font-size: 14px;">
                                                        <strong>⏰ Penting:</strong> Undangan ini berlaku selama
                                                        <strong>3 hari</strong> sejak dikirim.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Alternative Link -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                            style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                            <tr>
                                                <td>
                                                    <p style="margin: 0 0 10px; color: #6b7280; font-size: 13px;">
                                                        Jika tombol tidak berfungsi, salin dan tempel tautan ini ke
                                                        browser Anda:
                                                    </p>
                                                    <p style="margin: 0; word-break: break-all;">
                                                        <a href="{{ $url }}"
                                                            style="color: #225AD6; font-size: 13px;">{{ $url }}</a>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb; border-radius: 0 0 12px 12px;">
                            <p style="margin: 0 0 10px; color: #6b7280; font-size: 14px;">
                                Terima kasih telah menggunakan <strong style="color: #225AD6;">Koladi</strong>
                            </p>
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
                            </p>
                            <p style="margin: 20px 0 0; color: #d1d5db; font-size: 11px;">
                                © 2025 Koladi. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

                <!-- Bottom Text -->
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                    style="max-width: 600px; margin-top: 20px;">
                    <tr>
                        <td align="center">
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                Tidak mengenali email ini? Abaikan saja email ini dengan aman.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
