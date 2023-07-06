<?php

namespace Tests\Cases;

use JoeDixon\TranslationCore\Configuration;
use JoeDixon\TranslationCore\TranslationProvider;
use Orchestra\Testbench\TestCase;

class EloquentProviderTestCase extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app->useLangPath(__DIR__.'/../lang');
        
        TranslationProvider::init(
            $app,
            new Configuration('eloquent')
        );
    }
}
