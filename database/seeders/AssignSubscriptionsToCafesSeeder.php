<?php

namespace Database\Seeders;

use App\Models\Cafe;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class AssignSubscriptionsToCafesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['name' => 'Free', 'price' => 0, 'duration_months' => 0],
            ['name' => 'Plus', 'price' => 50000, 'duration_months' => 1],
            ['name' => 'Pro', 'price' => 150000, 'duration_months' => 1],
        ];

        foreach ($defaults as $d) {
            Subscription::firstOrCreate(['name' => $d['name']], [
                'price' => $d['price'],
                'duration_months' => $d['duration_months'],
                'features' => [],
                'is_active' => true,
            ]);
        }

        $free = Subscription::where('name', 'Free')->orWhere('name', 'free')->first();

        if (! $free) {
            $free = Subscription::first();
        }

        if ($free) {
            Cafe::whereNull('subscription_id')->update(['subscription_id' => $free->id]);
        }
    }
}
