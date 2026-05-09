<?php

namespace App\Filament\Resources\Subscriptions\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rincian Paket')
                    ->description('Informasi lengkap tentang paket langganan ini.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Paket'),
                        TextEntry::make('price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                        TextEntry::make('duration_months')
                            ->label('Durasi (bulan)')
                            ->formatStateUsing(fn ($state): string => $state.' bulan'),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ]),
                Section::make('Fitur Tersedia')
                    ->description('Daftar lengkap fitur yang tersedia dalam paket ini.')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('features')
                            ->label('Fitur')
                            ->formatStateUsing(function ($state): string {
                                if (is_array($state)) {
                                    return implode("\n", array_map(fn ($feature) => '• '.$feature, $state));
                                }

                                return (string) $state;
                            })
                            ->html(),
                    ]),
            ]);
    }
}
