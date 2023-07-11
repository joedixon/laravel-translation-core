<?php

namespace Tests\Cases;

use JoeDixon\TranslationCore\Configuration;
use JoeDixon\TranslationCore\TranslationProvider;
use Orchestra\Testbench\TestCase;

class EloquentProviderTestCase extends TestCase
{
    /**
     * Define the test environment.
     */
    protected function defineEnvironment($app): void
    {
        $app->useLangPath(__DIR__.'/../lang');

        TranslationProvider::init(
            $app,
            new Configuration('eloquent')
        );
    }
}
