<?php

namespace Unusualify\Modularous\Console;

use Illuminate\Console\Command;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;

class DevAuthStatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'modularous:dev-auth:status
        {--json : Print the status as JSON}';

    /**
     * @var string
     */
    protected $description = 'Show the local dev auto-login status (flag, environment, resolved user, caches, Vite hot file)';

    public function handle(DevAutoLoginService $devAutoLogin): int
    {
        $status = $devAutoLogin->status();

        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $status['enabled'] && $status['user'] ? self::SUCCESS : self::FAILURE;
        }

        $this->table(['Check', 'Value'], [
            ['MODULAROUS_DEV_AUTO_LOGIN', $this->yesNo($status['flag'])],
            ['Environment', $status['environment'] . ($status['environment_allowed'] ? ' (allowed)' : ' (not allowed)')],
            ['Configured email', $status['configured_email'] ?? '—'],
            ['Resolved user', $status['user'] ? "#{$status['user']['id']} {$status['user']['email']}" : '—'],
            ['Config cached', $this->yesNo($status['config_cached'])],
            ['Routes cached', $this->yesNo($status['routes_cached'])],
            ['Vite hot (public/modularous.hot)', $this->yesNo($status['vite_hot'])],
        ]);

        foreach ($this->hints($status) as $hint) {
            $this->warn($hint);
        }

        if ($status['enabled'] && $status['user']) {
            $this->info("Dev auto-login active: panel requests run as {$status['user']['email']}.");

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * @param array<string, mixed> $status
     * @return list<string>
     */
    protected function hints(array $status): array
    {
        $hints = [];

        if (! $status['flag']) {
            $hints[] = 'Set MODULAROUS_DEV_AUTO_LOGIN=true in .env, then run `php artisan config:clear`.';
        } elseif (! $status['environment_allowed']) {
            $hints[] = 'APP_ENV is not in modularous.dev_auth.allowed_environments; dev auto-login stays off.';
        } elseif (! $status['user']) {
            $hints[] = 'No user resolved: fix MODULAROUS_DEV_AUTO_LOGIN_EMAIL or create a superadmin (`php artisan modularous:create:superadmin`).';
        } elseif ($status['configured_email'] && $status['user']['email'] !== $status['configured_email']) {
            $hints[] = "Configured email {$status['configured_email']} matches no user; falling back to the first superadmin.";
        }

        if ($status['config_cached']) {
            $hints[] = 'Config is cached: run `php artisan config:clear` after changing .env.';
        }

        if ($status['routes_cached']) {
            $hints[] = 'Routes are cached: run `php artisan route:clear` after route changes.';
        }

        if (! $status['vite_hot']) {
            $hints[] = 'Vite dev server is not running: the panel uses built assets, so Vue changes need a rebuild.';
        }

        return $hints;
    }

    protected function yesNo(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
