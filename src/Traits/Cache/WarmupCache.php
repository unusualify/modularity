<?php

namespace Unusualify\Modularous\Traits\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use Modules\Cms\Support\CmsPublicFrontViewName;
use Modules\Cms\Support\CmsPublicPresentationInnerData;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\CmsPublicPresentationWarmupContext;
use Modules\ErrorPage\Entities\ErrorPage;
use Modules\ErrorPage\Support\ErrorPagePresentationCache;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Support\ModularousCacheLogger;

trait WarmupCache
{
    /**
     * Warmup the counts cache for a controller.
     *
     * @param BaseController $controller
     * @return bool
     */
    public function warmupControllerCounts($controller)
    {
        $useUserAwareCache = $controller->getRepository()->shouldUseUserAwareCache();

        if ($useUserAwareCache) {
            return;
        }

        $controller->preload();
        $countsList = $controller->getMainCountsList();
        foreach ($countsList as $filter) {
            $controller->handleFilterCount($filter, true);
        }

        return true;
    }

    /**
     * Warmup the item cache for a controller.
     *
     * @param BaseController $controller
     * @param Model $item
     * @param bool $cacheFormItem
     * @param bool $cacheFormattedItem
     * @return void
     */
    public function warmupControllerItem($controller, $item, $cacheFormItem, $cacheFormattedItem)
    {
        $controller->preload();

        if ($cacheFormattedItem) {
            $controller->getFormattedIndexItem($item);
        }
        if ($cacheFormItem) {
            $controller->getFormItem($item->id, withoutDefaultScopes: true);
        }
    }

    /**
     * Warmup the items cache for a controller.
     *
     * @param BaseController $controller
     * @param int $chunkSize
     * @return void
     */
    public function warmupControllerItems($controller, $chunkSize = 100)
    {
        $controller->preload();
        $repository = $controller->getRepository();

        $cacheFormItem = ModularousCache::isEnabled($controller->getModuleName(), $controller->getRouteName(), 'formItem');
        $cacheFormattedItem = ModularousCache::isEnabled($controller->getModuleName(), $controller->getRouteName(), 'formattedItem');

        $repository->getModel()->each(function ($item, $key) use ($controller, $cacheFormItem, $cacheFormattedItem) {
            $this->warmupControllerItem($controller, $item, $cacheFormItem, $cacheFormattedItem);
        }, $chunkSize);
    }

    /**
     * Warmup the cache for a model.
     *
     * @return void
     */
    public function warmupByModel(Model $model)
    {
        $moduleName = method_exists($model, 'getCacheModuleName') ? $model->getCacheModuleName() : (method_exists($model, 'getModuleName') ? $model->getModuleName() : null);

        if (! $moduleName) {
            return;
        }
        $moduleRouteName = method_exists($model, 'getCacheModuleRouteName') ? $model->getCacheModuleRouteName() : (method_exists($model, 'getRouteName') ? $model->getModuleRouteName() : null);

        $module = Modularous::find($moduleName);

        if (! $module) {
            return;
            throw new \Exception("Module not found: {$moduleName}");
        }

        if (! $module->hasRoute($moduleRouteName)) {
            return;
            throw new \Exception("Route not found: {$moduleRouteName}");
        }

        $controller = $module->getController($moduleRouteName);
        if (! $controller) {
            return;
            throw new \Exception("Controller not found: {$moduleRouteName}");
        }

        if (ModularousCache::isEnabled($moduleName, $moduleRouteName, 'counts')) {
            $this->warmupControllerCounts($controller);
        }

        $cacheFormItem = ModularousCache::isEnabled($moduleName, $moduleRouteName, 'formItem');
        $cacheFormattedItem = ModularousCache::isEnabled($moduleName, $moduleRouteName, 'formattedItem');

        $this->warmupControllerItem($controller, $model, $cacheFormItem, $cacheFormattedItem);

        if (ModularousCache::isEnabled($moduleName, $moduleRouteName, 'presentationItem')) {
            $this->warmupPresentationItem($moduleName, $moduleRouteName, $model);
        }
    }

    /**
     * Warmup the counts cache for a module route.
     *
     * @param string $moduleName
     * @param string $routeName
     * @return void
     */
    public function warmupModuleRouteCacheCounts($moduleName, $routeName)
    {
        $module = Modularous::find($moduleName);

        if (! $module) {
            return;
            throw new \Exception("Module not found: {$moduleName}");
        }
        // dd($module->getRoute($routeName));
        // $route = $module->getRoute($routeName);
        // if (! $route) {
        //     return;
        //     throw new \Exception("Route not found: {$routeName}");
        // }
        $controller = $module->getController($routeName);

        if (! $controller) {
            throw new \Exception("Controller not found: {$routeName}");
        }

        if (! ModularousCache::isEnabled($moduleName, $routeName, 'counts')) {
            return;
        }

        $this->warmupControllerCounts($controller);
    }

    /**
     * Warmup the items cache for a module route.
     *
     * @param string $moduleName
     * @param string $routeName
     * @param int $chunkSize
     * @return void
     */
    public function warmupModuleRouteCacheItems($moduleName, $routeName, $chunkSize = 100)
    {
        $module = Modularous::find($moduleName);
        if (! $module) {
            return;
            throw new \Exception("Module not found: {$moduleName}");
        }
        $route = $module->getRoute($routeName);
        if (! $route) {
            return;
            throw new \Exception("Route not found: {$routeName}");
        }
        $controller = $module->getController($routeName);
        if (! $controller) {
            throw new \Exception("Controller not found: {$routeName}");
        }

        $cacheFormItem = ModularousCache::isEnabled($moduleName, $routeName, 'formItem');
        $cacheFormattedItem = ModularousCache::isEnabled($moduleName, $routeName, 'formattedItem');

        if (! $cacheFormItem && ! $cacheFormattedItem) {
            return;
        }

        $controller->getModel()->each(function ($item, $key) use ($controller, &$count, $cacheFormItem, $cacheFormattedItem) {
            if ($cacheFormattedItem) {
                $controller->getFormattedIndexItem($item);
            }
            if ($cacheFormItem) {
                $controller->getFormItem($item->id, withoutDefaultScopes: true);
            }
        }, $chunkSize);
    }

    /**
     * Warmup the cache for a module route.
     *
     * @param string $moduleName
     * @param string $routeName
     * @param int $chunkSize
     * @return void
     */
    public function warmupModuleRouteCache($moduleName, $routeName, $chunkSize = 100)
    {
        $this->warmupModuleRouteCacheCounts($moduleName, $routeName);
        $this->warmupModuleRouteCacheItems($moduleName, $routeName, $chunkSize);
    }

    /**
     * Warmup {@see presentationItem} cache for a public CMS record (requires a resolved {@see Model}).
     */
    public function warmupPresentationItem(string $moduleName, string $routeName, Model $item, ?string $locale = null): bool
    {
        // ErrorPage has no UrlRoute — HTML lives in model-scoped StaleFileCache via
        // ErrorPagePresentationCache, not CmsPublicPresentationItemCache URL/path keys.
        if (
            class_exists(ErrorPage::class)
            && $item instanceof ErrorPage
            && class_exists(ErrorPagePresentationCache::class)
        ) {
            try {
                $resolved = $this->resolvePresentationWarmupModel($item);
            } catch (\Throwable) {
                $resolved = $item;
            }

            if (! $resolved instanceof ErrorPage || $resolved->getKey() === null) {
                return false;
            }

            return ErrorPagePresentationCache::warm($resolved, $locale);
        }

        [$moduleName, $routeName] = $this->resolvePresentationItemWarmupContext($moduleName, $routeName, $item);

        if ($moduleName === null || $moduleName === '' || $routeName === null || $routeName === '') {
            return false;
        }

        if (! class_exists(CmsPublicPresentationItemCache::class)
            || ! CmsPublicPresentationItemCache::isEnabled($moduleName, $routeName)) {
            return false;
        }

        $item = $this->resolvePresentationWarmupModel($item);

        if ($item->getKey() === null) {
            return false;
        }

        $viewName = class_exists(CmsPublicFrontViewName::class)
            ? CmsPublicFrontViewName::forModel($item)
            : null;

        if ($viewName === null || $viewName === '') {
            return false;
        }

        $pathsByLocale = $this->resolvePresentationWarmupLocalesAndPaths($item);
        if ($locale !== null && $locale !== '') {
            $pathsByLocale = array_filter(
                $pathsByLocale,
                static fn (string $path, string $loc): bool => $loc === $locale,
                ARRAY_FILTER_USE_BOTH,
            );
        }

        if ($pathsByLocale === []) {
            ModularousCacheLogger::info('cache.warmup.presentation_item.skip', [
                'module' => $moduleName,
                'route' => $routeName,
                'model' => $item::class,
                'id' => $item->getKey(),
                'reason' => $locale !== null && $locale !== '' ? 'locale_not_found' : 'no_locale_paths',
            ]);

            return false;
        }

        ModularousCacheLogger::info('cache.warmup.presentation_item.start', [
            'module' => $moduleName,
            'route' => $routeName,
            'model' => $item::class,
            'id' => $item->getKey(),
            'viewName' => $viewName,
            'pathsByLocale' => $pathsByLocale,
        ]);

        $warmedAny = false;

        foreach ($pathsByLocale as $locale => $normalizedPath) {
            $iterationWarmed = CmsPublicPresentationWarmupContext::run((string) $locale, function () use (
                $moduleName,
                $routeName,
                $item,
                $viewName,
                $locale,
                $normalizedPath,
            ): bool {
                $warmupItem = $this->resolvePresentationWarmupModelForLocale(
                    $item,
                    (string) $locale,
                );

                if ($warmupItem === null) {
                    return false;
                }

                $innerData = $this->buildPresentationWarmupInnerData(
                    (string) $locale,
                    (string) $normalizedPath,
                    $warmupItem,
                );

                $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
                    $moduleName,
                    $routeName,
                    $warmupItem,
                    $viewName,
                    (string) $locale,
                );

                $resolved = CmsPublicPresentationItemCache::resolvePresentationHtml(
                    $moduleName,
                    $routeName,
                    $warmupItem,
                    $viewName,
                    $innerData,
                    (string) $locale,
                    bypassSwr: true,
                    normalizedPath: (string) $normalizedPath,
                );

                $html = $resolved['html'] ?? null;
                $urlStored = CmsPublicPresentationItemCache::usesUrlStore()
                    && ModularousCache::getUrlPresentationCacheStore()->get((string) $locale, (string) $normalizedPath) !== null;

                $result = match (true) {
                    ! is_string($html) || $html === '' => 'empty',
                    CmsPublicPresentationItemCache::usesUrlStore() && ! $urlStored => 'store_failed',
                    $resolved['status'] === CmsPublicPresentationItemCache::CACHE_STATUS_HIT => 'hit',
                    default => 'written',
                };

                ModularousCacheLogger::info('cache.warmup.presentation_item.locale', [
                    'module' => $moduleName,
                    'route' => $routeName,
                    'model' => $warmupItem::class,
                    'id' => $warmupItem->getKey(),
                    'locale' => (string) $locale,
                    'path' => (string) $normalizedPath,
                    'canonicalUrl' => $innerData['canonicalUrl'] ?? null,
                    'cacheKey' => $cacheKey,
                    'result' => $result,
                    'urlStored' => $urlStored,
                ]);

                return $result === 'written' || $result === 'hit';
            });

            if ($iterationWarmed) {
                $warmedAny = true;
            }
        }

        ModularousCacheLogger::info('cache.warmup.presentation_item.complete', [
            'module' => $moduleName,
            'route' => $routeName,
            'model' => $item::class,
            'id' => $item->getKey(),
            'warmedAny' => $warmedAny,
        ]);

        return $warmedAny;
    }

    /**
     * Resolve module + route keys for {@see presentationItem} warmup.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolvePresentationItemWarmupContext(string $moduleName, string $routeName, Model $item): array
    {
        if (class_exists(CmsPublicFrontViewName::class)) {
            $context = CmsPublicFrontViewName::presentationItemCacheContextForModel($item);
            if ($context !== null) {
                return [$context['moduleName'], $context['moduleRouteName']];
            }
        }

        if (
            $moduleName !== ''
            && $routeName !== ''
            && ModularousCache::isEnabled($moduleName, $routeName, 'presentationItem')
        ) {
            return [$moduleName, $routeName];
        }

        if (method_exists($item, 'getCacheModuleName') && method_exists($item, 'getCacheModuleRouteName')) {
            $resolvedModuleName = $item->getCacheModuleName();
            $resolvedRouteName = $item->getCacheModuleRouteName();

            if (
                is_string($resolvedModuleName) && $resolvedModuleName !== ''
                && is_string($resolvedRouteName) && $resolvedRouteName !== ''
            ) {
                return [$resolvedModuleName, $resolvedRouteName];
            }
        }

        return [$moduleName !== '' ? $moduleName : null, $routeName !== '' ? $routeName : null];
    }

    /**
     * Reload the record from storage so relationship-sourced models carry translations, URLs, and relations needed to render.
     */
    protected function resolvePresentationWarmupModel(Model $item): Model
    {
        if ($item->getKey() === null) {
            return $item;
        }

        $fresh = $item::class::query()->find($item->getKey());

        return $fresh instanceof Model ? $fresh : $item;
    }

    protected function buildPresentationWarmupInnerData(string $locale, string $registryPath, Model $item): array
    {
        if (! class_exists(CmsPublicPresentationInnerData::class)
            || ! interface_exists(CanonicalUrlResolverInterface::class)
        ) {
            return ['item' => $item];
        }

        try {
            $canonical = app(CanonicalUrlResolverInterface::class);

            return CmsPublicPresentationInnerData::buildForCache(
                $locale,
                $registryPath,
                $item,
                $canonical,
            );
        } catch (\Throwable) {
            return ['item' => $item];
        }
    }

    /**
     * @return array<string, string> locale => normalized registry path
     */
    protected function resolvePresentationWarmupLocalesAndPaths(Model $item): array
    {
        $fromUrlRoutes = $this->resolvePresentationWarmupLocalesAndPathsFromUrlRoutes($item);
        if ($fromUrlRoutes !== []) {
            return $fromUrlRoutes;
        }

        if (class_exists(CmsUrlRouteRegistry::class)) {
            try {
                $paths = app(CmsUrlRouteRegistry::class)->publicPagePathsByLocale($item);
            } catch (\Throwable) {
                $paths = [];
            }

            $filtered = [];
            foreach ($paths as $locale => $path) {
                $locale = (string) $locale;
                if ($locale === '') {
                    continue;
                }
                $filtered[$locale] = (string) $path;
            }

            if ($filtered !== []) {
                return $filtered;
            }
        }

        return $this->fallbackPresentationWarmupLocalePath();
    }

    /**
     * Prefer synced {@see UrlRoute} rows — same source {@see CmsPublicModelResolver} matches at request time.
     *
     * @return array<string, string>
     */
    protected function resolvePresentationWarmupLocalesAndPathsFromUrlRoutes(Model $item): array
    {
        if ($item->getKey() === null || ! class_exists(\Modules\Cms\Entities\UrlRoute::class)) {
            return [];
        }

        try {
            $table = (new \Modules\Cms\Entities\UrlRoute)->getTable();
            if (! Schema::hasTable($table)) {
                return [];
            }

            $rows = \Modules\Cms\Entities\UrlRoute::query()
                ->where('urlable_type', $item->getMorphClass())
                ->where('urlable_id', $item->getKey())
                ->where('kind', \Modules\Cms\Entities\UrlRoute::KIND_PAGE_PUBLIC)
                ->get(['locale', 'normalized_path']);

        } catch (\Throwable) {
            return [];
        }

        $paths = [];
        foreach ($rows as $row) {
            $locale = (string) $row->locale;
            if ($locale === '') {
                continue;
            }
            $paths[$locale] = (string) $row->normalized_path;
        }

        return $paths;
    }

    protected function resolvePresentationWarmupModelForLocale(Model $item, string $locale): ?Model
    {
        if ($item->getKey() === null) {
            return $item;
        }

        if (class_exists(\Modules\Cms\Services\CmsPublicModelResolver::class)) {
            return \Modules\Cms\Services\CmsPublicModelResolver::loadForPresentationWarmup(
                $item::class,
                $item->getKey(),
                $locale,
            );
        }

        $query = $item::class::query()->whereKey($item->getKey());

        if (method_exists($item::class, 'translations')) {
            $query->with(['translations' => fn ($q) => $q->where('locale', $locale)]);
        }

        $fresh = $query->first();

        if ($fresh instanceof Model && method_exists($fresh, 'setDefaultLocale')) {
            $fresh->setDefaultLocale($locale);
            $fresh->unsetRelation('translation');
        }

        return $fresh instanceof Model ? $fresh : null;
    }

    /**
     * Models without slug / parent-segment public routes still warm a single locale entry.
     *
     * @return array<string, string>
     */
    protected function fallbackPresentationWarmupLocalePath(): array
    {
        $locale = (string) config('app.locale', 'en');
        if ($locale === '') {
            return [];
        }

        return [$locale => '/'];
    }
}
