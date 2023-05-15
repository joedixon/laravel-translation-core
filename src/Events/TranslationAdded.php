<?php

namespace JoeDixon\TranslationCore\Events;

class TranslationAdded
{
    public function __construct(
        public string $language,
        public string $group,
        public string $key,
        public string $value,
    ) {
    }
}
