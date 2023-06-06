<?php

namespace JoeDixon\TranslationCore\Providers\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JoeDixon\TranslationCore\Providers\Eloquent\Language;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'language' => fake()->word,
            'name' => fake()->word,
        ];
    }
}
