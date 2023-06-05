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
            ->groupBy(function (Translation $item) {
                return $item->vendor ? "{$item->vendor}::{$item->group}" : $item->group;
            });

        return $translations->map(function ($translations) {
            return Arr::undot(
                $translations->mapWithKeys(function (Translation $translation) {
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
    public function addShortKeyTranslation(string $language, string $group, string $key, string $value = '', string|null $vendor = null): void
    {
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        $this->getLanguage($language)
            ->translations()
            ->updateOrCreate([
                'group' => $group,
                'vendor' => $vendor,
                'key' => $key,
            ], [
                'group' => $group,
                'vendor' => $vendor,
                'key' => $key,
                'value' => $value,
            ]);
    }
}
