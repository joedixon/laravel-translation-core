<?php

namespace JoeDixon\Translation\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use JoeDixon\Translation\Events\TranslationAdded;
use JoeDixon\Translation\Exceptions\LanguageExistsException;
use JoeDixon\TranslationCore\Translation;
use Tests\Cases\FileProviderTestCase;

uses(FileProviderTestCase::class);

beforeEach(function () {
    $this->translation = $this->app->make(Translation::class);
});

it('can build a map of translation files', function () {
    expect($this->translation->map()->last())
        ->toEqual('vendor/laravel-translation/en/validation.php');
});

it('can find a translation file from the translation file map', function () {
    expect($this->translation->map('en'))
        ->toEqual('en.json');
});

it('returns all languages', function () {
    $languages = $this->translation->allLanguages();

    expect($languages)->toHaveCount(5);
    expect($languages->toArray())
        ->toEqual(['de' => 'de', 'en' => 'en', 'es' => 'es', 'fr' => 'fr', 'jp' => 'jp']);
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
    $this->assertArrayHasKey('fr', $translations->toArray());
    $this->assertArrayHasKey('jp', $translations->toArray());
});

// test('it_returns_all_translations_for_a_given_language', function () {
//     $translations = $this->translation->allTranslationsFor('en');
//     $this->assertEquals($translations->count(), 2);
//     $this->assertEquals(['string' => ['string' => ['Hello' => 'Hello', "What's up" => "What's up!"]], 'short' => ['test' => ['hello' => 'Hello', 'whats_up' => "What's up!"]]], $translations->toArray());
//     $this->assertArrayHasKey('string', $translations->toArray());
//     $this->assertArrayHasKey('short', $translations->toArray());
// });

// test('it_throws_an_exception_if_a_language_exists', function () {
//     $this->expectException(LanguageExistsException::class);
//     $this->translation->addLanguage('en');
// });

// test('it_can_add_a_new_language', function () {
//     $this->translation->addLanguage('fr');

//     $this->assertTrue(file_exists(__DIR__.'/fixtures/lang/fr.json'));
//     $this->assertTrue(file_exists(__DIR__.'/fixtures/lang/fr'));

//     rmdir(__DIR__.'/fixtures/lang/fr');
//     unlink(__DIR__.'/fixtures/lang/fr.json');
// });

// test('it_can_add_a_new_translation_to_a_new_group', function () {
//     $this->translation->addShortKeyTranslation('es', 'test', 'hello', 'Hola!');

//     $translations = $this->translation->allTranslationsFor('es');

//     $this->assertEquals(['test' => ['hello' => 'Hola!']], $translations->toArray()['short']);

//     unlink(__DIR__.'/fixtures/lang/es/test.php');
// });

// test('it_can_add_a_new_translation_to_an_existing_translation_group', function () {
//     $this->translation->addShortKeyTranslation('en', 'test', 'test', 'Testing');

//     $translations = $this->translation->allTranslationsFor('en');

//     $this->assertEquals(['test' => ['hello' => 'Hello', 'whats_up' => 'What\'s up!', 'test' => 'Testing']], $translations->toArray()['short']);

//     file_put_contents(
//         app()['path.lang'].'/en/test.php',
//         "<?php\n\nreturn ".var_export(['hello' => 'Hello', 'whats_up' => 'What\'s up!'], true).';'.\PHP_EOL
//     );
// });

// test('it_can_add_a_new_string_key_translation', function () {
//     $this->translation->addStringKeyTranslation('es', 'string', 'Hello', 'Hola!');

//     $translations = $this->translation->allTranslationsFor('es');

//     $this->assertEquals(['string' => ['Hello' => 'Hola!']], $translations->toArray()['string']);

//     unlink(__DIR__.'/fixtures/lang/es.json');
// });

// test('it_can_add_a_new_string_key_translation_to_an_existing_language', function () {
//     $this->translation->addStringKeyTranslation('en', 'string', 'Test', 'Testing');

//     $translations = $this->translation->allTranslationsFor('en');

//     $this->assertEquals(['string' => ['Hello' => 'Hello', 'What\'s up' => 'What\'s up!', 'Test' => 'Testing']], $translations->toArray()['string']);

//     file_put_contents(
//         app()['path.lang'].'/en.json',
//         json_encode((object) ['Hello' => 'Hello', 'What\'s up' => 'What\'s up!'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
//     );
// });

// test('it_can_get_a_collection_of_group_names_for_a_given_language', function () {
//     $groups = $this->translation->allShortKeyGroupsFor('en');

//     $this->assertEquals($groups->toArray(), ['test']);
// });

// test('it_can_merge_a_language_with_the_base_language', function () {
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
