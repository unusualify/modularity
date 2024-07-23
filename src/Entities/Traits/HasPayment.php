<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Priceable\Traits\HasPriceable;

trait HasPayment
{
  // Will be defining the relation between the completed payment model and payable model
  
  public function price() : \Illuminate\Database\Eloquent\Relations\MorphOne
  {
    return $this->morphOne(config('priceable.models.price'), 'priceable');
  }


}
