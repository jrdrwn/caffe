<?php

namespace App\Filament\Resources\TransactionHistories\Pages;

use App\Filament\Resources\TransactionHistories\TransactionHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransactionHistories extends ListRecords
{
    protected static string $resource = TransactionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
