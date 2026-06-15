<?php

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Modules\Cms\Http\Controllers\Front\CmsPublicFrontController;
use Unusualify\Modularous\Module;

/**
 * Picks the Blade for {@see CmsPublicFrontController} from config or by matching
 * the resolved model to a CMS submodule (same {@code module::route.custom} as per–route front controllers).
 */
final class CmsPublicFrontViewName
{
    /**
     * @return non-empty-string
     */
    public static function forModel(Model $item): string
    {
        $map = (array) modularousConfig('cms_routing.public_front_views_by_model', []);
        $class = get_class($item);
        if (isset($map[$class]) && is_string($map[$class]) && $map[$class] !== '') {
            return $map[$class];
        }

        $context = self::moduleRouteContextForModel($item);
        if ($context !== null) {
            return self::resolveViewNameForModuleRoute($context['viewPrefix'], $context);
        }

        return self::universalFallbackViewName();
    }

    /**
     * @return array{module: string, route: string, viewPrefix: string}|null
     */
    public static function moduleRouteContextForModel(Model $item): ?array
    {
        $module = method_exists($item, 'isModuleRouteClass') && $item->isModuleRouteClass()
            ? $item->getModule()
            : null;
        if (! $module instanceof Module) {
            return null;
        }

        foreach ($module->getRouteNames() as $routeName) {
            if (! $module->isEnabledRoute($routeName)) {
                continue;
            }
            try {
                $m = $module->getModel($routeName, true);
            } catch (\Throwable) {
                continue;
            }

            if ($m::class !== $item::class) {
                continue;
            }

            $moduleKey = Str::snake($module->getName());
            $routeKey = Str::snake($routeName);

            return [
                'module' => $moduleKey,
                'route' => $routeKey,
                'viewPrefix' => $moduleKey . '::' . $routeKey,
            ];
        }

        return null;
    }

    /**
     * Priority: {@code custom} → static {@code page_layout/*} → informational fallback → universal fallback.
     *
     * @param array{module: string, route: string, viewPrefix: string} $moduleRouteContext
     * @return non-empty-string
     */
    public static function resolveViewNameForModuleRoute(string $viewPrefix, array $moduleRouteContext): string
    {
        $customViewName = $viewPrefix . '.custom';
        if (View::exists($customViewName)) {
            return $customViewName;
        }

        foreach (['body', 'head', 'footer'] as $segment) {
            $segmentViewName = $viewPrefix . '.page_layout.' . $segment;
            if (View::exists($segmentViewName)) {
                return $segmentViewName;
            }
        }

        if (self::informationalFallbackEnabled()) {
            $informationalView = self::informationalFallbackViewName();
            if ($informationalView !== '' && View::exists($informationalView)) {
                return $informationalView;
            }
        }

        return self::universalFallbackViewName();
    }

    public static function informationalFallbackEnabled(): bool
    {
        return (bool) modularousConfig('cms_page_layouts.public_presentation_informational_fallback_enabled', true);
    }

    public static function informationalFallbackViewName(): string
    {
        return trim((string) modularousConfig(
            'cms_page_layouts.public_presentation_informational_fallback_view',
            'cms::page.page_layout.body',
        ));
    }

    /**
     * @return non-empty-string
     */
    public static function universalFallbackViewName(): string
    {
        $fallback = (string) modularousConfig('cms_routing.universal_public_front_fallback_view', 'cms::page.custom');
        if ($fallback === '') {
            return 'cms::page.custom';
        }

        return $fallback;
    }
}
