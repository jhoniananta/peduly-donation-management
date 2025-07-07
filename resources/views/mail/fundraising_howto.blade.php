<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran Donasi</title>
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
                            <h2 style="margin:0 0 8px 0;font-size:22px;color:#F25B6A;">Satu Langkah Lagi!</h2>
                            <p style="margin:0 0 16px 0;font-size:16px;color:#222;">
                                Berikut adalah instruksi untuk menyelesaikan pembayaran donasi Anda.
                            </p>
                        </td>
                    </tr>
<tr style="text-align:center;margin:18px 0;">
    <td>
        <img src="{{ $qris_image_url ?? '#' }}" alt="QRIS" width="180" style="border-radius:8px;border:1px solid #eee;">
    </td>
</tr>
<tr>
    <td style="height:24px;"></td>
</tr>
                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <div style="background:#fef3f2;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                                <strong style="color:#F25B6A;font-size:16px;">Detail Donasi</strong>
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
                                    <tr>
                                        <td style="color:#555;">Batas Waktu:</td>
                                        <td style="color:#F25B6A;font-weight:600;">
                                            {{ $expired_at ? \Carbon\Carbon::parse($expired_at)->format('d M Y H:i') : '-' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div style="background:#f1f5f9;border-radius:8px;padding:18px 20px 18px 20px;margin-bottom:20px;">
                                <h3 style="margin:0 0 10px 0;font-size:17px;color:#F25B6A;">Instruksi Pembayaran QRIS</h3>
                                <ol style="padding-left:18px;margin:0 0 12px 0;color:#222;font-size:15px;">
                                    <li>Buka aplikasi e-wallet atau m-banking Anda yang mendukung QRIS  (Gojek, Shopee, Dana, LinkAja, Mobile Banking, dll)</li>
                                    <li>Scan atau upload gambar kode QRIS di atas</li>
                                    <li>Periksa detail pembayaran yang muncul di layar Anda.</li>
                                    <li>Lanjutkan proses pembayaran. Anda akan mendapatkan email konfirmasi dari kami apabila pembayaran berhasil.</li>
                                </ol>
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