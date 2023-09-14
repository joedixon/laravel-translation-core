<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Support\Arr as SupportArr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Translations
{
    /**
     * @param  Collection<string, array|string>  $stringKeyTranslations
     * @param  Collection<string, array>  $shortKeyTranslations
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

    /**
     * Get the short key translations grouped with dot notation.
     */
    public function shortByGroup(): Collection
    {
        return $this->shortKeyTranslations->mapWithKeys(function ($group, $key) {
            return [$key => SupportArr::dot($group)];
        });
    }

    /**
     * Create a new instance of the class.
     */
    public static function make(Collection $stringKeyTranslations = null, Collection $shortKeyTranslations = null): self
    {
        return new static(
            $stringKeyTranslations ?? collect(),
            $shortKeyTranslations ?? collect(),
        );
    }

    /**
     * Reset the values of the translations.
     */
    public function reset()
    {
        $this->stringKeyTranslations = $this->resetCollection($this->stringKeyTranslations);
        $this->shortKeyTranslations = $this->resetCollection($this->shortKeyTranslations);

        return $this;
    }

    /**
     * Reset the values of a collection.
     */
    public function resetCollection(Collection $collection): Collection
    {
        return $collection->map(function ($item) {
            if ($item instanceof Collection) {
                return $this->resetCollection($item);
            }

            if (is_array($item) && (! array_is_list($item) || empty($item))) {
                return $this->resetCollection(collect($item))->toArray();
            }

            return '';
        });
    }

    /**
     * Return the keys and values of the current translations which don't exist in the given translations.
     */
    public function diffKeys(Translations $translations): Translations
    {
        $stringKeyTranslations = $this->diffKeysRecursive($this->stringKeyTranslations, $translations->stringKeyTranslations);
        $shortKeyTranslations = $this->diffKeysRecursive($this->shortKeyTranslations, $translations->shortKeyTranslations);

        return new static($stringKeyTranslations, $shortKeyTranslations);
    }

    /**
     * Merge the translations with the given translations.
     */
    public function merge(Translations $translations): Translations
    {
        $stringKeyTranslations = $this->mergeRecursive($this->stringKeyTranslations, $translations->stringKeyTranslations);
        $shortKeyTranslations = $this->mergeRecursive($this->shortKeyTranslations, $translations->shortKeyTranslations);

        return new static($stringKeyTranslations, $shortKeyTranslations);
    }

    /**
     * Filter the translations by the given query.
     */
    public function search(?string $query): Translations
    {
        if (! $query) {
            return $this;
        }

        $stringKeyTranslations = $this->searchRecursive($this->stringKeyTranslations, $query);
        $shortKeyTranslations = $this->searchRecursive($this->shortKeyTranslations, $query);

        return new static($stringKeyTranslations, $shortKeyTranslations);
    }

    /**
     * Determine if the translations are empty.
     */
    public function isEmpty(): bool
    {
        return $this->stringKeyTranslations->isEmpty() && $this->shortKeyTranslations->isEmpty();
    }

    /**
     * Recusively diff the keys of two collections returning those which exist in the first, but not the second.
     */
    protected function diffKeysRecursive(Collection $collectionOne, Collection $collectionTwo): Collection
    {
        return collect(
            Arr::undotUsing(
                collect(Arr::dotUsing($collectionOne))
                    ->diffKeys(
                        collect(Arr::dotUsing($collectionTwo))
                    )
            )
        );
    }

    /**
     * Recusively merge two collections.
     */
    protected function mergeRecursive(Collection $collectionOne, Collection $collectionTwo): Collection
    {
        return collect(
            Arr::undotUsing(
                collect(Arr::dotUsing($collectionOne))
                    ->merge(
                        collect(Arr::dotUsing($collectionTwo))
                    )
            )
        );
    }

    /**
     * Recursively search the translations for the given query.
     */
    protected function searchRecursive(Collection $translations, string $query): Collection
    {
        return collect(
            Arr::undotUsing(
                collect(Arr::dotUsing($translations))
                    ->filter(fn ($value, $key) => (is_string($key) && Str::contains($key, $query)) || (is_string($value) && Str::contains($value, $query)))
            )
        );
    }
}
