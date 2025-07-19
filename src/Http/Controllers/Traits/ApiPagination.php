<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait ApiPagination
{
    /**
     * Get pagination metadata
     *
     * @param mixed $paginator
     */
    protected function getPaginationMetadata($paginator): array
    {
        if (! method_exists($paginator, 'total')) {
            return [];
        }

        return [
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ];
    }

    /**
     * Get per page value from request with validation
     */
    protected function getPerPage(?Request $request = null): int
    {
        $request = $request ?: $this->request;
        $defaultPerPage = property_exists($this, 'defaultPerPage') ? $this->defaultPerPage : 15;
        $maxPerPage = property_exists($this, 'maxPerPage') ? $this->maxPerPage : 100;

        $perPage = $request->get('per_page', $defaultPerPage);

        return min((int) $perPage, $maxPerPage);
    }

    /**
     * Get pagination links
     *
     * @param mixed $paginator
     */
    protected function getPaginationLinks($paginator): array
    {
        if (! method_exists($paginator, 'url')) {
            return [];
        }

        $links = [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];

        return array_filter($links); // Remove null values
    }

    /**
     * Transform paginated response
     *
     * @param mixed $paginator
     */
    protected function transformPaginatedResponse($paginator): array
    {
        $response = [];

        if (method_exists($paginator, 'items')) {
            $response['data'] = $paginator->items();
        } else {
            $response['data'] = $paginator;
        }

        if (method_exists($paginator, 'total')) {
            $response['meta'] = $this->getPaginationMetadata($paginator);
            $response['links'] = $this->getPaginationLinks($paginator);
        }

        return $response;
    }

    /**
     * Get cursor pagination metadata
     *
     * @param mixed $paginator
     */
    protected function getCursorPaginationMetadata($paginator): array
    {
        if (! method_exists($paginator, 'hasMorePages')) {
            return [];
        }

        return [
            'cursor' => [
                'has_more_pages' => $paginator->hasMorePages(),
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
            ],
        ];
    }
}
