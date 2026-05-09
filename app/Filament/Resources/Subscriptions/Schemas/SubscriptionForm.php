<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rincian Paket')
                    ->description('Paket langganan dipakai untuk mengatur akses dan periode aktif sistem.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->placeholder('Contoh: Starter / Pro')
                            ->maxLength(255),
                        TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        TextInput::make('duration_months')
                            ->label('Durasi (bulan)')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Paket nonaktif tidak dipilih pada pembuatan langganan baru.'),
                        Textarea::make('features')
                            ->label('Fitur')
                            ->helperText('Satu fitur per baris.')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
