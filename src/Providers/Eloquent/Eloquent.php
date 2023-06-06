<?php

namespace JoeDixon\TranslationCore\Providers\Eloquent;

use Illuminate\Support\Collection;
use JoeDixon\TranslationCore\Exceptions\LanguageExistsException;
use JoeDixon\TranslationCore\Translation;

class Eloquent extends Translation
{
    use InteractsWithStringKeys, InteractsWithShortKeys;

    public function __construct(
        protected string $sourceLanguage,
    ) {
    }

    /**
     * Get all languages.
     */
    public function languages(): Collection
    {
        return Language::all()->mapWithKeys(function ($language) {
            return [$language->language => $language->name ?: $language->language];
        });
    }

    /**
     * Determine whether the given language exists.
     */
    public function languageExists(string $language): bool
    {
        return Language::where('language', $language)->count() > 0;
    }

    /**
     * Add a new language.
     */
    public function addLanguage(string $language, ?string $name = null): void
    {
        if ($this->languageExists($language)) {
            throw new LanguageExistsException(__('translation::errors.language_exists', ['language' => $language]));
        }

        Language::create([
            'language' => $language,
            'name' => $name,
        ]);
    }

    /**
     * Get a language from the database.
     */
    private function getLanguage(string $language): Language
    {
        return Language::where('language', $language)->firstOrFail();
    }

    private function getOrCreateLanguage(string $language): Language
    {
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        return $this->getLanguage($language);
    }

    public function allTranslationsFromMap(string $key): Collection
    {
        return collect();
    }

    public function map(string|null $key = null, string|null $default = null): Collection|string|null
    {
        return collect();
    }
}
