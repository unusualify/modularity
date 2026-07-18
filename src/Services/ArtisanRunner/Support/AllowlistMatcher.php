<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner\Support;

final class AllowlistMatcher
{
    /**
     * @param  list<string>  $patterns
     */
    public function matches(string $command, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern === '') {
                continue;
            }

            if ($pattern === $command) {
                return true;
            }

            if (! str_contains($pattern, '*')) {
                continue;
            }

            $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';

            if (preg_match($regex, $command) === 1) {
                return true;
            }
        }

        return false;
    }
}
