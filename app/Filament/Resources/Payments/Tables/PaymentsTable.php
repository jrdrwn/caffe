<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction.transaction_number')
                    ->label('Transaksi')
                    ->sortable(),
                TextColumn::make('paymentMethod.name')
                    ->label('Metode')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state): string {
                        return match ($state) {
                            'success' => 'Success',
                            'pending' => 'Pending',
                            'failed' => 'Failed',
                            default => (string) $state,
                        };
                    })
                    ->colors([
                        'success' => 'success',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
