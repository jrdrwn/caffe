<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        $role = Auth::user()?->role;
        $isManager = is_string($role) && in_array($role, ['manager'], true);

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Paket')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('duration_months')
                    ->label('Durasi (bulan)'),
                TextColumn::make('features')
                    ->label('Fitur')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', $state) : (string) $state)
                    ->wrap()
                    ->limit(60),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(function ($state): string {
                        return $state ? 'Aktif' : 'Nonaktif';
                    })
                    ->colors(['success' => 1, 'secondary' => 0]),
            ])
            ->filters([
                //
            ])
            ->recordActions(
                $isManager
                    ? [ViewAction::make()]
                    : [EditAction::make()]
            )
            ->toolbarActions(
                $isManager
                    ? []
                    : [
                        BulkActionGroup::make([
                            DeleteBulkAction::make(),
                        ]),
                    ]
            );
    }
}
