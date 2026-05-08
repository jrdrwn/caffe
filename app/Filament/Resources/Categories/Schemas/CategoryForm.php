<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('cafe_id')->relationship('cafe', 'name')->required()->label('Cafe'),
                    TextInput::make('name')->required()->label('Category Name'),
                    TextInput::make('display_order')->numeric()->default(0)->label('Display Order'),
                    Toggle::make('is_active')->label('Active'),
                    FileUpload::make('image_url')->image()->label('Image'),
                    Textarea::make('description')->label('Description')->columnSpanFull(),
                ]),
            ]);
    }
}
