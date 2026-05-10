<?php

namespace App\Filament\Widgets;

use App\Models\Cafe;
use App\Models\CafeManager;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SuperAdminStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 3;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }

    protected function getStats(): array
    {
        $totalCafes = Cafe::query()->count();
        $activeCafes = Cafe::query()->where('is_active', true)->count();
        $inactiveCafes = $totalCafes - $activeCafes;

        $activePlans = Subscription::query()->where('is_active', true)->count();

        $totalTransactions = \App\Models\Transaction::count();
        $totalStaff = \App\Models\User::whereIn('role', ['manager', 'cashier'])->count();

        return [
            Stat::make('Total Klien Cafe', $totalCafes)
                ->description($activeCafes . ' aktif · ' . $inactiveCafes . ' nonaktif')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('amber'),

            Stat::make('Total Transaksi', $totalTransactions)
                ->description('Semua transaksi tercatat')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Total Staff', $totalStaff)
                ->description('Manager & Kasir terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Plan Tersedia', $activePlans)
                ->description('subscription plan aktif')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
        ];
    }
}
