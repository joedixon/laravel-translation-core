<?php

namespace Tests\Cases;

use JoeDixon\TranslationCore\Configuration;
use JoeDixon\TranslationCore\TranslationProvider;
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

    /**
     * Define the test environment.
     */
    protected function defineEnvironment($app): void
    {
        $app->useLangPath(__DIR__.'/../lang');
    }
}
