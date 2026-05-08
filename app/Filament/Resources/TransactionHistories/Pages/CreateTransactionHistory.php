<?php

namespace App\Filament\Resources\TransactionHistories\Pages;

use App\Filament\Resources\TransactionHistories\TransactionHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransactionHistory extends CreateRecord
{
    protected static string $resource = TransactionHistoryResource::class;
}
