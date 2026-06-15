<?php

namespace Unusualify\Modularous\Support;

use Illuminate\Database\Schema\Blueprint;
use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Entities\Traits\HasTranslation;

/**
 * Translatable public metadata columns (SEO title/description, canonical, robots, sitemap inclusion) for
 * {@see HasTranslation} models that opt in via
 * {@see HasTranslatableMetadata}.
 *
 * Migrations: {@see createTranslatableMetadataFields()}.
 * Repository form inputs: {@see \Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait}.
 */
final class TranslatableMetadata
{
    /**
     * Translation column names (for {@see $translatedAttributes} / spreads).
     *
     * @var list<string>
     */
    public const TRANSLATED_ATTRIBUTES = [
        'seo_title',
        'seo_description',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'sitemap_include',
    ];

    /**
     * Casts for a dedicated translation model, if you define one (optional).
     *
     * @return array<string, string>
     */
    public static function translationCasts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'sitemap_include' => 'boolean',
        ];
    }

    /**
     * Add standard metadata columns to a translations table blueprint.
     */
    public static function addColumns(Blueprint $table, bool $withSitemapInclude = true): void
    {
        $table->string('seo_title')->nullable();
        $table->text('seo_description')->nullable();
        $table->string('canonical_url', 2048)->nullable();
        $table->boolean('robots_index')->default(true);
        $table->boolean('robots_follow')->default(true);

        if ($withSitemapInclude) {
            $table->boolean('sitemap_include')->default(true);
        }
    }

    /**
     * Default Modularous form input definitions (usually appended via {@see TranslatableMetadataTrait}).
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultFormInputs(): array
    {
        return [
            ['name' => 'seo_title', 'label' => 'SEO Title', 'type' => 'text', 'translated' => true, 'isSecondary' => true],
            ['name' => 'seo_description', 'label' => 'SEO Description', 'type' => 'textarea', 'translated' => true, 'isSecondary' => true],
            ['name' => 'canonical_url', 'label' => 'Canonical URL', 'type' => 'text', 'translated' => true, 'isSecondary' => true],
            ['name' => 'robots_index', 'label' => 'Robots Index', 'type' => 'switch', 'translated' => true, 'isSecondary' => true],
            ['name' => 'robots_follow', 'label' => 'Robots Follow', 'type' => 'switch', 'translated' => true, 'isSecondary' => true],
            ['name' => 'sitemap_include', 'label' => 'Include in sitemap', 'type' => 'switch', 'translated' => true, 'isSecondary' => true],
        ];
    }
}
