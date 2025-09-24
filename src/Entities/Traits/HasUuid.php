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
            $model->{static::getUuidColumn()} ??= (string) Str::orderedUuid();
        });
    }

    public function initializeHasUuid(): void
    {
        // $uuidColumn = static::getUuidColumn();
        // $columnTypes = $this->getTableColumnTypes();

        // if (! isset($columnTypes[$uuidColumn])) {
        //     throw new \Exception('Column ' . $uuidColumn . ' not found in ' . static::class);
        // }

        // if (! in_array($columnTypes[$uuidColumn], ['varchar', 'char'])) {
        //     throw new \Exception('Column "' . $uuidColumn . '" is not proper, because the column type is "' . $columnTypes[$uuidColumn] . '" in ' . static::class);
        // }
    }

    public static function getUuidColumn(): string
    {
        return static::$uuidColumn ?? 'id';
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
