<?php

namespace Modules\Cms\Http\Controllers\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Modules\Cms\Support\CmsPublicFrontViewName;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Http\Controllers\PanelController;

/**
 * Single place to resolve the Blade used for admin preview and public front when they must match
 * ({@code module::route.custom} → static {@code page_layout/*} → fallbacks, or legacy site.{singular module}).
 *
 * PageLayout shell wrapping for CMR models ({@see \Modules\Cms\Entities\Concerns\IsCmr}) is handled by
 * {@see \Modules\Cms\Support\CmsPageLayoutPresentationWrapper} after this name is resolved.
 */
trait ResolvesPublicPresentationView
{
    /**
     * Optional override for the Blade used for public display and admin preview.
     * When null or empty, {@see presentationViewName()} uses {@see CmsPublicFrontViewName::resolveViewNameForModuleRoute()}
     * or the legacy {@code modularous.frontend.views_path}.{singular module}.
     *
     * @var string|null
     */
    public $previewView = null;

    /**
     * Snake-case module::route namespace (e.g. cms::page), aligned with
     * {@see BaseController::getViewPrefix()}.
     */
    protected function presentationViewPrefix(): string
    {
        $module = $this->getModuleName();
        $route = $this->getRouteName();

        if ($module === null || $route === null || $module === '' || $route === '') {
            return '';
        }

        return Str::snake($module) . '::' . Str::snake($route);
    }

    /**
     * Dot-separated route-name prefix (e.g. cms.page) for public helpers; mirrors admin
     * {@see PanelController::$routePrefix} shape.
     */
    protected function presentationRoutePrefix(): string
    {
        $module = $this->getModuleName();
        $route = $this->getRouteName();

        if ($module === null || $route === null || $module === '' || $route === '') {
            return '';
        }

        return Str::snake($module) . '.' . Str::snake($route);
    }

    /**
     * Blade view name shared by admin preview and public CMS when using the same presentation.
     *
     * Keeps a {@code module::route.*} shape for {@see LayoutBladeResolver} filesystem segment resolution.
     * Prefers {@code .custom} when present; otherwise static {@code page_layout} segments / configured fallbacks.
     */
    protected function presentationViewName(): string
    {
        if ($this->previewView !== null && $this->previewView !== '') {
            return $this->previewView;
        }

        $prefix = $this->presentationViewPrefix();
        if ($prefix !== '') {
            $module = Str::snake((string) $this->getModuleName());
            $route = Str::snake((string) $this->getRouteName());

            return CmsPublicFrontViewName::resolveViewNameForModuleRoute($prefix, [
                'module' => $module,
                'route' => $route,
                'viewPrefix' => $prefix,
            ]);
        }

        $moduleKey = $this->getModuleName() ?? '';

        return Config::get('modularous.frontend.views_path', 'site') . '.' . Str::singular($moduleKey);
    }
}
