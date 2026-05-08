<?php

namespace App\Filament\Resources\TransactionItems\Schemas;

use Filament\Schemas\Schema;

class TransactionItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // form components for transaction item (transaction_id, product_id, quantity...)
            ]);
    }
}
