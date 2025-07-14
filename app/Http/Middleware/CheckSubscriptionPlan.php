<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\Auth;

class CheckSubscriptionPlan
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @param  string  $requiredPlan
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next, string $requiredPlan = 'Paid Basic')
  {
    $user = Auth::user();

    if (!$user) {
      return BaseResponse::errorMessage('User tidak terautentikasi');
    }

    // Get user's current subscription
    $currentSubscription = $user->currentSubscription()->with('subscription')->first();

    if (!$currentSubscription) {
      return BaseResponse::errorMessage('Anda perlu berlangganan untuk mengakses fitur ini. Silakan upgrade ke plan ' . $requiredPlan);
    }

    // Check if subscription is active
    if ($currentSubscription->status !== 'active') {
      return BaseResponse::errorMessage('Subscription Anda tidak aktif. Silakan perpanjang subscription Anda');
    }

    // Check if subscription has expired
    if ($currentSubscription->end_date && $currentSubscription->end_date < now()) {
      return BaseResponse::errorMessage('Subscription Anda telah berakhir. Silakan perpanjang subscription Anda');
    }

    // Define plan hierarchy (higher number = higher plan)
    $planHierarchy = [
      'Free' => 0,
      'Paid Basic' => 1,
      'Paid Premium' => 2,
    ];

    $currentPlanLevel = $planHierarchy[$currentSubscription->subscription->plan] ?? 0;
    $requiredPlanLevel = $planHierarchy[$requiredPlan] ?? 1;

    if ($currentPlanLevel < $requiredPlanLevel) {
      return BaseResponse::errorMessage('Fitur ini memerlukan subscription plan ' . $requiredPlan . ' atau lebih tinggi. Silakan upgrade subscription Anda');
    }

    return $next($request);
  }
}
