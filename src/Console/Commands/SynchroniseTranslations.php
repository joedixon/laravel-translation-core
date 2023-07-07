<?php

namespace JoeDixon\TranslationCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JoeDixon\TranslationCore\TranslationManager;
use JoeDixon\TranslationCore\Translations;

class SynchroniseTranslations extends Command
{
    protected $signature = 'translation:sync-translations {from} {to} {language=all}';

    protected $description = 'Synchronize translations between drivers';

    protected $to;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $language = $this->argument('language');
        $from = app()->make(TranslationManager::class)->driver(
            $this->argument('from')
        );
        $this->to = app()->make(TranslationManager::class)->driver(
            $this->argument('to')
        );

        $this->line(__('translation::translation.syncing'));

        if ($language !== 'all') {
            $this->mergeTranslations($language, $from->allTranslationsFor($language));
        } else {
            $this->mergeLanguages($from->allTranslations());
        }

        $this->info(__('translation::translation.synced'));
    }

    /**
     * @param  Collection<string,Translations>  $languages
     */
    private function mergeLanguages(Collection $languages): void
    {
        foreach ($languages as $language => $translations) {
            $this->mergeTranslations($language, $translations);
        }
    }

    private function mergeTranslations(string $language, Translations $translations): void
    {
        $this->mergeShortKeyTranslations($language, $translations->shortKeyTranslations);
        $this->mergeStringKeyTranslations($language, $translations->stringKeyTranslations);
    }

    /**
     * @param  Collection<string, array>  $groups
     */
    private function mergeShortKeyTranslations(string $language, Collection $groups): void
    {
        $groups->each(function ($translations, $group) use ($language) {
            $vendor = null;

            if (Str::contains($group, '::')) {
                $vendor = Str::before($group, '::');
                $group = Str::after($group, '::');
            }

            collect($translations)->each(function ($value, $key) use ($language, $group, $vendor) {
                if (is_array($value)) {
                    foreach (Arr::dot($value) as $subKey => $subValue) {
                        $this->to->addShortKeyTranslation($language, $group, $key.'.'.$subKey, $subValue, $vendor);
                    }
                } else {
                    $this->to->addShortKeyTranslation($language, $group, $key, $value, $vendor);
                }
            });
        });
    }

    /**
     * @param  Collection<string,string|array>  $translations
     */
    private function mergeStringKeyTranslations(string $language, Collection $translations, string|null $vendor = null): void
    {
        foreach ($translations as $key => $value) {
            if (is_array($value)) {
                $this->mergeStringKeyTranslations($language, collect($value), $key);

                return;
            }

            $this->to->addStringKeyTranslation($language, $key, $value, $vendor);
        }
    }
}
