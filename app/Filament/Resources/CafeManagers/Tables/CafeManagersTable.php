<?php

namespace App\Filament\Resources\CafeManagers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CafeManagersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cafe.name')->label('Cafe')->searchable()->sortable(),
                TextColumn::make('manager.name')->label('Manager')->searchable()->sortable(),
                TextColumn::make('assignedBy.name')->label('Assigned By')->toggleable(),
                TextColumn::make('assigned_at')->dateTime()->sortable(),
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
