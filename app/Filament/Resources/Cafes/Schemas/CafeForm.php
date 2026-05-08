<?php

namespace App\Filament\Resources\Cafes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CafeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->label('Cafe Name'),
                    TextInput::make('owner_name')->label('Owner Name'),
                    TextInput::make('email')->email()->label('Email'),
                    TextInput::make('phone')->label('Phone'),
                    TextInput::make('city')->label('City'),
                    TextInput::make('province')->label('Province'),
                    FileUpload::make('logo_url')->image()->label('Logo'),
                    Toggle::make('is_active')->label('Active'),
                    Textarea::make('address')->label('Address')->columnSpanFull(),
                    Textarea::make('description')->label('Description')->columnSpanFull(),
                ]),
            ]);
    }
}
