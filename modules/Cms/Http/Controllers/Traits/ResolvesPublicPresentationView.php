<?php

namespace Modules\Cms\Http\Controllers\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Unusualify\Modularous\Http\Controllers\BaseController;
use Unusualify\Modularous\Http\Controllers\PanelController;

/**
 * Single place to resolve the Blade used for admin preview and public front when they must match
 * (module::route.custom convention or legacy site.{singular module}).
 */
trait ResolvesPublicPresentationView
{
    /**
     * Optional override for the Blade used for public display and admin preview.
     * When null or empty, {@see presentationViewName()} uses {@see presentationViewPrefix()} + ".custom"
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
     */
    protected function presentationViewName(): string
    {
        if ($this->previewView !== null && $this->previewView !== '') {
            return $this->previewView;
        }

        $prefix = $this->presentationViewPrefix();
        if ($prefix !== '') {
            return $prefix . '.custom';
        }

        $moduleKey = $this->getModuleName() ?? '';

        return Config::get('modularous.frontend.views_path', 'site') . '.' . Str::singular($moduleKey);
    }
}
