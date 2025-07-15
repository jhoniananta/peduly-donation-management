<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\Donation;
use App\Models\Notification;
use App\Response\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\DonationSuccess;

class DonorController extends Controller
{
    /**
     * Tampilkan daftar semua donatur beserta donasi mereka
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Perbaikan query berdasarkan ERD
            $donors = Donor::whereHas('donations', function ($query) use ($user) {
                $query->whereHas('fundraising', function ($q) use ($user) {
                    $q->where('company_id', $user->company_id); // Gunakan company_id dari user
                });
            })
                ->with(['donations' => function ($query) use ($user) {
                    $query->whereHas('fundraising', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                        ->with('fundraising');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            return BaseResponse::successData($donors->toArray(), 'Daftar donatur berhasil diambil');
        } catch (\Throwable $th) {
            Log::error('Gagal mengambil daftar donatur: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil daftar donatur: ' . $th->getMessage());
        }
    }

    /**
     * Tampilkan detail donatur beserta history donasi
     */
    public function show(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $donor = Donor::whereHas('donations', function ($query) use ($user) {
                $query->whereHas('fundraising', function ($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                });
            })
                ->with(['donations' => function ($query) use ($user) {
                    $query->whereHas('fundraising', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                        ->with('fundraising')
                        ->orderBy('created_at', 'desc');
                }])
                ->findOrFail($id);

            return BaseResponse::successData($donor->toArray(), 'Detail donatur berhasil diambil');
        } catch (\Throwable $th) {
            Log::error('Gagal mengambil detail donatur: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil detail donatur: ' . $th->getMessage());
        }
    }

    /**
     * Update data donatur
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:16',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }

        try {
            $user = Auth::user();

            // Pastikan donatur terkait dengan company user yang login
            $donor = Donor::whereHas('donations', function ($query) use ($user) {
                $query->whereHas('fundraising', function ($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                });
            })->findOrFail($id);

            $donor->name = $request->name;
            $donor->email = $request->email;
            $donor->phone = $request->phone ?? $donor->phone;
            $donor->save();

            return BaseResponse::successData($donor->toArray(), 'Data donatur berhasil diperbarui');
        } catch (\Throwable $th) {
            Log::error('Gagal memperbarui data donatur: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal memperbarui data donatur: ' . $th->getMessage());
        }
    }

    /**
     * Kirim ulang email bukti donasi ke donatur
     */
    public function resendDonationReceipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'donation_id' => 'required|integer|exists:donations,id',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }

        try {
            $user = Auth::user();

            // Ambil donasi beserta relasi yang diperlukan
            $donation = Donation::whereHas('fundraising.company', function ($query) use ($user) {
                $query->where('company_id', $user->id);
            })
                ->with(['donor', 'fundraising.company'])
                ->where('status', 'settlement')
                ->findOrFail($request->donation_id);

            // Kirim email bukti donasi
            if ($donation->donor->email && $donation->donor->email !== '-') {
                Mail::to($donation->donor->email)
                    ->send(new DonationSuccess($donation, $donation->donor, $donation->fundraising));

                // Buat notifikasi
                Notification::create([
                    'company_id' => $user->id,
                    'content' => "📧 Email bukti donasi telah dikirim ulang ke {$donation->donor->email} untuk donasi sebesar Rp "
                        . number_format($donation->total, 0, ',', '.')
                        . " pada kampanye \"{$donation->fundraising->name}\"."
                ]);

                return BaseResponse::successMessage('Email bukti donasi berhasil dikirim ulang');
            } else {
                return BaseResponse::errorMessage('Email donatur tidak valid atau tidak tersedia');
            }
        } catch (\Throwable $th) {
            Log::error('Gagal mengirim ulang email bukti donasi: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengirim ulang email bukti donasi: ' . $th->getMessage());
        }
    }

    /**
     * Export data donatur ke CSV
     */
    public function exportDonors(Request $request)
    {
        try {
            $user = Auth::user();

            $donors = Donor::whereHas('donations.fundraising.company', function ($query) use ($user) {
                $query->where('company_id', $user->id);
            })
                ->with(['donations' => function ($query) use ($user) {
                    $query->whereHas('fundraising.company', function ($q) use ($user) {
                        $q->where('company_id', $user->id);
                    })
                        ->where('status', 'settlement')
                        ->with('fundraising');
                }])
                ->get();

            $csvData = [];
            $csvData[] = ['Nama Donatur', 'Email', 'Telepon', 'Total Donasi', 'Jumlah Donasi', 'Terakhir Donasi'];

            foreach ($donors as $donor) {
                $totalDonasi = $donor->donations->sum('total');
                $jumlahDonasi = $donor->donations->count();
                $terakhirDonasi = $donor->donations->max('created_at');

                $csvData[] = [
                    $donor->name === 'Anonim' ? 'Donatur Anonim' : $donor->name,
                    $donor->email === '-' ? 'Tidak tersedia' : $donor->email,
                    $donor->phone ?? 'Tidak tersedia',
                    'Rp ' . number_format($totalDonasi, 0, ',', '.'),
                    $jumlahDonasi,
                    $terakhirDonasi ? date('d/m/Y H:i', strtotime($terakhirDonasi)) : 'Tidak ada'
                ];
            }

            return BaseResponse::successData($csvData, 'Data export donatur berhasil diambil');
        } catch (\Throwable $th) {
            Log::error('Gagal export data donatur: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal export data donatur: ' . $th->getMessage());
        }
    }

    /**
     * Statistik donatur
     */
    public function statistics(Request $request)
    {
        try {
            $user = Auth::user();

            $stats = [
                'total_donors' => Donor::whereHas('donations', function ($query) use ($user) {
                    $query->whereHas('fundraising', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    });
                })->count(),

                'new_donors_this_month' => Donor::whereHas('donations', function ($query) use ($user) {
                    $query->whereHas('fundraising', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    });
                })
                    ->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))
                    ->count(),

                'repeat_donors' => Donor::whereHas('donations', function ($query) use ($user) {
                    $query->whereHas('fundraising', function ($q) use ($user) {
                        $q->where('company_id', $user->company_id);
                    })
                        ->where('status', 'settlement');
                }, '>', 1)->count(),

                'anonymous_donors' => Donor::where('name', 'Anonim')
                    ->whereHas('donations', function ($query) use ($user) {
                        $query->whereHas('fundraising', function ($q) use ($user) {
                            $q->where('company_id', $user->company_id);
                        });
                    })->count(),
            ];

            return BaseResponse::successData($stats, 'Statistik donatur berhasil diambil');
        } catch (\Throwable $th) {
            Log::error('Gagal mengambil statistik donatur: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil statistik donatur: ' . $th->getMessage());
        }
    }
}
