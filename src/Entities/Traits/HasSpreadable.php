<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Oobook\Database\Eloquent\Concerns\ManageEloquent;

trait HasSpreadable
{
    use ManageEloquent;

    protected $_pendingSpreadData = [];



    public static function bootHasSpreadable()
    {
        self::saving(static function(Model $model) {

            if (!$model->exists) {
                // For new models, preserve the spread data for after creation.
                // dd($model);
                // dd($model->attributesToArray(), $model->fillable);
                $model->_pendingSpreadData = array_merge($model->_pendingSpreadData, $model->_spread);
                // dd($model, $model->_spread, $model->_pendingSpreadData);
                // dd($model->attributesToArray());
                // dd($model->_pendingSpreadData);
            } else if($model->_spread){
                // For existing models, rebuild the _spread attribute from the spreadable fillable inputs,
                // and clean those fillables from the model.
                // dd($model);

                // $spreadData = $model->updateSpreadableFromFillable();
                // Update the related spread record with the rebuilt spread data.
                // dd($model);
                // dd($model->attributesToArray(), $model->fillable);

                $model->spreadable()->update([
                    'content' => $model->_spread
                ]);
            }
            $model->offsetUnset('_spread');

            // Clean any remaining spreadable attributes from the main model.
            // $model->cleanSpreadableAttributes();
        });

        self::created(static function(Model $model) {
            $model->spreadable()->create([
                'content' => $model->_pendingSpreadData ?? []
            ]);
        });

        self::retrieved(static function(Model $model) {
            if ($model->spreadable) {
                $jsonData = $model->spreadable->content ?? [];
                foreach ($jsonData as $key => $value) {
                    if (! $model->isProtectedAttribute($key)) {
                        $model->setAttribute($key, $value);
                    }
                }
                $model->setAttribute('_spread', $jsonData);
            } else {
                $model->setAttribute('_spread', []);
            }
        });
    }

    public function initializeHasSpreadable()
    {
        $spreadableFields = ['_spread'];
        $this->mergeFillable($spreadableFields);
    }

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
}
