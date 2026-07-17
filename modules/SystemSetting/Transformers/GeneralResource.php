<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class GeneralResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $data = parent::toArray($request);

        if (isset($data['smtp']) && is_array($data['smtp']) && array_key_exists('password', $data['smtp'])) {
            $password = $data['smtp']['password'];
            $data['smtp']['password'] = ($password !== null && $password !== '') ? '********' : null;
        }

        foreach ((array) modularousConfig('system_settings.sensitive_keys', ['smtp.password']) as $dotKey) {
            if (Arr::has($data, $dotKey)) {
                $value = Arr::get($data, $dotKey);
                Arr::set($data, $dotKey, ($value !== null && $value !== '') ? '********' : null);
            }
        }

        return $data;
    }
}
