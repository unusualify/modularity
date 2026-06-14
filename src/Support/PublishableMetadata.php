<?php

namespace Unusualify\Modularous\Support;

use Illuminate\Database\Schema\Blueprint;

/**
 * Publishable metadata columns (SEO title/description, canonical, robots, sitemap inclusion) for
 * {@see \Unusualify\Modularous\Entities\Traits\Publishable}.
 *
 * Repository form inputs: {@see \Unusualify\Modularous\Repositories\Traits\PublishableTrait}.
 */
final class PublishableMetadata
{
    /**
     * Add standard metadata columns to a translations table blueprint.
     */
    public static function addColumns(Blueprint $table, bool $publishable = false, $publishDates = false): void
    {
        if ($publishable) {
            $table->boolean('published')->default(true);
        }

        if ($publishDates) {
            $table->timestamp('publish_start_date')->nullable();
            $table->timestamp('publish_end_date')->nullable();
        }
    }

    /**
     * Publishable column names that may be locale-specific when listed in {@see HasTranslation::$translatedAttributes}.
     *
     * @var list<string>
     */
    public const PUBLISHABLE_ATTRIBUTES = [
        'published',
        'publish_start_date',
        'publish_end_date',
    ];

    /**
     * Default Modularous form input definitions (usually appended via {@see PublishableTrait}).
     *
     * @param list<string>|bool $translatedFields Field names with {@code translated => true}, or legacy bool for all three.
     * @return list<array<string, mixed>>
     */
    public static function defaultFormInputs(array|bool $translatedFields = false): array
    {
        $translated = self::normalizeTranslatedFields($translatedFields);
        $translatedPublished = in_array('published', $translated, true);
        $publishedExtraConfig = $translatedPublished ? ['isSecondary' => true] : ['isEvent' => true];
        return [
            ['type' => 'switch', 'name' => 'published', 'label' => 'Published', 'trueValue' => true, 'falseValue' => false, ...$publishedExtraConfig, 'translated' => $translatedPublished],
            ['name' => 'publish_start_date', 'label' => 'Publish from', 'type' => 'date', 'isSecondary' => true, 'translated' => in_array('publish_start_date', $translated, true)],
            ['name' => 'publish_end_date', 'label' => 'Publish until', 'type' => 'date', 'isSecondary' => true, 'translated' => in_array('publish_end_date', $translated, true)],
        ];
    }

    /**
     * @param list<string>|bool $translatedFields
     * @return list<string>
     */
    public static function normalizeTranslatedFields(array|bool $translatedFields): array
    {
        if (is_bool($translatedFields)) {
            return $translatedFields ? self::PUBLISHABLE_ATTRIBUTES : [];
        }

        return array_values(array_intersect(self::PUBLISHABLE_ATTRIBUTES, $translatedFields));
    }
}
