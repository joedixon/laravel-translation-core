<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Manager;
use JoeDixon\TranslationCore\Providers\Eloquent\Eloquent;
use JoeDixon\TranslationCore\Providers\File\File;

class TranslationManager extends Manager
{
    public function getDefaultDriver()
    {
        return app('translation.config')->driver;
    }

    protected function createFileDriver(): File
    {
        return new File(
            new Filesystem,
            $this->container->make('path.lang'),
            config('app.locale')
        );
    }

    protected function createEloquentDriver(): Eloquent
    {
        return new Eloquent($this->config['app.locale']);
    }
}
