<?php

namespace App\Filament\Resources\UserActivityLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('cafe_id')->relationship('cafe', 'name')->required()->label('Cafe'),
                    Select::make('user_id')->relationship('user', 'name')->required()->label('User'),
                    TextInput::make('activity_type')->required()->label('Activity Type'),
                    TextInput::make('ip_address')->label('IP Address'),
                    Textarea::make('description')->label('Description')->columnSpanFull(),
                    Textarea::make('user_agent')->label('User Agent')->columnSpanFull(),
                ]),
            ]);
    }
}
