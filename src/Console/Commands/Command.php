<?php

namespace JoeDixon\TranslationCore\Console\Commands;

use Illuminate\Console\Command as BaseCommand;
use JoeDixon\TranslationCore\TranslationManager;

class Command extends BaseCommand
{
    public function __construct(protected TranslationManager $translation)
    {
        parent::__construct();
    }
}
