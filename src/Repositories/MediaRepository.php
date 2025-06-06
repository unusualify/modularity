<?php

namespace Unusualify\Modularity\Repositories;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularity\Entities\Media;
use Unusualify\Modularity\Repositories\Traits\CreatorTrait;
use Unusualify\Modularity\Repositories\Traits\TagsTrait;
use Unusualify\Modularity\Services\MediaLibrary\ImageService;

class MediaRepository extends Repository
{
    use CreatorTrait, TagsTrait;

    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    public function filter($query, array $scopes = [])
    {
        $this->searchIn($query, $scopes, 'search', ['alt_text', 'filename', 'caption']);

        return parent::filter($query, $scopes);
    }

    public function afterDelete($object)
    {
        $storageId = $object->uuid;
        if (Config::get('twill.media_library.cascade_delete')) {
            Storage::disk(Config::get('twill.media_library.disk'))->delete($storageId);
        }
    }

    public function prepareFieldsBeforeCreate($fields)
    {
        if (Config::get('twill.media_library.init_alt_text_from_filename', true)) {
            $fields['alt_text'] = $this->model->altTextFrom($fields['filename']);
        }

        // if we were not able to determine dimensions with the browser File API, let's ask the Image service
        if (! isset($fields['width']) || ! isset($fields['height'])) {
            $dimensions = ImageService::getDimensions($fields['uuid']);
            $fields['width'] = $dimensions['width'] ?? 0;
            $fields['height'] = $dimensions['height'] ?? 0;
        }

        return $fields;
    }
}
