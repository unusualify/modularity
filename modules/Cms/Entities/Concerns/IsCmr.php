<?php

namespace Modules\Cms\Entities\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Modules\Cms\Repositories\Traits\CmrTrait;
use Modules\Cms\Services\CmsPublicModelResolver;

/**
 * Content module route (CMR): CMS panel routes that participate in parent-segment URL bindings and UrlRoute syncing.
 *
 * Composes {@see HasParentSegment} (URL prefix per locale) and {@see HasPageLayout} (locale-agnostic shell defaults).
 * Pair repositories with {@see CmrTrait}.
 */
trait IsCmr
{
    use HasPageLayout, HasParentSegment;

    /**
     * Absolute public URL for the current or given locale ({@see LocaleUrls::publicFullUrlForLocale()}).
     */
    public function getCmrUrl(?string $locale = null): ?string
    {
        return $this->publicFullUrlForLocale($locale);
    }

    /**
     * Absolute public URLs keyed by locale ({@see LocaleUrls::publicFullUrlsByLocale()}).
     *
     * @return array<string, string>
     */
    public function getCmrUrlsByLocale(): array
    {
        return $this->publicFullUrlsByLocale();
    }

    public static function scopeCmsActiveLocaleUrl(): Builder
    {
        $query = static::withPublicUrlRouteForLocale();

        CmsPublicModelResolver::applyPublishedVisibilityScopes($query, static::class);

        return $query;
    }
}
