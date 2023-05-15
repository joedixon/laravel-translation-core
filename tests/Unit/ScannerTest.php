<?php

use Illuminate\Filesystem\Filesystem;
use JoeDixon\TranslationCore\Scanner;

test('it can find all translations', function () {
    $scanner = new Scanner(new Filesystem, [__DIR__.'/../fixtures/templates'], ['__', 'trans', 'trans_choice', '@lang', 'Lang::get']);
    $matches = $scanner->findTranslations()->toArray();

    expect($matches['string']->toArray())
        ->toEqual(['This will go in the string array' => '', 'trans' => '']);
    expect($matches['short']->toArray())
        ->toEqual(['lang' => ['first_match' => ''], 'lang_get' => ['first' => '', 'second' => ''], 'trans' => ['first_match' => '', 'third_match' => ''], 'trans_choice' => ['with_params' => '']]);
});
