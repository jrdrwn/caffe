<?php

namespace App\Filament\Resources\UserActivityLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konteks Aktivitas')
                    ->description('Log ini dipakai untuk audit dan pelacakan kejadian penting.')
                    ->columns(2)
                    ->schema([
                        Select::make('cafe_id')
                            ->label('Cafe')
                            ->relationship('cafe', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('activity_type')
                            ->label('Tipe Aktivitas')
                            ->required()
                            ->placeholder('login, update_profile, checkout')
                            ->maxLength(255),
                        TextInput::make('ip_address')
                            ->label('IP Address')
                            ->placeholder('127.0.0.1')
                            ->maxLength(45),
                    ]),
                Section::make('Detail Audit')
                    ->description('Tambahkan konteks tambahan untuk membantu investigasi.')
                    ->columns(2)
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('user_agent')
                            ->label('User Agent')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
