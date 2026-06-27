<?php

namespace Modules\Cms\Support;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Modules\Cms\Http\Controllers\Front\CmsPublicFrontController;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;

/**
 * Cross-request cache for {@see CmsFrontRouteRegistrar} gate checks.
 *
 * Universal mode ({@see modularousConfig('cms_routing.universal_cms_public_front')}): only ParentSegment registry
 * state is cached — one {@see CmsPublicFrontController} catch-all, no per-module scan.
 *
 * Legacy mode: additionally resolves per-module / per-model front controllers for signed preview and legacy routing.
 */
final class CmsFrontRouteRegistrationCache
{
    /** @var array<string, mixed>|null */
    private static ?array $runtimeSnapshot = null;

    private static ?string $runtimeCacheKey = null;

    public static function forgetRuntime(): void
    {
        self::$runtimeSnapshot = null;
        self::$runtimeCacheKey = null;
    }

    /**
     * @deprecated Prefer {@see CmsPublicUrlRegistryCoordinator::invalidateParentSegmentRegistry()}.
     */
    public static function forget(): void
    {
        self::forgetRuntime();
    }

    public static function warm(): void
    {
        self::forgetRuntime();
        self::snapshot();
    }

    /**
     * Drop in-process and persisted front-route registration snapshots.
     */
    public static function clearPersistent(): void
    {
        self::forgetRuntime();

        if (! self::persistentCacheEnabled()) {
            return;
        }

        self::store()->forget(self::resolveCacheKey());
    }

    /**
     * Whether a persisted front-route registration snapshot exists for the current revision + fingerprint.
     */
    public static function hasPersistentSnapshot(): bool
    {
        if (! self::persistentCacheEnabled()) {
            return false;
        }

        $cached = self::store()->get(self::resolveCacheKey());

        return is_array($cached)
            && array_key_exists('public_front_catch_all_controller', $cached)
            && array_key_exists('has_enabled_parent_segments', $cached);
    }

    public static function usesUniversalPublicFront(): bool
    {
        return (bool) modularousConfig('cms_routing.universal_cms_public_front', true);
    }

    /**
     * @return class-string|null
     */
    public static function publicFrontCatchAllControllerOrNull(): ?string
    {
        $controller = self::snapshot()['public_front_catch_all_controller'];

        return is_string($controller) && $controller !== '' ? $controller : null;
    }

    /**
     * @return class-string|null
     */
    public static function globalControllerOrNull(): ?string
    {
        return self::publicFrontCatchAllControllerOrNull();
    }

    public static function parentSegmentTableReady(): bool
    {
        return (bool) self::snapshot()['parent_segment_table_ready'];
    }

    public static function hasEnabledParentSegments(): bool
    {
        return (bool) self::snapshot()['has_enabled_parent_segments'];
    }

    /**
     * @return list<string>
     */
    public static function enabledTargetModelClasses(): array
    {
        /** @var list<string> */
        return self::snapshot()['enabled_target_model_classes'];
    }

    /**
     * Legacy / signed-preview: first front controller for a module submodule.
     *
     * @return class-string|null
     */
    public static function frontControllerForModule(Module $module): ?string
    {
        if (self::usesUniversalPublicFront()) {
            return self::legacyFrontControllerForModule($module);
        }

        $key = $module->getLowerName();
        $controllers = self::snapshot()['module_controllers'];

        if (array_key_exists($key, $controllers)) {
            $controller = $controllers[$key];

            return is_string($controller) && $controller !== '' ? $controller : null;
        }

        return self::resolveFrontControllerForModuleLive($module);
    }

    /**
     * @param class-string $modelClass
     * @return class-string|null
     */
    public static function frontControllerForModelClass(string $modelClass): ?string
    {
        $controllers = self::snapshot()['model_controllers'];

        if (array_key_exists($modelClass, $controllers)) {
            $controller = $controllers[$modelClass];

            return is_string($controller) && $controller !== '' ? $controller : null;
        }

        return self::resolveFrontControllerForModelClassLive($modelClass);
    }

    /**
     * @return array<string, class-string>
     */
    public static function legacyQualifiedModules(): array
    {
        /** @var array<string, class-string> */
        return self::snapshot()['legacy_qualified_modules'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function snapshot(): array
    {
        $cacheKey = self::resolveCacheKey();

        if (self::$runtimeSnapshot !== null && self::$runtimeCacheKey === $cacheKey) {
            return self::$runtimeSnapshot;
        }

        if (self::persistentCacheEnabled()) {
            $cached = self::store()->get($cacheKey);
            if (is_array($cached)
                && array_key_exists('public_front_catch_all_controller', $cached)
                && array_key_exists('has_enabled_parent_segments', $cached)) {
                self::$runtimeCacheKey = $cacheKey;

                return self::$runtimeSnapshot = $cached;
            }
        }

        $built = self::buildSnapshot();

        if (self::persistentCacheEnabled() && self::registryReadable()) {
            self::store()->forever($cacheKey, $built);
        }

        self::$runtimeCacheKey = $cacheKey;

        return self::$runtimeSnapshot = $built;
    }

    private static function registryReadable(): bool
    {
        return database_exists()
            && Schema::hasTable((new ParentSegment)->getTable());
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildSnapshot(): array
    {
        $tableReady = self::registryReadable();

        $hasEnabled = $tableReady
            && ParentSegment::query()->where('enabled', true)->exists();

        /** @var list<string> $targetClasses */
        $targetClasses = [];
        if ($hasEnabled) {
            $targetClasses = ParentSegment::query()
                ->where('enabled', true)
                ->select('target_model_class')
                ->groupBy('target_model_class')
                ->orderBy('target_model_class')
                ->pluck('target_model_class')
                ->filter(static fn ($class) => is_string($class) && $class !== '')
                ->values()
                ->all();
        }

        /** @var array<string, class-string|null> $moduleControllers */
        $moduleControllers = [];
        /** @var array<string, class-string> $legacyQualifiedModules */
        $legacyQualifiedModules = [];
        /** @var array<string, class-string|null> $modelControllers */
        $modelControllers = [];
        $catchAllController = null;

        if ($hasEnabled && (bool) modularousConfig('cms_routing.public_pages_enabled', true)) {
            if (self::usesUniversalPublicFront() && class_exists(CmsPublicFrontController::class)) {
                $catchAllController = CmsPublicFrontController::class;
            } else {
                $catchAllController = self::resolveLegacyCatchAllController($targetClasses, $modelControllers);

                foreach (Modularous::allEnabled() as $module) {
                    if (! $module instanceof Module) {
                        continue;
                    }

                    $controller = self::resolveFrontControllerForModuleLive($module, $hasEnabled, $modelControllers);
                    $moduleControllers[$module->getLowerName()] = $controller;

                    if ($controller !== null) {
                        $legacyQualifiedModules[$module->getLowerName()] = $controller;
                    }
                }
            }
        }

        return [
            'parent_segment_table_ready' => $tableReady,
            'has_enabled_parent_segments' => $hasEnabled,
            'enabled_target_model_classes' => $targetClasses,
            'public_front_catch_all_controller' => $catchAllController,
            'legacy_qualified_modules' => $legacyQualifiedModules,
            'module_controllers' => $moduleControllers,
            'model_controllers' => $modelControllers,
        ];
    }

    /**
     * @param list<string> $targetClasses
     * @param array<string, class-string|null> $modelControllers
     * @return class-string|null
     */
    private static function resolveLegacyCatchAllController(array $targetClasses, array &$modelControllers): ?string
    {
        foreach ($targetClasses as $modelClass) {
            if (! class_exists($modelClass) || ! classHasTrait($modelClass, HasParentSegment::class)) {
                continue;
            }

            $resolved = self::resolveFrontControllerForModelClassLive($modelClass, $modelControllers);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    /**
     * Per-module controller for signed preview / legacy per-route resolution (not the universal catch-all).
     *
     * @return class-string|null
     */
    private static function legacyFrontControllerForModule(Module $module): ?string
    {
        foreach ($module->getRouteNames() as $routeName) {
            if (! $module->isEnabledRoute($routeName)) {
                continue;
            }

            $modelClass = self::resolveModelClassForRoute($module, $routeName);
            if ($modelClass === null || ! classHasTrait($modelClass, HasParentSegment::class)) {
                continue;
            }

            $controller = self::frontControllerForModelClass($modelClass);
            if ($controller !== null) {
                return $controller;
            }
        }

        return null;
    }

    /**
     * @param array<string, class-string|null> $modelControllers
     * @return class-string|null
     */
    private static function resolveFrontControllerForModuleLive(
        Module $module,
        ?bool $hasEnabledParentSegments = null,
        array &$modelControllers = [],
    ): ?string {
        foreach ($module->getRouteNames() as $routeName) {
            if (! $module->isEnabledRoute($routeName)) {
                continue;
            }

            $modelClass = self::resolveModelClassForRoute($module, $routeName);
            if ($modelClass === null || ! class_exists($modelClass) || ! classHasTrait($modelClass, HasParentSegment::class)) {
                continue;
            }

            $controllerFqcn = $module->getTargetClassNamespace(
                'front-controller',
                Str::studly($routeName) . 'Controller'
            );

            if (! class_exists($controllerFqcn)) {
                continue;
            }

            if (! is_subclass_of($controllerFqcn, CmsController::class, true)) {
                continue;
            }

            return $controllerFqcn;
        }

        return null;
    }

    /**
     * @param array<string, class-string|null> $modelControllers
     * @return class-string|null
     */
    private static function resolveFrontControllerForModelClassLive(string $modelClass, array &$modelControllers = []): ?string
    {
        if (array_key_exists($modelClass, $modelControllers)) {
            return $modelControllers[$modelClass];
        }

        $configured = modularousConfig('cms_routing.public_front_handlers', []);
        if (is_array($configured) && isset($configured[$modelClass])) {
            $override = $configured[$modelClass];
            if (is_string($override) && $override !== '' && class_exists($override)
                && is_subclass_of($override, CmsController::class, true)) {
                return $modelControllers[$modelClass] = $override;
            }
        }

        foreach (Modularous::allEnabled() as $module) {
            if (! $module instanceof Module) {
                continue;
            }

            foreach ($module->getRouteNames() as $routeName) {
                if (! $module->isEnabledRoute($routeName)) {
                    continue;
                }

                $resolvedModelClass = self::resolveModelClassForRoute($module, $routeName);
                if ($resolvedModelClass === null || $resolvedModelClass !== $modelClass) {
                    continue;
                }

                if (! classHasTrait($resolvedModelClass, HasParentSegment::class)) {
                    continue;
                }

                $fqcn = $module->getTargetClassNamespace(
                    'front-controller',
                    Str::studly($routeName) . 'Controller'
                );

                if (! class_exists($fqcn) || ! is_subclass_of($fqcn, CmsController::class, true)) {
                    continue;
                }

                return $modelControllers[$modelClass] = $fqcn;
            }
        }

        return $modelControllers[$modelClass] = null;
    }

    private static function resolveModelClassForRoute(Module $module, string $routeName): ?string
    {
        if (! $module->isEnabledRoute($routeName)) {
            return null;
        }

        try {
            $modelClass = $module->getModel($routeName, false);

            return is_string($modelClass) && $modelClass !== '' ? $modelClass : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function cacheFingerprint(): string
    {
        if (self::usesUniversalPublicFront()) {
            return hash('xxh128', implode('|', [
                'universal',
                (string) modularousConfig('cms_routing.public_pages_enabled', true),
            ]));
        }

        $parts = [];
        foreach (Modularous::allEnabled() as $module) {
            if (! $module instanceof Module) {
                continue;
            }

            $parts[] = $module->getLowerName();
            foreach ($module->getRouteNames() as $routeName) {
                $parts[] = $routeName . ':' . ($module->isEnabledRoute($routeName) ? '1' : '0');
            }
        }
        sort($parts);

        $handlers = modularousConfig('cms_routing.public_front_handlers', []);
        $handlerFingerprint = is_array($handlers)
            ? hash('xxh128', serialize(self::normalizeConfigArray($handlers)))
            : '';

        return hash('xxh128', implode("\0", $parts) . "\0legacy\0" . $handlerFingerprint);
    }

    /**
     * @param array<mixed> $value
     * @return array<mixed>
     */
    private static function normalizeConfigArray(array $value): array
    {
        ksort($value);

        foreach ($value as $key => $nested) {
            if (is_array($nested)) {
                $value[$key] = self::normalizeConfigArray($nested);
            }
        }

        return $value;
    }

    private static function resolveCacheKey(): string
    {
        $base = (string) modularousConfig(
            'cms_routing.front_route_registration_cache_key',
            'modularous_cms.front_route_registration_v1'
        );

        return implode('.', [
            $base,
            CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision(),
            self::cacheFingerprint(),
        ]);
    }

    private static function persistentCacheEnabled(): bool
    {
        return (bool) modularousConfig('cms_routing.front_route_registration_cache_enabled', true);
    }

    private static function store(): CacheRepository
    {
        return Cache::store(self::storeName());
    }

    private static function storeName(): string
    {
        return (string) modularousConfig(
            'cms_routing.public_url_registry_cache_store',
            'file'
        );
    }
}
