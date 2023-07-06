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
        $config = app('translation.config');

        Schema::connection($config->database['connection'])
            ->table($config->database['translations_table'], function (Blueprint $table) {
                $table->string('vendor')
                    ->after('language_id')
                    ->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $config = app('translation.config');

        Schema::connection($config->database['connection'])
            ->table($config->database['translations_table'], function (Blueprint $table) {
                $table->dropColumn('vendor');
            });
    }
};
