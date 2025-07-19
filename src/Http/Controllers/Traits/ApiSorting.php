<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

trait ApiSorting
{
    /**
     * Available sort columns
     *
     * @var array
     */
    protected $availableSorts = ['id', 'created_at', 'updated_at'];

    /**
     * Default sort column
     *
     * @var string
     */
    protected $defaultSort = 'created_at';

    /**
     * Default sort direction
     *
     * @var string
     */
    protected $defaultSortDirection = 'desc';

    /**
     * Get sorts from request
     */
    protected function getSorts(): array
    {
        $sorts = [];

        $sortBy = $this->request->get('sort', $this->defaultSort);
        $sortDirection = $this->request->get('direction', $this->defaultSortDirection);

        // Validate sort column
        if (in_array($sortBy, $this->availableSorts)) {
            $sorts[$sortBy] = mb_strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        } else {
            $sorts[$this->defaultSort] = $this->defaultSortDirection;
        }

        return $sorts;
    }

    /**
     * Validate sorting parameters from request
     */
    protected function validateSorting(): array
    {
        $rules = [
            'sort' => 'string|in:' . implode(',', $this->availableSorts),
            'direction' => 'string|in:asc,desc',
        ];

        return $this->validateApi($rules);
    }
}
