<?php

namespace Unusualify\Modularity\Entities;

class Asset extends Model{

    protected $fillable = [
        'uuid',
        'file_name',
        'assetable_id',
        'assetable_type',
        'role',
        'locale',
    ];


    public function canDeleteSafely()
    {
        // return DB::table(unusualConfig('tables.fileables', 'unusual_fileables'))->where('file_id', $this->id)->count() === 0;
    }

    public function getTable()
    {
        return unusualConfig('tables.assets', parent::getTable());
    }

    public function mediableFormat(){
        return [
            // 'source' => route('admin.filepond.preview', $this->uuid),
            'folderName' => $this->uuid,
            'fileName' => $this->file_name,
        ];
    }
}
