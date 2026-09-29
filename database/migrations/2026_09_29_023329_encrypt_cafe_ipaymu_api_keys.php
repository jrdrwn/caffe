<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cafes', function (Blueprint $table) {
            $table->text('ipaymu_api_key')->nullable()->change();
        });

        DB::table('cafes')
            ->whereNotNull('ipaymu_api_key')
            ->orderBy('id')
            ->chunkById(100, function ($cafes): void {
                foreach ($cafes as $cafe) {
                    if ($cafe->ipaymu_api_key === '') {
                        continue;
                    }

                    try {
                        Crypt::decryptString($cafe->ipaymu_api_key);

                        continue;
                    } catch (DecryptException) {
                        DB::table('cafes')->where('id', $cafe->id)->update([
                            'ipaymu_api_key' => Crypt::encryptString($cafe->ipaymu_api_key),
                        ]);
                    }
                }
            });
    }

    /**
     * Preserve encrypted keys when rolling back.
     */
    public function down(): void {}
};
