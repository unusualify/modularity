<?php

namespace Unusualify\Modularous\Tests\Exceptions;

use Illuminate\Http\JsonResponse;
use Unusualify\Modularous\Exceptions\ValidationException;
use Unusualify\Modularous\Tests\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function test_variant_returns_same_instance()
    {
        $exception = ValidationException::withMessages([
            'email' => ['The email field is required.'],
        ]);

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertSame($exception, $exception->variant());
    }

    public function test_variant_sets_json_response_with_default_variant()
    {
        $exception = ValidationException::withMessages([
            'email' => ['The email field is required.'],
        ]);

        $response = $exception->variant()->response;

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('The email field is required.', $data['message']);
        $this->assertEquals(['email' => ['The email field is required.']], $data['errors']);
        $this->assertEquals('error', $data['variant']);
    }

    public function test_variant_accepts_custom_variant()
    {
        $exception = ValidationException::withMessages([
            'name' => ['The name field is required.'],
        ]);

        $data = json_decode($exception->variant('warning')->response->getContent(), true);

        $this->assertEquals('warning', $data['variant']);
    }

    public function test_variant_uses_first_error_message_as_message()
    {
        $exception = ValidationException::withMessages([
            'email' => ['First email error.', 'Second email error.'],
            'name' => ['Name error.'],
        ]);

        $data = json_decode($exception->variant()->response->getContent(), true);

        $this->assertEquals('First email error.', $data['message']);
    }

    public function test_variant_falls_back_to_default_message_when_no_errors()
    {
        // No messages means errors() is empty, so summarizeErrors() hits its
        // default return branch.
        $exception = ValidationException::withMessages([]);

        $data = json_decode($exception->variant()->response->getContent(), true);

        $this->assertEquals(__('The given data was invalid.'), $data['message']);
        $this->assertEquals([], $data['errors']);
    }
}
