<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Unusualify\Modularous\Http\Controllers\Auth\Controller;
use Unusualify\Modularous\Http\Controllers\Traits\Utilities\RespondsWithJsonOrRedirect;
use Unusualify\Modularous\Tests\TestCase;

/**
 * Test controller that uses RespondsWithJsonOrRedirect for testing.
 */
class TestRespondsController extends Controller
{
    use RespondsWithJsonOrRedirect;

    public function callSendSuccessResponse(Request $request, string $message, string $redirectUrl, array $data = [])
    {
        return $this->sendSuccessResponse($request, $message, $redirectUrl, $data);
    }

    public function callSendFailedResponse(Request $request, string $message, string $field = 'email', int $jsonStatus = 200)
    {
        return $this->sendFailedResponse($request, $message, $field, $jsonStatus);
    }

    public function callSendValidationFailedResponse(Request $request, $validator)
    {
        return $this->sendValidationFailedResponse($request, $validator);
    }
}

class RespondsWithJsonOrRedirectTest extends TestCase
{
    protected TestRespondsController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TestRespondsController;
    }

    /** @test */
    public function it_returns_json_success_response_when_request_wants_json(): void
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->callSendSuccessResponse(
            $request,
            'Success message',
            'https://example.com/redirect'
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Success message', $data['message']);
        $this->assertEquals('https://example.com/redirect', $data['redirector']);
    }

    /** @test */
    public function it_returns_redirect_response_when_request_does_not_want_json(): void
    {
        $request = Request::create('/test', 'POST');

        $response = $this->controller->callSendSuccessResponse(
            $request,
            'Success message',
            'https://example.com/redirect'
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://example.com/redirect', $response->getTargetUrl());
    }

    /** @test */
    public function it_returns_json_failed_response_when_request_wants_json(): void
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->callSendFailedResponse(
            $request,
            'Error message',
            'email'
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Error message', $data['message']);
        $this->assertArrayHasKey('email', $data);
        $this->assertEquals(['Error message'], $data['email']);
    }

    /** @test */
    public function it_returns_redirect_with_errors_when_request_does_not_want_json(): void
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('Accept', 'text/html');

        $response = $this->controller->callSendFailedResponse(
            $request,
            'Error message',
            'email'
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirection());
    }

    /** @test */
    public function it_returns_json_validation_failed_response_when_request_wants_json(): void
    {
        $request = Request::create('/test', 'POST', ['email' => 'invalid']);
        $request->headers->set('Accept', 'application/json');

        $validator = Validator::make(
            ['email' => ''],
            ['email' => 'required']
        );

        $response = $this->controller->callSendValidationFailedResponse($request, $validator);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
    }

    /** @test */
    public function it_returns_redirect_validation_failed_response_when_request_does_not_want_json(): void
    {
        $request = Request::create('/test', 'POST', ['email' => '']);
        $request->headers->set('Accept', 'text/html');

        $validator = Validator::make(
            ['email' => ''],
            ['email' => 'required']
        );

        $response = $this->controller->callSendValidationFailedResponse($request, $validator);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirection());
    }
}
