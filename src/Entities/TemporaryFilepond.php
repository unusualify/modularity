<?php

namespace Unusualify\Modularity\Entities;

class TemporaryFilepond extends Model
{

    protected $fillable = [
        'file_name',
        'input_role',
        'folder_name',
    ];


    public function canDeleteSafely()
    {
        // return DB::table(unusualConfig('tables.mediables', 'unusual_mediables'))->where('media_id', $this->id)->count() === 0;
    }

    public function getTable()
    {
        return unusualConfig('tables.temporary_fileponds', parent::getTable());
    }
}

