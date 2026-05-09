<?php

namespace App\Filament\Resources\Cafes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CafesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Cafe')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('manager.manager.name')
                    ->label('Manager')
                    ->toggleable()
                    ->searchable(),
                BadgeColumn::make('subscription.name')
                    ->label('Langganan')
                    ->colors([
                        'secondary' => fn ($state): bool => strtolower((string) $state) === 'free',
                        'warning' => fn ($state): bool => strtolower((string) $state) === 'plus',
                        'success' => fn ($state): bool => strtolower((string) $state) === 'pro',
                    ])
                    ->sortable()
                    ->searchable(),
                TextColumn::make('owner_name')
                    ->label('Pemilik')
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('Kota')
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
