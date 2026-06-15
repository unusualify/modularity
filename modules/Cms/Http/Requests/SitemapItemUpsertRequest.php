<?php

namespace Modules\Cms\Http\Requests;

use Modules\Cms\Entities\CmsSitemapableItem;
use Unusualify\Modularous\Http\Requests\Request;

/**
 * Panel JSON POST to upsert {@see CmsSitemapableItem} (per public page / urlable).
 */
class SitemapItemUpsertRequest extends Request
{
    /**
     * @return array<string, mixed>
     */
    public function rulesForAll(): array
    {
        return [
            'sitemapable_type' => 'required|string|max:512',
            'sitemapable_id' => 'required|integer|min:1',
            'changefreq' => 'required|string|in:always,hourly,daily,weekly,monthly,yearly,never',
            'priority' => 'required|numeric|min:0|max:1',
        ];
    }

    public function rulesForCreate(): array
    {
        return [];
    }

    public function rulesForUpdate(): array
    {
        return [];
    }
}
