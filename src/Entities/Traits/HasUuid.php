<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Str;
use Oobook\Database\Eloquent\Concerns\ManageEloquent;

trait HasUuid
{
    use ManageEloquent;

    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            $model->{static::getUuidColumn()} = (string) Str::orderedUuid();

        });
    }

    public static function getUuidColumn(): string
    {
        return static::$uuidColumn ?? 'uuid';
    }

    public function initializeHasUuid(): void
    {
        $columnTypes = $this->getColumnTypes();
        if (! isset($columnTypes[static::getUuidColumn()])) {
            throw new \Exception('Column ' . static::getUuidColumn() . ' not found in ' . static::class);
        }

        $columnType = $columnTypes[static::getUuidColumn()];
        if (! ($columnType == 'varchar' || $columnType == 'char')) {

        }

        if (! in_array($columnTypes[static::getUuidColumn()], ['varchar', 'char'])) {
            throw new \Exception('Column "' . static::getUuidColumn() . '" is not proper, because the column type is "' . $columnTypes[static::getUuidColumn()] . '" in ' . static::class);
        }

    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
