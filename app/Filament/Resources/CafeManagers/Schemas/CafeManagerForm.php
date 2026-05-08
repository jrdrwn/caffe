<?php

namespace App\Filament\Resources\CafeManagers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CafeManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('cafe_id')->relationship('cafe', 'name')->required()->label('Cafe'),
                    Select::make('manager_id')->relationship('manager', 'name')->required()->label('Manager'),
                    DateTimePicker::make('assigned_at')->label('Assigned At'),
                    Select::make('assigned_by')->relationship('assignedBy', 'name')->label('Assigned By'),
                ]),
            ]);
    }
}
