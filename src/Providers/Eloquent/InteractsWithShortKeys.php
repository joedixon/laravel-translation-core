<?php

namespace JoeDixon\TranslationCore\Providers\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait InteractsWithShortKeys
{
    /**
     * Get short key translations for a given language.
     */
    public function shortKeyTranslations(string $language): Collection
    {
        $translations = $this->getLanguage($language)
            ->translations()
            ->whereNotNull('group')
            ->get()
            ->groupBy('group');

        return $translations->map(function ($translations) {
            return Arr::undot(
                $translations->mapWithKeys(function ($translation) {
                    return [$translation->key => $translation->value];
                })
            );
        });
    }

    /**
     * Get all the short key groups for a given language.
     *
     * @return Collection<string>
     */
    public function shortKeyGroups(string $language): Collection
    {
        return Translation::getGroupsForLanguage($language)
            ->map(
                fn (Translation $translation): string => $translation->group
            );
    }

    /**
     * Add a short key translation.
     */
    public function addShortKeyTranslation(string $language, string $group, string $key, string $value = ''): void
    {
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        $this->getLanguage($language)
            ->translations()
            ->updateOrCreate([
                'group' => $group,
                'key' => $key,
            ], [
                'group' => $group,
                'key' => $key,
                'value' => $value,
            ]);
    }
}
