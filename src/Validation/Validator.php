<?php

namespace Unusualify\Modularous\Validation;

use Illuminate\Validation\Validator as IlluminateValidator;

/**
 * Aligns validation message placeholders with Modularous's Translator:
 * lang lines may use curly placeholders while Laravel's Validator only replaces colon placeholders.
 */
class Validator extends IlluminateValidator
{
    /**
     * {@inheritdoc}
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array<int, string> $parameters
     */
    public function makeReplacements($message, $attribute, $rule, $parameters)
    {
        $message = $this->normalizeCurlyValidationPlaceholdersToColons((string) $message);

        return parent::makeReplacements($message, $attribute, $rule, $parameters);
    }

    /**
     * Convert {min}, {attribute}, {first-index}, etc. to Laravel's :min, :attribute, :first-index.
     */
    protected function normalizeCurlyValidationPlaceholdersToColons(string $message): string
    {
        return (string) preg_replace_callback(
            '/\{([^}]+)\}/',
            static fn (array $matches) => ':' . $matches[1],
            $message
        );
    }
}
