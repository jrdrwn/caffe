<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('cafe_id')->relationship('cafe', 'name')->required()->label('Cafe'),
                    TextInput::make('name')->required()->label('Method Name'),
                    Select::make('type')->options([
                        'cash' => 'Cash',
                        'debit' => 'Debit',
                        'qris' => 'QRIS',
                    ])->required()->label('Type'),
                    Toggle::make('is_active')->label('Active'),
                ]),
            ]);
    }
}
