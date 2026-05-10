<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultPaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cafes = \App\Models\Cafe::whereDoesntHave('paymentMethods')->get();
        
        foreach ($cafes as $cafe) {
            $cafe->paymentMethods()->createMany([
                ['name' => 'Tunai', 'type' => 'cash', 'is_active' => true],
                ['name' => 'QRIS', 'type' => 'qris', 'is_active' => true],
            ]);
        }
    }
}
