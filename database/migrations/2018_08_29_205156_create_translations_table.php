<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $config = app('translation.config');

        Schema::connection($config->database['connection'])
            ->create($config->database['translations_table'], function (Blueprint $table) use ($config) {
                $table->increments('id');
                $table->unsignedInteger('language_id');
                $table->foreign('language_id')->references('id')
                    ->on($config->database['languages_table']);
                $table->string('group')->nullable();
                $table->text('key');
                $table->text('value')->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $config = app('translation.config');

        Schema::connection($config->database['connection'])
            ->dropIfExists($config->database['translations_table']);
    }
}
