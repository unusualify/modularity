<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Entities\Traits\IsAuthorizedable;
use Unusualify\Modularity\Services\FileLibrary\FileService;

class File extends Model
{
    use IsAuthorizedable;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'filename',
        'size',
    ];

    public function getSizeAttribute($value)
    {
        return bytesToHuman($value);
    }

    public function canDeleteSafely()
    {
        return DB::table(unusualConfig('tables.fileables', 'unusual_fileables'))->where('file_id', $this->id)->count() === 0;
    }

    public function scopeUnused ($query)
    {
        $usedIds = DB::table(unusualConfig('tables.fileables'))->get()->pluck('file_id');
        return $query->whereNotIn('id', $usedIds->toArray())->get();
    }

    public function mediableFormat()
    {
        return [
            'id' => $this->id,
            'name' => $this->filename,
            'src' => FileService::getUrl($this->uuid),
            'original' => FileService::getUrl($this->uuid),
            'size' => $this->size,
            'filesizeInMb' => number_format($this->attributes['size'] / 1048576, 2),
        ];
    }

    public function getTable()
    {
        return unusualConfig('tables.files', parent::getTable());
    }
}
