<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Entities\Traits;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Unusualify\Modularous\Entities\RemoteApiSource;
use Unusualify\Modularous\Observers\RemoteApiSourceableObserver;

trait HasRemoteApiSource
{
    /**
     * @var array<string, bool>
     */
    protected array $remoteApiVirtualKeys = [];

    /**
     * Virtual attributes accepted from admin forms and persisted on {@see RemoteApiSource}.
     *
     * @var array<int, string>
     */
    protected array $remoteApiFillable = [
        'remote_id',
    ];

    /**
     * @var class-string
     */
    protected static string $remoteApiSourceableObserver = RemoteApiSourceableObserver::class;

    public static function bootHasRemoteApiSource(): void
    {
        static::observe(static::resolveRemoteApiSourceableObserver());
    }

    public function initializeHasRemoteApiSource(): void
    {
        $this->makeHidden(array_merge($this->hidden ?? [], ['remoteApiSource']));

        $this->mergeFillable($this->getRemoteApiFillableKeys());
    }

    /**
     * @return array<int, string>
     */
    protected function getRemoteApiFillableKeys(): array
    {
        if (property_exists($this, 'remoteApiFillable') && is_array($this->remoteApiFillable)) {
            return $this->remoteApiFillable;
        }

        return ['remote_id'];
    }

    protected function isRemoteApiFillableAttribute(string $key): bool
    {
        return in_array($key, $this->getRemoteApiFillableKeys(), true);
    }

    public function setRemoteIdAttribute(mixed $value): void
    {
        $this->registerRemoteApiVirtualAttribute('remote_id', $value);
    }

    /**
     * @return class-string
     */
    protected static function resolveRemoteApiSourceableObserver(): string
    {
        return static::$remoteApiSourceableObserver ?? RemoteApiSourceableObserver::class;
    }

    public function remoteApiSource(): MorphOne
    {
        return $this->morphOne(RemoteApiSource::class, 'sourceable');
    }

    public function getRemoteApiId(): int|string|null
    {
        if (array_key_exists('remote_id', $this->remoteApiVirtualKeys)) {
            return $this->getAttribute('remote_id');
        }

        return $this->remoteApiSource?->remote_id;
    }

    public function getRemoteApiIdColumn(): string
    {
        return 'remote_id';
    }

    /**
     * Admin table/form chip for last remote API sync time (user/browser timezone).
     */
    protected function remoteApiLastSync(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->formatRemoteApiLastSyncChip(
                    $this->resolveRemoteApiLastSyncedAt(),
                );
            },
        );
    }

    protected function resolveRemoteApiLastSyncedAt(): ?Carbon
    {
        if (array_key_exists('remote_synced_at', $this->remoteApiVirtualKeys)) {
            $value = $this->attributes['remote_synced_at'] ?? null;
        } else {
            $value = $this->remoteApiSource?->remote_synced_at;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function formatRemoteApiLastSyncChip(?Carbon $syncedAt): string
    {
        if ($syncedAt === null) {
            $tooltip = e(__('messages.remote-api.last-sync.never'));
            $label = e(__('messages.remote-api.last-sync.never-label'));

            return "<v-tooltip text=\"{$tooltip}\" location=\"top\">"
                . "<v-chip color=\"secondary\" prepend-icon=\"mdi-sync-off\" variant=\"text\">{$label}</v-chip>"
                . '</v-tooltip>';
        }

        $formatted = $syncedAt
            ->timezone($this->resolveRemoteApiDisplayTimezone())
            ->format('Y-m-d H:i');

        $tooltip = e(__('messages.remote-api.last-sync.synced', ['at' => $formatted]));
        $label = e($formatted);

        return "<v-tooltip text=\"{$tooltip}\" location=\"top\">"
            . "<v-chip color=\"success\" prepend-icon=\"mdi-sync\" variant=\"text\">{$label}</v-chip>"
            . '</v-tooltip>';
    }

    /**
     * Prefer browser timezone from login session, then user profile, then app config.
     */
    protected function resolveRemoteApiDisplayTimezone(): string
    {
        $timezone = session('modularous_timezone')
            ?? auth()->user()?->timezone
            ?? modularousConfig('timezone')
            ?? config('app.timezone', 'UTC');

        if (! is_string($timezone) || $timezone === '') {
            return 'UTC';
        }

        try {
            new \DateTimeZone($timezone);

            return $timezone;
        } catch (\Throwable) {
            return 'UTC';
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toRemoteApiArray(): array
    {
        $attributes = $this->attributesToArray();
        $source = $this->relationLoaded('remoteApiSource')
            ? $this->remoteApiSource
            : $this->remoteApiSource()->first();

        if ($source === null) {
            return $attributes;
        }

        return array_merge(
            (array) ($source->synced_attributes ?? []),
            $attributes,
            [
                'remote_id' => $source->remote_id,
                'remote_payload' => $source->remote_payload,
                'remote_synced_at' => $source->remote_synced_at,
            ]
        );
    }

    public function toRemoteApiPresentationArray(): array
    {
        return $this->toRemoteApiArray();
    }

    public function hydrateRemoteApiAttributes(): void
    {
        if (! $this->hasRemoteApiSource()) {
            return;
        }

        $source = $this->remoteApiSource;

        if ($source === null) {
            return;
        }

        foreach ((array) ($source->synced_attributes ?? []) as $key => $value) {
            if ($this->isRemoteApiProtectedAttribute($key)) {
                continue;
            }

            $this->registerRemoteApiVirtualAttribute($key, $value);
        }

        $this->registerRemoteApiVirtualAttribute('remote_id', $source->remote_id);
        $this->registerRemoteApiVirtualAttribute('remote_synced_at', $source->remote_synced_at);
    }

    public function persistRemoteApiVirtualAttributes(): void
    {
        if ($this->remoteApiVirtualKeys === [] || ! $this->exists) {
            return;
        }

        $dirtySynced = [];
        $remoteId = null;
        $remoteSyncedAt = null;

        foreach (array_keys($this->remoteApiVirtualKeys) as $key) {
            if (! $this->isRemoteApiVirtualDirty($key)) {
                continue;
            }

            $value = $this->getAttribute($key);

            if ($key === 'remote_id') {
                $remoteId = $value;

                continue;
            }

            if ($key === 'remote_synced_at') {
                $remoteSyncedAt = $value;

                continue;
            }

            $dirtySynced[$key] = $value;
        }

        if ($remoteId === null && $dirtySynced === [] && $remoteSyncedAt === null) {
            return;
        }

        $source = $this->relationLoaded('remoteApiSource')
            ? $this->remoteApiSource
            : $this->remoteApiSource()->first();

        if ($source === null) {
            $payload = [
                'synced_attributes' => $dirtySynced,
            ];

            if ($remoteId !== null) {
                $payload['remote_id'] = $remoteId;
            }

            if ($remoteSyncedAt !== null) {
                $payload['remote_synced_at'] = $remoteSyncedAt;
            }

            $this->remoteApiSource()->create($payload);

            return;
        }

        $payload = [
            'synced_attributes' => array_merge((array) ($source->synced_attributes ?? []), $dirtySynced),
        ];

        if ($remoteId !== null) {
            $payload['remote_id'] = $remoteId;
        }

        if ($remoteSyncedAt !== null) {
            $payload['remote_synced_at'] = $remoteSyncedAt;
        }

        $source->updateQuietly($payload);
    }

    public function stripRemoteApiVirtualAttributes(): void
    {
        foreach (array_keys($this->remoteApiVirtualKeys) as $key) {
            $this->offsetUnset($key);
        }
    }

    /**
     * Virtual API mirror fields must never be persisted on the parent table.
     *
     * @return array<string, mixed>
     */
    public function getDirty(): array
    {
        $dirty = parent::getDirty();

        foreach (array_keys($this->remoteApiVirtualKeys) as $key) {
            unset($dirty[$key]);
        }

        return $dirty;
    }

    /**
     * @return array<int, string>
     */
    public function getRemoteApiVirtualKeys(): array
    {
        return array_keys($this->remoteApiVirtualKeys);
    }

    protected function hasRemoteApiSource(): bool
    {
        if ($this->relationLoaded('remoteApiSource')) {
            return $this->remoteApiSource !== null;
        }

        if (! $this->exists || $this->getKey() === null) {
            return false;
        }

        return RemoteApiSource::query()
            ->where('sourceable_type', $this->getMorphClass())
            ->where('sourceable_id', $this->getKey())
            ->exists();
    }

    protected function registerRemoteApiVirtualAttribute(string $key, mixed $value): void
    {
        $this->remoteApiVirtualKeys[$key] = true;
        $this->attributes[$key] = $value;
    }

    protected function isRemoteApiVirtualDirty(string $key): bool
    {
        if (! isset($this->remoteApiVirtualKeys[$key])) {
            return false;
        }

        $current = $this->attributes[$key] ?? null;
        $original = $this->original[$key] ?? null;

        return $current !== $original;
    }

    protected function isRemoteApiProtectedAttribute(string $key): bool
    {
        return in_array($key, $this->getRemoteApiReservedKeys(), true);
    }

    /**
     * @return array<int, string>
     */
    protected function getRemoteApiReservedKeys(): array
    {
        return array_merge(
            array_keys($this->getAttributes()),
            ['remoteApiSource', 'remote_api_source'],
        );
    }
}
