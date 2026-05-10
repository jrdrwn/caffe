<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['cafe_id', 'category_id', 'name', 'description', 'price', 'discount_percentage', 'stock', 'sku', 'image_url', 'is_active', 'has_variants', 'variants'];

    protected $casts = [
        'has_variants' => 'boolean',
        'discount_percentage' => 'integer',
        'variants' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if ($product->is_active) {
                $cafe = \App\Models\Cafe::find($product->cafe_id);
                if ($cafe) {
                    $subscription = app(\App\Services\SubscriptionService::class)->subscriptionFor($cafe);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_products');
                        if ($max !== null) {
                            $activeCount = $cafe->products()->where('is_active', true)->count();
                            if ($activeCount >= $max) {
                                $oldest = $cafe->products()
                                    ->where('is_active', true)
                                    ->orderBy('id', 'asc')
                                    ->first();
                                    
                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
                                }
                            }
                        }
                    }
                }
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('is_active') && $product->is_active) {
                $cafe = \App\Models\Cafe::find($product->cafe_id);
                if ($cafe) {
                    $subscription = app(\App\Services\SubscriptionService::class)->subscriptionFor($cafe);
                    if ($subscription) {
                        $max = $subscription->getLimit('max_products');
                        if ($max !== null) {
                            $activeCount = $cafe->products()->where('is_active', true)->where('id', '!=', $product->id)->count();
                            if ($activeCount >= $max) {
                                $oldest = $cafe->products()
                                    ->where('is_active', true)
                                    ->where('id', '!=', $product->id)
                                    ->orderBy('id', 'asc')
                                    ->first();
                                    
                                if ($oldest) {
                                    $oldest->update(['is_active' => false]);
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
