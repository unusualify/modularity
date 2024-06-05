<?php

namespace Modules\SystemPricing\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PriceTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
