<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionPlan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
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
                if ($subscription && $subscription->plan !== SubscriptionPlan::Free) {
                    // Find latest successful payment
                    $latestPayment = SubscriptionPayment::where('cafe_id', $cafe->id)
                        ->where('status', 'success')
                        ->latest()
                        ->first();

                    if ($latestPayment && $latestPayment->settlement_time) {
                        $expiresAt = $latestPayment->settlement_time->addMonths((int) $subscription->duration_months);
                        if (now()->greaterThan($expiresAt)) {
                            // Expired!
                            // Downgrade to Free plan!
                            $freePlan = Subscription::where('plan', SubscriptionPlan::Free)->first();
                            if ($freePlan) {
                                $cafe->update(['subscription_id' => $freePlan->id]);

                                // Enforce limits!
                                app(SubscriptionService::class)->enforceLimits($cafe);
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
