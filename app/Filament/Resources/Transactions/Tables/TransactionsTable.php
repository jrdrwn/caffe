<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')->label('Transaction #')->searchable()->sortable(),
                TextColumn::make('cashier.name')->label('Cashier')->sortable(),
                TextColumn::make('total_amount')->label('Total')->sortable(),
                BadgeColumn::make('status')->label('Status')->formatStateUsing(function ($state) {
                    return match ($state) {
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        default => (string) $state,
                    };
                })->colors([
                    'warning' => 'pending',
                    'success' => 'completed',
                    'danger' => 'cancelled',
                ]),
                TextColumn::make('created_at')->label('Date')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
