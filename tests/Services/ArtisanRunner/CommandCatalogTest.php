<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\ArtisanRunner;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Unusualify\Modularous\Services\ArtisanRunner\CommandCatalog;
use Unusualify\Modularous\Services\ArtisanRunner\Support\AllowlistMatcher;
use Unusualify\Modularous\Tests\TestCase;

class CommandCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerDemoCommand();

        config([
            'modularous.artisan_runner.enabled' => true,
            'modularous.artisan_runner.allowlist' => ['artisan-runner:demo', 'modularous:cache:*'],
            'modularous.artisan_runner.allowed_roles' => ['superadmin', 'admin'],
        ]);
    }

    /** @test */
    public function superadmin_sees_all_non_hidden_commands(): void
    {
        $catalog = new CommandCatalog(new AllowlistMatcher);
        $user = $this->fakeUser(isSuperadmin: true);

        $names = array_column($catalog->forUser($user), 'name');

        $this->assertNotEmpty($names);
        $this->assertContains('artisan-runner:demo', $names);
        $this->assertTrue($catalog->isAllowed($user, 'artisan-runner:demo'));

        $nonSuperNames = array_column($catalog->forUser($this->fakeUser(false)), 'name');
        $this->assertGreaterThan(count($nonSuperNames), count($names));
    }

    /** @test */
    public function non_superadmin_is_limited_to_allowlist(): void
    {
        $catalog = new CommandCatalog(new AllowlistMatcher);
        $user = $this->fakeUser(isSuperadmin: false);

        $names = array_column($catalog->forUser($user), 'name');

        $this->assertContains('artisan-runner:demo', $names);
        $this->assertTrue($catalog->isAllowed($user, 'artisan-runner:demo'));
        $this->assertFalse($catalog->isAllowed($user, 'artisan-runner:other'));
    }

    /** @test */
    public function empty_allowlist_yields_empty_catalog_for_non_superadmin(): void
    {
        config(['modularous.artisan_runner.allowlist' => []]);

        $catalog = new CommandCatalog(new AllowlistMatcher);
        $user = $this->fakeUser(isSuperadmin: false);

        $this->assertSame([], $catalog->forUser($user));
        $this->assertFalse($catalog->isAllowed($user, 'artisan-runner:demo'));
    }

    private function registerDemoCommand(): void
    {
        $command = new class extends Command
        {
            protected $signature = 'artisan-runner:demo {path?} {--force}';

            protected $description = 'ArtisanRunner demo command';

            public function handle(): int
            {
                $this->line('demo-ok');

                return self::SUCCESS;
            }
        };

        $this->app->make(ConsoleKernel::class)->registerCommand($command);
    }

    private function fakeUser(bool $isSuperadmin): Authenticatable
    {
        return new class($isSuperadmin) extends Authenticatable
        {
            public bool $is_superadmin;

            /** @var list<string> */
            public array $roles = [];

            public function __construct(bool $isSuperadmin)
            {
                $this->is_superadmin = $isSuperadmin;
                $this->roles = $isSuperadmin ? ['superadmin'] : ['admin'];
            }

            public function hasRole($roles): bool
            {
                $roles = (array) $roles;

                return count(array_intersect($roles, $this->roles)) > 0;
            }

            public function hasAnyRole(...$roles): bool
            {
                $flat = [];
                foreach ($roles as $role) {
                    foreach ((array) $role as $item) {
                        $flat[] = $item;
                    }
                }

                return count(array_intersect($flat, $this->roles)) > 0;
            }
        };
    }
}
