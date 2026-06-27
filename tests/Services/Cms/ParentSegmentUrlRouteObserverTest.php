<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Modules\Cms\Entities\Page;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Observers\ParentSegmentUrlRouteObserver;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;
use Unusualify\Modularous\Tests\TestCase;

class ParentSegmentUrlRouteObserverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $table = modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');
        Schema::dropIfExists($table);
        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->string('target_model_class');
            $table->string('locale', 24)->nullable();
            $table->string('normalized_prefix', 2048)->nullable();
            $table->string('admin_label', 191)->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->app['config']->set('modularous.cms_parent_segments.enabled', true);
        $this->app['config']->set('modularous.cms_routing.resync_registry_after_parent_segments_change', true);
        $this->app->singleton(CanonicalUrlResolverInterface::class, CanonicalUrlResolver::class);
    }

    public function test_created_requests_resync_for_target_class(): void
    {
        $registry = $this->createMock(PublicUrlRegistryContract::class);
        $registry->expects($this->once())
            ->method('syncPublicPageRoutesForAllModelsOfClass')
            ->with(Page::class);

        $observer = $this->makeObserver($registry);
        $segment = ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'enabled' => true,
            'sort_order' => 0,
        ]);
        $observer->created($segment);
    }

    public function test_updated_skips_non_url_relevant_field_changes(): void
    {
        $registry = $this->createMock(PublicUrlRegistryContract::class);
        $registry->expects($this->never())->method('syncPublicPageRoutesForAllModelsOfClass');

        $observer = $this->makeObserver($registry);
        $segment = ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $segment->admin_label = 'renamed';
        $observer->saving($segment);
        $segment->save();
        $observer->updated($segment);
    }

    public function test_updated_resyncs_when_prefix_changes(): void
    {
        $registry = $this->createMock(PublicUrlRegistryContract::class);
        $registry->expects($this->once())
            ->method('syncPublicPageRoutesForAllModelsOfClass')
            ->with(Page::class);

        $observer = $this->makeObserver($registry);
        $segment = ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'enabled' => true,
            'sort_order' => 0,
        ]);
        $segment->normalized_prefix = 'blog';
        $observer->saving($segment);
        $segment->save();

        $observer->updated($segment);
    }

    public function test_updated_resyncs_both_targets_when_target_model_class_changes(): void
    {
        $calls = [];

        $registry = $this->createMock(PublicUrlRegistryContract::class);
        $registry->expects($this->exactly(2))
            ->method('syncPublicPageRoutesForAllModelsOfClass')
            ->willReturnCallback(function (string $class) use (&$calls): void {
                $calls[] = $class;
            });

        $observer = $this->makeObserver($registry);
        $segment = ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $segment->target_model_class = 'App\\Blog\\BlogPost';
        $observer->saving($segment);
        $segment->save();

        $observer->updated($segment);

        $this->assertSame([Page::class, 'App\\Blog\\BlogPost'], $calls);
    }

    public function test_deleted_requests_resync_for_target_class(): void
    {
        $registry = $this->createMock(PublicUrlRegistryContract::class);
        $registry->expects($this->once())
            ->method('syncPublicPageRoutesForAllModelsOfClass')
            ->with(Page::class);

        $observer = $this->makeObserver($registry);
        $segment = ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'enabled' => true,
            'sort_order' => 0,
        ]);
        $segment->delete();
        $observer->deleted($segment);
    }

    public function test_observer_skips_when_resync_disabled_in_config(): void
    {
        $this->app['config']->set('modularous.cms_routing.resync_registry_after_parent_segments_change', false);

        $registry = $this->createMock(PublicUrlRegistryContract::class);
        $registry->expects($this->never())->method('syncPublicPageRoutesForAllModelsOfClass');

        $observer = $this->makeObserver($registry);
        $segment = ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'enabled' => true,
            'sort_order' => 0,
        ]);
        $observer->created($segment);
        $observer->deleted($segment);
    }

    /** Smoke: container binds {@see ParentSegmentUrlRouteObserver} via {@see CmsServiceProvider}. */
    public function test_cms_service_provider_resolves_observer(): void
    {
        $this->app['config']->set('modularous.cms_features.enabled', true);
        $this->app['config']->set('modularous.cms_routing.resync_registry_after_parent_segments_change', true);

        $this->app->register(\Modules\Cms\Providers\CmsServiceProvider::class);

        /** @var ParentSegmentUrlRouteObserver $observer Resolved with {@see PublicUrlRegistryContract}. */
        $observer = $this->app->make(ParentSegmentUrlRouteObserver::class);

        $this->assertInstanceOf(ParentSegmentUrlRouteObserver::class, $observer);
    }

    private function makeObserver(PublicUrlRegistryContract $registry): ParentSegmentUrlRouteObserver
    {
        return new ParentSegmentUrlRouteObserver(
            $registry,
            $this->app->make(CmsPublicUrlRegistryCacheManager::class),
        );
    }
}
