<?php

namespace JoeDixon\Translation\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use JoeDixon\Translation\Events\TranslationAdded;
use JoeDixon\TranslationCore\Exceptions\LanguageExistsException;
use JoeDixon\TranslationCore\Translation;
use Tests\Cases\FileProviderTestCase;

uses(FileProviderTestCase::class);

beforeEach(function () {
    $this->translation = $this->app->make(Translation::class);
});

it('can build a map of translation files', function () {
    dd($this->translation->map());
    expect($this->translation->map()->last())
        ->toEqual('vendor/laravel-translation/en/validation.php');
});

it('can find a translation file from the translation file map', function () {
    expect($this->translation->map('en'))
        ->toEqual('en.json');
});

it('returns all languages', function () {
    $languages = $this->translation->allLanguages();

    expect($languages)->toHaveCount(4);
    expect($languages->toArray())
        ->toEqual(['de' => 'de', 'en' => 'en', 'es' => 'es', 'jp' => 'jp']);
});

it('returns all translations', function () {
    $translations = $this->translation->allTranslations();

    expect(array_keys($translations->get('en')->short()->toArray()))
        ->toEqual(['empty', 'home', 'products', 'validation', 'laravel-translation::laravel-translation', 'laravel-translation::validation']);
    expect($translations->get('en')->short()['products'])
        ->toEqual(['products' => ['product_one' => ['title' => 'Product 1', 'description' => 'This is product one']], 'title' => 'Product 1']);
    expect($translations->get('en')->string()->toArray())
        ->toEqual(['string' => ['Hello' => 'Hello', "What's up" => "What's up!"], 'laravel-translation::string' => ['key' => 'value']]);
    $this->assertArrayHasKey('de', $translations->toArray());
    $this->assertArrayHasKey('en', $translations->toArray());
    $this->assertArrayHasKey('es', $translations->toArray());
    $this->assertArrayHasKey('jp', $translations->toArray());
});

it('returns all translations for a given language', function () {
    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->string())->toBeEmpty();
    expect($translations->short()->toArray())->toEqual(['empty' => [], 'products' => ['title' => 'Product 1'], 'test' => ['hello' => 'Hola!', 'whats_up' => '¡Qué pasa!',]]);
});

it('throws an exception if a language exists', function () {
    $this->translation->addLanguage('en');
})->throws(LanguageExistsException::class);

it('can add a new language', function () {
    $this->translation->addLanguage('pt');

    expect(file_exists(__DIR__.'/../fixtures/lang/pt.json'))->toBeTrue();
    expect(file_exists(__DIR__.'/../fixtures/lang/pt'))->toBeTrue();

    rmdir(__DIR__.'/../fixtures/lang/pt');
    unlink(__DIR__.'/../fixtures/lang/pt.json');
});

it('can add a new translation to a group', function () {
    $this->translation->addShortKeyTranslation('jp', 'test', 'hello', 'Kon\'nichiwa');

    $translations = $this->translation->allTranslationsFor('jp');

    expect($translations->short()->toArray())
        ->toEqual(['test' => ['hello' => 'Kon\'nichiwa']]);

    unlink(__DIR__.'/../fixtures/lang/jp/test.php');
});

it('can add a new translation to an existing translation group', function () {
    $this->translation->addShortKeyTranslation('es', 'test', 'test', 'Pruebas');

    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->short()->toArray()['test'])
        ->toEqual(['hello' => 'Hola!', 'whats_up' => '¡Qué pasa!', 'test' => 'Pruebas']);

    file_put_contents(
        $this->app['path.lang'].'/es/test.php',
        "<?php\n\nreturn ".var_export(['hello' => 'Hola!', 'whats_up' => '¡Qué pasa!'], true).';'.\PHP_EOL
    );
});

it('can add a new string key translation', function () {
    $this->translation->addStringKeyTranslation('es', 'string', 'Hello', 'Hola!');

    $translations = $this->translation->allTranslationsFor('es');

    expect($translations->string()->toArray())
        ->toEqual(['string' => ['Hello' => 'Hola!']]);

    unlink(__DIR__.'/../fixtures/lang/es.json');
});

it('can add a new string key translation to an existing language', function () {
    $this->translation->addStringKeyTranslation('en', 'string', 'Test', 'Testing');

    $translations = $this->translation->allTranslationsFor('en');

    expect($translations->string()->toArray())
        ->toEqual(['string' => ['Hello' => 'Hello', "What's up" => "What's up!", 'Test' => 'Testing'], 'laravel-translation::string' => ['key' => 'value']]);

    file_put_contents(
        $this->app['path.lang'].'/en.json',
        json_encode((object) ['Hello' => 'Hello', 'What\'s up' => 'What\'s up!'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
});

it('can get a= collection of group names for a given language', function () {
    $groups = $this->translation->allShortKeyGroupsFor('de');

    $this->assertEquals($groups->toArray(), ['errors', 'validation']);
});

// it('it can merge a language with the base language', function () {
//     $this->translation->addShortKeyTranslation('es', 'test', 'hello', 'Hola!');
//     $translations = $this->translation->getSourceLanguageTranslationsWith('es');

//     $this->assertEquals($translations->toArray(), [
//         'short' => [
//             'test' => [
//                 'hello' => ['en' => 'Hello', 'es' => 'Hola!'],
//                 'whats_up' => ['en' => "What's up!", 'es' => ''],
//             ],
//         ],
//         'string' => [
//             'string' => [
//                 'Hello' => [
//                     'en' => 'Hello',
//                     'es' => '',
//                 ],
//                 "What's up" => [
//                     'en' => "What's up!",
//                     'es' => '',
//                 ],
//             ],
//         ],
//     ]);

//     unlink(__DIR__.'/fixtures/lang/es/test.php');
// });

// test('it_can_add_a_vendor_namespaced_translations', function () {
//     $this->translation->addShortKeyTranslation('es', 'translation_test::test', 'hello', 'Hola!');

//     $this->assertEquals($this->translation->allTranslationsFor('es')->toArray(), [
//         'short' => [
//             'translation_test::test' => [
//                 'hello' => 'Hola!',
//             ],
//         ],
//         'string' => [],
//     ]);

//     File::deleteDirectory(__DIR__.'/fixtures/lang/vendor');
// });

// test('it_can_add_a_nested_translation', function () {
//     $this->translation->addShortKeyTranslation('en', 'test', 'test.nested', 'Nested!');

//     $this->assertEquals($this->translation->allShortKeyTranslationsFor('en')->toArray(), [
//         'test' => [
//             'hello' => 'Hello',
//             'test.nested' => 'Nested!',
//             'whats_up' => 'What\'s up!',
//         ],
//     ]);

//     file_put_contents(
//         app()['path.lang'].'/en/test.php',
//         "<?php\n\nreturn ".var_export(['hello' => 'Hello', 'whats_up' => 'What\'s up!'], true).';'.\PHP_EOL
//     );
// });

// test('it_can_add_nested_vendor_namespaced_translations', function () {
//     $this->translation->addShortKeyTranslation('es', 'translation_test::test', 'nested.hello', 'Hola!');

//     $this->assertEquals($this->translation->allTranslationsFor('es')->toArray(), [
//         'short' => [
//             'translation_test::test' => [
//                 'nested.hello' => 'Hola!',
//             ],
//         ],
//         'string' => [],
//     ]);

//     File::deleteDirectory(__DIR__.'/fixtures/lang/vendor');
// });

// test('it_can_merge_a_namespaced_language_with_the_base_language', function () {
//     $this->translation->addShortKeyTranslation('en', 'translation_test::test', 'hello', 'Hello');
//     $this->translation->addShortKeyTranslation('es', 'translation_test::test', 'hello', 'Hola!');
//     $translations = $this->translation->getSourceLanguageTranslationsWith('es');

//     $this->assertEquals($translations->toArray(), [
//         'short' => [
//             'test' => [
//                 'hello' => ['en' => 'Hello', 'es' => ''],
//                 'whats_up' => ['en' => "What's up!", 'es' => ''],
//             ],
//             'translation_test::test' => [
//                 'hello' => ['en' => 'Hello', 'es' => 'Hola!'],
//             ],
//         ],
//         'string' => [
//             'string' => [
//                 'Hello' => [
//                     'en' => 'Hello',
//                     'es' => '',
//                 ],
//                 "What's up" => [
//                     'en' => "What's up!",
//                     'es' => '',
//                 ],
//             ],
//         ],
//     ]);

//     File::deleteDirectory(__DIR__.'/fixtures/lang/vendor');
// });

// test('a_list_of_languages_can_be_viewed', function () {
//     $this->get(config('translation.ui_url'))
//         ->assertSee('en');
// });

// test('the_language_creation_page_can_be_viewed', function () {
//     $this->get(config('translation.ui_url').'/create')
//         ->assertSee('Add a new language');
// });

// test('a_language_can_be_added', function () {
//     $this->post(config('translation.ui_url'), ['locale' => 'de'])
//         ->assertRedirect();

//     $this->assertTrue(file_exists(__DIR__.'/fixtures/lang/de.json'));
//     $this->assertTrue(file_exists(__DIR__.'/fixtures/lang/de'));

//     rmdir(__DIR__.'/fixtures/lang/de');
//     unlink(__DIR__.'/fixtures/lang/de.json');
// });

// test('a_list_of_translations_can_be_viewed', function () {
//     $this->get(config('translation.ui_url').'/en/translations')
//         ->assertSee('hello')
//         ->assertSee('whats_up');
// });

// test('the_translation_creation_page_can_be_viewed', function () {
//     $this->get(config('translation.ui_url').'/'.config('app.locale').'/translations/create')
//         ->assertSee('Add a translation');
// });

// test('a_new_translation_can_be_added', function () {
//     $this->post(config('translation.ui_url').'/en/translations', ['key' => 'joe', 'value' => 'is cool'])
//         ->assertRedirect();
//     $translations = $this->translation->allStringKeyTranslationsFor('en');

//     $this->assertEquals(['Hello' => 'Hello', 'What\'s up' => 'What\'s up!', 'joe' => 'is cool'], $translations->toArray()['string']);

//     file_put_contents(
//         app()['path.lang'].'/en.json',
//         json_encode((object) ['Hello' => 'Hello', 'What\'s up' => 'What\'s up!'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
//     );
// });

// test('a_translation_can_be_updated', function () {
//     $this->post(config('translation.ui_url').'/en', ['group' => 'test', 'key' => 'hello', 'value' => 'Hello there!'])
//         ->assertStatus(200);

//     $translations = $this->translation->allShortKeyTranslationsFor('en');

//     $this->assertEquals(['hello' => 'Hello there!', 'whats_up' => 'What\'s up!'], $translations->toArray()['test']);

//     file_put_contents(
//         app()['path.lang'].'/en/test.php',
//         "<?php\n\nreturn ".var_export(['hello' => 'Hello', 'whats_up' => 'What\'s up!'], true).';'.\PHP_EOL
//     );
// });

// test('adding_a_translation_fires_an_event_with_the_expected_data', function () {
//     Event::fake();

//     $data = ['key' => 'joe', 'value' => 'is cool'];
//     $this->post(config('translation.ui_url').'/en/translations', $data);

//     Event::assertDispatched(TranslationAdded::class, function ($event) use ($data) {
//         return $event->language === 'en' &&
//             $event->group === 'string' &&
//             $event->value === $data['value'] &&
//             $event->key === $data['key'];
//     });
//     file_put_contents(
//         app()['path.lang'].'/en.json',
//         json_encode((object) ['Hello' => 'Hello', 'What\'s up' => 'What\'s up!'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
//     );
// });

// test('updating_a_translation_fires_an_event_with_the_expected_data', function () {
//     Event::fake();

//     $data = ['group' => 'test', 'key' => 'hello', 'value' => 'Hello there!'];
//     $this->post(config('translation.ui_url').'/en/translations', $data);

//     Event::assertDispatched(TranslationAdded::class, function ($event) use ($data) {
//         return $event->language === 'en' &&
//             $event->group === $data['group'] &&
//             $event->value === $data['value'] &&
//             $event->key === $data['key'];
//     });
//     file_put_contents(
//         app()['path.lang'].'/en/test.php',
//         "<?php\n\nreturn ".var_export(['hello' => 'Hello', 'whats_up' => 'What\'s up!'], true).';'.\PHP_EOL
//     );
// });
