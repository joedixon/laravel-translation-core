<?php

namespace JoeDixon\TranslationCore\Providers\File;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use JoeDixon\TranslationCore\Exceptions\LanguageExistsException;
use JoeDixon\TranslationCore\Translation;
use Symfony\Component\Finder\SplFileInfo;

class File extends Translation
{
    use InteractsWithStringKeys, InteractsWithShortKeys;

    public function __construct(
        private Filesystem $disk,
        private string $languageFilesPath,
        protected string $sourceLanguage
    ) {
    }

    /**
     * Get a map of each language with it's associated file path.
     */
    public function map(string $key = null, string $default = null): Collection|string|null
    {
        $map = collect($this->disk->allFiles($this->languageFilesPath))
            ->flatMap(function (SplFileInfo $file) {
                return $this->getKeyAndPath($file);
            });

        if ($key) {
            return $map->get($key, $default);
        }

        return $map;
    }

    /**
     * Get all languages.
     */
    public function languages(): Collection
    {
        return collect($this->disk->allFiles($this->languageFilesPath))
            ->mapWithKeys(function (SplFileInfo $file) {
                if ($file->getExtension() == 'json') {
                    return [$language = Str::replace(".{$file->getExtension()}", '', $file->getFilename()) => $language];
                }

                if ($file->getExtension() == 'php') {
                    if (Str::contains($file->getPathname(), 'vendor')) {
                        $language = Str::of($file->getPathname())
                            ->after('vendor'.DIRECTORY_SEPARATOR)
                            ->explode(DIRECTORY_SEPARATOR)
                            ->get(1);

                        return [$language => $language];
                    }

                    $language = Str::of($file->getPathname())
                        ->after($this->languageFilesPath.DIRECTORY_SEPARATOR)
                        ->explode(DIRECTORY_SEPARATOR)
                        ->first();

                    return [$language => $language];
                }

                return [];
            })
            ->filter();
    }

    /**
     * Determine whether the given language exists.
     */
    public function languageExists(string $language): bool
    {
        return $this->languages()->contains($language);
    }

    /**
     * Add a new language.
     */
    public function addLanguage(string $language, string $name = null): void
    {
        if ($this->languageExists($language)) {
            throw new LanguageExistsException(
                Lang::get('translation::errors.language_exists', ['language' => $language])
            );
        }

        $this->disk->makeDirectory($this->path($language));

        if (! $this->disk->exists($this->path("{$language}.json"))) {
            $this->saveStringKeyTranslations($language, collect());
        }
    }

    /**
     * Get all the translations for a given language key.
     */
    public function allTranslationsFromMap(string $key): Collection
    {
        if (! $file = $this->map($key)) {
            return collect();
        }

        if (Str::endsWith($file, '.php')) {
            return collect($this->disk->getRequire("{$this->languageFilesPath}/{$file}"));
        }

        return collect(json_decode($this->disk->get("{$this->languageFilesPath}/{$file}"), true));
    }

    /**
     * Get the language name and the associated path from a file.
     *
     * @return array<string, string>
     */
    protected function getKeyAndPath(SplFileInfo $file): array
    {
        $path = Str::of($file->getPathname())
            ->replace($this->languageFilesPath, '')
            ->replaceFirst(DIRECTORY_SEPARATOR, '');

        $key = Str::of($path)
            ->replaceLast(".{$file->getExtension()}", '')
            ->replace(DIRECTORY_SEPARATOR, '.');

        return [(string) $key => (string) $path];
    }

    /**
     * Generate a path from the given arguments.
     */
    protected function path(...$args): string
    {
        $path = implode(DIRECTORY_SEPARATOR, $args);

        return Str::startsWith($path, $this->languageFilesPath) ? $path : $this->languageFilesPath.DIRECTORY_SEPARATOR.$path;
    }
}
