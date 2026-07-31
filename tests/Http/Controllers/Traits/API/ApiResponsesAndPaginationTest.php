<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Unusualify\Modularous\Http\Controllers\Traits\API\ApiPagination;
use Unusualify\Modularous\Http\Controllers\Traits\API\ApiResponses;
use Unusualify\Modularous\Tests\TestCase;

class ApiResponsesAndPaginationTest extends TestCase
{
    /** @test */
    public function api_responses_cover_success_and_error_helpers(): void
    {
        $controller = new class
        {
            use ApiResponses;

            public function call(string $method, ...$args): JsonResponse
            {
                return $this->{$method}(...$args);
            }
        };

        $ok = $controller->call('respondWithMessage', 'Saved', ['id' => 1]);
        $this->assertTrue($ok->getData(true)['success']);
        $this->assertSame(['id' => 1], $ok->getData(true)['data']);

        $error = $controller->call('respondWithError', 'Nope', 400, ['field' => ['bad']]);
        $this->assertFalse($error->getData(true)['success']);
        $this->assertSame(['field' => ['bad']], $error->getData(true)['errors']);

        $this->assertSame(404, $controller->call('respondNotFound')->getStatusCode());
        $this->assertSame(422, $controller->call('respondWithValidationError', ['x' => ['y']])->getStatusCode());
        $this->assertSame(401, $controller->call('respondUnauthorized')->getStatusCode());
        $this->assertSame(403, $controller->call('respondForbidden')->getStatusCode());
    }

    /** @test */
    public function api_pagination_builds_metadata_links_and_transformed_response(): void
    {
        $controller = new class
        {
            use ApiPagination;

            public Request $request;

            public int $defaultPerPage = 15;

            public int $maxPerPage = 50;

            public function __construct()
            {
                $this->request = Request::create('/', 'GET', ['per_page' => 200]);
            }

            public function callGetPaginationMetadata($paginator): array
            {
                return $this->getPaginationMetadata($paginator);
            }

            public function callGetPerPage(?Request $request = null): int
            {
                return $this->getPerPage($request);
            }

            public function callGetPaginationLinks($paginator): array
            {
                return $this->getPaginationLinks($paginator);
            }

            public function callTransformPaginatedResponse($paginator): array
            {
                return $this->transformPaginatedResponse($paginator);
            }

            public function callGetCursorPaginationMetadata($paginator): array
            {
                return $this->getCursorPaginationMetadata($paginator);
            }
        };

        $this->assertSame([], $controller->callGetPaginationMetadata(collect([1])));
        $this->assertSame([], $controller->callGetPaginationLinks(collect([1])));
        $this->assertSame([], $controller->callGetCursorPaginationMetadata(collect([1])));
        $this->assertSame(50, $controller->callGetPerPage());
        $this->assertSame(10, $controller->callGetPerPage(Request::create('/', 'GET', ['per_page' => 10])));

        $paginator = new LengthAwarePaginator(range(1, 10), 25, 10, 2, [
            'path' => '/api/items',
        ]);
        $meta = $controller->callGetPaginationMetadata($paginator);
        $this->assertSame(25, $meta['pagination']['total']);
        $this->assertSame(2, $meta['pagination']['current_page']);

        $links = $controller->callGetPaginationLinks($paginator);
        $this->assertArrayHasKey('first', $links);
        $this->assertArrayHasKey('last', $links);

        $transformed = $controller->callTransformPaginatedResponse($paginator);
        $this->assertArrayHasKey('data', $transformed);
        $this->assertArrayHasKey('meta', $transformed);
        $this->assertArrayHasKey('links', $transformed);

        $cursorPaginator = new class
        {
            public function hasMorePages(): bool
            {
                return true;
            }

            public function perPage(): int
            {
                return 15;
            }

            public function nextCursor()
            {
                return new class
                {
                    public function encode(): string
                    {
                        return 'next';
                    }
                };
            }

            public function previousCursor()
            {
                return null;
            }
        };

        $cursorMeta = $controller->callGetCursorPaginationMetadata($cursorPaginator);
        $this->assertTrue($cursorMeta['cursor']['has_more_pages']);
        $this->assertSame('next', $cursorMeta['cursor']['next_cursor']);
    }
}
