<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('transaction_number')->label('Transaction #')->required()->columnSpan(1),
                    Select::make('cashier_id')->relationship('cashier', 'name')->label('Cashier')->columnSpan(1),
                    TextInput::make('total_amount')->label('Total Amount')->numeric()->minValue(0)->columnSpan(1),
                    TextInput::make('discount_amount')->label('Discount')->numeric()->minValue(0)->columnSpan(1),
                    TextInput::make('tax_amount')->label('Tax')->numeric()->minValue(0)->columnSpan(1),
                    Select::make('status')->options(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])->label('Status')->columnSpan(1),
                    Textarea::make('notes')->label('Notes')->columnSpanFull(),
                ]),
            ]);
    }
}
