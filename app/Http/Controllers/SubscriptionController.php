<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use App\Models\SubscriptionUser;
use App\Http\Service\MidtransCharge;
use App\Mail\SubscriptionUpgradeSuccess;
use App\Mail\SubscriptionUpgradeInstruction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    public function listPlan()
    {
        $subscriptions = Subscription::all();

        return BaseResponse::successData($subscriptions->toArray(), 'Data plan berhasil diambil');
    }
    public function createPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|string|max:255',
            'feature' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors());
        }

        $validatedData = $validator->validated();
        $subscription = Subscription::create($validatedData);

        return BaseResponse::successData($subscription->toArray(), 'Plan berhasil dibuat');
    }
    public function updatePlan(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|string|max:255',
            'feature' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors());
        }

        $validatedData = $validator->validated();
        $subscription = Subscription::find($id);

        if (!$subscription) {
            return BaseResponse::errorMessage('Plan tidak ditemukan');
        }

        $subscription->update($validatedData);

        return BaseResponse::successData($subscription->toArray(), 'Plan berhasil diperbarui');
    }

    public function upgradePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'method' => 'required|string|in:qris,gopay',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            if (!$user) {
                return BaseResponse::errorMessage('User tidak terautentikasi');
            }

            // Get current subscription
            $currentSubscription = SubscriptionUser::where('user_id', $user->id)->first();

            $targetSubscription = Subscription::find($request->subscription_id);
            if (!$targetSubscription) {
                return BaseResponse::errorMessage('Plan subscription tidak ditemukan');
            }

            if ($currentSubscription && $currentSubscription->subscription_id >= $request->subscription_id) {
                return BaseResponse::errorMessage('Anda hanya dapat melakukan upgrade ke plan yang lebih tinggi');
            }

            $orderId = 'SUB' . time() . $user->id;
            $amount = $targetSubscription->price;

            if ($currentSubscription) {
                $currentPlan = Subscription::find($currentSubscription->subscription_id);
                $proratedAmount = $amount - $currentPlan->price;
                $amount = max(0, $proratedAmount);
            }

            // Process free tier
            if ($amount == 0) {
                if ($currentSubscription) {
                    $currentSubscription->update([
                        'subscription_id' => $request->subscription_id,
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => now()->addMonth(),
                    ]);
                } else {
                    SubscriptionUser::create([
                        'user_id' => $user->id,
                        'subscription_id' => $request->subscription_id,
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => now()->addMonth(),
                        'order_id' => $orderId,
                        'amount' => 0,
                        'payment_method' => 'free',
                    ]);
                }

                DB::commit();
                return BaseResponse::successData([
                    'order_id' => $orderId,
                    'amount' => 0,
                    'status' => 'success'
                ], 'Berhasil upgrade ke plan ' . $targetSubscription->plan);
            }

            // Process payment with Midtrans
            $midtrans = new MidtransCharge($request->method, $orderId, $amount);

            $response = $midtrans->charge();

            if (isset($response['status_code']) && $response['status_code'] == 201) {
                $subscriptionUserData = [
                    'user_id' => $user->id,
                    'subscription_id' => $request->subscription_id,
                    'status' => 'pending',
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'payment_method' => $request->method,
                    'payment_link' => $response['actions'][0]['url'] ?? '',
                    'expiring_time' => date('Y-m-d H:i:s', strtotime($response['transaction_time'] . ' + 3 hours')),
                ];

                if ($currentSubscription) {
                    $currentSubscription->update($subscriptionUserData);
                    $subscriptionUser = $currentSubscription;
                } else {
                    $subscriptionUser = SubscriptionUser::create($subscriptionUserData);
                }

                // Send payment instruction email
                try {
                    Mail::to($user->email)->send(new SubscriptionUpgradeInstruction($subscriptionUser, $user, $targetSubscription));
                } catch (\Throwable $e) {
                    Log::error("Failed to send subscription upgrade instruction email: " . $e->getMessage());
                }

                DB::commit();

                return BaseResponse::successData([
                    'order_id' => $orderId,
                    'payment_link' => $subscriptionUser->payment_link,
                    'amount' => $amount,
                    'expiring_time' => $subscriptionUser->expiring_time,
                    'status' => 'pending',
                    'subscription_plan' => $targetSubscription->plan
                ], 'Link pembayaran berhasil dibuat untuk upgrade subscription');
            } else {
                DB::rollBack();
                return BaseResponse::errorMessage('Gagal membuat link pembayaran: ' . ($response['status_message'] ?? 'Unknown error'));
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Gagal melakukan upgrade subscription: " . $th->getMessage());
            return BaseResponse::errorMessage('Gagal melakukan upgrade subscription: ' . $th->getMessage());
        }
    }

    public function checkSubscriptionPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }

        try {
            $subscriptionUser = SubscriptionUser::where('order_id', $request->order_id)
                ->with(['subscription'])
                ->first();

            if (!$subscriptionUser) {
                return BaseResponse::errorMessage('Order subscription tidak ditemukan');
            }

            // Check status from Midtrans
            $serverKey = config('midtrans.MIDTRANS_SERVER_KEY');
            $authString = base64_encode($serverKey);
            $orderId = $subscriptionUser->order_id;
            $midtransApiLink = config('midtrans.MIDTRANS_API_LINK');
            $midtransUrl = rtrim($midtransApiLink, '/') . '/v2/' . $orderId . '/status';

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $authString,
            ])->get($midtransUrl);

            if ($response->failed()) {
                return BaseResponse::errorMessage('Failed to fetch status from Midtrans');
            }

            $midtransStatus = $response->json();

            // Update status if different
            if (
                isset($midtransStatus['transaction_status']) &&
                $subscriptionUser->status !== $midtransStatus['transaction_status']
            ) {

                $subscriptionUser->status = $midtransStatus['transaction_status'];

                if ($midtransStatus['transaction_status'] == 'settlement') {
                    // Activate subscription
                    $subscriptionUser->status = 'active';
                    $subscriptionUser->start_date = now();
                    $subscriptionUser->end_date = now()->addMonth();

                    // Send success notification/email
                    $user = $subscriptionUser->user;
                    if ($user && $user->email) {
                        try {
                            Mail::to($user->email)->send(new SubscriptionUpgradeSuccess($subscriptionUser, $user));
                        } catch (\Throwable $e) {
                            Log::error("Failed to send subscription upgrade success email: " . $e->getMessage());
                        }
                    }
                }

                if ($midtransStatus['transaction_status'] == 'expire') {
                    $subscriptionUser->status = 'expired';
                }

                $subscriptionUser->save();
            }

            return BaseResponse::successData([
                'order_id' => $subscriptionUser->order_id,
                'status' => $subscriptionUser->status,
                'subscription_plan' => $subscriptionUser->subscription->plan ?? '',
                'amount' => $subscriptionUser->amount,
                'start_date' => $subscriptionUser->start_date,
                'end_date' => $subscriptionUser->end_date,
            ], 'Status subscription berhasil diambil');
        } catch (\Throwable $th) {
            Log::error("Failed to check subscription payment status: " . $th->getMessage());
            return BaseResponse::errorMessage('Failed to check subscription payment status');
        }
    }

    public function getUserSubscription()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return BaseResponse::errorMessage('User tidak terautentikasi');
            }

            $subscriptionUser = SubscriptionUser::where('user_id', $user->id)
                ->where('status', 'active')
                ->with(['subscription'])
                ->first();

            if (!$subscriptionUser) {
                return BaseResponse::successData([
                    'subscription_plan' => 'Free',
                    'status' => 'active',
                    'features' => [],
                ], 'User menggunakan plan Free');
            }

            return BaseResponse::successData([
                'subscription_plan' => $subscriptionUser->subscription->plan,
                'status' => $subscriptionUser->status,
                'start_date' => $subscriptionUser->start_date,
                'end_date' => $subscriptionUser->end_date,
                'features' => $subscriptionUser->subscription->feature,
            ], 'Data subscription user berhasil diambil');
        } catch (\Throwable $th) {
            Log::error("Failed to get user subscription: " . $th->getMessage());
            return BaseResponse::errorMessage('Failed to get user subscription');
        }
    }

    public function cancelSubscription()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return BaseResponse::errorMessage('User tidak terautentikasi');
            }

            $subscriptionUser = SubscriptionUser::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$subscriptionUser) {
                return BaseResponse::errorMessage('Tidak ada subscription aktif yang ditemukan');
            }

            $subscriptionUser->update([
                'status' => 'cancelled',
                'end_date' => now() // Cancel immediately
            ]);

            return BaseResponse::successMessage('Subscription berhasil dibatalkan');
        } catch (\Throwable $th) {
            Log::error("Failed to cancel subscription: " . $th->getMessage());
            return BaseResponse::errorMessage('Failed to cancel subscription');
        }
    }

    public function extendSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'months' => 'required|integer|min:1|max:12',
            'method' => 'required|string|in:qris,gopay',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            if (!$user) {
                return BaseResponse::errorMessage('User tidak terautentikasi');
            }

            $currentSubscription = SubscriptionUser::where('user_id', $user->id)
                ->where('status', 'active')
                ->with(['subscription'])
                ->first();

            if (!$currentSubscription) {
                return BaseResponse::errorMessage('Tidak ada subscription aktif yang ditemukan');
            }

            // Calculate extension amount
            $monthlyPrice = $currentSubscription->subscription->price;
            $extensionAmount = $monthlyPrice * $request->months;

            // Create order for extension
            $orderId = 'EXT' . time() . $user->id;

            // Process payment with Midtrans
            $midtrans = new MidtransCharge($request->method, $orderId, $extensionAmount);

            $response = $midtrans->charge();

            if (isset($response['status_code']) && $response['status_code'] == 201) {
                // Create new subscription record for extension
                $extensionData = [
                    'user_id' => $user->id,
                    'subscription_id' => $currentSubscription->subscription_id,
                    'status' => 'pending',
                    'order_id' => $orderId,
                    'amount' => $extensionAmount,
                    'payment_method' => $request->method,
                    'payment_link' => $response['actions'][0]['url'] ?? '',
                    'expiring_time' => date('Y-m-d H:i:s', strtotime($response['transaction_time'] . ' + 3 hours')),
                    'start_date' => $currentSubscription->end_date, // Extension starts when current ends
                    'end_date' => $currentSubscription->end_date->addMonths($request->months),
                ];

                $extensionSubscription = SubscriptionUser::create($extensionData);

                DB::commit();

                return BaseResponse::successData([
                    'order_id' => $orderId,
                    'payment_link' => $extensionSubscription->payment_link,
                    'amount' => $extensionAmount,
                    'months' => $request->months,
                    'expiring_time' => $extensionSubscription->expiring_time,
                    'status' => 'pending',
                ], 'Link pembayaran untuk perpanjangan subscription berhasil dibuat');
            } else {
                DB::rollBack();
                return BaseResponse::errorMessage('Gagal membuat link pembayaran: ' . ($response['status_message'] ?? 'Unknown error'));
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Failed to extend subscription: " . $th->getMessage());
            return BaseResponse::errorMessage('Failed to extend subscription: ' . $th->getMessage());
        }
    }

    public function getSubscriptionHistory()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return BaseResponse::errorMessage('User tidak terautentikasi');
            }

            $subscriptionHistory = SubscriptionUser::where('user_id', $user->id)
                ->with(['subscription'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'order_id' => $item->order_id,
                        'subscription_plan' => $item->subscription->plan ?? '',
                        'amount' => $item->amount,
                        'payment_method' => $item->payment_method,
                        'status' => $item->status,
                        'start_date' => $item->start_date,
                        'end_date' => $item->end_date,
                        'created_at' => $item->created_at,
                    ];
                });

            return BaseResponse::successData($subscriptionHistory->toArray(), 'Riwayat subscription berhasil diambil');
        } catch (\Throwable $th) {
            Log::error("Failed to get subscription history: " . $th->getMessage());
            return BaseResponse::errorMessage('Failed to get subscription history');
        }
    }
}
