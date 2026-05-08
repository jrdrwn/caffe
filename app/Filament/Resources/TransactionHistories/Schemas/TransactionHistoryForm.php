<?php

namespace App\Filament\Resources\TransactionHistories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TransactionHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('cafe_id')->relationship('cafe', 'name')->required()->label('Cafe'),
                    Select::make('transaction_id')->relationship('transaction', 'transaction_number')->required()->label('Transaction'),
                    Select::make('action')->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'cancelled' => 'Cancelled',
                        'paid' => 'Paid',
                    ])->required(),
                    Select::make('performed_by')->relationship('performedBy', 'name')->required()->label('Performed By'),
                    Textarea::make('description')->label('Description')->columnSpanFull(),
                ]),
            ]);
    }
}
