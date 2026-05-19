<?php

namespace App\Filament\Resources\CafeManagers\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CafeManagerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Penugasan')
                    ->description('Detail hubungan manager dengan cafe yang ditugaskan.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('cafe.name')
                            ->label('Cafe'),
                        TextEntry::make('manager.name')
                            ->label('Manager'),
                        TextEntry::make('assigned_at')
                            ->label('Assigned At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Belum ditetapkan'),
                        TextEntry::make('assignedBy.name')
                            ->label('Assigned By')
                            ->placeholder('Belum ditetapkan'),
                    ]),
                Section::make('Cafe')
                    ->description('Info cafe yang dipakai oleh manager ini.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('cafe.owner_name')
                            ->label('Pemilik Cafe')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('cafe.is_active')
                            ->label('Status Cafe')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                    ]),
                Section::make('Langganan')
                    ->description('Paket aktif cafe beserta masa berlakunya.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('cafe.subscription.name')
                            ->label('Paket')
                            ->badge()
                            ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                                'free' => 'gray',
                                'medium', 'premium' => 'primary',
                                default => 'gray',
                            })
                            ->placeholder('Belum diatur'),
                        TextEntry::make('cafe.subscription.duration_months')
                            ->label('Durasi (bulan)')
                            ->placeholder('Belum diatur'),
                        TextEntry::make('cafe.subscription.price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.'))
                            ->placeholder('Belum diatur'),
                    ]),
            ]);
    }
}
