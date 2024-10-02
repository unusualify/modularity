<?php

namespace Unusualify\Modularity\Entities;

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
        // return DB::table(unusualConfig('tables.fileables', 'unusual_fileables'))->where('file_id', $this->id)->count() === 0;
    }

    public function mediableFormat()
    {
        return [
            'folder_name' => $this->uuid,
            'file_name' => $this->file_name,
            'source' => route('filepond.preview', ['folder' => $this->uuid]),
            // 'source' => $this->uuid,
            // 'source' => $this->uuid . '/' .  $this->file_name,
        ];
    }

    public function getTable()
    {
        return unusualConfig('tables.fileponds', parent::getTable());
    }
}
