<?php

namespace JoeDixon\TranslationCore\Providers\File;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait InteractsWithShortKeys
{
    /**
     * Get short key translations for a given language.
     */
    public function shortKeyTranslations(string $language): Collection
    {
        return $this->shortFilesFor($language)->mapWithKeys(function ($group) {
            // here we check if the path contains 'vendor' as these will be the
            // files which need namespacing
            if (Str::contains($group->getPathname(), 'vendor')) {
                $vendor = Str::before(Str::after($group->getPathname(), 'vendor'.DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);

                return ["{$vendor}::{$group->getBasename('.php')}" => $this->disk->getRequire($group->getPathname())];
            }

            $translations = $this->disk->getRequire($group->getPathname());

            return [$group->getBasename('.php') => is_array($translations) ? $translations : []];
        });
    }

    /**
     * Get all the short key groups for a given language.
     */
    public function shortKeyGroups(string $language): Collection
    {
        return $this->shortFilesFor($language)->map(function ($file) {
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
    public function addShortKeyTranslation(string $language, string $group, string $key, string $value = ''): void
    {
        if (! $this->languageExists($language)) {
            $this->addLanguage($language);
        }

        $translations = $this->shortKeyTranslations($language);

        // does the group exist? If not, create it.
        if (! $translations->keys()->contains($group)) {
            $translations->put($group, collect());
        }

        $values = $translations->get($group);
        $values[$key] = $value;
        $translations->put($group, collect($values));

        $this->saveShortTranslations($language, $group, collect($translations->get($group)));
    }

    /**
     * Add a new group of short key translations.
     */
    protected function addShortGroup(string $language, string $group): void
    {
        $this->saveShortTranslations($language, $group, collect());
    }

    /**
     * Get all the short key files for a given language.
     */
    protected function shortFilesFor(string $language): Collection
    {
        $path = $this->path($language);

        if (! $this->disk->exists($path)) {
            return collect();
        }

        $groups = collect($this->disk->allFiles($path));

        // namespaced files reside in the vendor directory so we'll grab these
        // the `vendorShortFilesFor` method
        return $groups->merge($this->vendorShortFilesFor($language))
            ->filter(function ($file) {
                return Str::endsWith($file->getBasename(), '.php');
            });
    }

    /**
     * Get all the vendor short key files for a given language.
     */
    protected function vendorShortFilesFor(string $language): Collection
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
    protected function saveShortTranslations(string $language, string $group, Collection $translations): void
    {
        // here we check if it's a namespaced translation which need saving to a
        // different path
        if (Str::contains($group, '::')) {
            $this->saveNamespacedShortTranslations($language, $group, $translations);

            return;
        }

        $this->disk->put($this->path($language, "{$group}.php"), "<?php\n\nreturn ".var_export($translations->toArray(), true).';'.\PHP_EOL);
    }

    /**
     * Save namespaced short key translations.
     */
    protected function saveNamespacedShortTranslations(string $language, string $group, Collection $translations): void
    {
        [$namespace, $group] = explode('::', $group);
        $directory = $this->path('vendor', $namespace, $language);

        if (! $this->disk->exists($directory)) {
            $this->disk->makeDirectory($directory, 0755, true);
        }

        $this->disk->put($this->path($directory, "{$group}.php"), "<?php\n\nreturn ".var_export($translations->toArray(), true).';'.\PHP_EOL);
    }
}
