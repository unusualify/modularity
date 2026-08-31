<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Requests;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularous\Http\Requests\Request;
use Unusualify\Modularous\Tests\TestCase;

class ModularousRequestCoverageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(MessageStage::class)) {
            class_alias(
                \Unusualify\Modularous\Services\MessageStage::class,
                MessageStage::class
            );
        }

        config(['translatable.locales' => ['en', 'tr']]);
    }

    private function makeRequest(string $method = 'POST', array $rulesAll = [], ?object $model = null): Request
    {
        $request = new class($rulesAll, $model) extends Request
        {
            public array $allRules;

            public $forcedModel;

            public string $forcedMethod = 'POST';

            public function __construct(array $allRules, $forcedModel)
            {
                $this->allRules = $allRules;
                $this->forcedModel = $forcedModel;
                parent::__construct([]);
            }

            public function method($upper = true)
            {
                return $this->forcedMethod;
            }

            public function rulesForAll(): array
            {
                return $this->allRules;
            }

            public function rulesForCreate(): array
            {
                return ['title' => 'required'];
            }

            public function rulesForUpdate(): array
            {
                return ['title' => 'sometimes|required'];
            }

            public function model()
            {
                return $this->forcedModel;
            }
        };

        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        return $request;
    }

    /** @test */
    public function authorize_rules_by_method_and_delete_helpers(): void
    {
        $request = $this->makeRequest('POST', ['slug' => 'nullable']);
        $this->assertTrue($request->authorize());

        $request->forcedMethod = 'POST';
        $rules = $request->rules();
        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('slug', $rules);

        $request->forcedMethod = 'PUT';
        $this->assertArrayHasKey('title', $request->rules());

        $request->forcedMethod = 'DELETE';
        $this->assertSame([], $request->rules());
        $this->assertSame([], $request->rulesForDelete());

        $request->forcedMethod = 'GET';
        $this->assertSame([], $request->rules());
    }

    /** @test */
    public function prepare_for_validation_merges_route_id_on_delete(): void
    {
        $http = HttpRequest::create('/items/15', 'DELETE');
        $route = new Route('DELETE', 'items/{item}', static fn () => null);
        $route->bind($http);
        $route->setParameter('item', 15);
        $http->setRouteResolver(static fn () => $route);

        $form = Request::createFrom($http, $this->makeRequest());
        $form->forcedMethod = 'DELETE';
        $form->setContainer($this->app);
        $form->setRedirector($this->app->make('redirect'));

        $method = new \ReflectionMethod(Request::class, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($form);

        $this->assertSame(15, $form->input('id'));
    }

    /** @test */
    public function failed_validation_returns_json_for_delete(): void
    {
        $http = HttpRequest::create('/items/1', 'DELETE', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $form = Request::createFrom($http, $this->makeRequest());
        $form->forcedMethod = 'DELETE';
        $form->setContainer($this->app);
        $form->setRedirector($this->app->make('redirect'));

        $validator = $this->app['validator']->make([], ['id' => 'required']);
        $method = new \ReflectionMethod(Request::class, 'failedValidation');
        $method->setAccessible(true);

        try {
            $method->invoke($form, $validator);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $this->assertArrayHasKey('variant', $e->getResponse()->getData(true));
        }
    }

    /** @test */
    public function merge_hydrate_translated_rules_and_messages(): void
    {
        $model = new class
        {
            public function isTranslatable(): bool
            {
                return true;
            }

            public function getTranslatedAttributes(): array
            {
                return ['title'];
            }

            public function getTable(): string
            {
                return 'posts';
            }

            public function getTranslationModelName(): string
            {
                return CoverageTranslationModel::class;
            }

            public function getTranslationRelationKey(): string
            {
                return 'post_id';
            }
        };

        $http = HttpRequest::create('/posts', 'POST', [
            'id' => 3,
            'languages' => [
                ['value' => 'en', 'published' => true],
                ['value' => 'tr', 'published' => false],
            ],
        ]);
        $request = Request::createFrom($http, $this->makeRequest('POST', [
            'title' => 'required|unique_table',
            'slug' => 'nullable|unique_table',
            'body' => 'nullable',
        ], $model));
        $request->forcedModel = $model;
        $request->forcedMethod = 'POST';
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        $merged = $request->mergeRules([
            'title' => 'required',
            'slug' => 'nullable|unique_table',
            'body' => 'nullable',
        ]);

        $this->assertArrayHasKey('title.en', $merged);
        $this->assertArrayHasKey('title.tr', $merged);
        $this->assertArrayHasKey('slug', $merged);
        $this->assertStringContainsString('unique:posts,slug,3', $merged['slug']);

        $hydrated = $request->hydrateRules([
            'code' => 'required|unique_table',
            'plain' => ['required'],
        ]);
        $this->assertStringContainsString('unique:posts,code,3', $hydrated['code']);
        $this->assertSame(['required'], $hydrated['plain']);

        $translated = (new \ReflectionMethod(Request::class, 'rulesForTranslatedFields'))
            ->invoke($request, [], [
                'title' => 'required|required_with:slug',
                'slug' => 'nullable',
            ]);
        $this->assertArrayHasKey('title.en', $translated);
        $this->assertArrayHasKey('title.tr', $translated);

        $noLangHttp = HttpRequest::create('/posts', 'POST', ['id' => 1]);
        $noLang = Request::createFrom($noLangHttp, $this->makeRequest('POST', [], $model));
        $noLang->forcedModel = $model;
        $noLang->setContainer($this->app);
        $fallback = (new \ReflectionMethod(Request::class, 'rulesForTranslatedFields'))
            ->invoke($noLang, [], ['title' => 'required']);
        $this->assertArrayHasKey('title.en', $fallback);

        $messages = (new \ReflectionMethod(Request::class, 'messagesForTranslatedFields'))
            ->invoke($request, [], ['title.required' => 'Title required for {lang}']);
        $this->assertSame('Title required for en', $messages['title.en.required']);
        $this->assertSame('Title required for tr', $messages['title.tr.required']);

        $schema = (new \ReflectionMethod(Request::class, 'mergeSchemaRules'))
            ->invoke($request, ['a' => 'required']);
        $this->assertSame(['a' => 'required'], $schema);
    }
}

class CoverageTranslationModel
{
    public static function query()
    {
        return new class
        {
            public function whereNot($col, $id)
            {
                return $this;
            }

            public function where($col, $val)
            {
                return $this;
            }

            public function get()
            {
                return collect([(object) ['id' => 1]]);
            }
        };
    }
}
