<?php

namespace App\Filament\Resources\CafeManagers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCafeManagers extends ListRecords
{
    protected static string $resource = 'App\\Filament\\Resources\\CafeManagers\\CafeManagerResource';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        // For manager users, if there's only one cafe manager record for them,
        // redirect to the detail page for that record to simplify the flow.
        if ($user?->role === 'manager') {
            $query = static::getResource()::getEloquentQuery();
            $count = $query->count();

            if ($count === 1) {
                $record = $query->first();
                if ($record) {
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $record->getKey()]));
                }
            }
        }
    }
}
