<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\MediaLibrary;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Services\MediaLibrary\ImageServiceDefaults;
use Unusualify\Modularous\Tests\TestCase;

class ImageServiceDefaultsTest extends TestCase
{
    public function test_get_social_fallback_url_uses_configured_media_id(): void
    {
        Config::set('modularous.seo.image_default_id', 15);

        $service = new class
        {
            use ImageServiceDefaults;

            public function getSocialUrl($id): string
            {
                return "https://cdn.test/media/{$id}";
            }
        };

        $this->assertSame('https://cdn.test/media/15', $service->getSocialFallbackUrl());
    }

    public function test_get_social_fallback_url_uses_local_fallback_when_id_missing(): void
    {
        Config::set('modularous.seo.image_default_id', null);
        Config::set('modularous.seo.image_local_fallback', '/images/fallback.png');

        $service = new class
        {
            use ImageServiceDefaults;

            public function getSocialUrl($id): string
            {
                return "https://cdn.test/media/{$id}";
            }
        };

        $this->assertSame('/images/fallback.png', $service->getSocialFallbackUrl());
    }

    public function test_get_transparent_fallback_url(): void
    {
        $service = new class
        {
            use ImageServiceDefaults;
        };

        $this->assertStringStartsWith('data:image/gif;base64,', $service->getTransparentFallbackUrl());
    }
}
