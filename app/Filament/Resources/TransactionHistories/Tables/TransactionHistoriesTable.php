<?php

namespace App\Filament\Resources\TransactionHistories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cafe.name')->label('Cafe')->searchable()->sortable(),
                TextColumn::make('transaction.transaction_number')->label('Transaction')->searchable()->sortable(),
                TextColumn::make('action')->badge(),
                TextColumn::make('performedBy.name')->label('Performed By'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
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
