<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cafes', function (Blueprint $table) {
            $table->renameColumn('doku_client_id', 'ipaymu_va');
            $table->renameColumn('doku_secret_key', 'ipaymu_api_key');
            $table->renameColumn('doku_is_production', 'ipaymu_is_production');
        });

        DB::table('cafes')->where('qris_type', 'doku')->update(['qris_type' => 'ipaymu']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cafes')->where('qris_type', 'ipaymu')->update(['qris_type' => 'doku']);

        Schema::table('cafes', function (Blueprint $table) {
            $table->renameColumn('ipaymu_va', 'doku_client_id');
            $table->renameColumn('ipaymu_api_key', 'doku_secret_key');
            $table->renameColumn('ipaymu_is_production', 'doku_is_production');
        });
    }
};
