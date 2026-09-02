<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Providers;

use Unusualify\Modularous\Contracts\ModulePresentationAssetLoaderInterface;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Providers\ModuleServiceProvider;
use Unusualify\Modularous\Services\ModulePresentationAssetLoader;
use Unusualify\Modularous\Tests\TestModulesCase;

class ModuleServiceProviderTest extends TestModulesCase
{
    public function test_register_binds_module_presentation_asset_loader(): void
    {
        $provider = new ModuleServiceProvider($this->app);

        $provider->register();

        $this->assertInstanceOf(
            ModulePresentationAssetLoader::class,
            $this->app->make(ModulePresentationAssetLoaderInterface::class),
        );
    }

    public function test_boot_modules_loads_enabled_module_assets(): void
    {
        $provider = new ModuleServiceProvider($this->app);
        $provider->bootModules();

        $module = Modularous::find('TestModule');
        $this->assertNotNull($module);

        $migrationPath = $module->getDirectoryPath('Database/Migrations');
        $this->assertContains($migrationPath, $this->app['migrator']->paths());
        $this->assertTrue($module->isEnabled());
    }

    public function test_boot_delegates_to_boot_modules(): void
    {
        $provider = $this->getMockBuilder(ModuleServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['bootModules'])
            ->getMock();

        $provider->expects($this->once())->method('bootModules');
        $provider->boot();
    }
}
