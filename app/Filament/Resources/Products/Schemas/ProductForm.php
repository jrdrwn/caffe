<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    FileUpload::make('image_url')->label('Image')->image()->columnSpan(1),
                    TextInput::make('name')->required()->columnSpan(1)->label('Product Name'),
                    TextInput::make('sku')->label('SKU')->columnSpan(1),
                    TextInput::make('price')->required()->label('Price')->numeric()->minValue(0)->columnSpan(1),
                    TextInput::make('cost')->label('Cost')->numeric()->minValue(0)->columnSpan(1),
                    TextInput::make('stock')->label('Stock')->numeric()->minValue(0)->columnSpan(1),
                    Select::make('category_id')->relationship('category', 'name')->label('Category')->columnSpan(1),
                    Toggle::make('is_active')->label('Active')->columnSpan(1),
                    RichEditor::make('description')->label('Description')->columnSpanFull(),
                ]),
            ]);
    }
}
