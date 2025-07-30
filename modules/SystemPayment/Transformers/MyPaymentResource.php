<?php

namespace Modules\SystemPayment\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class MyPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
