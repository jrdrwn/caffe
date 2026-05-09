<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Sistem')
                    ->description('Gunakan key yang konsisten dan nilai yang mudah dipelihara antar environment.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->placeholder('contoh: tax_rate')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Setting nonaktif tidak dipakai oleh sistem.'),
                        Textarea::make('value')
                            ->label('Value')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
