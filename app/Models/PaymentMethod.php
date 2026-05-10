<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id', 'name', 'type', 'is_active'];

    protected static function booted()
    {
        static::updating(function ($paymentMethod) {
            if ($paymentMethod->isDirty('is_active') && $paymentMethod->is_active) {
                $cafe = $paymentMethod->cafe;
                if ($cafe) {
                    $subscription = app(\App\Services\SubscriptionService::class)->subscriptionFor($cafe);
                    if ($subscription && $subscription->plan?->value === 'free') {
                        $max = 2; // Limit for Free plan
                        $activeCount = $cafe->paymentMethods()->where('is_active', true)->where('id', '!=', $paymentMethod->id)->count();
                        if ($activeCount >= $max) {
                            $oldest = $cafe->paymentMethods()
                                ->where('is_active', true)
                                ->where('id', '!=', $paymentMethod->id)
                                ->orderBy('id', 'asc')
                                ->first();
                                
                            if ($oldest) {
                                $oldest->update(['is_active' => false]);
                            }
                        }
                    }
                }
            }
        });
    }

    public function cafe()
    {
        return $this->belongsTo(Cafe::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
