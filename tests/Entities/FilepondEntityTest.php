<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities;

use Unusualify\Modularous\Entities\Filepond;
use Unusualify\Modularous\Tests\TestCase;

class FilepondEntityTest extends TestCase
{
    public function test_can_delete_safely_is_callable(): void
    {
        $filepond = new Filepond;

        $this->assertNull($filepond->canDeleteSafely());
    }
}
