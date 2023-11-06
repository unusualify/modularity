<?php

namespace OoBook\CRM\Base\Entities;

use Illuminate\Support\Facades\DB;
use OoBook\CRM\Base\Services\FileLibrary\FileService;

class File extends Model
{
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
        return DB::table(config(unusualBaseKey() . '.fileables_table', 'unusual_fileables'))->where('file_id', $this->id)->count() === 0;
    }

    public function scopeUnused ($query)
    {
        $usedIds = DB::table(config(unusualBaseKey() . '.fileables_table'))->get()->pluck('file_id');
        return $query->whereNotIn('id', $usedIds->toArray())->get();
    }

    public function toCmsArray()
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
        return config(unusualBaseKey() . '.files_table', 'unusual_files');
    }
}
