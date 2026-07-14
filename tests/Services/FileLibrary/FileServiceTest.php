<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\FileLibrary;

use Unusualify\Modularous\Services\FileLibrary\FileService;
use Unusualify\Modularous\Tests\TestCase;

class FileServiceTest extends TestCase
{
    public function test_facade_accessor_points_to_file_service_binding(): void
    {
        $method = new \ReflectionMethod(FileService::class, 'getFacadeAccessor');
        $method->setAccessible(true);

        $this->assertSame('fileService', $method->invoke(null));
    }
}
