<?php

namespace Modules\SourceModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelationshipSourceModel extends Model
{
    protected $table = 'relationship_source_models';

    public $timestamps = false;

    public static ?self $findResult = null;

    public static function find($id, $columns = ['*'])
    {
        return static::$findResult;
    }

    public function getEloquentRelationships(): array
    {
        return [
            'dependentItems' => [
                'relationship_class' => DependentTargetModel::class,
            ],
        ];
    }

    public function dependentItems(): HasMany
    {
        return $this->hasMany(DependentTargetModel::class, 'relationship_source_model_id');
    }
}
