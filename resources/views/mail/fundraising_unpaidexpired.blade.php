<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Donasi Kedaluwarsa</title>
</head>
<body style="margin:0;padding:0;background:#f7fafc;font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7fafc;padding:0;margin:0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:12px;margin:32px 0;box-shadow:0 2px 12px #0001;">
                    <tr>
                        <td style="padding:16px 24px 16px 24px;text-align:center;">
                            @if ($company_logo)
                            <img src="{{ $company_logo }}" alt="{{ $company_name }}" width="120" style="margin-bottom:16px;">
                            @endif
                            <!-- Warning Icon (Iconify: mdi:alert-circle) -->
                        </td>
                    </tr>
                    <tr>
                        <td  style="padding:32px 24px 16px 24px;text-align:center;">
                            <span style="display:inline-block;margin-bottom:12px;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="#f59e42" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 8h2v6H9zm4-7H7v2h6zm4.03 6.39A8.96 8.96 0 0 1 19 13c0 4.97-4 9-9 9a9 9 0 0 1 0-18c2.12 0 4.07.74 5.62 2l1.42-1.44c.51.44.96.9 1.41 1.41zM17 13c0-3.87-3.13-7-7-7s-7 3.13-7 7s3.13 7 7 7s7-3.13 7-7m4-6v6h2V7zm0 10h2v-2h-2z"/>
                                </svg>
                            </span>
                            <h2 style="margin:0 0 8px 0;font-size:22px;color:#f59e42;">Pembayaran Donasi Kadaluwarsa</h2>
                            <p style="margin:0 0 16px 0;font-size:16px;color:#b91c1c;">
                                Maaf, pembayaran donasi Anda telah melewati batas waktu yang ditentukan.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <div style="background:#fef3f2;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                                <strong style="color:#b91c1c;font-size:16px;">Detail Donasi</strong>
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
                            <div style="background:#fef9c3;border-radius:8px;padding:14px 18px;margin-bottom:18px;">
                                <span style="color:#b45309;font-size:14px;">
                                    <strong>Kenapa ini terjadi?</strong> Pembayaran donasi semestinya dilakukan dalam batas waktu yang telah ditentukan (3 jam setelah kode QRIS terbit) demi keamanan transaksi. Namun, Anda sangat dipersilakan untuk dapat berdonasi kembali dengan klik tombol di bawah 🤗
                                </span>
                            </div>
                            <div style="text-align:center;margin:24px 0 0 0;">
                                <a href="{{ $donate_again_url ?? '#' }}"
                                   style="display:inline-block;padding:12px 28px;background:#F25B6A;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;font-size:16px;box-shadow:0 2px 8px #f25b6a22;">
                                    Donasi Lagi
                                </a>
                            </div>
                            <div style="text-align:center;color:#888;font-size:13px;margin-top:28px;">
                                <hr style="border:none;border-top:1px solid #eee;margin:18px 0;">
                                <span>{{ $company_name }} &copy; {{ date('Y') }} &mdash; Terima kasih atas niat baik Anda, Warga Baik!</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>