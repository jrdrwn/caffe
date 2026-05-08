<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('key')->required()->label('Key'),
                    Toggle::make('is_active')->label('Active'),
                    Textarea::make('value')->label('Value')->columnSpanFull(),
                    Textarea::make('description')->label('Description')->columnSpanFull(),
                ]),
            ]);
    }
}
