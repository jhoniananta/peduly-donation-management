<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckForAnyAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, $next, ...$abilities)
    {
        if (! $request->user() || ! $request->user()->currentAccessToken()) {
            return BaseResponse::unauthorizedMessage('Unauthenticated');
        }
        
        foreach ($abilities as $ability) {
            // dd($abilities);
            // dd($request->user()->tokenCan($ability));
            if ($request->user()->tokenCan($ability)) {
                return $next($request);
            }
        }
        
        return BaseResponse::unauthorizedMessage('Unauthorized');
    }
}
