<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

/**
 * Provides a non-persisted {@see Model} (or generic object) so layout shell segments / appends
 * can reference {@code $item} and SEO keys during panel HTML preview (no public URL record).
 */
final class CmsLayoutShellPreviewPlaceholder
{
    /**
     * Data merged into {@see LayoutBladeResolver} preview renders (segment {@see Blade::render} + shell view).
     *
     * @return array{item: object, seoTitle: string, seoDescription: string, canonicalUrl: string, robotsMeta: string}
     */
    public static function mergeDataForShellPreview(?string $targetModelClass): array
    {
        $item = self::makePreviewItem($targetModelClass);

        return [
            'item' => $item,
            'seoTitle' => __('Preview placeholder'),
            'seoDescription' => '',
            'canonicalUrl' => '',
            'robotsMeta' => 'noindex, nofollow',
        ];
    }

    /**
     * Unsaved model when {@code $targetModelClass} is an Eloquent class; otherwise a generic object with {@code name}/{@code title}.
     */
    public static function makePreviewItem(?string $targetModelClass): object
    {
        $class = self::normalizeModelClass($targetModelClass);
        if ($class === null) {
            return self::genericFallbackObject();
        }

        try {
            if (method_exists($class, 'factory')) {
                $instance = $class::factory()->make();
                $instance->exists = false;
                self::seedPreviewAttributes($instance);
                self::attachEmptyCmsRelations($instance);

                return $instance;
            }
        } catch (\Throwable) {
        }

        $instance = new $class;
        $instance->exists = false;

        $keyName = $instance->getKeyName();
        if (is_string($keyName) && $keyName !== '' && $instance->getIncrementing()) {
            $instance->setAttribute($keyName, 0);
        }

        self::seedPreviewAttributes($instance);
        self::attachEmptyCmsRelations($instance);

        return $instance;
    }

    /**
     * @return class-string<Model>|null
     */
    private static function normalizeModelClass(?string $targetModelClass): ?string
    {
        if ($targetModelClass === null) {
            return null;
        }
        $class = trim($targetModelClass);
        if ($class === '') {
            return null;
        }
        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return null;
        }

        return $class;
    }

    private static function genericFallbackObject(): object
    {
        $o = new \stdClass;
        $o->id = 0;
        $o->name = (string) __('Preview name');
        $o->title = (string) __('Preview title');
        $o->slug = 'preview-placeholder';

        return $o;
    }

    private static function seedPreviewAttributes(Model $model): void
    {
        if (method_exists($model, 'translateOrNew')) {
            $locale = app()->getLocale();
            try {
                /** @var object $t */
                $t = $model->translateOrNew($locale);

                foreach ([
                    'title' => (string) __('Preview title'),
                    'active' => true,
                    'excerpt' => (string) __('Preview excerpt.'),
                    'content' => '<p>' . e(__('Preview body.')) . '</p>',
                ] as $prop => $value) {
                    try {
                        $t->{$prop} = $value;
                    } catch (\Throwable) {
                    }
                }
            } catch (\Throwable) {
            }
        }

        foreach (['name' => __('Preview name'), 'title' => __('Preview title'), 'slug' => 'preview-placeholder'] as $attr => $value) {
            try {
                $model->setAttribute($attr, $value);
            } catch (\Throwable) {
            }
        }
    }

    private static function attachEmptyCmsRelations(Model $model): void
    {
        foreach (['files', 'medias', 'fileponds', 'repeaters'] as $relation) {
            if (! method_exists($model, $relation)) {
                continue;
            }
            $model->setRelation($relation, new EloquentCollection);
        }
    }
}
