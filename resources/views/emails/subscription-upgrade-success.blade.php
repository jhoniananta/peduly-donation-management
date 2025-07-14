<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade Subscription Berhasil</title>
</head>

<body style="margin:0;padding:0;background:#f7fafc;font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7fafc;padding:0;margin:0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:480px;background:#fff;border-radius:12px;margin:32px 0;box-shadow:0 2px 12px #0001;">
                    <tr>
                        <td style="padding:32px 24px 16px 24px;text-align:center;">
                            <!-- Success Icon -->
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">
                            <span style="display:inline-block;margin-bottom:12px;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="#16a34a"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.59l-4.3-4.29 1.41-1.42L10 13.17l6.29-6.3 1.41 1.42L10 16.59z" />
                                </svg>
                            </span>
                            <h2 style="margin:0 0 8px 0;font-size:22px;color:#16a34a;">Upgrade Subscription Berhasil!
                            </h2>
                            <p style="margin:0 0 16px 0;font-size:16px;color:#222;">
                                Selamat <strong>{{ $user->name }}</strong>! Upgrade subscription Anda ke plan
                                <strong>{{ $subscriptionUser->subscription->plan }}</strong> telah berhasil diproses.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <div style="background:#f0fdf4;border-radius:8px;padding:16px 20px;margin-bottom:20px;">
                                <strong style="color:#16a34a;font-size:16px;">Detail Subscription</strong>
                                <table width="100%" style="margin-top:10px;font-size:15px;">
                                    <tr>
                                        <td style="color:#555;">Plan:</td>
                                        <td style="color:#222;font-weight:600;">
                                            {{ $subscriptionUser->subscription->plan }}</td>
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
                                        <td style="color:#555;">Mulai:</td>
                                        <td style="color:#222;font-weight:600;">
                                            {{ $subscriptionUser->start_date->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Berakhir:</td>
                                        <td style="color:#222;font-weight:600;">
                                            {{ $subscriptionUser->end_date->format('d M Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#555;">Status:</td>
                                        <td style="color:#16a34a;font-weight:600;">
                                            {{ ucfirst($subscriptionUser->status) }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div
                                style="background:#f1f5f9;border-radius:8px;padding:18px 20px 18px 20px;margin-bottom:20px;">
                                <h3 style="margin:0 0 10px 0;font-size:17px;color:#16a34a;">Fitur
                                    {{ $subscriptionUser->subscription->plan }}</h3>
                                <p style="padding-left:0;margin:0 0 12px 0;color:#222;font-size:15px;">
                                    {{ $subscriptionUser->subscription->feature ?: 'Fitur akan segera tersedia untuk Anda' }}
                                </p>
                            </div>

                            <div
                                style="background:#e8f4fd;border-radius:8px;padding:18px 20px;margin-bottom:20px;text-align:center;">
                                <h3 style="margin:0 0 10px 0;font-size:17px;color:#4a90e2;">Apa Selanjutnya?</h3>
                                <ul
                                    style="padding-left:18px;margin:0 0 12px 0;color:#222;font-size:15px;text-align:left;">
                                    <li>Subscription Anda sudah aktif dan siap digunakan</li>
                                    <li>Akses semua fitur premium sesuai plan Anda</li>
                                    <li>Kami akan mengirimkan update dan informasi penting ke email ini</li>
                                </ul>
                            </div>

                            <div style="background:#fef9c3;border-radius:8px;padding:14px 18px;margin-bottom:18px;">
                                <span style="color:#b45309;font-size:14px;">
                                    <strong>Catatan:</strong> Jika Anda tidak menemukan email update dari kami, silakan
                                    cek folder Spam.
                                </span>
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
