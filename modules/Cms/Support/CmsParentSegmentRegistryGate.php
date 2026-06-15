<?php

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Entities\UrlRoute;

/**
 * Data-driven allow-list for public CMS URLs and sitemap lines: an enabled {@see ParentSegment} row whose
 * {@code target_model_class} matches the concrete entity (FQCN and/or {@see Model::getMorphClass()}).
 *
 * {@see IsSingular} entities live on the singletons table but still use their concrete class as {@code urlable_type};
 * they are included when registered here even if only {@see IsSingular} + slug-less paths via {@see ParentSegment} bindings.
 */
final class CmsParentSegmentRegistryGate
{
    /**
     * Whether this model class may be served publicly / listed when synced {@see UrlRoute} rows exist.
     *
     * @param class-string<Model> $modelClass
     */
    public static function allowsModelClass(string $modelClass): bool
    {
        if (! is_a($modelClass, Model::class, true)) {
            return false;
        }

        if (! classHasTrait($modelClass, HasParentSegment::class)
        && ! classHasTrait($modelClass, \Unusualify\Modularous\Entities\Traits\IsSingular::class)) {
            return false;
        }

        return self::targetMatchesEnabledParentSegment($modelClass);
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private static function targetMatchesEnabledParentSegment(string $modelClass): bool
    {
        if (! Schema::hasTable((new ParentSegment)->getTable())) {
            return false;
        }

        $aliases = array_values(array_unique(array_filter([
            $modelClass,
            (new $modelClass)->getMorphClass(),
        ])));

        return ParentSegment::query()
            ->where('enabled', true)
            ->whereIn('target_model_class', $aliases)
            ->exists();
    }
}
