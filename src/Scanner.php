<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Filesystem\Filesystem;

class Scanner
{
    public function __construct(
        private Filesystem $disk,
        private array $scanPaths,
        private array $translationMethods,
    ) {
    }

    /**
     * Scan all the files in the provided $scanPath for translations.
     */
    public function findTranslations(): Translations
    {
        $results = Translations::make();

        // This has been derived from a combination of the following:
        // * Laravel Language Manager GUI from Mohamed Said (https://github.com/themsaid/laravel-langman-gui)
        // * Laravel 5 Translation Manager from Barry vd. Heuvel (https://github.com/barryvdh/laravel-translation-manager)
        $matchingPattern =
            '[^\w]'. // Must not start with any alphanum or _
            '(?<!->)'. // Must not start with ->
            '('.implode('|', $this->translationMethods).')'. // Must start with one of the functions
            "\(". // Match opening parentheses
            "[\'\"]". // Match " or '
            '('. // Start a new group to match:
            '.+'. // Must start with group
            ')'. // Close group
            "[\'\"]". // Closing quote
            "[\),]";  // Close parentheses or new parameter

        foreach ($this->scanPaths as $path) {
            foreach ($this->disk->allFiles($path) as $file) {
                if (preg_match_all("/$matchingPattern/siU", $file->getContents(), $matches)) {
                    foreach ($matches[2] as $key) {
                        if ($groupedMatches = $this->groupedMatches($key)) {
                            [$file, $k] = explode('.', $groupedMatches[0], 2);
                            $results->shortKeyTranslations->put(
                                $file,
                                array_merge($results->shortKeyTranslations->get($file, []), [$k => ''])
                            );

                            continue;
                        } else {
                            $results->stringKeyTranslations->put($key, '');
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Determine if the key is a grouped key.
     */
    protected function groupedMatches(string $key): false|array
    {
        return preg_match("/(^[a-zA-Z0-9:_-]+([.][^\1)\ ]+)+$)/siU", $key, $groupedMatches) === 1 ? $groupedMatches : false;
    }
}
