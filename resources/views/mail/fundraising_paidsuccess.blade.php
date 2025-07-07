<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Donasi Berhasil</title>
</head>
<body style="margin:0;padding:0;background:#f7fafc;font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7fafc;padding:0;margin:0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:12px;margin:32px 0;box-shadow:0 2px 12px #0001;">
                    <tr>
                        <td style="padding:32px 24px 16px 24px;text-align:center;">
                             @if ($company_logo)
                            <img src="{{ $company_logo }}" alt="{{ $company_name }}" width="120" style="margin-bottom:16px;">
                            @endif
                            <!-- Success Icon (Iconify: mdi:check-circle) -->
                            
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                        <span style="display:inline-block;margin-bottom:12px;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="#16a34a" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.59l-4.3-4.29 1.41-1.42L10 13.17l6.29-6.3 1.41 1.42L10 16.59z"/>
                            </svg>
                        </span>
                        <h2 style="margin:0 0 8px 0;font-size:22px;color:#16a34a;">Pembayaran Donasi Berhasil!</h2>
                        <p style="margin:0 0 16px 0;font-size:16px;color:#222;">
                            Terima kasih, donasi Anda telah kami terima dan akan segera diteruskan ke penerima manfaat.
                        </p>
                    </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <div style="background:#f0fdf4;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                                <strong style="color:#16a34a;font-size:16px;">Detail Donasi</strong>
                                <table width="100%" style="margin-top:10px;font-size:15px;">
                                    <tr>
                                        <td style="color:#555;">Nama:</td>
                                        <td style="color:#222;font-weight:600;">{{ $donor_name ?? 'Anonim' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Email:</td>
                                        <td style="color:#222;font-weight:600;">{{ $donor_email ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Nominal:</td>
                                        <td style="color:#222;font-weight:600;">Rp {{ number_format($amount ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Kampanye:</td>
                                        <td style="color:#222;font-weight:600;">{{ $campaign_name ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div style="background:#f1f5f9;border-radius:8px;padding:18px 20px 18px 20px;margin-bottom:20px;">
                                <h3 style="margin:0 0 10px 0;font-size:17px;color:#16a34a;">Apa Selanjutnya?</h3>
                                <ul style="padding-left:18px;margin:0 0 12px 0;color:#222;font-size:15px;">
                                    <li>Donasi Anda akan segera diteruskan ke penerima manfaat.</li>
                                    <li>Kami akan mengirimkan update perkembangan penggunaan donasi dan informasi terkait ke email ini.</li>
                                </ul>
                            </div>
                            <div style="background:#fef9c3;border-radius:8px;padding:14px 18px;margin-bottom:18px;">
                                <span style="color:#b45309;font-size:14px;">
                                    <strong>Catatan:</strong> Jika Anda tidak menemukan email update dari kami, silakan cek folder Spam.
                                </span>
                            </div>
                            <div style="text-align:center;color:#888;font-size:13px;margin-top:18px;">
                                <hr style="border:none;border-top:1px solid #eee;margin:18px 0;">
                                <span>{{ $company_name }} &copy; {{ date('Y') }} &mdash; Terima kasih atas kebaikan Anda, Warga Baik! 💗</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>