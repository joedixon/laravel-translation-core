<?php

namespace JoeDixon\TranslationCore;

use Closure;
use Illuminate\Support\Collection;

class Translations
{
    /**
     * @param  Collection<string,Collection<string,array|string>>  $stringKeyTranslations
     * @param  Collection<string,Collection<string,array|string>>  $shortKeyTranslations
     * @return void
     */
    public function __construct(
        public Collection $stringKeyTranslations,
        public Collection $shortKeyTranslations,
    ) {
    }

    /**
     * Get the string key translations.
     */
    public function string(): Collection
    {
        return $this->stringKeyTranslations;
    }

    /**
     * Get the short key translations.
     */
    public function short(): Collection
    {
        return $this->shortKeyTranslations;
    }

    public static function make(?Collection $stringKeyTranslations = null, ?Collection $shortKeyTranslations = null): self
    {
        return new static(
            $stringKeyTranslations ?? new Collection(),
            $shortKeyTranslations ?? new Collection(),
        );
    }
}
