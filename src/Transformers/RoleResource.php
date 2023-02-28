<?php

namespace Unusual\CRM\Base\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        // dd(parent::toArray($request));
        return parent::toArray($request);
    }
}
