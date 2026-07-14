<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Entities\Traits\IsSingular;

/**
 * Publication snapshot stored alongside URL-keyed stale HTML at warmup/write time.
 */
final class StalePublicationMeta
{
    public const PROFILE_SINGULAR = 'singular';

    public const PROFILE_STANDARD = 'standard';

    /**
     * @return array<string, mixed>
     */
    public static function fromModel(
        Model $item,
        string $locale,
        string $normalizedPath,
        string $moduleName,
        string $moduleRouteName,
        ?int $freshTtl = null,
    ): array {
        $locale = CmsPublicPresentationItemCache::normalizeCacheLocale($locale);
        $profile = self::resolveVisibilityProfile($item);

        $meta = [
            'locale' => $locale,
            'normalized_path' => self::normalizePath($normalizedPath),
            'visibility_profile' => $profile,
            'urlable_type' => $item->getMorphClass(),
            'urlable_id' => $item->getKey(),
            'module' => $moduleName,
            'route' => $moduleRouteName,
            'warmed_at' => gmdate('c'),
        ];

        if ($freshTtl !== null) {
            $meta['fresh_ttl'] = $freshTtl;
        }

        if ($profile === self::PROFILE_SINGULAR) {
            $meta['published'] = self::singularPublished($item);
            $meta['publish_start_date'] = self::nullableDateString($item->getAttribute('publish_start_date'));
            $meta['publish_end_date'] = self::nullableDateString($item->getAttribute('publish_end_date'));
        } else {
            $published = self::standardPublished($item, $locale);
            $meta['published'] = $published;
            $meta['publish_start_date'] = self::nullableDateString(
                self::standardDateAttribute($item, $locale, 'publish_start_date'),
            );
            $meta['publish_end_date'] = self::nullableDateString(
                self::standardDateAttribute($item, $locale, 'publish_end_date'),
            );
        }

        return $meta;
    }

    public static function resolveVisibilityProfile(Model $item): string
    {
        return self::usesSingularVisibility($item)
            ? self::PROFILE_SINGULAR
            : self::PROFILE_STANDARD;
    }

    public static function usesSingularVisibility(Model $item): bool
    {
        return in_array(IsSingular::class, class_uses_recursive($item), true);
    }

    protected static function singularPublished(Model $item): bool
    {
        if ($item->getAttribute('published') !== null) {
            return (bool) $item->getAttribute('published');
        }

        return false;
    }

    protected static function standardPublished(Model $item, string $locale): bool
    {
        if (method_exists($item, 'translate')) {
            try {
                $translation = $item->translate($locale, false);
                if ($translation !== null && $translation->getAttribute('published') !== null) {
                    return (bool) $translation->getAttribute('published');
                }
            } catch (\Throwable) {
                // Translations table may be unavailable during cache-only writes.
            }
        }

        return (bool) ($item->getAttribute('published') ?? false);
    }

    protected static function standardDateAttribute(Model $item, string $locale, string $field): mixed
    {
        if (method_exists($item, 'translate')) {
            try {
                $translation = $item->translate($locale, false);
                if ($translation !== null && $translation->getAttribute($field) !== null) {
                    return $translation->getAttribute($field);
                }
            } catch (\Throwable) {
                // Translations table may be unavailable during cache-only writes.
            }
        }

        return $item->getAttribute($field);
    }

    protected static function nullableDateString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    protected static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
