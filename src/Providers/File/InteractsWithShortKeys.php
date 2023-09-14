<?php

namespace JoeDixon\TranslationCore\Providers\File;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SplFileInfo;

trait InteractsWithShortKeys
{
    /**
     * Get short key translations for a given language.
     */
    public function shortKeyTranslations(string $language): Collection
    {
        return $this->shortKeyFiles($language)->mapWithKeys(function ($group) use ($language) {
            $path = Str::after($group->getPathname(), $this->languageFilesPath);
            if (Str::contains($path, 'vendor')) {
                $translations = $this->disk->getRequire($group->getPathname());
                $vendor = Str::before(Str::after($path, 'vendor'.DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
                $group = $this->extractGroup($language, $group);

                return ["{$vendor}::{$group}" => $translations];
            }

            $translations = $this->disk->getRequire($group->getPathname());
            $group = $this->extractGroup($language, $group);

            return [$group => is_array($translations) ? $translations : []];
        });
    }

    /**
     * Get all the short key groups for a given language.
     */
    public function shortKeyGroups(string $language): Collection
    {
        return $this->shortKeyFiles($language)->map(function ($file) {
            if (Str::contains($file->getPathname(), 'vendor')) {
                $vendor = Str::before(Str::after($file->getPathname(), 'vendor'.DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);

                return "{$vendor}::{$file->getBasename('.php')}";
            }

            return $file->getBasename('.php');
        });
    }

    /**
     * Add a short key translation.
     */
    public function addShortKeyTranslation(string $language, string $group, string $key, string $value = '', string|null $vendor = null): void
    {
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        if ($vendor) {
            $group = "{$vendor}::{$group}";
        }

        $translations = $this->shortKeyTranslations($language);

        // Does the group exist? If not, create it.
        if (! $translations->keys()->contains($group)) {
            $translations->put($group, collect());
        }

        $values = Arr::dot($translations->get($group));
        $values[$key] = $value;
        $translations->put($group, collect(Arr::undot($values)));

        $this->saveShortKeyTranslations($language, $group, collect($translations->get($group)));
    }

    /**
     * Add a new group of short key translations.
     */
    protected function addShortKeyGroup(string $language, string $group): void
    {
        $this->saveShortKeyTranslations($language, $group, collect());
    }

    /**
     * Get all the short key files for a given language.
     */
    protected function shortKeyFiles(string $language): Collection
    {
        $path = $this->path($language);

        if (! $this->disk->exists($path)) {
            $groups = collect();
        } else {
            $groups = collect($this->disk->allFiles($path));
        }

        return $groups->merge($this->vendorShortKeyFiles($language))
            ->filter(function ($file) {
                return Str::endsWith($file->getBasename(), '.php');
            });
    }

    /**
     * Get all the vendor short key files for a given language.
     */
    protected function vendorShortKeyFiles(string $language): Collection
    {
        if (! $this->disk->exists("{$this->languageFilesPath}".DIRECTORY_SEPARATOR.'vendor')) {
            return collect();
        }

        return collect($this->disk->directories($this->path('vendor')))
            ->flatMap(function ($directory) use ($language) {
                $vendor = Str::afterLast($directory, DIRECTORY_SEPARATOR);
                if (! $this->disk->exists($this->path('vendor', $vendor, $language))) {
                    return [];
                } else {
                    return $this->disk->allFiles($this->path('vendor', $vendor, $language));
                }
            });
    }

    /**
     * Save short key translations.
     */
    protected function saveShortKeyTranslations(string $language, string $group, Collection $translations): void
    {
        if (Str::contains($group, '::')) {
            $this->saveNamespacedShortKeyTranslations($language, $group, $translations);

            return;
        }

        $groups = explode('/', $group);
        $group = array_pop($groups);
        $directory = $this->path($language, ...$groups);

        if (! $this->disk->exists($directory)) {
            $this->disk->makeDirectory($directory, 0755, true);
        }

        $this->disk->put(
            $this->path($directory, "{$group}.php"),
            "<?php\n\nreturn ".var_export($translations->toArray(), true).';'.\PHP_EOL
        );
    }

    /**
     * Save namespaced short key translations.
     */
    protected function saveNamespacedShortKeyTranslations(string $language, string $group, Collection $translations): void
    {
        [$namespace, $group] = explode('::', $group);
        $groups = explode('/', $group);
        $group = array_pop($groups);
        $directory = $this->path('vendor', $namespace, $language, ...$groups);

        if (! $this->disk->exists($directory)) {
            $this->disk->makeDirectory($directory, 0755, true);
        }

        $this->disk->put(
            $this->path($directory, "{$group}.php"),
            "<?php\n\nreturn ".var_export($translations->toArray(), true).';'.\PHP_EOL
        );
    }

    /**
     * Extract the group from the file path.
     */
    protected function extractGroup(string $language, SplFileInfo $path): string
    {
        return Str::of($path->getPathname())
            ->after(DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR)
            ->replace('.'.$path->getExtension(), '')
            ->replace(DIRECTORY_SEPARATOR, '/')
            ->__toString();
    }
}
