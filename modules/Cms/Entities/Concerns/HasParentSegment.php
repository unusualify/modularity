<?php

namespace Modules\Cms\Entities\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Entities\UrlRoute;

/**
 * Opt-in marker for Eloquent models that participate in URL parent-segment bindings
 * (shared path prefixes per model class + locale).
 *
 * Used by {@see \Modules\Cms\Repositories\Traits\ParentSegmentTrait},
 * {@see \Unusualify\Modularous\Modularous::getModuleRouteModelSelectItems()},
 * and CMS slug validation when resolving public paths.
 */
trait HasParentSegment
{
    use LocaleUrls;

    public static function supportsParentSegmentBindings(): bool
    {
        return true;
    }

    public function parentSegments(): Collection
    {
        return ParentSegment::where('target_model_class', static::class)->get();
    }

    public function urlRoutes() : \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(UrlRoute::class, 'urlable');
    }

    public function localeUrlRoute(?string $locale = null): ?UrlRoute
    {
        $locale = $locale ?? app()->getLocale();

        if ($this->relationLoaded('urlRoutes')) {
            return $this->urlRoutes->first(
                fn (UrlRoute $route) => $route->locale === $locale
                    && $route->kind === UrlRoute::KIND_PAGE_PUBLIC
            );
        }

        return $this->urlRoutes()
            ->where('locale', $locale)
            ->where('kind', UrlRoute::KIND_PAGE_PUBLIC)
            ->first();
    }
}
