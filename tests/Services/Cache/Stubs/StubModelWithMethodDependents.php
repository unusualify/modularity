<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache\Stubs;

use Illuminate\Database\Eloquent\Model;

class StubModelWithMethodDependents extends Model
{
    protected $table = 'stub_with_method_dependents';

    /**
     * @return list<array<string, mixed>>
     */
    public function getCacheDependents(): array
    {
        return [
            [
                'moduleName' => 'FromMethod',
                'moduleRouteName' => 'Route',
            ],
        ];
    }
}
