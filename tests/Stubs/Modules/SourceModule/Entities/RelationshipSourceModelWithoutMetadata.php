<?php

namespace Modules\SourceModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Source model with an Eloquent relationship only — no {@see getEloquentRelationships()} metadata.
 */
class RelationshipSourceModelWithoutMetadata extends Model
{
    protected $table = 'relationship_source_models_without_metadata';

    public $timestamps = false;

    public static ?self $findResult = null;

    public static function find($id, $columns = ['*'])
    {
        return static::$findResult;
    }

    public function dependentItems(): HasMany
    {
        return $this->hasMany(DependentTargetModel::class, 'relationship_source_model_id');
    }
}
