<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->label('Plan Name'),
                    TextInput::make('price')->required()->numeric()->minValue(0)->label('Price'),
                    TextInput::make('duration_months')->required()->numeric()->minValue(1)->label('Duration (months)'),
                    Textarea::make('features')->label('Features (one per line)')->columnSpanFull(),
                    Toggle::make('is_active')->label('Active'),
                ]),
            ]);
    }
}
