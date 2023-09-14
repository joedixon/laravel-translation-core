<?php

namespace JoeDixon\TranslationCore\Providers\File;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait InteractsWithStringKeys
{
    /**
     * Get string key translations for a given language.
     */
    public function stringKeyTranslations(string $language): Collection
    {
        $files = collect($this->disk->allFiles($this->languageFilesPath));

        return $files->filter(
            fn ($file) => Str::endsWith($file, "{$language}.json")
        )->flatMap(function ($file) {
            $path = Str::after($file->getPathname(), $this->languageFilesPath);
            if (Str::contains($path, 'vendor')) {
                $vendor = Str::before(Str::after($path, 'vendor'.DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);

                return [$vendor => json_decode($this->disk->get($file), true)];
            }

            $translations = json_decode($this->disk->get($file), true);

            return $translations !== null ? $translations : [];
        });
    }

    /**
     * Add a string key translation.
     */
    public function addStringKeyTranslation(string $language, string $key, string $value = '', string|null $vendor = null): void
    {
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        $translations = $this->stringKeyTranslations($language)->toArray();

        if ($vendor) {
            isset($translations[$vendor]) ? $translations[$vendor][$key] = $value : $translations[$vendor] = [$key => $value];
        } else {
            $translations[$key] = $value;
        }

        $this->saveStringKeyTranslations($language, collect($translations));
    }

    /**
     * Save string key translations.
     */
    private function saveStringKeyTranslations(string $language, Collection $translations): void
    {
        [$root, $vendor] = $translations->partition(fn ($value) => ! is_array($value));

        $this->disk->put(
            $this->path("{$language}.json"),
            json_encode((object) $root->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $vendor->each(function ($translations, $vendor) use ($language) {
            $this->disk->put(
                $this->path('vendor', $vendor, "{$language}.json"),
                json_encode((object) $translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );
        });
    }
}
