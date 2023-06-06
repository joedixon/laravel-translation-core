<?php

namespace JoeDixon\TranslationCore\Console\Commands;

use Illuminate\Console\Command as BaseCommand;
use JoeDixon\TranslationCore\Providers\Eloquent\Translation;

class Command extends BaseCommand
{
    public function __construct(protected Translation $translation)
    {
        parent::__construct();
    }
}
