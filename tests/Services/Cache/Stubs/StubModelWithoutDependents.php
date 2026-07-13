<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache\Stubs;

use Illuminate\Database\Eloquent\Model;

class StubModelWithoutDependents extends Model
{
    protected $table = 'stub_without_dependents';

    /** @var list<array<string, mixed>> */
    public array $cacheDependents = [];
}
