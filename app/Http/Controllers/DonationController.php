<?php

namespace App\Http\Controllers;

use App\Mail\DonationExpired;
use App\Mail\DonationInstruction;
use App\Mail\DonationSuccess;
use App\Models\Donor;
use App\Models\Donation;
use App\Models\Fundraising;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\MidtransCharge;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class DonationController extends Controller
{
    //
    public function donation(Request $request)
    {
        if ($request->input('method') == 'manual') {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|max:255',
                'fundraising_id' => 'required|integer|exists:fundraisings,id',
                'wish' => 'nullable|string|max:255',
                'total' => 'required|numeric|min:0',
                'method' => 'required|string',
                'is_anonymous' => 'required|boolean',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:16',
                'fundraising_id' => 'required|integer|exists:fundraisings,id',
                'wish' => 'nullable|string|max:255',
                'total' => 'required|numeric|min:0',
                'method' => 'required|string|in:qris,gopay',
                'is_anonymous' => 'required|boolean',
            ]);
        }

        if ($validator->fails()) {
            return BaseResponse::errorMessage(
                $validator->errors()->first()
            );
        }
        try {
            //code...
            DB::beginTransaction();

            if ($request->method != 'manual') {
                $donor = Donor::where('email', $request->email)->first();
                if (!$donor) {
                    $donor = new Donor();
                    $donor->name = $request->name;
                    $donor->email = $request->email;
                    $donor->phone = $request->phone;
                    $donor->save();
                }
            } else {
                $donor = Donor::where('name', $request->name)->first();
                if (!$donor) {
                    $donor = new Donor();
                    $donor->name = $request->name;
                    $donor->email = '-';
                    $donor->save();
                }
            }

            $donation = new Donation();
            $donation->fundraising_id = $request->fundraising_id;
            $donation->is_anonymous = $request->is_anonymous;
            $donation->donor_id = $donor->id;
            $donation->wish = $request->wish;
            $donation->total = $request->total;
            $donation->method = $request->method;
            // dd($donation->method);
            $donation->order_id = 'INV' . time();
            if ($request->method != 'manual') {

                $midtrans = new MidtransCharge($donation->method, $donation->order_id, $donation->total);
                $response = $midtrans->charge();
                if (isset($response['status_code']) && $response['status_code'] == 201) {
                    // dd($response);
                    $donation->payment_link = $response['actions'][0]['url'];
                    $donation->expiring_time = date('Y-m-d H:i:s', strtotime($response['transaction_time'] . ' + 3 hours'));
                    $donation->status = $response['transaction_status'];
                    $donation->save();
                    // Kirim email ke donor
                    $fundraising_id = $request->fundraising_id;
                    $fundraising = Fundraising::where('id', $fundraising_id)->with(['company'])->first();

                    Mail::to($donor->email)->send(new DonationInstruction($donation, $donor, $fundraising));
                } else {
                    throw new \Exception($response->message);
                }
            } else {
                $donation->status = 'settlement';
                $donation->payment_link = '-';
                $donation->save();

                // Load relasi untuk mendapatkan company owner
                $donation->load('fundraising.company', 'donor');


                $ownerId = optional($donation->fundraising->company)->id;

                // Jika owner ID dari company kosong, gunakan user yang sedang login
                if (!$ownerId) {
                    $currentUser = Auth::user();
                    if ($currentUser) {
                        $ownerId = $currentUser->id;
                    }
                }
                // Buat notifikasi untuk donasi manual (input dari admin perusahaan)
                if ($ownerId) {
                    try {
                        $content = "🎉 Donasi baru: Rp "
                            . number_format($donation->total, 0, ',', '.')
                            . " dari "
                            . ($donation->is_anonymous ? 'Donatur Anonim' : $donation->donor->name)
                            . " untuk kampanye \"{$donation->fundraising->name}\".";

                        Notification::create([
                            'user_id' => $ownerId,
                            'status' => 'unread',
                            'content' => $content
                        ]);
                    } catch (\Throwable $e) {
                        Log::error("Error membuat notification: " . $e->getMessage());
                    }
                } else {
                    Log::warning("Owner ID tidak ditemukan, notifikasi tidak dibuat");
                }
            }

            DB::commit();
            return BaseResponse::successData($donation->toArray(), 'Berhasil melakukan donasi');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::error("Gagal melakukan donasi " . $th->getMessage());
            return BaseResponse::errorMessage('Gagal melakukan donasi ' . $th->getMessage());
        }
        // Proceed with further logic if validation passes
    }
    // callback not use because using only one midtrans , just use checkStatus bolo
    public function callback(Request $request)
    {

        try {
            $donation = Donation::where('order_id', $request->order_id)->first();

            if (!$donation) {
                return BaseResponse::errorMessage('Donation not found');
            }

            $calculatedSignatureKey = hash('sha512', $donation->order_id . $request->status_code . $donation->total . config('MIDTRANS_SERVER_KEY'));
            if ($calculatedSignatureKey !== $request->signature_key) {
                return BaseResponse::errorMessage('Invalid signature key');
            }

            $donation->status = $request->transaction_status;

            $donation->save();

            return BaseResponse::successMessage('Callback processed successfully');
        } catch (\Throwable $th) {
            Log::error("Failed to process callback: " . $th->getMessage());
            return BaseResponse::errorMessage('Failed to process callback');
        }
    }
    public function checkStatus(Request $request)
    {
        try {
            $donation = Donation::where('order_id', $request->order_id)->with(['fundraising', 'donor', 'fundraising.company'])->first();
            if (!$donation) {
                return BaseResponse::errorMessage('Donation not found');
            }
            // Cek status ke Midtrans
            $serverKey = config('midtrans.MIDTRANS_SERVER_KEY');
            $authString = base64_encode($serverKey);
            // dd($authString);
            $orderId = $donation->order_id;
            $midtransApiLink = config('midtrans.MIDTRANS_API_LINK');
            $midtransUrl = rtrim($midtransApiLink, '/') . '/v2/' . $orderId . '/status';
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $authString,
            ])
                ->get($midtransUrl);
            if ($response->failed()) {
                return BaseResponse::errorMessage('Failed to fetch status from Midtrans');
            }
            $midtransStatus = $response->json();

            // Update status di database jika berbeda
            if (isset($midtransStatus['transaction_status']) && $donation->status !== $midtransStatus['transaction_status']) {
                $donation->status = $midtransStatus['transaction_status'];
                if ($midtransStatus['transaction_status'] == 'settlement') {
                    try {
                        $donor = $donation->donor;

                        // Update data donator jika diperlukan
                        if ($donor) {
                            // Update name jika masih kosong atau default
                            if (empty($donor->name) || $donor->name === '-' || $donor->name === 'Anonymous') {
                                $donor->name = $donation->donor->name ?? $donor->name;
                            }

                            // Update email jika masih kosong atau default
                            if (empty($donor->email) || $donor->email === '-') {
                                $donor->email = $donation->donor->email ?? $donor->email;
                            }

                            // Update phone jika masih kosong
                            if (empty($donor->phone)) {
                                $donor->phone = $donation->donor->phone ?? $donor->phone;
                            }

                            $donor->save();

                            Log::info("Updated basic donor info for ID: {$donor->id}");
                        }
                    } catch (\Throwable $th) {
                        Log::error("Error updating donor information: " . $th->getMessage());
                    }
                    Mail::to($donation->donor->email)->send(new DonationSuccess($donation, $donation->donor, $donation->fundraising));

                    // Buat notifikasi untuk settlement
                    $ownerId = optional($donation->fundraising->company)->id;
                    if ($ownerId) {
                        try {
                            $content = "🎉 Donasi baru: Rp "
                                . number_format($donation->total, 0, ',', '.')
                                . " dari "
                                . ($donation->is_anonymous ? 'Donatur Anonim' : $donation->donor->name)
                                . " untuk kampanye \"{$donation->fundraising->name}\".";

                            Notification::create([
                                'user_id' => $ownerId,
                                'status' => 'unread',
                                'content' => $content
                            ]);
                        } catch (\Throwable $e) {
                            Log::error("Error membuat notification settlement: " . $e->getMessage());
                        }
                    }
                }
                if ($midtransStatus['transaction_status'] == 'expire') {
                    Mail::to($donation->donor->email)->send(new DonationExpired($donation, $donation->donor, $donation->fundraising));
                }
                $donation->save();
            }
            return BaseResponse::successData($donation->toArray(), 'Donation status retrieved successfully');
        } catch (\Throwable $th) {
            Log::error("Failed to check donation status: " . $th->getMessage());
            return BaseResponse::errorMessage('Failed to check donation status');
        }
    }
}
