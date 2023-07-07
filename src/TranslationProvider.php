<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Translation\Translator;
use JoeDixon\TranslationCore\Console\Commands\SynchroniseTranslations;

class TranslationProvider
{
    public function __construct(
        protected Application $app,
        protected Configuration $config)
    {
        //
    }

    public static function init(Application $application, Configuration $config): self
    {
        return (new static($application, $config))
            ->registerContainerBindings()
            ->registerCommands()
            ->configureDriver($config->driver);
    }

    protected function configureDriver(string $driver): self
    {
        if ($driver === 'eloquent') {
            $this->loadMigrations();
            $this->registerDatabaseTranslator();
        }

        return $this;
    }

    public function loadMigrations()
    {
        $callback = function ($migrator) {
            $migrator->path(__DIR__.'/../database/migrations');
        };

        $this->app->afterResolving('migrator', $callback);

        if ($this->app->resolved('migrator')) {
            $callback($this->app->make('migrator'));
        }
    }

    /**
     * Register package bindings in the container.
     */
    private function registerContainerBindings(): self
    {
        $this->app->singleton(Scanner::class, function () {
            return new Scanner(
                new Filesystem,
                Arr::wrap($this->config->scanPaths),
                $this->config->translationMethods
            );
        });

        $this->app->singleton('translation.config', function () {
            return $this->config;
        });

        $this->app->singleton(Translation::class, function (Application $app) {
            return new TranslationManager($app);
        });

        return $this;
    }

    /**
     * Register package commands.
     */
    private function registerCommands(): self
    {
        if ($this->app->runningInConsole()) {
            Artisan::starting(function ($artisan) {
                $artisan->resolveCommands([
                    SynchroniseTranslations::class,
                ]);
            });
        }

        return $this;
    }

    private function registerDatabaseTranslator()
    {
        $this->registerDatabaseLoader();

        $this->app->extend('translator', function ($translator, $app) {
            $translator = new Translator(
                $app->make('translation.loader'),
                $app['config']['app.locale']
            );
            $translator->setFallback($app['config']['app.fallback_locale']);

            return $translator;
        });
    }

    protected function registerDatabaseLoader()
    {
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new DatabaseLoader(
                $app->make(TranslationManager::class)
            );
        });
    }
}
