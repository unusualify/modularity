<?php

namespace Unusualify\Modularous\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;

abstract class Resource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            ...parent::toArray($request),
            ...$this->mergeResource($request),
        ];
    }

    /**
     * Extra fields merged on top of the underlying payload (model or array).
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    protected function mergeResource($request): array
    {
        return [];
    }

    /**
     * Read a value from the underlying resource (works for models and arrays).
     */
    protected function value(string $key, mixed $default = null): mixed
    {
        return data_get($this->resource, $key, $default);
    }
}
