<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Database\Factories\FileFactory;
use Unusualify\Modularity\Entities\Traits\HasCreator;
use Unusualify\Modularity\Services\FileLibrary\FileService;

class File extends Model
{
    use HasFactory, HasCreator;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'filename',
        'size',
    ];

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return FileFactory::new();
    }

    public function sizeForHuman(): Attribute
    {
        return Attribute::make(
            get: fn () => bytesToHuman($this->size ?? 0),
        );
    }

    public function sizeInMb(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->size ?? 0) / 1048576,
        );
    }

    public function canDeleteSafely()
    {
        return DB::table(modularityConfig('tables.fileables'))->where('file_id', $this->id)->count() === 0;
    }

    public function scopeUnused($query)
    {
        $usedIds = DB::table(modularityConfig('tables.fileables'))->get()->pluck('file_id');

        return $query->whereNotIn('id', $usedIds->toArray())->get();
    }

    public function mediableFormat()
    {
        return [
            'id' => $this->id,
            'name' => $this->filename,
            'src' => FileService::getUrl($this->uuid),
            'original' => FileService::getUrl($this->uuid),
            'size' => $this->size_for_human,
            'filesizeInMb' => number_format($this->size_in_mb, 2),
        ];
    }

    public function getTable()
    {
        return modularityConfig('tables.files', parent::getTable());
    }
}
