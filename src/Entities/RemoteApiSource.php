<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Unusualify\Modularous\Observers\RemoteApiSourceObserver;

class RemoteApiSource extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'sourceable_type',
        'sourceable_id',
        'remote_id',
        'synced_attributes',
        'remote_payload',
        'remote_synced_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'synced_attributes' => 'array',
        'remote_payload' => 'array',
        'remote_synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::observe(RemoteApiSourceObserver::class);
    }

    public function getTable(): string
    {
        return modularousConfig('tables.remote_api_sources', 'um_remote_api_sources');
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, mixed>
     */
    public function toMergedAttributes(): array
    {
        return array_merge(
            (array) ($this->synced_attributes ?? []),
            [
                'remote_id' => $this->remote_id,
                'remote_payload' => $this->remote_payload,
                'remote_synced_at' => $this->remote_synced_at,
            ]
        );
    }
}
