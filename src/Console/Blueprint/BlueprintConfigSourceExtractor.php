<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint;

/**
 * Pulls raw PHP array literals from a module Config/config.php so Blueprint
 * stubs keep `__()` / `Component::` / short-array formatting from source.
 */
final class BlueprintConfigSourceExtractor
{
    /**
     * @return string|null Array literal including outer `[` `]`, or null if not found
     */
    public function extract(
        string $configPath,
        string $routeSnake,
        string $legacyKey,
        ?string $nestedKey = null,
    ): ?string {
        if (! is_file($configPath)) {
            return null;
        }

        $src = (string) file_get_contents($configPath);
        $route = $this->locateRouteArray($src, $routeSnake);
        if ($route === null) {
            return null;
        }

        $routeLiteral = $route['literal'];

        if (is_string($nestedKey) && $nestedKey !== '' && str_contains($nestedKey, '.')) {
            [$surface, $leaf] = explode('.', $nestedKey, 2);
            $surfaceHit = $this->locateKeyedArray($routeLiteral, $surface);
            if ($surfaceHit !== null) {
                $nestedHit = $this->locateKeyedArray($surfaceHit['literal'], $leaf);
                // Nested class / driver meta is wiring, not a seedable payload.
                if ($nestedHit !== null && ! $this->literalLooksLikeProviderMeta($nestedHit['literal'])) {
                    return $this->normalizeLiteral($nestedHit['literal']);
                }
            }
        }

        // Prefer a real payload array. Skip legacy `blueprint.inputs` / `blueprint.headers`
        // driver-meta blocks that appear before flat `inputs` / `headers` in source order.
        $flat = $this->locateKeyedPayloadArray($routeLiteral, $legacyKey);

        return $flat !== null ? $this->normalizeLiteral($flat) : null;
    }

    /**
     * Find `'key' => [ … ]` that is a field payload, not `{ driver, class }` meta.
     */
    public function locateKeyedPayloadArray(string $haystack, string $key): ?string
    {
        $pattern = '/[\'"]' . preg_quote($key, '/') . '[\'"]\s*=>\s*/';
        $offset = 0;

        while (preg_match($pattern, $haystack, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $matchStart = $match[0][1];
            $matchLen = strlen($match[0][0]);

            if ($this->isInLineComment($haystack, $matchStart)) {
                $offset = $matchStart + $matchLen;

                continue;
            }

            $valueStart = $matchStart + $matchLen;
            $literal = $this->readArrayLiteral($haystack, $valueStart);
            if ($literal !== null && ! $this->literalLooksLikeProviderMeta($literal)) {
                return $literal;
            }

            $offset = $matchStart + $matchLen;
        }

        return null;
    }

    /**
     * True when a value literal is provider/driver meta, not a field payload array.
     */
    private function literalLooksLikeProviderMeta(string $literal): bool
    {
        $trimmed = trim($literal);

        if (str_ends_with($trimmed, '::class')) {
            return true;
        }

        // `{ 'driver' => …, 'class' => … }` (order of keys may vary).
        if (preg_match('/^\s*\[\s*[\'"](?:driver|class)[\'"]\s*=>/s', $trimmed) === 1) {
            return true;
        }

        return preg_match('/^\s*array\s*\(\s*[\'"](?:driver|class)[\'"]\s*=>/s', $trimmed) === 1;
    }

    /**
     * Locate `'routes' => [ ... 'route' => [ ... ] ... ]` route array in a config file body.
     *
     * @return array{key_start: int, value_start: int, value_end: int, literal: string}|null
     */
    public function locateRouteArray(string $src, string $routeSnake): ?array
    {
        $routes = $this->locateKeyedArray($src, 'routes');
        if ($routes === null) {
            return null;
        }

        $relative = $this->locateKeyedArray($routes['literal'], $routeSnake);
        if ($relative === null) {
            return null;
        }

        $valueStart = $routes['value_start'] + $relative['value_start'];
        $valueEnd = $routes['value_start'] + $relative['value_end'];
        $keyStart = $routes['value_start'] + $relative['key_start'];

        return [
            'key_start' => $keyStart,
            'value_start' => $valueStart,
            'value_end' => $valueEnd,
            'literal' => substr($src, $valueStart, $valueEnd - $valueStart),
        ];
    }

    /**
     * @return array{key_start: int, value_start: int, value_end: int, literal: string}|null
     */
    public function locateKeyedArray(string $haystack, string $key): ?array
    {
        $entry = $this->locateKeyedEntry($haystack, $key);
        if ($entry === null) {
            return null;
        }

        $literal = $this->readArrayLiteral($haystack, $entry['value_start']);
        if ($literal === null) {
            return null;
        }

        return [
            'key_start' => $entry['key_start'],
            'value_start' => $entry['value_start'],
            'value_end' => $entry['value_start'] + strlen($literal),
            'literal' => $literal,
        ];
    }

    /**
     * Locate `'key' => <value>` where value is an array literal or a single expression
     * (e.g. `\Foo::class`) ending at the next top-level comma or newline before `]`.
     *
     * @return array{key_start: int, value_start: int, value_end: int, value: string}|null
     */
    public function locateKeyedEntry(string $haystack, string $key): ?array
    {
        $pattern = '/[\'"]' . preg_quote($key, '/') . '[\'"]\s*=>\s*/';
        $offset = 0;

        while (preg_match($pattern, $haystack, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $matchStart = $match[0][1];
            $matchLen = strlen($match[0][0]);

            if ($this->isInLineComment($haystack, $matchStart)) {
                $offset = $matchStart + $matchLen;

                continue;
            }

            $valueStart = $matchStart + $matchLen;
            while ($valueStart < strlen($haystack) && ctype_space($haystack[$valueStart])) {
                $valueStart++;
            }

            $arrayLiteral = $this->readArrayLiteral($haystack, $valueStart);
            if ($arrayLiteral !== null) {
                return [
                    'key_start' => $matchStart,
                    'value_start' => $valueStart,
                    'value_end' => $valueStart + strlen($arrayLiteral),
                    'value' => $arrayLiteral,
                ];
            }

            $expr = $this->readExpression($haystack, $valueStart);
            if ($expr !== null) {
                return [
                    'key_start' => $matchStart,
                    'value_start' => $valueStart,
                    'value_end' => $valueStart + strlen($expr),
                    'value' => $expr,
                ];
            }

            $offset = $matchStart + $matchLen;
        }

        return null;
    }

    /**
     * True when $pos sits on a // line comment (config.php style).
     */
    public function isInLineComment(string $src, int $pos): bool
    {
        $lineStart = strrpos(substr($src, 0, $pos), "\n");
        $lineStart = $lineStart === false ? 0 : $lineStart + 1;
        $before = substr($src, $lineStart, max(0, $pos - $lineStart));

        // Strip string literals on the line so // inside quotes does not count.
        $before = preg_replace("/'(?:\\\\'|[^'])*'/", "''", $before) ?? $before;
        $before = preg_replace('/"(?:\\\\"|[^"])*"/', '""', $before) ?? $before;

        return str_contains($before, '//');
    }

    public function readArrayLiteral(string $src, int $offset): ?string
    {
        $len = strlen($src);
        while ($offset < $len && ctype_space($src[$offset])) {
            $offset++;
        }

        if ($offset >= $len) {
            return null;
        }

        if ($src[$offset] === '[') {
            return $this->readBalanced($src, $offset, '[', ']');
        }

        if (preg_match('/\Garray\s*\(/A', $src, $m, 0, $offset) === 1) {
            $parenPos = $offset + strlen($m[0]) - 1;
            $balanced = $this->readBalanced($src, $parenPos, '(', ')');
            if ($balanced === null) {
                return null;
            }

            return substr($src, $offset, ($parenPos - $offset) + strlen($balanced));
        }

        return null;
    }

    private function readExpression(string $src, int $offset): ?string
    {
        $len = strlen($src);
        if ($offset >= $len) {
            return null;
        }

        if ($src[$offset] === '[' || (preg_match('/\Garray\s*\(/A', $src, $m, 0, $offset) === 1)) {
            return null;
        }

        $inSingle = false;
        $inDouble = false;
        $escaped = false;
        $depthParen = 0;

        for ($i = $offset; $i < $len; $i++) {
            $ch = $src[$i];

            if ($inSingle) {
                if ($escaped) {
                    $escaped = false;

                    continue;
                }
                if ($ch === '\\') {
                    $escaped = true;

                    continue;
                }
                if ($ch === "'") {
                    $inSingle = false;
                }

                continue;
            }

            if ($inDouble) {
                if ($escaped) {
                    $escaped = false;

                    continue;
                }
                if ($ch === '\\') {
                    $escaped = true;

                    continue;
                }
                if ($ch === '"') {
                    $inDouble = false;
                }

                continue;
            }

            if ($ch === "'") {
                $inSingle = true;

                continue;
            }
            if ($ch === '"') {
                $inDouble = true;

                continue;
            }

            if ($ch === '(') {
                $depthParen++;

                continue;
            }
            if ($ch === ')') {
                if ($depthParen > 0) {
                    $depthParen--;
                }

                continue;
            }

            if ($depthParen === 0 && ($ch === ',' || $ch === "\n" || $ch === ']')) {
                $expr = rtrim(substr($src, $offset, $i - $offset));

                return $expr !== '' ? $expr : null;
            }
        }

        $expr = rtrim(substr($src, $offset));

        return $expr !== '' ? $expr : null;
    }

    private function readBalanced(string $src, int $start, string $open, string $close): ?string
    {
        $len = strlen($src);
        if ($start >= $len || $src[$start] !== $open) {
            return null;
        }

        $depth = 0;
        $inSingle = false;
        $inDouble = false;
        $escaped = false;

        for ($i = $start; $i < $len; $i++) {
            $ch = $src[$i];

            if ($inSingle) {
                if ($escaped) {
                    $escaped = false;

                    continue;
                }
                if ($ch === '\\') {
                    $escaped = true;

                    continue;
                }
                if ($ch === "'") {
                    $inSingle = false;
                }

                continue;
            }

            if ($inDouble) {
                if ($escaped) {
                    $escaped = false;

                    continue;
                }
                if ($ch === '\\') {
                    $escaped = true;

                    continue;
                }
                if ($ch === '"') {
                    $inDouble = false;
                }

                continue;
            }

            if ($ch === "'") {
                $inSingle = true;

                continue;
            }
            if ($ch === '"') {
                $inDouble = true;

                continue;
            }

            // Line comments
            if ($ch === '/' && $i + 1 < $len && $src[$i + 1] === '/') {
                $nl = strpos($src, "\n", $i);
                if ($nl === false) {
                    return null;
                }
                $i = $nl;

                continue;
            }

            if ($ch === $open) {
                $depth++;

                continue;
            }
            if ($ch === $close) {
                $depth--;
                if ($depth === 0) {
                    return substr($src, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }

    private function normalizeLiteral(string $literal): string
    {
        $literal = trim($literal);
        $lines = preg_split("/\r\n|\n|\r/", $literal) ?: [$literal];

        $indents = [];
        foreach ($lines as $index => $line) {
            if ($index === 0 || trim($line) === '') {
                continue;
            }
            if (preg_match('/^(\s*)/', $line, $m) === 1) {
                $indents[] = strlen(str_replace("\t", '    ', $m[1]));
            }
        }

        $min = $indents === [] ? 0 : min($indents);
        $out = [];
        foreach ($lines as $index => $line) {
            if ($index === 0) {
                $out[] = trim($line);

                continue;
            }
            if (trim($line) === '') {
                $out[] = '';

                continue;
            }
            $normalized = str_replace("\t", '    ', $line);
            if ($min > 0 && strlen($normalized) >= $min) {
                $normalized = substr($normalized, $min);
            }
            $out[] = $normalized;
        }

        return implode("\n", $out);
    }
}
