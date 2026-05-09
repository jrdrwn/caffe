<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pembayaran')
                    ->description('Pembayaran biasanya dihasilkan dari transaksi, jadi data di sini perlu mudah diaudit.')
                    ->columns(2)
                    ->schema([
                        Select::make('transaction_id')
                            ->label('Transaksi')
                            ->relationship('transaction', 'transaction_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->placeholder('REF-0001')
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'success' => 'Success',
                                'pending' => 'Pending',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
