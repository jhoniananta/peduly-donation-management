<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruksi Pembayaran Upgrade Subscription</title>
</head>

<body style="margin:0;padding:0;background:#f7fafc;font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7fafc;padding:0;margin:0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:480px;background:#fff;border-radius:12px;margin:32px 0;box-shadow:0 2px 12px #0001;">
                    <tr>
                        <td style="padding:32px 24px 16px 24px;text-align:center;">
                            <h2 style="margin:0 0 8px 0;font-size:22px;color:#4a90e2;">Satu Langkah Lagi!</h2>
                            <p style="margin:0 0 16px 0;font-size:16px;color:#222;">
                                Halo <strong>{{ $user->name }}</strong>, berikut adalah instruksi untuk menyelesaikan
                                pembayaran upgrade subscription Anda ke plan <strong>{{ $subscription->plan }}</strong>.
                            </p>
                        </td>
                    </tr>

                    @if ($subscriptionUser->payment_method == 'qris' || $subscriptionUser->payment_method == 'gopay')
                        <tr style="text-align:center;margin:18px 0;">
                            <td>
                                @if (isset($qris_image_url) && $qris_image_url)
                                    <img src="{{ $qris_image_url }}" alt="QRIS Code" width="180"
                                        style="border-radius:8px;border:1px solid #eee;margin-bottom:16px;">
                                @endif
                                <div style="background:#e8f4fd;border-radius:8px;padding:16px;margin:0 24px;">
                                    <a href="{{ $subscriptionUser->payment_link }}"
                                        style="display:inline-block;background:#4a90e2;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;">
                                        @if ($subscriptionUser->payment_method == 'qris')
                                            Bayar dengan QRIS
                                        @else
                                            Bayar dengan GoPay
                                        @endif
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="height:24px;"></td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <div style="background:#e8f4fd;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                                <strong style="color:#4a90e2;font-size:16px;">Detail Pembayaran</strong>
                                <table width="100%" style="margin-top:10px;font-size:15px;">
                                    <tr>
                                        <td style="color:#555;">Plan:</td>
                                        <td style="color:#222;font-weight:600;">{{ $subscription->plan }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Order ID:</td>
                                        <td style="color:#222;font-weight:600;">{{ $subscriptionUser->order_id }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Nominal:</td>
                                        <td style="color:#222;font-weight:600;">Rp
                                            {{ number_format($subscriptionUser->amount, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Metode:</td>
                                        <td style="color:#222;font-weight:600;">
                                            {{ ucfirst(str_replace('_', ' ', $subscriptionUser->payment_method)) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Batas Waktu:</td>
                                        <td style="color:#4a90e2;font-weight:600;">
                                            {{ $subscriptionUser->expiring_time->format('d M Y H:i') }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div
                                style="background:#f1f5f9;border-radius:8px;padding:18px 20px 18px 20px;margin-bottom:20px;">
                                <h3 style="margin:0 0 10px 0;font-size:17px;color:#4a90e2;">Instruksi Pembayaran</h3>
                                <ol style="padding-left:18px;margin:0 0 12px 0;color:#222;font-size:15px;">
                                    @if ($subscriptionUser->payment_method == 'qris')
                                        <li>Klik tombol "Bayar dengan QRIS" di atas</li>
                                        <li>Scan QR Code yang muncul menggunakan aplikasi e-wallet atau m-banking Anda
                                        </li>
                                        <li>Konfirmasi pembayaran sebesar Rp
                                            {{ number_format($subscriptionUser->amount, 0, ',', '.') }}</li>
                                        <li>Selesaikan pembayaran sebelum
                                            {{ $subscriptionUser->expiring_time->format('d M Y H:i') }}</li>
                                    @elseif($subscriptionUser->payment_method == 'gopay')
                                        <li>Klik tombol "Bayar dengan GoPay" di atas</li>
                                        <li>Scan QR Code menggunakan aplikasi GoPay/Gojek atau aplikasi pembayaran
                                            lainnya</li>
                                        <li>Konfirmasi pembayaran sebesar Rp
                                            {{ number_format($subscriptionUser->amount, 0, ',', '.') }}</li>
                                        <li>Selesaikan pembayaran sebelum
                                            {{ $subscriptionUser->expiring_time->format('d M Y H:i') }}</li>
                                    @else
                                        <li>Ikuti instruksi pembayaran yang diberikan melalui link pembayaran</li>
                                        <li>Pastikan jumlah pembayaran sesuai</li>
                                        <li>Selesaikan sebelum waktu kedaluwarsa</li>
                                    @endif
                                </ol>
                            </div>

                            <div style="background:#fef9c3;border-radius:8px;padding:14px 18px;margin-bottom:18px;">
                                <span style="color:#b45309;font-size:14px;">
                                    <strong>Penting:</strong> Pembayaran akan otomatis dibatalkan jika tidak
                                    diselesaikan dalam 3 jam. Setelah pembayaran berhasil, Anda akan mendapat email
                                    konfirmasi.
                                </span>
                            </div>

                            <div style="background:#f0fdf4;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                                <strong style="color:#16a34a;font-size:16px;">Fitur {{ $subscription->plan }}</strong>
                                <p style="margin:10px 0 0 0;color:#222;font-size:15px;">
                                    {{ $subscription->feature ?: 'Fitur akan segera tersedia setelah pembayaran berhasil' }}
                                </p>
                            </div>

                            <div style="text-align:center;color:#888;font-size:13px;margin-top:18px;">
                                <hr style="border:none;border-top:1px solid #eee;margin:18px 0;">
                                <span>Pedulydonation &copy; {{ date('Y') }} &mdash; Terima kasih atas kepercayaan
                                    Anda! 💙</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
