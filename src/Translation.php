<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use JoeDixon\TranslationCore\Events\TranslationAdded;

abstract class Translation
{
    protected string $sourceLanguage;

    /**
     * Get a map of each language with it's associated file path.
     */
    abstract public function map(string|null $key = null, string|null $default = null): Collection|string|null;

    /**
     * Get all languages.
     */
    abstract public function languages(): Collection;

    /**
     * Determine whether the given language exists.
     */
    abstract public function languageExists(string $language): bool;

    /**
     * Add a new language.
     */
    abstract public function addLanguage(string $language, ?string $name = null): void;

    /**
     * Get short key translations for a given language.
     */
    abstract public function shortKeyTranslations(string $language): Collection;

    /**
     * Get all the short key groups for a given language.
     */
    abstract public function shortKeyGroups(string $language): Collection;

    /**
     * Add a short key translation.
     */
    abstract public function addShortKeyTranslation(string $language, string $group, string $key, string $value = ''): void;

    /**
     * Get string key translations for a given language.
     */
    abstract public function stringKeyTranslations(string $language): Collection;

    /**
     * Add a string key translation.
     */
    abstract public function addStringKeyTranslation(string $language, string $key, string $value = '', string|null $vendor = null): void;

    /**
     * Get all the translations for a given language key.
     */
    abstract public function allTranslationsFromMap(string $key): Collection;

    /**
     * Get all translations.
     */
    public function allTranslations(): Collection
    {
        return $this->languages()->mapWithKeys(
            fn ($name, $language) => [$language => $this->allTranslationsFor($language)]
        );
    }

    /**
     * Get all translations for a given language.
     */
    public function allTranslationsFor(string $language): Translations
    {
        return new Translations(
            $this->stringKeyTranslations($language),
            $this->shortKeyTranslations($language),
        );
    }

    public function add(Request $request, string $language, bool $isGroupTranslation): void
    {
        $namespace = $request->has('namespace') && $request->get('namespace') ? "{$request->get('namespace')}::" : '';
        $group = $namespace.$request->get('group');
        $key = $request->get('key');
        $value = $request->get('value') ?: '';

        if ($isGroupTranslation) {
            $this->addShortKeyTranslation($language, $group, $key, $value);
        } else {
            $this->addStringKeyTranslation($language, $key, $value, null);
        }

        Event::dispatch(new TranslationAdded($language, $group ?: 'string', $key, $value));
    }

    /**
     * Return a set of translation keys merged across all languages.
     */
    public function keys(): Translations
    {
        return $this->allTranslations()->reduce(function ($carry, $item) {
            $carry->shortKeyTranslations = $carry->shortKeyTranslations->mergeRecursive($item->shortKeyTranslations);
            $carry->stringKeyTranslations = $carry->stringKeyTranslations->mergeRecursive($item->stringKeyTranslations);

            return $carry;
        }, Translations::make())->reset();
    }
}
