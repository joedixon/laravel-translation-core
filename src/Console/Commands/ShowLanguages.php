<?php

namespace JoeDixon\TranslationCore\Console\Commands;

use JoeDixon\TranslationCore\Console\Commands\Command;

class ShowLanguages extends Command
{
    protected $signature = 'translation:list-languages';

    protected $description = 'List all of the available languages in the application';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->components->twoColumnDetail(
            '<fg=green;options=bold>'.__('translation::translation.language_name').'</>', 
            __('translation::translation.language')
        );

        foreach ($this->translation->languages() as $language => $name) {
            $this->components->twoColumnDetail($name, $language);
        }
    }
}