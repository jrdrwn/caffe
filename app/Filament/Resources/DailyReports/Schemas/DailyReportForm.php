<?php

namespace App\Filament\Resources\DailyReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class DailyReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Select::make('cafe_id')->relationship('cafe', 'name')->required()->label('Cafe'),
                    DatePicker::make('report_date')->required()->label('Report Date'),
                    TextInput::make('total_transactions')->numeric()->required()->label('Total Transactions'),
                    TextInput::make('total_sales')->numeric()->required()->label('Total Sales'),
                    TextInput::make('total_discount')->numeric()->required()->label('Total Discount'),
                    TextInput::make('total_tax')->numeric()->required()->label('Total Tax'),
                    TextInput::make('total_cash')->numeric()->required()->label('Total Cash'),
                    TextInput::make('total_debit')->numeric()->required()->label('Total Debit'),
                    TextInput::make('total_qris')->numeric()->required()->label('Total QRIS'),
                    TextInput::make('opening_balance')->numeric()->required()->label('Opening Balance'),
                    TextInput::make('closing_balance')->numeric()->required()->label('Closing Balance'),
                    Select::make('created_by')->relationship('creator', 'name')->label('Created By'),
                ]),
            ]);
    }
}
