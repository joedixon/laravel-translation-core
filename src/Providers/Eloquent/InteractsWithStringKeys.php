<?php

namespace JoeDixon\TranslationCore\Providers\Eloquent;

use Illuminate\Support\Collection;

trait InteractsWithStringKeys
{
    /**
     * Get single translations for a given language.
     */
    public function stringKeyTranslations(string $language): Collection
    {
        $translations = $this->getLanguage($language)
            ->translations()
            ->where('group', 'like', '%string')
            ->orWhereNull('group')
            ->get()
            ->groupBy('group');

        // if there is no group, this is a legacy translation so we need to
        // update to 'single'. We do this here so it only happens once.
        if ($this->hasLegacyGroups($translations->keys())) {
            Translation::whereNull('group')->update(['group' => 'string']);
            // if any legacy groups exist, rerun the method so we get the
            // updated keys.
            return $this->stringKeyTranslations($language);
        }

        return $translations->map(function ($translations) {
            return $translations->mapWithKeys(function ($translation) {
                return [$translation->key => $translation->value];
            });
        });
    }

    /**
     * Add a single translation.
     */
    public function addStringKeyTranslation(string $language, string $key, string $value = '', string|null $vendor = null): void
    {
        $this->getOrCreateLanguage($language)
            ->translations()
            ->updateOrCreate([
                'group' => $vendor,
                'key' => $key,
            ], [
                'key' => $key,
                'value' => $value,
            ]);
    }
}
