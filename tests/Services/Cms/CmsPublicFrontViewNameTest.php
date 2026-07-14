<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Support\Facades\View;
use Modules\BusinessPackage\Entities\PackageCountryCmrStub;
use Modules\Cms\Support\CmsPublicFrontViewName;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicFrontViewNameTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicFrontViewName::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }
    }

    /** @test */
    public function it_resolves_module_route_view_for_cmr_models_without_module_route_metadata(): void
    {
        $item = new PackageCountryCmrStub;
        $item->setRawAttributes(['id' => 7]);
        $item->exists = true;

        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('getName')->andReturn('BusinessPackage');
        $mockModule->shouldReceive('getRouteNames')->andReturn(['PackageCountry']);
        $mockModule->shouldReceive('isEnabledRoute')->with('PackageCountry')->andReturn(true);
        $mockModule->shouldReceive('getModel')->with('PackageCountry', true)->andReturn(new PackageCountryCmrStub);

        Modularous::shouldReceive('find')->with('BusinessPackage')->andReturn($mockModule);

        View::shouldReceive('exists')
            ->with('business_package::package_country.custom')
            ->andReturn(true);

        View::shouldReceive('exists')
            ->with('business_package::package_country.page_layout.body')
            ->andReturn(false);

        View::shouldReceive('exists')
            ->with('business_package::package_country.page_layout.head')
            ->andReturn(false);

        View::shouldReceive('exists')
            ->with('business_package::package_country.page_layout.footer')
            ->andReturn(false);

        $viewName = CmsPublicFrontViewName::forModel($item);

        $this->assertSame('business_package::package_country.custom', $viewName);
    }

    /** @test */
    public function presentation_item_cache_context_matches_for_model_resolution(): void
    {
        $item = new PackageCountryCmrStub;
        $item->setRawAttributes(['id' => 7]);
        $item->exists = true;

        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('getName')->andReturn('BusinessPackage');
        $mockModule->shouldReceive('getRouteNames')->andReturn(['PackageCountry']);
        $mockModule->shouldReceive('isEnabledRoute')->with('PackageCountry')->andReturn(true);
        $mockModule->shouldReceive('getModel')->with('PackageCountry', true)->andReturn(new PackageCountryCmrStub);

        Modularous::shouldReceive('find')->with('BusinessPackage')->andReturn($mockModule);

        View::shouldReceive('exists')->andReturn(true);

        $cacheContext = CmsPublicFrontViewName::presentationItemCacheContextForModel($item);
        $viewName = CmsPublicFrontViewName::forModel($item);

        $this->assertSame([
            'moduleName' => 'BusinessPackage',
            'moduleRouteName' => 'PackageCountry',
        ], $cacheContext);
        $this->assertStringContainsString('package_country', $viewName);
    }
}
