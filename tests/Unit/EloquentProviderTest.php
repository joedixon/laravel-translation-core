<?php

namespace JoeDixon\Translation\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use JoeDixon\TranslationCore\Exceptions\LanguageExistsException;
use JoeDixon\TranslationCore\Translation;
use JoeDixon\TranslationCore\Translations;
use Tests\Cases\EloquentProviderTestCase;

uses(EloquentProviderTestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    File::deleteDirectory($this->app->langPath());
    File::copyDirectory(__DIR__.'/../fixtures/lang', $this->app->langPath());
    Artisan::call('translation:sync-translations file eloquent');
    $this->translation = $this->app->make(Translation::class);
});

afterEach(function () {
    File::deleteDirectory($this->app->langPath());
});

it('returns all languages', function () {
    $languages = $this->translation->languages();

    expect($languages)->toHaveCount(4);
    expect($languages->toArray())
        ->toEqual(['de' => 'de', 'en' => 'en', 'es' => 'es', 'nl' => 'nl']);
});

it('returns all translations', function () {
    $translations = $this->translation->allTranslations();

    expect(array_keys($translations->get('en')->short()->toArray()))
        ->toEqual(['home', 'home/nav/items', 'products', 'validation', 'laravel-translation::laravel-translation']);
    expect($translations->get('en')->short()['products'])
        ->toEqual(['products' => ['product_one' => ['title' => 'Product 1', 'description' => 'This is product one']], 'title' => 'Product 1']);
    expect($translations->get('en')->string()->toArray())
        ->toEqual(['Hello' => 'Hello', "What's up" => "What's up!", 'laravel-translation' => ['key' => 'value']]);
    expect($translations->toArray())->toHaveKey('de');
    expect($translations->toArray())->toHaveKey('en');
    expect($translations->toArray())->toHaveKey('es');
});

it('returns all translations for a given language', function () {
    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->string())->toBeEmpty();
    expect($translations->short()->toArray())->toEqual(['products' => ['title' => 'Product 1'], 'test' => ['hello' => 'Hola!', 'whats_up' => '¡Qué pasa!']]);
});

it('throws an exception if a language exists', function () {
    $this->translation->addLanguage('en');
})->throws(LanguageExistsException::class);

it('can add a new language', function () {
    $this->translation->addLanguage('pt');

    $this->assertDatabaseHas('languages', ['language' => 'pt']);
});

it('can add a new translation to a group', function () {
    $this->translation->addShortKeyTranslation('jp', 'test', 'hello', 'Kon\'nichiwa');

    $translations = $this->translation->allTranslationsFor('jp');

    expect($translations->short()->toArray())
        ->toEqual(['test' => ['hello' => 'Kon\'nichiwa']]);
});

it('can add a new translation to an existing translation group', function () {
    $this->translation->addShortKeyTranslation('es', 'test', 'test', 'Pruebas');

    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->short()->toArray()['test'])
        ->toEqual(['hello' => 'Hola!', 'whats_up' => '¡Qué pasa!', 'test' => 'Pruebas']);
});

it('can add a new translation to a nested translation group', function () {
    $this->translation->addShortKeyTranslation('es', 'test/test/test', 'test.test', 'Pruebas');

    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->short()->toArray()['test/test/test'])
        ->toEqual(['test' => ['test' => 'Pruebas']]);
});

it('can add a new string key translation', function () {
    $this->translation->addStringKeyTranslation('es', 'Hello', 'Hola!');

    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->string()->toArray())
        ->toEqual(['Hello' => 'Hola!']);
});

it('can add a new string key translation to an existing language', function () {
    $this->translation->addStringKeyTranslation('en', 'Test', 'Testing');

    $translations = $this->translation->allTranslationsFor('en');

    expect($translations->string()->toArray())
        ->toEqual(['Hello' => 'Hello', "What's up" => "What's up!", 'Test' => 'Testing', 'laravel-translation' => ['key' => 'value']]);
});

it('can add a new vendor string key translation', function () {
    $this->translation->addStringKeyTranslation('es', 'Hello', 'Hola!', 'laravel-translation');

    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->string()->toArray())
        ->toEqual(['laravel-translation' => ['Hello' => 'Hola!']]);
});

it('can add a new vendor string key translation to an existing language', function () {
    $this->translation->addStringKeyTranslation('en', 'Test', 'Testing', 'laravel-translation');

    $translations = $this->translation->allTranslationsFor('en');

    expect($translations->string()->toArray())
        ->toEqual(['Hello' => 'Hello', "What's up" => "What's up!", 'laravel-translation' => ['key' => 'value', 'Test' => 'Testing']]);
});

it('can get a collection of group names for a given language', function () {
    $groups = $this->translation->shortKeyGroups('de');

    expect($groups->toArray())->toEqual(['validation']);
});

it('can add a vendor namespaced translation', function () {
    $this->translation->addShortKeyTranslation('es', 'test', 'hello', 'Hola!', 'translation-test');
    expect($this->translation->allTranslationsFor('es')->short()['translation-test::test'])
        ->toEqual(['hello' => 'Hola!']);
});

it('can add a nested translation', function () {
    $this->translation->addShortKeyTranslation('es', 'test', 'test.nested.again', 'Nested!');

    expect($this->translation->allTranslationsFor('es')->short()['test'])
        ->toEqual(['hello' => 'Hola!', 'whats_up' => '¡Qué pasa!', 'test' => ['nested' => ['again' => 'Nested!']]]);
});

it('can add nested vendor namespaced translations', function () {
    $this->translation->addShortKeyTranslation('es', 'translation-test::test', 'nested.hello', 'Hola!');

    expect($this->translation->allTranslationsFor('es')->short()['translation-test::test'])
        ->toEqual(['nested' => ['hello' => 'Hola!']]);
});

it('can return a full list of available keys across all languages', function () {
    expect($this->translation->keys())
        ->toEqual(Translations::make(
            collect([
                'Hello' => '',
                "What's up" => '',
                'laravel-translation' => ['key' => ''],
            ]),
            collect([
                'validation' => [
                    'filled' => '',
                    'gt' => [
                        'array' => '',
                        'file' => '',
                        'numeric' => '',
                        'string' => '',
                    ],
                    'before_or_equal' => '',
                    'between' => [
                        'array' => '',
                        'file' => '',
                        'numeric' => '',
                        'string' => '',
                    ],
                ],
                'home' => [
                    'title' => '',
                ],
                'products' => [
                    'products' => [
                        'product_one' => [
                            'title' => '',
                            'description' => '',
                        ],
                    ],
                    'title' => '',
                ],
                'laravel-translation::laravel-translation' => [
                    'key' => '',
                ],
                'test' => [
                    'hello' => '',
                    'whats_up' => '',
                ],
                'home/nav/items' => [
                    'home' => '',
                    'about' => '',
                ],
                'laravel-translation::test' => [
                    'test' => '',
                ],
            ])
        ));
});

it('can save a string key translation in an empty file if it exists', function () {
    $this->translation->addStringKeyTranslation('jp', 'Hello', 'こんにちは');

    expect($this->translation->allTranslationsFor('jp')->string()->toArray())
        ->toEqual(['Hello' => 'こんにちは']);
});

it('can add a string key translation to an existing file', function () {
    $this->translation->addStringKeyTranslation('en', 'Hey', 'Hey there!');

    expect($this->translation->allTranslationsFor('en')->string()->toArray())
        ->toEqual(['Hello' => 'Hello', "What's up" => "What's up!", 'Hey' => 'Hey there!', 'laravel-translation' => ['key' => 'value']]);
});
