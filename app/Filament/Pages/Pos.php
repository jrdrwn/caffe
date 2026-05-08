<?php

namespace App\Filament\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;

class Pos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected string $view = 'filament.pages.pos';

    public array $products = [];

    public function mount(): void
    {
        $this->products = Product::where('is_active', true)->get()->toArray();
    }
}
