<?php

use App\Filament\Resources\Cafes\CafeResource;
use App\Models\Cafe;
use App\Models\User;

test('manager can open cafe edit form', function () {
    $cafe = Cafe::factory()->create();
    $manager = User::factory()->create([
        'role' => 'manager',
        'cafe_id' => $cafe->id,
        'is_active' => true,
    ]);

    $this->actingAs($manager)
        ->get(CafeResource::getUrl('edit', ['record' => $cafe], panel: 'manager'))
        ->assertSuccessful();
});
