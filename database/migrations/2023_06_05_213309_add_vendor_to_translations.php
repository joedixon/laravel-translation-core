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
        Schema::connection(config('translation.database.connection'))
            ->table(config('translation.database.translations_table'), function (Blueprint $table) {
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
        Schema::connection(config('translation.database.connection'))
            ->table(config('translation.database.translations_table'), function (Blueprint $table) {
                $table->dropColumn('vendor');
            });
    }
};
