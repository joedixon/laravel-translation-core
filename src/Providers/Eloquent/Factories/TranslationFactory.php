<?php

namespace JoeDixon\TranslationCore\Providers\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JoeDixon\TranslationCore\Providers\Eloquent\Language;
use JoeDixon\TranslationCore\Providers\Eloquent\Translation;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'language_id' => Language::factory(),
            'group' => fake()->word,
            'key' => fake()->word,
            'value' => fake()->sentence,
        ];
    }

    public function shortKey(): static
    {
        return $this->state(function () {
            return [
                'group' => fake()->word,
            ];
        });
    }

    public function stringKey(): static
    {
        return $this->state(function () {
            return [
                'group' => 'string',
            ];
        });
    }
}
