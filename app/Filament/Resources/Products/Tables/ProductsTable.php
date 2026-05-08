<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('Image')->rounded()->size(48),
                TextColumn::make('name')->searchable()->sortable()->label('Name'),
                TextColumn::make('sku')->label('SKU')->sortable(),
                TextColumn::make('price')->label('Price')->sortable(),
                TextColumn::make('stock')->label('Stock')->sortable(),
                BadgeColumn::make('is_active')->label('Status')->formatStateUsing(function ($state) {
                    return $state ? 'Active' : 'Inactive';
                })->colors([
                    'primary' => 1,
                    'secondary' => 0,
                ]),
            ])
            ->filters([
                // future filters (category, price range)
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
