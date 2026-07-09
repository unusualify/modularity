<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Unusualify\Modularous\Exceptions\ValidationException;
use Unusualify\Modularous\Services\ValidationExceptionFactory;
use Unusualify\Modularous\Tests\TestCase;

class ValidationExceptionFactoryTest extends TestCase
{
    public function test_with_messages_returns_variant_exception(): void
    {
        $exception = (new ValidationExceptionFactory)->withMessages([
            'email' => ['The email field is required.'],
        ]);

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertSame(422, $exception->response->getStatusCode());

        $payload = $exception->response->getData(true);
        $this->assertSame('The email field is required.', $payload['message']);
        $this->assertSame('error', $payload['variant']);
        $this->assertSame(['email' => ['The email field is required.']], $payload['errors']);
    }
}
