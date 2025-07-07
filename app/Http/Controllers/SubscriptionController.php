<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use Illuminate\Support\Facades\Validator;

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
}
