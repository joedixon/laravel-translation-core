<?php

namespace JoeDixon\TranslationCore;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Manager;
use JoeDixon\TranslationCore\Providers\File\File;

class TranslationManager extends Manager
{
    public function getDefaultDriver()
    {
        return 'file';
    }

    protected function createFileDriver(): File
    {
        return new File(
            new Filesystem, 
            $this->container->make('path.lang'), 
            config('app.locale')
        );
    }
}
