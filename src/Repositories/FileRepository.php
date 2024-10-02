<?php

namespace Unusualify\Modularity\Repositories;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Unusualify\Modularity\Entities\File;
use Unusualify\Modularity\Repositories\Traits\AuthorizedTrait;
use Unusualify\Modularity\Repositories\Traits\TagsTrait;

class FileRepository extends Repository
{
    use AuthorizedTrait, TagsTrait;

    public function __construct(File $model)
    {
        $this->model = $model;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function filter($query, array $scopes = [])
    {
        $this->searchIn($query, $scopes, 'search', ['filename']);

        return parent::filter($query, $scopes);
    }

    /**
     * @param A17\Twill\Models\File $object
     * @return void
     */
    public function afterDelete($object)
    {
        $storageId = $object->uuid;
        if (Config::get('twill.file_library.cascade_delete')) {
            Storage::disk(Config::get('twill.file_library.disk'))->delete($storageId);
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeCreate($fields)
    {
        if (! isset($fields['size'])) {
            $uuid = str_replace(Config::get('filesystems.disks.twill_file_library.root'), '', $fields['uuid']);
            $fields['size'] = Storage::disk(Config::get('twill.file_library.disk'))->size($uuid);
        }

        return $fields;
    }
}
