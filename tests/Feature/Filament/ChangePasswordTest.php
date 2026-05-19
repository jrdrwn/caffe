<?php

use App\Filament\Resources\Cafes\Pages\ViewCafe;
use App\Models\Cafe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('manager can change their own password without current password in view cafe page', function (): void {
    $manager = User::factory()->createOne([
        'role' => 'manager',
        'is_active' => true,
    ]);

    $cafe = Cafe::query()->create([
        'name' => 'Cafe Test',
        'created_by' => $manager->id,
    ]);

    $manager->update(['cafe_id' => $cafe->id]);

    Livewire::actingAs($manager);

    Livewire::test(ViewCafe::class, ['record' => $cafe->id])
        ->callAction('ganti_password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ])
        ->assertHasNoActionErrors();

    expect(Hash::check('newpassword123', $manager->refresh()->password))->toBeTrue();
});
