<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\SystemSetting\Services\SystemSettingsService;
use Modules\SystemSetting\Support\AnalyticsScripts;
use Modules\SystemSetting\Support\ApplySmtpMailConfig;
use Modules\SystemSetting\Support\MigrateRobotsTxtFromSiteSetting;
use Modules\SystemSetting\Support\SystemSettingsInputMerger;
use Unusualify\Modularous\Facades\Modularous;

class SystemSettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SystemSettingsService::class);
        $this->app->alias(SystemSettingsService::class, 'system.settings');

        $this->app->singleton(AnalyticsScripts::class);
        $this->app->singleton(ApplySmtpMailConfig::class);
        $this->app->singleton(SystemSettingsInputMerger::class);
        $this->app->singleton(MigrateRobotsTxtFromSiteSetting::class);
    }

    public function boot(): void
    {
        $this->registerInputAliases();

        $isFront = ! $this->app->runningInConsole() && Modularous::isFrontUrl();

        if (! $isFront) {
            $this->mergeRouteInputs();
        }

        try {
            $this->app->make(ApplySmtpMailConfig::class)->apply();
        } catch (\Throwable) {
            // Mail override is best-effort when settings / DB are unavailable.
        }

        if ($this->app->runningInConsole() || $isFront) {
            return;
        }

        try {
            $this->app->make(MigrateRobotsTxtFromSiteSetting::class)->migrateIfNeeded();
        } catch (\Throwable) {
            // Migration is best-effort when CMS tables are absent.
        }
    }

    protected function mergeRouteInputs(): void
    {
        $module = Modularous::find('SystemSetting');

        if ($module === null) {
            return;
        }

        $defaultInputs = (array) $module->getConfig('routes.general.inputs', []);
        $merged = $this->app->make(SystemSettingsInputMerger::class)->merge($defaultInputs);

        $module->setConfig($merged, 'routes.general.inputs');
    }

    protected function registerInputAliases(): void
    {
        $aliases = (array) modularousConfig('system_settings.input_aliases', []);

        foreach ($aliases as $alias => $definition) {
            if (! is_string($alias) || ! is_array($definition)) {
                continue;
            }

            config(["modularous.input_types.{$alias}" => $definition]);
        }
    }
}
