<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ApiValidation
{
    /**
     * Validate API request with custom rules
     *
     * @param array $rules
     * @param array $messages
     * @return array
     */
    protected function validateApi(array $rules, array $messages = []): array
    {
        try {
            return $this->request->validate($rules, $messages);
        } catch (ValidationException $e) {
            throw new ValidationException($e->validator, $this->respondWithValidationError($e->errors()));
        }
    }

    /**
     * Validate pagination parameters
     *
     * @return array
     */
    protected function validatePagination(): array
    {
        $maxPerPage = property_exists($this, 'maxPerPage') ? $this->maxPerPage : 100;

        return $this->validateApi([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:' . $maxPerPage,
        ]);
    }

    /**
     * Validate includes parameter with relationship validation
     *
     * @return array
     */
    protected function validateIncludes(): array
    {
        $validated = $this->validateApi([
            'include' => ['sometimes', function($attribute, $value, $fail) {
                if (!is_string($value) && !is_array($value)) {
                    $fail('The '.$attribute.' must be either a string or an array.');
                }

                $includes = is_string($value) ? explode(',', $value) : $value;
                foreach ($includes as $include) {
                    $include = trim($include);

                    if(!in_array($include, $this->availableIncludes)) {
                        $fail('The '.$attribute.' contains invalid include: '.$include);
                    }
                }
            }],
        ]);

        return $validated;
    }

    /**
     * Validate relationship constraint format
     *
     * @param string $include
     * @return void
     */
    protected function validateRelationshipConstraint(string $include): void
    {
        if (strpos($include, ':') === false) {
            return;
        }

        [$relationship, $constraint] = explode(':', $include, 2);

        // Check if relationship is allowed
        if (method_exists($this, 'isRelationshipAllowed') && !$this->isRelationshipAllowed($relationship)) {
            throw ValidationException::withMessages([
                'include' => ["The relationship '{$relationship}' is not allowed."]
            ]);
        }

        // Validate constraint format
        $constraintParts = explode(',', $constraint);
        $method = $constraintParts[0] ?? '';

        switch ($method) {
            case 'limit':
                if (!isset($constraintParts[1]) || !is_numeric($constraintParts[1])) {
                    throw ValidationException::withMessages([
                        'include' => ["Invalid limit constraint for relationship '{$relationship}'. Expected format: 'limit:number'"]
                    ]);
                }
                break;

            case 'where':
                if (count($constraintParts) < 3) {
                    throw ValidationException::withMessages([
                        'include' => ["Invalid where constraint for relationship '{$relationship}'. Expected format: 'where:column,value'"]
                    ]);
                }
                break;

            case 'orderBy':
                if (!isset($constraintParts[1])) {
                    throw ValidationException::withMessages([
                        'include' => ["Invalid orderBy constraint for relationship '{$relationship}'. Expected format: 'orderBy:column,direction'"]
                    ]);
                }
                $direction = $constraintParts[2] ?? 'asc';
                if (!in_array(strtolower($direction), ['asc', 'desc'])) {
                    throw ValidationException::withMessages([
                        'include' => ["Invalid orderBy direction for relationship '{$relationship}'. Must be 'asc' or 'desc'"]
                    ]);
                }
                break;

            default:
                throw ValidationException::withMessages([
                    'include' => ["Invalid constraint method '{$method}' for relationship '{$relationship}'. Supported methods: limit, where, orderBy"]
                ]);
        }
    }
}
