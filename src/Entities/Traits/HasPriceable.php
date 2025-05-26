<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Request;
use Modules\SystemPricing\Entities\Price;
use Oobook\Priceable\Traits\HasPriceable as TraitsHasPriceable;
use Unusualify\Modularity\Entities\Mutators\HasPriceableMutators;

trait HasPriceable
{
    use TraitsHasPriceable,
        HasPriceableMutators;

    /**
     * Boot the trait.
     *
     * Sets up event listeners for model creation, updating, retrieval, and deletion.
     *
     * @return void
     */
    public static function bootHasPriceable()
    {
        // parent::bootHasPriceable();
    }

    public function prices(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function basePrice(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Price::class, 'priceable')
            ->where('currency_id', Request::getUserCurrency()->id);
    }

    public function scopeHasBasePrice($query)
    {
        return $query->whereHas('basePrice');
    }

    public function scopeOrderByCurrencyPrice($query, $currencyId, $direction = 'asc')
    {
        $table = $this->getTable();
        $priceTable = app(Price::class)->getTable();

        return $query->leftJoin($priceTable, function ($join) use ($table, $priceTable, $currencyId) {
            $join->on("{$priceTable}.priceable_id", '=', "{$table}.id")
                ->where("{$priceTable}.priceable_type", '=', get_class($this))
                ->where("{$priceTable}.currency_id", '=', $currencyId);
        })
            ->orderBy("{$priceTable}.raw_amount", $direction)
            ->select("{$table}.*"); // Ensure we only select fields from the main table
    }

    /**
     * Scope a query to order by the base price's raw_amount.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByBasePrice($query, $direction = 'asc')
    {
        return $query->orderByCurrencyPrice(Request::getUserCurrency()->id, $direction);
    }
}
