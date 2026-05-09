<?php

namespace App\Filament\Resources\CafeManagers\Schemas;

use App\Models\CafeManager;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CafeManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Cafe Manager')
                    ->tabs([
                        Tab::make('Cafe')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('cafe_id')
                                        ->relationship('cafe', 'name')
                                        ->required()
                                        ->label('Cafe'),
                                    Select::make('assigned_by')
                                        ->relationship('assignedBy', 'name')
                                        ->label('Assigned By'),
                                ]),
                            ]),
                        Tab::make('Manager')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('manager_id')
                                        ->relationship('manager', 'name')
                                        ->required()
                                        ->label('Manager'),
                                    DateTimePicker::make('assigned_at')
                                        ->label('Assigned At'),
                                ]),
                            ]),
                        Tab::make('Ringkasan')
                            ->schema([
                                Grid::make(1)->schema([
                                    Placeholder::make('subscription_name')
                                        ->label('Langganan Cafe')
                                        ->content(fn (?CafeManager $record): string => $record?->cafe?->subscription?->name ?? 'Belum diatur'),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
