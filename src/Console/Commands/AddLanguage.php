<?php

namespace JoeDixon\TranslationCore\Console\Commands;

class AddLanguage extends Command
{
    protected $signature = 'translation:add-language
                            {language : The language code for the language you wish to add}
                            {name? : The name of the language you wish to add}';

    protected $description = 'Add a new language to the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $language = $this->argument('language');
        $name = $this->argument('name');

        try {
            $this->translation->addLanguage($language, $name);
            $this->components->info(__('translation::translation.language_added'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
