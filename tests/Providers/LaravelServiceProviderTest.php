<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use ReflectionClass;
use ReflectionMethod;
use Unusualify\Modularous\LaravelServiceProvider;
use Unusualify\Modularous\Tests\TestCase;

class LaravelServiceProviderTest extends TestCase
{
    public function test_register_is_no_op(): void
    {
        $provider = new LaravelServiceProvider($this->app);

        $provider->register();

        $this->assertTrue(true);
    }

    public function test_boot_registers_all_publish_groups(): void
    {
        $provider = new LaravelServiceProvider($this->app);
        $provider->boot();

        $publishes = $this->publishedPaths(LaravelServiceProvider::class);

        $this->assertNotEmpty($publishes);
        $this->assertTrue(
            (bool) array_filter(
                array_keys($publishes),
                static fn (string $source): bool => str_ends_with($source, 'config/publishes/modules.php')
            )
        );
        $this->assertTrue(
            (bool) array_filter(
                array_keys($publishes),
                static fn (string $source): bool => str_ends_with($source, '/lang')
            )
        );
        $this->assertTrue(
            (bool) array_filter(
                array_keys($publishes),
                static fn (string $source): bool => str_ends_with($source, '/operations')
            )
        );
    }

    public function test_publish_migrations_registers_modularous_migrations(): void
    {
        $provider = new LaravelServiceProvider($this->app);

        $method = new ReflectionMethod($provider, 'publishMigrations');
        $method->setAccessible(true);
        $method->invoke($provider);

        $publishes = $this->publishedPaths(LaravelServiceProvider::class, 'modularous-migrations');
        $expectedSource = realpath(__DIR__ . '/../../database/migrations/default')
            ?: __DIR__ . '/../../database/migrations/default';

        $matchedSource = null;
        foreach (array_keys($publishes) as $source) {
            if ((realpath($source) ?: $source) === $expectedSource) {
                $matchedSource = $source;
                break;
            }
        }

        $this->assertNotNull($matchedSource);
        $this->assertSame(
            $this->app->databasePath('migrations'),
            $publishes[$matchedSource]
        );
    }

    public function test_publish_ignored_lang_uses_package_lang_when_app_lang_missing(): void
    {
        $provider = new LaravelServiceProvider($this->app);

        $method = new ReflectionMethod($provider, 'publishIgnoredLang');
        $method->setAccessible(true);
        $method->invoke($provider);

        $publishes = $this->publishedPaths(LaravelServiceProvider::class, 'ignored-lang');

        $expectedSource = is_dir(base_path('lang'))
            ? base_path('lang')
            : __DIR__ . '/../../lang';

        $this->assertArrayHasKey($expectedSource, $publishes);
        $this->assertSame(base_path('modularous/lang'), $publishes[$expectedSource]);
    }

    /**
     * @return array<string, string>
     */
    private function publishedPaths(string $providerClass, ?string $group = null): array
    {
        $property = (new ReflectionClass(BaseServiceProvider::class))->getProperty('publishes');
        $property->setAccessible(true);

        /** @var array<string, array<string, string>> $all */
        $all = $property->getValue();

        $paths = $all[$providerClass] ?? [];

        if ($group === null) {
            return $paths;
        }

        $groupsProperty = (new ReflectionClass(BaseServiceProvider::class))->getProperty('publishGroups');
        $groupsProperty->setAccessible(true);

        /** @var array<string, array<string, string>> $groups */
        $groups = $groupsProperty->getValue();

        return $groups[$group] ?? [];
    }
}
