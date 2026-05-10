<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && $user->role === 'manager' && $user->cafe_id) {
            $cafe = $user->cafe;
            if ($cafe && $cafe->subscription_id) {
                $subscription = $cafe->subscription;
                if ($subscription && $subscription->plan !== \App\Enums\SubscriptionPlan::Free) {
                    // Find latest successful payment
                    $latestPayment = \App\Models\SubscriptionPayment::where('cafe_id', $cafe->id)
                        ->where('status', 'success')
                        ->latest()
                        ->first();
                        
                    if ($latestPayment && $latestPayment->settlement_time) {
                        $expiresAt = $latestPayment->settlement_time->addMonths($subscription->duration_months);
                        if (now()->greaterThan($expiresAt)) {
                            // Expired!
                            // Downgrade to Free plan!
                            $freePlan = \App\Models\Subscription::where('plan', \App\Enums\SubscriptionPlan::Free)->first();
                            if ($freePlan) {
                                $cafe->update(['subscription_id' => $freePlan->id]);
                                
                                // Enforce limits!
                                app(\App\Services\SubscriptionService::class)->enforceLimits($cafe);
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
