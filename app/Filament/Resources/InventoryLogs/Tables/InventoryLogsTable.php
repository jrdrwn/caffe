<?php

namespace App\Filament\Resources\InventoryLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cafe.name')->label('Cafe')->searchable()->sortable(),
                TextColumn::make('product.name')->label('Product')->searchable()->sortable(),
                TextColumn::make('action')->badge(),
                TextColumn::make('quantity_change')->sortable(),
                TextColumn::make('quantity_after')->sortable(),
                TextColumn::make('creator.name')->label('Created By'),
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
