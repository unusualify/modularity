<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\MediaLibrary;

use Unusualify\Modularous\Services\MediaLibrary\ImageService;
use Unusualify\Modularous\Tests\TestCase;

class ImageServiceTest extends TestCase
{
    public function test_facade_accessor_points_to_image_service_binding(): void
    {
        $method = new \ReflectionMethod(ImageService::class, 'getFacadeAccessor');
        $method->setAccessible(true);

        $this->assertSame('imageService', $method->invoke(null));
    }
}
