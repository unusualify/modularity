<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Jobs\Cache\Concerns;

use Unusualify\Modularous\Events\Cache\CacheWarmProgress;
use Unusualify\Modularous\Jobs\Cache\Concerns\BuildsCacheWarmBroadcastToast;
use Unusualify\Modularous\Tests\TestCase;

class BuildsCacheWarmBroadcastToastTest extends TestCase
{
    /** @test */
    public function it_builds_module_route_detail_without_id(): void
    {
        $helper = $this->toastHelper();

        $this->assertSame('Blog:Post', $helper->detail('Blog', 'Post'));
        $this->assertSame('Blog:Post', $helper->detail('Blog', 'Post', null));
        $this->assertSame('Blog:Post', $helper->detail('Blog', 'Post', ''));
    }

    /** @test */
    public function it_appends_model_id_when_present(): void
    {
        $helper = $this->toastHelper();

        $this->assertSame('Blog:Post:12', $helper->detail('Blog', 'Post', 12));
        $this->assertSame('Blog:Post:12', $helper->detail('Blog', 'Post', '12'));
    }

    /** @test */
    public function it_returns_null_when_module_or_route_is_missing(): void
    {
        $helper = $this->toastHelper();

        $this->assertNull($helper->detail(null, 'Post', 12));
        $this->assertNull($helper->detail('Blog', null, 12));
        $this->assertNull($helper->detail('', 'Post', 12));
        $this->assertNull($helper->detail('Blog', '', 12));
    }

    /** @test */
    public function it_maps_skipped_status_to_warning_variant(): void
    {
        $helper = $this->toastHelper();

        $toast = $helper->toast(
            CacheWarmProgress::STATUS_SKIPPED,
            'Cache warm',
            'Skipped: cache is disabled for this route',
            'Blog:Post',
        );

        $this->assertSame('warning', $toast['variant']);
        $this->assertSame('Blog:Post', $toast['detail']);
    }

    private function toastHelper(): object
    {
        return new class
        {
            use BuildsCacheWarmBroadcastToast;

            public function detail(?string $moduleName, ?string $moduleRouteName, mixed $id = null): ?string
            {
                return $this->cacheWarmDetail($moduleName, $moduleRouteName, $id);
            }

            /**
             * @return array{title: string, description: string, detail: ?string, variant: string}
             */
            public function toast(
                string $status,
                string $title,
                string $description,
                ?string $detail = null,
                ?string $variant = null,
            ): array {
                return $this->cacheWarmToast($status, $title, $description, $detail, $variant);
            }
        };
    }
}
