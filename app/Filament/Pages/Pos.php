<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Pos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected string $view = 'filament.pages.pos';

    public array $products = [];

    public array $categories = [];

    public function mount(): void
    {
        $user = Auth::user();

        $productQuery = Product::query()
            ->where('is_active', true)
            ->select(['id', 'cafe_id', 'category_id', 'name', 'sku', 'price', 'stock', 'image_url', 'has_variants', 'variants']);

        $categoryQuery = Category::query()
            ->select(['id', 'name']);

        if ($user?->role === 'cashier' && filled($user->cafe_id)) {
            $productQuery->where('cafe_id', $user->cafe_id);
            $categoryQuery->where('cafe_id', $user->cafe_id);
        }

        $this->products = $productQuery->orderBy('name')->get()->toArray();
        $this->categories = $categoryQuery->orderBy('name')->get()->toArray();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'cashier';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Operasional';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
