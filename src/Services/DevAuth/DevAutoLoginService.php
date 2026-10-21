<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\DevAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Modules\SystemUser\Repositories\UserRepository;
use RuntimeException;

/**
 * Resolves the superadmin used by the local dev auto-login and guards the
 * feature against running outside the allowed environments.
 */
class DevAutoLoginService
{
    public const SUPERADMIN_ROLE = 'superadmin';

    protected ?int $resolvedUserId = null;

    public function __construct(protected Application $app) {}

    /**
     * Whether the flag is on in config, regardless of the environment.
     */
    public function isRequested(): bool
    {
        return (bool) modularousConfig('dev_auth.enabled', false);
    }

    /**
     * Whether the current environment may use the dev auto-login.
     */
    public function isAllowedEnvironment(): bool
    {
        $allowed = (array) modularousConfig('dev_auth.allowed_environments', ['local']);

        return $this->app->environment($allowed);
    }

    /**
     * Whether the dev auto-login should run for this application.
     */
    public function isEnabled(): bool
    {
        return $this->isRequested() && $this->isAllowedEnvironment();
    }

    /**
     * @throws RuntimeException When the flag is on in a forbidden environment.
     */
    public function assertSafeEnvironment(): void
    {
        if ($this->isRequested() && ! $this->isAllowedEnvironment()) {
            throw new RuntimeException(sprintf(
                'MODULAROUS_DEV_AUTO_LOGIN is enabled in the "%s" environment. It is only allowed in: %s.',
                $this->app->environment(),
                implode(', ', (array) modularousConfig('dev_auth.allowed_environments', ['local']))
            ));
        }
    }

    /**
     * Resolve the id of the user to authenticate as: the configured email first,
     * then the first superadmin when the email is empty or matches no user.
     */
    public function resolveUserId(): ?int
    {
        if ($this->resolvedUserId !== null) {
            return $this->resolvedUserId;
        }

        $model = $this->app->make(UserRepository::class)->getModel();
        $email = modularousConfig('dev_auth.email');
        $user = null;

        if (filled($email)) {
            $user = $model->newQuery()->where('email', $email)->first();

            if (! $user) {
                Log::warning("[modularous] Dev auto-login email \"{$email}\" matches no user; falling back to the first superadmin.");
            }
        }

        $user ??= $model->newQuery()
            ->whereHas('roles', fn ($roles) => $roles->where('name', self::SUPERADMIN_ROLE))
            ->first();

        if (! $user) {
            Log::warning('[modularous] Dev auto-login found no superadmin user; requests stay unauthenticated.');
        }

        return $this->resolvedUserId = $user ? (int) $user->getKey() : null;
    }

    /**
     * Snapshot of the dev auto-login setup, used by `modularous:dev-auth:status`.
     *
     * @return array{enabled: bool, flag: bool, environment: string, environment_allowed: bool, configured_email: ?string, user: ?array{id: int, email: string}, config_cached: bool, routes_cached: bool, vite_hot: bool}
     */
    public function status(): array
    {
        $userId = $this->isRequested() ? $this->resolveUserId() : null;
        $user = $userId !== null
            ? $this->app->make(UserRepository::class)->getModel()->newQuery()->find($userId)
            : null;

        return [
            'enabled' => $this->isEnabled(),
            'flag' => $this->isRequested(),
            'environment' => (string) $this->app->environment(),
            'environment_allowed' => $this->isAllowedEnvironment(),
            'configured_email' => modularousConfig('dev_auth.email') ?: null,
            'user' => $user ? ['id' => (int) $user->getKey(), 'email' => (string) $user->email] : null,
            'config_cached' => $this->app->configurationIsCached(),
            'routes_cached' => $this->app->routesAreCached(),
            'vite_hot' => is_file(public_path('modularous.hot')),
        ];
    }
}
