<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Model;

class Spread extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'published',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    // protected function casts(): array
    // {
    //     return [
    //         'json' => 'object',
    //     ];
    // }

    public function getTable()
    {
        return unusualConfig('tables.spreads', 'modularity_spreads');
    }
}
