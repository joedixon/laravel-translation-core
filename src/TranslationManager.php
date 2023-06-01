<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Manager;
use JoeDixon\TranslationCore\Providers\File\File;
use JoeDixon\TranslationCore\Providers\Eloquent\Eloquent;

class TranslationManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->config['translation.driver'] ?? 'file';
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
