<?php

declare(strict_types=1);

namespace Modules\ErrorPage\Entities;

use Modules\Cms\Entities\Concerns\HasPageLayout;
use Modules\ErrorPage\Support\ErrorPagePresentationCache;
use Modules\ErrorPage\Support\ErrorPageRenderer;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\Core\HasScopes;
use Unusualify\Modularous\Entities\Traits\Publishable;

/**
 * HTTP error presentation rows (404 / 403 / 500, …). No public URL / ParentSegment —
 * resolved by {@see ErrorPageRenderer} from the exception status code.
 */
class ErrorPage extends Model
{
    use HasPageLayout,
        HasScopes,
        Publishable;

    public bool $usePublishDates = false;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'error_code',
        'published',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'published' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = static function (ErrorPage $page): void {
            ErrorPagePresentationCache::invalidate($page);
        };

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Presentation HTML is flushed by {@see ErrorPagePresentationCache} on save/delete.
     * Skip the generic CacheObserver refresh/warm path (no UrlRoute for ErrorPage).
     */
    public function shouldCacheInvalidate(): bool
    {
        return false;
    }

    public function getTable(): string
    {
        return modularousConfig('tables.cms_error_pages', 'um_cms_error_pages');
    }
}
