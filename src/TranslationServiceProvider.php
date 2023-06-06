<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use JoeDixon\TranslationCore\Console\Commands\SynchroniseTranslations;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfiguration();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerContainerBindings();
    }

    /**
     * Register package bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfiguration();
        $this->registerCommands();
    }

    /**
     * Publish package configuration.
     *
     * @return void
     */
    private function publishConfiguration()
    {
        $this->publishes([
            __DIR__.'/../config/translation.php' => config_path('translation.php'),
        ], 'config');
    }

    /**
     * Merge package configuration.
     *
     * @return void
     */
    private function mergeConfiguration()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/translation.php', 'translation');
    }

    /**
     * Load package translations.
     *
     * @return void
     */
    private function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'translation');

        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/translation'),
        ]);
    }

    /**
     * Register package commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SynchroniseTranslations::class,
            ]);
        }
    }

    /**
     * Load package migrations.
     *
     * @return void
     */
    private function loadMigrations()
    {
        if (config('translation.driver') !== 'eloquent') {
            return;
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register package bindings in the container.
     *
     * @return void
     */
    private function registerContainerBindings()
    {
        $config = $this->app->get('config')['translation'];

        $this->app->singleton(Scanner::class, function () use ($config) {
            return new Scanner(new Filesystem, Arr::wrap($config['scan_paths']), $config['translation_methods']);
        });

        $this->app->singleton(Translation::class, function (Application $app) {
            return new TranslationManager($app);
        });
    }
}
