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
            ->whereNull('group')
            ->get()
            ->groupBy('vendor');

        return $translations->mapWithKeys(function ($translations, $vendor) {
            // Root translations are stored with a blank vendor.
            if ($vendor === '') {
                return $translations->mapWithKeys(function ($translation) {
                    return [$translation->key => $translation->value];
                })->all();
            }

            return [$vendor => $translations->mapWithKeys(function ($translation) {
                return [$translation->key => $translation->value];
            })->all()];
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
                'vendor' => $vendor,
                'key' => $key,
                'group' => null,
            ], [
                'key' => $key,
                'value' => $value,
            ]);
    }
}
