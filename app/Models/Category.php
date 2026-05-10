<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id', 'name', 'description', 'image_url', 'display_order', 'is_active'];

    protected static function booted()
    {
        static::creating(function ($category) {
            if ($category->is_active) {
                $cafe = \App\Models\Cafe::find($category->cafe_id);
                if ($cafe) {
                    $subscription = app(\App\Services\SubscriptionService::class)->subscriptionFor($cafe);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_categories');
                        if ($max !== null) {
                            $activeCount = $cafe->categories()->where('is_active', true)->count();
                            if ($activeCount >= $max) {
                                $oldest = $cafe->categories()
                                    ->where('is_active', true)
                                    ->orderBy('id', 'asc')
                                    ->first();
                                    
                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
                                    $oldest->products()->update(['is_active' => false]);
                                }
                            }
                        }
                    }
                }
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('is_active') && $category->is_active) {
                $cafe = \App\Models\Cafe::find($category->cafe_id);
                if ($cafe) {
                    $subscription = app(\App\Services\SubscriptionService::class)->subscriptionFor($cafe);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_categories');
                        if ($max !== null) {
                            $activeCount = $cafe->categories()->where('is_active', true)->where('id', '!=', $category->id)->count();
                            if ($activeCount >= $max) {
                                $oldest = $cafe->categories()
                                    ->where('is_active', true)
                                    ->where('id', '!=', $category->id)
                                    ->orderBy('id', 'asc')
                                    ->first();
                                    
                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
                                    $oldest->products()->update(['is_active' => false]);
                                }
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

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
