<?php

namespace JoeDixon\TranslationCore\Console\Commands;

use Illuminate\Support\Str;

class ShowMissingTranslationKeys extends Command
{
    protected $signature = 'translation:show-missing-translation-keys
                            {language? : The language to show missing translations for}';

    protected $description = 'Show all of the translation keys which don\'t have a corresponding translation';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $language = $this->argument('language');
        $keys = $this->translation->keys();

        if (! $language) {
            $missingTranslations = $this->translation->allTranslations()
                ->map(fn ($translations) => $keys->diffKeys($translations));
        } else {
            $missingTranslations = collect([
                $language => $keys->diffKeys($this->translation->allTranslationsFor($language))]
            );
        }

        $missingTranslations = $missingTranslations->filter(fn ($translations) => ! $translations->isEmpty());

        if ($missingTranslations->isEmpty()) {
            $this->info(__('translation::translation.no_missing_keys'));

            return;
        }

        $this->components->twoColumnDetail(
            '<fg=green;options=bold>'.__('translation::translation.key').'</>',
            '<fg=yellow;options=bold>'.__('translation::translation.type').'</> <fg=gray>/</> '.__('translation::translation.group')
        );

        $missingTranslations->each(function ($translations, $language) {
            $translations->shortKeyTranslations->each(function ($translations, $group) use ($language) {
                collect($translations)->each(function ($value, $key) use ($group, $language) {
                    $namespace = null;

                    if (Str::contains($group, '::')) {
                        $namespace = Str::before($group, '::');
                        $group = Str::after($group, '::');
                    }

                    $this->components->twoColumnDetail(
                        $key.' <fg=gray>'.$language.'</>',
                        '<fg=yellow;options=bold>short</> <fg=gray>/</> '.($namespace ? $namespace.'<fg=gray>::</>' : '').$group
                    );
                });
            });

            $translations->stringKeyTranslations->each(function ($translations, $namespace) use ($language) {
                if (is_array($translations)) {
                    collect($translations)->each(function ($value, $key) use ($namespace, $language) {
                        $this->components->twoColumnDetail(
                            $key.' <fg=gray>'.$language.'</>',
                            '<fg=yellow;options=bold>string</> <fg=gray>/</> '.$namespace
                        );
                    });
                } else {
                    $this->components->twoColumnDetail(
                        $namespace.' <fg=gray>'.$language.'</>',
                        '<fg=yellow;options=bold>string</> <fg=gray>/</> root'
                    );
                }
            });
        });
    }
}
