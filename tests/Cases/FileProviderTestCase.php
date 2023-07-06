<?php

namespace Tests\Cases;

use JoeDixon\TranslationCore\Configuration;
use JoeDixon\TranslationCore\TranslationProvider;
use JoeDixon\TranslationCore\TranslationServiceProvider;
use Orchestra\Testbench\TestCase;

class FileProviderTestCase extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        TranslationProvider::init(
            $this->app,
            new Configuration
        );
    }

    protected function defineEnvironment($app)
    {
        $app->useLangPath(__DIR__.'/../lang');
    }
}
