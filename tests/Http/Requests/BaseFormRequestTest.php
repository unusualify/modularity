<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Unusualify\Modularous\Http\Requests\BaseFormRequest;
use Unusualify\Modularous\Tests\TestCase;

class BaseFormRequestTest extends TestCase
{
    /** @test */
    public function it_authorizes_and_routes_rules_by_http_method(): void
    {
        $request = new class extends BaseFormRequest
        {
            public string $forcedMethod = 'GET';

            public function method($upper = true)
            {
                return $this->forcedMethod;
            }
        };

        $this->assertTrue($request->authorize());
        $this->assertSame([], $request->view());
        $this->assertSame([], $request->store());
        $this->assertSame([], $request->update());
        $this->assertSame([], $request->destroy());

        $request->forcedMethod = 'POST';
        $this->assertSame([], $request->rules());

        $request->forcedMethod = 'PUT';
        $this->assertSame([], $request->rules());

        $request->forcedMethod = 'PATCH';
        $this->assertSame([], $request->rules());

        $request->forcedMethod = 'DELETE';
        $this->assertSame([], $request->rules());

        $request->forcedMethod = 'GET';
        $this->assertSame([], $request->rules());
    }

    /** @test */
    public function failed_validation_returns_json_or_redirect_response(): void
    {
        $jsonRequest = Request::create('/x', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $form = BaseFormRequest::createFrom($jsonRequest, new BaseFormRequest);
        $form->setContainer($this->app);
        $form->setRedirector($this->app->make('redirect'));

        $validator = $this->app['validator']->make([], ['name' => 'required']);
        $method = new \ReflectionMethod(BaseFormRequest::class, 'failedValidation');
        $method->setAccessible(true);

        try {
            $method->invoke($form, $validator);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(400, $e->getResponse()->getData(true)['status']);
        }

        $webRequest = Request::create('/x', 'POST');
        $webForm = BaseFormRequest::createFrom($webRequest, new BaseFormRequest);
        $webForm->setContainer($this->app);
        $webForm->setRedirector($this->app->make('redirect'));

        try {
            $method->invoke($webForm, $validator);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertTrue($e->getResponse()->isRedirect());
        }
    }
}
