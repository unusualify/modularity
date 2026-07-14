<?php

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\Traits\Cache\Cacheable;
use Unusualify\Modularous\Traits\Cache\CacheKeyGenerators;
use Unusualify\Modularous\Traits\Cache\HasUserAwareCache;
use Unusualify\Modularous\Traits\Cache\WarmupCache;

class CacheTraitsTest extends TestCase
{
    /** @test */
    public function it_can_check_if_cache_should_be_used()
    {
        $tester = new class
        {
            use Cacheable;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', null)->andReturn(true);
        $this->assertTrue($tester->shouldUseCache());

        $tester->withoutCache();
        $this->assertFalse($tester->shouldUseCache());
    }

    /** @test */
    public function it_can_generate_cache_keys_with_user_context()
    {
        $tester = new class
        {
            use Cacheable, HasUserAwareCache;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }

            // Override traitProperties for HasUserAwareCache detection since we are an anonymous class
            protected function traitProperties($method)
            {
                return [];
            }
        };
        $tester->withUserAwareCache(true);

        $user = \Mockery::mock(Authenticatable::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn(123);
        Auth::shouldReceive('user')->andReturn($user);

        ModularousCache::shouldReceive('generateCacheKey')->with('Blog', 'Post', 'index', ['_user' => 'u123'])->andReturn('blog:post:index:u123');

        $key = $tester->generateTypeCacheKey('index', []);
        $this->assertEquals('blog:post:index:u123', $key);
    }

    /** @test */
    public function it_can_manage_cache_enabled_status()
    {
        $tester = new class
        {
            use Cacheable;
        };

        $this->assertTrue($tester->getSelfCacheEnabled());
        $tester->withoutCache();
        $this->assertFalse($tester->getSelfCacheEnabled());
        $tester->withCache(true);
        $this->assertTrue($tester->getSelfCacheEnabled());
    }

    /** @test */
    public function it_can_handle_user_aware_cache_settings()
    {
        $tester = new class
        {
            use HasUserAwareCache;
        };

        $this->assertFalse($tester->shouldUseUserAwareCache());
        $tester->withUserAwareCache(true);
        $this->assertTrue($tester->shouldUseUserAwareCache());

        $tester->withSharedCache();
        $this->assertFalse($tester->shouldUseUserAwareCache());
    }

    /** @test */
    public function it_generates_guest_identifier_when_unauthenticated()
    {
        $tester = new class
        {
            use HasUserAwareCache;
        };

        Auth::shouldReceive('user')->andReturn(null);
        $this->assertEquals('guest', $tester->getUserCacheIdentifier());
    }

    /** @test */
    public function it_generates_record_cache_key()
    {
        $tester = new class
        {
            use CacheKeyGenerators;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        ModularousCache::shouldReceive('generateCacheKey')
            ->with('Blog', 'Post', 'record', ['id' => 42])
            ->once()
            ->andReturn('blog:post:record:42');

        $this->assertEquals('blog:post:record:42', $tester->generateRecordKey('Blog', 'Post', 42));
    }

    /** @test */
    public function it_resolves_formatted_item_cache_specifiers()
    {
        $tester = new class
        {
            use Cacheable;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        ModularousCache::shouldReceive('generateCacheKey')
            ->with('Blog', 'Post', 'formattedItem:99', [])
            ->andReturn('blog:post:formattedItem:99');

        $key = $tester->generateTypeCacheKey('formattedItem', ['id' => 99]);
        $this->assertEquals('blog:post:formattedItem:99', $key);
    }

    /** @test */
    public function it_resolves_form_item_cache_specifiers()
    {
        $tester = new class
        {
            use Cacheable;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        ModularousCache::shouldReceive('generateCacheKey')
            ->with('Blog', 'Post', 'formItem:5', [])
            ->andReturn('blog:post:formItem:5');

        $key = $tester->generateTypeCacheKey('formItem', ['id' => 5]);
        $this->assertEquals('blog:post:formItem:5', $key);
    }

    /** @test */
    public function it_throws_when_slug_missing_for_count_cache_type()
    {
        $tester = new class
        {
            use Cacheable;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug is required');
        $tester->generateTypeCacheKey('count', []);
    }

    /** @test */
    public function it_throws_when_id_missing_for_formatted_item_cache_type()
    {
        $tester = new class
        {
            use Cacheable;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID is required for formatted item');
        $tester->generateTypeCacheKey('formattedItem', []);
    }

    /** @test */
    public function it_throws_when_id_missing_for_form_item_cache_type()
    {
        $tester = new class
        {
            use Cacheable;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID is required for form item');
        $tester->generateTypeCacheKey('formItem', []);
    }

    /** @test */
    public function it_throws_for_invalid_cache_type()
    {
        $tester = new class
        {
            use Cacheable;

            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid cache type');
        $tester->generateTypeCacheKey('invalid', []);
    }

    /** @test */
    public function it_warmups_controller_counts_when_not_user_aware()
    {
        $tester = new class
        {
            use WarmupCache;
        };

        $mockRepo = \Mockery::mock();
        $mockRepo->shouldReceive('shouldUseUserAwareCache')->andReturn(false);

        $mockController = \Mockery::mock();
        $mockController->shouldReceive('getRepository')->andReturn($mockRepo);
        $mockController->shouldReceive('preload')->once();
        $mockController->shouldReceive('getMainCountsList')->andReturn([['slug' => 'all']]);
        $mockController->shouldReceive('handleFilterCount')->with(['slug' => 'all'], true)->once();

        $result = $tester->warmupControllerCounts($mockController);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_skips_warmup_controller_counts_when_user_aware()
    {
        $tester = new class
        {
            use WarmupCache;
        };

        $mockRepo = \Mockery::mock();
        $mockRepo->shouldReceive('shouldUseUserAwareCache')->andReturn(true);

        $mockController = \Mockery::mock();
        $mockController->shouldReceive('getRepository')->andReturn($mockRepo);
        $mockController->shouldNotReceive('preload');

        $result = $tester->warmupControllerCounts($mockController);
        $this->assertNull($result);
    }

    /** @test */
    public function it_warmups_controller_item()
    {
        $tester = new class
        {
            use WarmupCache;
        };

        $mockItem = (object) ['id' => 1];
        $mockController = \Mockery::mock();
        $mockController->shouldReceive('preload')->once();
        $mockController->shouldReceive('getFormattedIndexItem')->with($mockItem)->once();
        $mockController->shouldReceive('getFormItem')->once();

        $tester->warmupControllerItem($mockController, $mockItem, true, true);
    }

    /** @test */
    public function it_warmups_module_route_cache_counts()
    {
        $tester = new class
        {
            use WarmupCache;
        };

        $mockController = \Mockery::mock(BaseController::class);
        $mockRepo = \Mockery::mock(Repository::class);
        $mockRepo->shouldReceive('shouldUseUserAwareCache')->andReturn(false);
        $mockController->shouldReceive('getRepository')->andReturn($mockRepo);
        $mockController->shouldReceive('preload')->once();
        $mockController->shouldReceive('getMainCountsList')->andReturn([]);

        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('getRoute')->with('Post')->andReturn(true);
        $mockModule->shouldReceive('getController')->with('Post')->andReturn($mockController);

        Modularous::shouldReceive('find')->with('Blog')->andReturn($mockModule);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'counts')->andReturn(true);

        $tester->warmupModuleRouteCacheCounts('Blog', 'Post');
    }

    /** @test */
    public function it_warmups_module_route_cache()
    {
        $tester = new class
        {
            use WarmupCache;
        };

        $mockController = \Mockery::mock(BaseController::class);
        $mockRepo = \Mockery::mock(Repository::class);
        $mockRepo->shouldReceive('shouldUseUserAwareCache')->andReturn(false);
        $mockController->shouldReceive('getRepository')->andReturn($mockRepo);
        $mockController->shouldReceive('preload')->once();
        $mockController->shouldReceive('getMainCountsList')->andReturn([]);

        $mockModel = \Mockery::mock();
        $mockModel->shouldReceive('each')->andReturnUsing(function ($callback, $chunkSize) {
            $callback((object) ['id' => 1], 0);
        });

        $mockController->shouldReceive('getModel')->andReturn($mockModel);
        $mockController->shouldReceive('getFormattedIndexItem')->once();
        $mockController->shouldReceive('getFormItem')->once();

        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('getRoute')->with('Post')->andReturn(true);
        $mockModule->shouldReceive('getController')->with('Post')->andReturn($mockController);

        Modularous::shouldReceive('find')->with('Blog')->andReturn($mockModule);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'counts')->andReturn(true);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'formItem')->andReturn(true);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'formattedItem')->andReturn(true);

        $tester->warmupModuleRouteCache('Blog', 'Post', 100);
    }

    /** @test */
    public function it_generates_cache_keys_for_remaining_cache_types(): void
    {
        $tester = new class
        {
            use CacheKeyGenerators {
                resolveCacheSpecifiers as public exposeResolveCacheSpecifiers;
                createCacheKey as public exposeCreateCacheKey;
            }

            public function addUserContext(array $params): array
            {
                return $params + ['_user' => 'u9'];
            }
        };

        ModularousCache::shouldReceive('generateCacheKey')
            ->with('Blog', 'Post', 'count:featured', ['_user' => 'u9'])
            ->andReturn('blog:post:count:featured:u9');
        ModularousCache::shouldReceive('generateCacheKey')
            ->with('Blog', 'Post', 'index', ['_user' => 'u9'])
            ->andReturn('blog:post:index:u9');
        ModularousCache::shouldReceive('generateCacheKey')
            ->with('Blog', 'Post', 'presentationItem:7', ['_user' => 'u9'])
            ->andReturn('blog:post:presentationItem:7:u9');

        $this->assertSame(
            ['counts', 'count:featured', ['_user' => 'u9']],
            $tester->exposeResolveCacheSpecifiers('count', ['slug' => 'featured'])
        );
        $this->assertSame(
            ['index', 'index', ['_user' => 'u9']],
            $tester->exposeResolveCacheSpecifiers('index', [])
        );
        $this->assertSame(
            'blog:post:presentationItem:7:u9',
            $tester->exposeCreateCacheKey('Blog', 'Post', 'presentationItem:7', ['_user' => 'u9'])
        );

        $this->expectException(\InvalidArgumentException::class);
        $tester->exposeResolveCacheSpecifiers('presentationItem', []);
    }

    /** @test */
    public function it_throws_for_record_cache_type(): void
    {
        $tester = new class
        {
            use CacheKeyGenerators {
                resolveCacheSpecifiers as public exposeResolveCacheSpecifiers;
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Record cache type is not ready yet');
        $tester->exposeResolveCacheSpecifiers('record', ['id' => 1]);
    }

    /** @test */
    public function it_adds_user_context_only_when_user_aware_cache_is_enabled(): void
    {
        $tester = new class
        {
            use HasUserAwareCache;
        };

        Auth::shouldReceive('user')->andReturn(null);

        $tester->withUserAwareCache(false);
        $this->assertSame(['foo' => 'bar'], $tester->addUserContext(['foo' => 'bar']));

        $tester->withUserAwareCache(true);
        $this->assertSame(
            ['foo' => 'bar', '_user' => 'guest'],
            $tester->addUserContext(['foo' => 'bar'])
        );
    }

    /** @test */
    public function it_detects_user_aware_cache_from_declared_trait_property(): void
    {
        $tester = new class
        {
            use HasUserAwareCache;

            public bool $hasUserAwareCacheHasUserAwareCache = true;
        };

        $this->assertTrue($tester->shouldUseUserAwareCache());
    }

    /** @test */
    public function it_warmups_controller_items(): void
    {
        $tester = new class
        {
            use WarmupCache;
        };

        $mockModel = \Mockery::mock(Model::class);
        $mockModel->shouldReceive('each')->andReturnUsing(function ($callback) {
            $callback((object) ['id' => 5], 0);
        });

        $mockRepo = \Mockery::mock(Repository::class);
        $mockRepo->shouldReceive('shouldUseUserAwareCache')->andReturn(false);
        $mockRepo->shouldReceive('getModel')->andReturn($mockModel);

        $mockController = \Mockery::mock(BaseController::class);
        $mockController->shouldReceive('getRepository')->andReturn($mockRepo);
        $mockController->shouldReceive('getModuleName')->andReturn('Blog');
        $mockController->shouldReceive('getRouteName')->andReturn('Post');
        $mockController->shouldReceive('preload')->twice();
        $mockController->shouldReceive('getFormattedIndexItem')->once();
        $mockController->shouldReceive('getFormItem')->once();

        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'formItem')->andReturn(true);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'formattedItem')->andReturn(true);

        $tester->warmupControllerItems($mockController, 50);
    }

    /** @test */
    public function it_warmups_cache_from_model_metadata(): void
    {
        $tester = new class
        {
            use WarmupCache;
        };

        $mockRepo = \Mockery::mock(Repository::class);
        $mockRepo->shouldReceive('shouldUseUserAwareCache')->andReturn(false);

        $mockController = \Mockery::mock(BaseController::class);
        $mockController->shouldReceive('getRepository')->andReturn($mockRepo);
        $mockController->shouldReceive('preload')->once();
        $mockController->shouldReceive('getFormattedIndexItem')->once();
        $mockController->shouldReceive('getFormItem')->once();

        $warmModel = new class extends Model
        {
            public function getModuleName()
            {
                return 'Blog';
            }

            public function getRouteName()
            {
                return 'Post';
            }

            public function getModuleRouteName()
            {
                return 'Post';
            }

            public function getKey()
            {
                return 9;
            }
        };

        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('Post')->andReturn(true);
        $mockModule->shouldReceive('getController')->with('Post')->andReturn($mockController);

        Modularous::shouldReceive('find')->with('Blog')->andReturn($mockModule);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'counts')->andReturn(false);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'formItem')->andReturn(true);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'formattedItem')->andReturn(true);
        ModularousCache::shouldReceive('isEnabled')->with('Blog', 'Post', 'presentationItem')->andReturn(false);

        $tester->warmupByModel($warmModel);
    }
}
