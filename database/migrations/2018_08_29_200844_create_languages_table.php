<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(config('translation.database.connection'))
            ->create(config('translation.database.languages_table'), function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('language');
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('translation.database.connection'))
            ->dropIfExists(config('translation.database.languages_table'));
    }
}
