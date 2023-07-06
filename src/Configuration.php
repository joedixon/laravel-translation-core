<?php

namespace JoeDixon\TranslationCore;

class Configuration
{
    public function __construct(
        public string $driver = 'file',
        public array $translationMethods = ['trans', '__'],
        public array $scanPaths = [],
        public array $database = [
            'connection' => null,
            'languages_table' => 'languages',
            'translations_table' => 'translations',
        ]
    ) {
        //
    }
}
