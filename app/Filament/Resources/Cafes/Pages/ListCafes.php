<?php

namespace App\Filament\Resources\Cafes\Pages;

use App\Filament\Resources\Cafes\CafeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCafes extends ListRecords
{
    protected static string $resource = CafeResource::class;

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
