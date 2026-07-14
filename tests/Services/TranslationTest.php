<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use JoeDixon\Translation\Drivers\Translation;
use JoeDixon\Translation\Events\TranslationAdded;
use Mockery;
use Unusualify\Modularous\Tests\TestCase;

class TranslationTest extends TestCase
{
    public function test_find_missing_translations_returns_diff_between_scanner_and_language(): void
    {
        $driver = $this->makeDriver();
        $driver->scanner->shouldReceive('findTranslations')->andReturn([
            'group' => [
                'messages' => [
                    'hello' => 'Hello',
                    'goodbye' => 'Goodbye',
                ],
            ],
        ]);
        $driver->shouldReceive('allTranslationsFor')
            ->with('fr')
            ->andReturn(collect([
                'group' => collect([
                    'messages' => collect([
                        'hello' => 'Bonjour',
                    ]),
                ]),
            ]));

        $missing = $driver->findMissingTranslations('fr');

        $this->assertArrayHasKey('group', $missing);
        $this->assertSame('Goodbye', $missing['group']['messages']['goodbye']);
    }

    public function test_get_source_language_translations_with_merges_locales(): void
    {
        config(['app.locale' => 'en']);

        $driver = $this->makeDriver();
        $driver->shouldReceive('allTranslationsFor')
            ->with('en')
            ->andReturn(collect([
                'group' => collect([
                    'messages' => collect([
                        'hello' => 'Hello',
                    ]),
                ]),
            ]));
        $driver->shouldReceive('allTranslationsFor')
            ->with('fr')
            ->andReturn(collect([
                'group' => collect([
                    'messages' => collect([
                        'hello' => 'Bonjour',
                    ]),
                ]),
            ]));

        $merged = $driver->getSourceLanguageTranslationsWith('fr');

        $this->assertInstanceOf(Collection::class, $merged);
        $this->assertSame('Hello', $merged['group']['messages']['hello']['en']);
        $this->assertSame('Bonjour', $merged['group']['messages']['hello']['fr']);
    }

    public function test_filter_translations_for_returns_all_when_filter_is_empty(): void
    {
        $driver = Mockery::mock(StubTranslationDriver::class)->makePartial();
        $driver->shouldReceive('getSourceLanguageTranslationsWith')
            ->with('fr')
            ->andReturn(collect(['group' => collect()]));

        $this->assertEquals(
            collect(['group' => collect()]),
            $driver->filterTranslationsFor('fr', ''),
        );
    }

    public function test_add_dispatches_translation_added_event_for_group_translations(): void
    {
        Event::fake([TranslationAdded::class]);

        $driver = $this->makeDriver();
        $driver->shouldReceive('addGroupTranslation')
            ->once()
            ->with('fr', 'messages', 'title', 'Titre');

        $request = Request::create('/translations', 'POST', [
            'group' => 'messages',
            'key' => 'title',
            'value' => 'Titre',
        ]);

        $driver->add($request, 'fr', true);

        Event::assertDispatched(TranslationAdded::class, function (TranslationAdded $event) {
            return $event->language === 'fr'
                && $event->group === 'messages'
                && $event->key === 'title'
                && $event->value === 'Titre';
        });
    }

    public function test_save_missing_translations_persists_missing_group_keys(): void
    {
        $driver = Mockery::mock(StubTranslationDriver::class)->makePartial();
        $driver->shouldReceive('allLanguages')->andReturn(collect(['fr' => 'French']));
        $driver->shouldReceive('findMissingTranslations')->with('fr')->andReturn([
            'group' => [
                'messages' => [
                    'welcome' => 'Welcome',
                ],
            ],
        ]);
        $driver->shouldReceive('addGroupTranslation')
            ->once()
            ->with('fr', 'messages', 'welcome');

        $driver->saveMissingTranslations('fr');
    }

    private function makeDriver(): StubTranslationDriver
    {
        $driver = Mockery::mock(StubTranslationDriver::class)->makePartial();
        $driver->scanner = Mockery::mock('stdClass');

        return $driver;
    }
}

class StubTranslationDriver extends Translation
{
    public $scanner;

    public function allTranslationsFor($language)
    {
        return collect();
    }

    public function allLanguages()
    {
        return collect();
    }

    public function addGroupTranslation($language, $group, $key, $value = '')
    {
        return null;
    }

    public function addSingleTranslation($language, $group, $key, $value = '')
    {
        return null;
    }
}
