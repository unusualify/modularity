<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Traits\IsAuthorizedable;
use Unusualify\Modularity\Services\MediaLibrary\ImageService;

class Media extends Model
{
    use IsAuthorizedable;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'filename',
        'alt_text',
        'caption',
        'width',
        'height',
    ];

    public function __construct(array $attributes = [])
    {
        $this->fillable(array_merge($this->fillable, Collection::make(unusualConfig('media_library.extra_metadatas_fields', []))->map(function ($field) {
            return $field['name'];
        })->toArray()));

        Collection::make(unusualConfig('media_library.translatable_metadatas_fields', []))->each(function ($field) {
            $this->casts[$field] = 'json';
        });

        parent::__construct($attributes);
    }

    public function scopeUnused ($query)
    {
        $usedIds = DB::table(unusualConfig('tables.mediables'))->get()->pluck('media_id');
        return $query->whereNotIn('id', $usedIds->toArray())->get();
    }

    public function getDimensionsAttribute()
    {
        return $this->width . 'x' . $this->height;
    }

    public function altTextFrom($filename)
    {
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        if (Str::endsWith($filename, '@2x')) {
            $filename = substr($filename, 0, -2);
        }

        return ucwords(preg_replace('/[^a-zA-Z0-9]/', ' ', sanitizeFilename($filename)));
    }

    public function canDeleteSafely()
    {
        return DB::table(unusualConfig('tables.mediables', 'unusual_mediables'))->where('media_id', $this->id)->count() === 0;
    }

    public function isReferenced()
    {
        return DB::table(unusualConfig('tables.mediables', 'unusual_mediables'))->where('media_id', $this->id)->count() > 0;
    }

    public function mediableFormat()
    {
        $routeNamePrefix = adminRouteNamePrefix() ? adminRouteNamePrefix() . '.' : '';

        return [
            'id' => $this->id,
            'name' => $this->filename,
            'thumbnail' => ImageService::getCmsUrl($this->uuid, ["h" => "256"]),
            'original' => ImageService::getRawUrl($this->uuid),
            'medium' => ImageService::getUrl($this->uuid, ["h" => "430"]),
            'width' => $this->width,
            'height' => $this->height,
            'tags' => $this->tags->map(function ($tag) {
                return $tag->name;
            }),
            'deleteUrl' => $this->canDeleteSafely() ? moduleRoute('media', $routeNamePrefix . 'media-library', 'destroy', ['media' => $this->id]) : null,
            'updateUrl' => route(Route::hasAdmin('media-library.media.single-update')),
            'updateBulkUrl' => route(Route::hasAdmin('media-library.media.bulk-update')),
            'deleteBulkUrl' => route(Route::hasAdmin('media-library.media.bulk-delete')),
            'metadatas' => [
                'default' => [
                    'caption' => $this->caption,
                    'altText' => $this->alt_text,
                    'video' => null,
                ] + Collection::make(unusualConfig('media_library.extra_metadatas_fields', []))->mapWithKeys(function ($field) {
                    return [
                        $field['name'] => $this->{$field['name']},
                    ];
                })->toArray(),
                'custom' => [
                    'caption' => null,
                    'altText' => null,
                    'video' => null,
                ],
            ],
        ];
    }

    public function getMetadata($name, $fallback = null)
    {
        $metadatas = (object) json_decode($this->pivot->metadatas);
        $language = app()->getLocale();

        if ($metadatas->$name->$language ?? false) {
            return $metadatas->$name->$language;
        }

        $fallbackLocale = config('translatable.fallback_locale');

        if (in_array($name, unusualConfig('media_library.translatable_metadatas_fields', [])) && config('translatable.use_property_fallback', false) && ($metadatas->$name->$fallbackLocale ?? false)) {
            return $metadatas->$name->$fallbackLocale;
        }

        $fallbackValue = $fallback ? $this->$fallback : $this->$name;

        $fallback = $fallback ?? $name;

        if (in_array($fallback, unusualConfig('media_library.translatable_metadatas_fields', []))) {
            $fallbackValue = $fallbackValue[$language] ?? '';

            if ($fallbackValue === '' && config('translatable.use_property_fallback', false)) {
                $fallbackValue = $this->$fallback[config('translatable.fallback_locale')] ?? '';
            }
        }

        if (is_object($metadatas->$name ?? null)) {
            return $fallbackValue ?? '';
        }

        return $metadatas->$name ?? $fallbackValue ?? '';
    }

    public function replace($fields)
    {
        $prevHeight = $this->height;
        $prevWidth = $this->width;

        if ($this->update($fields) && $this->isReferenced())
        {
            DB::table(unusualConfig('tables.mediables', 'unusual_mediables'))->where('media_id', $this->id)->get()->each(function ($mediable) use ($prevWidth, $prevHeight) {

                if ($prevWidth != $this->width) {
                    $mediable->crop_x = 0;
                    $mediable->crop_w = $this->width;
                }

                if ($prevHeight != $this->height) {
                    $mediable->crop_y = 0;
                    $mediable->crop_h = $this->height;
                }

                DB::table(unusualConfig('tables.mediables', 'unusual_mediables'))->where('id', $mediable->id)->update((array)$mediable);
            });
        }
    }

    public function delete()
    {
        if ($this->canDeleteSafely()) {
            return parent::delete();
        }
        return false;
    }

    public function getTable()
    {
        return unusualConfig('tables.medias', parent::getTable());
    }
}
