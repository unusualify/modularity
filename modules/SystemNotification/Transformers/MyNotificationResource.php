<?php

namespace Modules\SystemNotification\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class MyNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
