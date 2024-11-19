<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Request;
use Money\Currency;
use Oobook\Database\Eloquent\Concerns\ManageEloquent;
use Oobook\Priceable\Models\Price;
use Unusualify\Modularity\Traits\ManageModuleRoute;

trait ModelHelpers
{
    use ManageEloquent, ManageModuleRoute, HasScopes;

    /**
     * Boot the trait.
     *
     * Sets up event listeners for model creation, updating, retrieval, and deletion.
     *
     * @return void
     */
    public static function bootModelHelpers()
    {
        static::retrieved(function ($model) {
            if (in_array('Oobook\Priceable\Traits\HasPriceable', class_uses_recursive($model)) && $model->price) {
                $basePrice = $model->basePrice;
                $basePriceFormatted = null;
                if ($basePrice) {
                    $currency = new Currency($basePrice->currency->iso_4217);
                    $basePriceFormatted = \Oobook\Priceable\Facades\PriceService::formatAmount($basePrice->display_price, $currency);
                }
                $model->setAttribute('base_price_show', $basePriceFormatted);
            }
        });
        static::saving(function ($model) {
            if (isset($model->base_price_show)) {
                $model->offsetUnset('base_price_show');
            }
        });
    }

    /**
     * Checks if this model is soft deletable.
     *
     * @param array|string|null $columns Optionally limit the check to a set of columns.
     */
    public function isSoftDeletable(): bool
    {
        // Model must have the trait
        if (! classHasTrait($this, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            return false;
        }

        return true;
    }

    public function getTitleField()
    {
        return $this->{$this->getRouteTitleColumnKey()};
    }

    public function getShowFormat()
    {
        return $this->{$this->getRouteTitleColumnKey()};
    }

    public function setRelationsShowFormat()
    {
        foreach ($this->getRelations() as $relationName => $relation) {

            if ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
                // dd($this, $relationName, $this->getRelations());
                $this->{$relationName} = $relation->map(function ($related) {

                    if (method_exists($related, 'setRelationsShowFormat')) {
                        $related->setRelationsShowFormat();
                    }

                    return $related;
                });

                $this["{$relationName}_show"] ??= $this->{$relationName}->map(fn ($model) => modelShowFormat($model))->implode(', ');

            } elseif ($relation) {

                if (method_exists($relation, 'setRelationsShowFormat')) {
                    $relation->setRelationsShowFormat();
                }

                // $this->{$relationName} = $relation;

                $this["{$relationName}_show"] ??= modelShowFormat($relation);

            }
        }
    }

    public function basePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        // dd(Request::getUserCurrency());
        // return $this->prices()->where('currency_id', Request::getUserCurrency()->id);
        return $this->morphOne(Price::class, 'priceable')->where('currency_id', Request::getUserCurrency()->id);
    }
}
