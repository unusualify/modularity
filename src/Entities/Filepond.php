<?php

namespace Unusualify\Modularity\Entities;

use Unusualify\Modularity\Facades\Filepond as FilepondFacade;

class Filepond extends Model
{
    protected $fillable = [
        'uuid',
        'file_name',
        'filepondable_id',
        'filepondable_type',
        'role',
        'locale',
    ];

    public function canDeleteSafely()
    {
        // return DB::table(modularityConfig('tables.fileponds'))->where('file_id', $this->id)->count() === 0;
    }

    public function mediableFormat()
    {
        return [
            'uuid' => $this->uuid,
            'file_name' => $this->file_name,
            'source' => route('filepond.preview', ['uuid' => $this->uuid]),
            'created_at' => $this->created_at,
            'file' => FilepondFacade::getFileInfo($this->uuid),
            // 'source' => $this->uuid,
            // 'source' => $this->uuid . '/' .  $this->file_name,
        ];
    }

    public function getTable()
    {
        return modularityConfig('tables.fileponds', parent::getTable());
    }
}
