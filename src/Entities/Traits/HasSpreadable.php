<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Oobook\Database\Eloquent\Concerns\ManageEloquent;

trait HasSpreadable
{
    use ManageEloquent;

    protected $_pendingSpreadData;

    public static function bootHasSpreadable()
    {
        // TODO: Keep the old spreadable data from model and remove attributes based on that don't remove all column fields
        self::saving(static function (Model $model) {
            // Store the spread data before cleaning
            if (! $model->exists) {
                // Set property to preserve data through events
                $model->_pendingSpreadData = $model->_spread ?: $model->prepareSpreadableJson();
            } elseif ($model->_spread) {
                // Handle existing spread updates
                $model->spreadable()->update([
                    'content' => $model->_spread,
                ]);
            }

            $model->cleanSpreadableAttributes();
        });

        self::created(static function (Model $model) {
            $model->spreadable()->create($model->_pendingSpreadData ?? []);
        });

        self::retrieved(static function (Model $model) {
            // If there's a spread model, load its attributes
            // dd('text');
            // dd($model);
            if ($model->spreadable) {
                $jsonData = $model->spreadable->content ?? [];

                // Set spreadable attributes on model, excluding protected attributes
                foreach ($jsonData as $key => $value) {
                    if (! $model->isProtectedAttribute($key)) {
                        $model->setAttribute($key, $value);
                    }
                }

                // Set _spread attribute
                $model->setAttribute('_spread', $jsonData);
                // dd($model->_spread);

            } else {
                // dd('here');
                // Initialize empty _spread if no spreadable exists
                $model->setAttribute('_spread', []);
            }
        });

    }

    public function initializeHasSpreadable()
    {
        $this->mergeFillable(['_spread']);
    }

    // TODO: rename relation to spread as well
    public function spreadable(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(\Unusualify\Modularity\Entities\Spread::class, 'spreadable');
    }

    protected function isProtectedAttribute(string $key): bool
    {

        return in_array($key, $this->getReservedKeys());
    }

    public function getReservedKeys(): array
    {

        return array_merge(
            $this->getColumns(),  // Using ManageEloquent's getColumns
            $this->definedRelations(), // Using ManageEloquent's definedRelations
            array_keys($this->getMutatedAttributes()),
            ['spreadable', '_spread']
        );
    }

    protected function prepareSpreadableJson(): array
    {
        $attributes = $this->getAttributes();
        $protectedKeys = array_merge(
            $this->getColumns(), // Using ManageEloquent's getColumns
            $this->definedRelations(), // Using ManageEloquent's definedRelations
            array_keys($this->getMutatedAttributes()),
            ['spreadable', '_spread']
        );

        return array_diff_key(
            $attributes,
            array_flip($protectedKeys)
        );
    }

    protected function cleanSpreadableAttributes(): void
    {
        $columns = $this->getColumns();
        $attributes = $this->getAttributes();
        // TODO: Instead of removing any attribute remove the ones that you know that needs to be removed
        // Remove any attributes that aren't database columns
        foreach ($attributes as $key => $value) {
            if (! in_array($key, $columns)) {
                unset($this->attributes[$key]);
            }
        }

    }
}
