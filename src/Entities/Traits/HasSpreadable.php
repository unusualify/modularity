<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Oobook\Database\Eloquent\Concerns\ManageEloquent;

trait HasSpreadable
{
    use ManageEloquent;

    protected $spreadablePayload;

    protected $spreadableMutatorMethods = [];

    protected $spreadableMutatorAttributes = [];

    public static function bootHasSpreadable()
    {
        // TODO: Keep the old spreadable data from model and remove attributes based on that don't remove all column fields
        self::saving(static function (Model $model) {
            // Store the spread data before cleaning
            if (! $model->exists) {
                // Set property to preserve data through events
                $model->spreadablePayload = $model->{$model->getSpreadableSavingKey()} ?: $model->prepareSpreadableJson();
            } elseif ($model->{$model->getSpreadableSavingKey()}) {
                if(!$model->spreadable){
                    $model->spreadable()->create([
                        'content' => $model->{$model->getSpreadableSavingKey()},
                    ]);
                }else{
                    // Handle existing spread updates
                    $model->spreadable()->update([
                        'content' => $model->{$model->getSpreadableSavingKey()},
                        'updated_at' => now(),
                    ]);
                }
            }

            $model->offsetUnset($model->getSpreadableSavingKey());

            // $model->cleanSpreadableAttributes();
            // dd($model);
        });

        self::created(static function (Model $model) {
            $model->spreadable()->create([
                'content' => $model->spreadablePayload ?? [],
            ]);
            foreach ($model->spreadablePayload as $key => $value) {
                if (! $model->isProtectedAttribute($key)) {
                    $model->append($key);
                    $model->spreadableMutatorMethods['get' . Str::studly($key) . 'Attribute'] = $value;
                    $model->spreadableMutatorAttributes[$key] = $value;
                    $model->spreadableMutatorAttributes[Str::camel($key)] = $value;
                }
            }

        });

        self::retrieved(static function (Model $model) {
            // If there's a spread model, load its attributes
            if ($model->spreadable()->exists()) {
                $jsonData = $model->spreadable->content ?? [];

                // Set spreadable attributes on model, excluding protected attributes
                // dd($jsonData, $model);
                foreach ($jsonData as $key => $value) {
                    if (! $model->isProtectedAttribute($key)) {
                        $model->append($key);
                        $model->spreadableMutatorMethods['get' . Str::studly($key) . 'Attribute'] = $value;
                        $model->spreadableMutatorAttributes[$key] = $value;
                        $model->spreadableMutatorAttributes[Str::camel($key)] = $value;
                    }
                }

                // Set _spread attribute
                // $model->setAttribute($model->getSpreadableSavingKey(), $jsonData);

            } else {
                // Initialize empty _spread if no spreadable exists
                // $model->setAttribute($model->getSpreadableSavingKey(), []);
            }
        });

    }

    public function initializeHasSpreadable()
    {
        $this->mergeFillable([$this->getSpreadableSavingKey()]);

        // $this->append($this->getSpreadableSavingKey());
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
            $this->getTableColumns(),  // Using ManageEloquent's getTableColumns
            $this->definedRelations(), // Using ManageEloquent's definedRelations
            array_keys($this->getMutatedAttributes()),
            ['spreadable', '_spread']
        );
    }

    protected function prepareSpreadableJson(): array
    {
        $attributes = $this->getAttributes();
        $protectedKeys = array_merge(
            $this->getTableColumns(), // Using ManageEloquent's getTableColumns
            $this->definedRelations(), // Using ManageEloquent's definedRelations
            array_keys($this->getMutatedAttributes()),
            ['spreadable', $this->getSpreadableSavingKey()]
        );

        return array_diff_key(
            $attributes,
            array_flip($protectedKeys)
        );
    }

    protected function cleanSpreadableAttributes(): void
    {
        $columns = $this->getTableColumns(); // Using ManageEloquent's getTableColumns
        $attributes = $this->getAttributes();
        // TODO: Instead of removing any attribute remove the ones that you know that needs to be removed
        // Remove any attributes that aren't database columns

        // $this->spreadable->content ??= [];
        foreach ($attributes as $key => $value) {
            if (! in_array($key, $columns)) {
                unset($this->attributes[$key]);
            }
        }

    }

    final public static function getSpreadableSavingKey()
    {
        return static::$spreadableSavingKey ?? 'spread_payload';
    }

    public function __call($method, $arguments)
    {
        if (array_key_exists($method, $this->spreadableMutatorMethods)) {
            return $this->spreadableMutatorMethods[$method];
        }

        return parent::__call($method, $arguments);
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->spreadableMutatorAttributes)) {
            return $this->spreadableMutatorAttributes[$key];
        }

        return parent::__get($key);
    }
}
