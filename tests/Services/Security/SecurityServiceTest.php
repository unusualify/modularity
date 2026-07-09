<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Security;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Unusualify\Modularous\Services\Security\SecurityService;
use Unusualify\Modularous\Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_user_requires_mfa_for_configured_roles(): void
    {
        config()->set('modularous.security.mfa.enabled', true);
        config()->set('modularous.security.mfa.provider', 'google_totp');
        config()->set('modularous.security.mfa.required_roles', ['admin']);

        $user = \Mockery::mock(Authenticatable::class);
        $user->shouldReceive('hasRole')->with('admin')->andReturn(true);
        $user->google_2fa_enabled = false;
        $user->google_2fa_secret = null;

        $service = new SecurityService;

        $this->assertTrue($service->userRequiresMfa($user));
        $this->assertFalse($service->userHasEnabledMfa($user));
    }

    public function test_email_otp_provider_does_not_require_google_2fa_columns(): void
    {
        config()->set('modularous.security.mfa.enabled', true);
        config()->set('modularous.security.mfa.provider', 'email_otp');

        $user = \Mockery::mock(Authenticatable::class);

        $service = new SecurityService;

        $this->assertTrue($service->userHasEnabledMfa($user));
    }

    public function test_field_permission_checks_are_applied(): void
    {
        config()->set('modularous.security.critical_field_permissions.canonical_url', 'cms-seo-override_edit');

        $user = \Mockery::mock(Authenticatable::class);
        $user->shouldReceive('can')->with('cms-seo-override_edit')->andReturn(true);

        $service = new SecurityService;

        $this->assertTrue($service->canWriteField($user, 'canonical_url'));
        $this->assertTrue($service->canWriteField($user, 'non_critical_field'));
    }

    public function test_user_capabilities_can_be_read_from_user_attribute(): void
    {
        $user = $this->makeCapabilityUser(['payments.refund', '']);

        $service = new SecurityService;

        $this->assertSame(['payments.refund'], $service->userCapabilities($user));
    }

    public function test_user_has_capability_delegates_to_user_method_when_available(): void
    {
        $user = \Mockery::mock(Authenticatable::class);
        $user->shouldReceive('hasCapability')->with('payments.refund')->andReturn(true);

        $service = new SecurityService;

        $this->assertTrue($service->userHasCapability($user, 'payments.refund'));
    }

    public function test_step_up_capabilities_for_route_reads_cached_route_map(): void
    {
        Cache::put('modularous.security.capabilities.step_up.routes', [
            'admin.payments.refund' => ['payments.refund'],
        ], 3600);

        $service = new SecurityService;

        $this->assertSame(['payments.refund'], $service->stepUpCapabilitiesForRoute('admin.payments.refund'));
        $this->assertTrue($service->routeMatchesStepUpCapability('payments.refund', 'admin.payments.refund'));
        $this->assertFalse($service->routeMatchesStepUpCapability('other.capability', 'admin.payments.refund'));
    }

    public function test_matched_user_step_up_capability_respects_hint_and_user_capabilities(): void
    {
        Cache::put('modularous.security.capabilities.step_up.routes', [
            'admin.payments.refund' => ['payments.refund', 'payments.void'],
        ], 3600);

        $user = $this->makeCapabilityUser(['payments.void']);

        $service = new SecurityService;

        $this->assertSame(
            'payments.void',
            $service->matchedUserStepUpCapability($user, 'admin.payments.refund', 'payments.void'),
        );
        $this->assertSame(
            'payments.void',
            $service->matchedUserStepUpCapability($user, 'admin.payments.refund', 'payments.refund'),
        );
    }

    public function test_can_promote_allows_configured_roles_and_emails(): void
    {
        config()->set('modularous.cms_promotion.approval.roles', ['publisher']);
        config()->set('modularous.cms_promotion.approval.emails', ['chief@example.com']);

        $roleUser = \Mockery::mock(Authenticatable::class);
        $roleUser->shouldReceive('hasRole')->with('publisher')->andReturn(true);
        $roleUser->email = 'editor@example.com';

        $emailUser = \Mockery::mock(Authenticatable::class);
        $emailUser->shouldReceive('hasRole')->andReturn(false);
        $emailUser->email = 'chief@example.com';

        $service = new SecurityService;

        $this->assertTrue($service->canPromote($roleUser));
        $this->assertTrue($service->canPromote($emailUser));
        $this->assertFalse($service->canPromote(null));
    }

    public function test_flush_persistent_cache_rebuilds_cached_maps(): void
    {
        Cache::put('modularous.security.capabilities.map', ['stale' => ['old']], 3600);

        $service = new SecurityService;
        $service->flushPersistentCache();

        $this->assertIsArray($service->getCapabilities());
        $this->assertIsArray($service->requiredStepUpCapabilities());
        $this->assertArrayNotHasKey('stale', $service->getCapabilities());
    }

    /**
     * @param list<string> $capabilities
     */
    private function makeCapabilityUser(array $capabilities): Authenticatable
    {
        return new class($capabilities) implements Authenticatable
        {
            public function __construct(private array $capabilities) {}

            public function getAuthIdentifierName() { return 'id'; }

            public function getAuthIdentifier() { return 1; }

            public function getAuthPassword() { return ''; }

            public function getRememberToken() { return null; }

            public function setRememberToken($value) {}

            public function getRememberTokenName() { return 'remember_token'; }

            public function getAuthPasswordName() { return 'password'; }

            public function getAttribute($key)
            {
                return $key === 'capabilities' ? $this->capabilities : null;
            }
        };
    }
}
