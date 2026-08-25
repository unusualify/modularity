<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Settings;

use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Services\Settings\AbstractSingularSettingsService;
use Unusualify\Modularous\Tests\TestCase;

class AbstractSingularSettingsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        config(['app.fallback_locale' => 'en']);
    }

    public function test_untranslated_media_list_resolves_leaf_for_any_locale(): void
    {
        $service = $this->service([
            'seo' => [
                'og_image' => [
                    [
                        'id' => 248,
                        'original' => 'https://cms.test/og.png',
                        'frontend' => 'https://front.test/og.png',
                    ],
                ],
            ],
        ]);

        $this->assertSame('https://cms.test/og.png', $service->get('seo.og_image.original'));
        $this->assertSame('https://cms.test/og.png', $service->get('seo.og_image.original', null, 'tr'));
        $this->assertSame('https://front.test/og.png', $service->value('seo.og_image.frontend', null, 'de'));
        $this->assertTrue($service->filled('seo.og_image.original', 'fr'));
    }

    public function test_translated_media_list_still_resolves_via_locale(): void
    {
        $service = $this->service([
            'site' => [
                'favicon' => [
                    'en' => [
                        [
                            'id' => 1,
                            'original' => 'https://cms.test/en-favicon.png',
                        ],
                    ],
                    'tr' => [
                        [
                            'id' => 2,
                            'original' => 'https://cms.test/tr-favicon.png',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('https://cms.test/en-favicon.png', $service->get('site.favicon.original', null, 'en'));
        $this->assertSame('https://cms.test/tr-favicon.png', $service->get('site.favicon.original', null, 'tr'));
    }

    public function test_translated_image_field_returns_locale_list_not_raw_map(): void
    {
        $enLogo = [['original' => 'https://cms.test/en-logo.png', 'url' => 'https://cms.test/en-logo.png']];
        $trLogo = [['original' => 'https://cms.test/tr-logo.png', 'url' => 'https://cms.test/tr-logo.png']];

        $service = $this->service([
            'site' => [
                'logo' => [
                    'en' => $enLogo,
                    'tr' => $trLogo,
                ],
            ],
        ]);

        app()->setLocale('tr');

        $this->assertSame($trLogo, $service->get('site.logo'));
        $this->assertSame($trLogo, $service->value('site.logo'));
        $this->assertSame('https://cms.test/tr-logo.png', $service->get('site.logo.original'));
    }

    public function test_explicit_list_index_is_not_swallowed_by_untranslated_dive(): void
    {
        $service = $this->service([
            'social' => [
                ['url' => 'https://a.example'],
                ['url' => 'https://b.example'],
            ],
        ]);

        $this->assertSame('https://b.example', $service->get('social.1.url'));
        $this->assertSame('https://a.example', $service->get('social.0.url'));
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function service(array $snapshot): AbstractSingularSettingsService
    {
        return new class($snapshot) extends AbstractSingularSettingsService
        {
            /**
             * @param array<string, mixed> $fixture
             */
            public function __construct(private array $fixture) {}

            /**
             * @return array<string, mixed>
             */
            public function snapshot(): array
            {
                return $this->fixture;
            }

            protected function cacheKey(): string
            {
                return 'test_settings.snapshot';
            }

            protected function cacheTtl(): int
            {
                return 1;
            }

            protected function modelClass(): string
            {
                return \stdClass::class;
            }

            protected function repository(): Repository
            {
                throw new \RuntimeException('Repository is not used in snapshot stub tests.');
            }

            /**
             * @return list<string>
             */
            protected function settingsSections(): array
            {
                return array_keys($this->fixture);
            }
        };
    }
}
