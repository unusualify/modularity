<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache\Stubs;

use Illuminate\Database\Eloquent\Model;

class StubModelWithPropertyDependents extends Model
{
    protected $table = 'stub_with_property_dependents';

    /** @var list<array<string, mixed>> */
    public array $cacheDependents = [
        [
            'moduleName' => 'Other',
            'moduleRouteName' => 'OtherRoute',
        ],
    ];
}
