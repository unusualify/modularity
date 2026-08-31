<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint;

/**
 * Formats Blueprint stub array bodies as short-array PHP (config.php style).
 */
final class BlueprintPhpArrayFormatter
{
    /**
     * Export an evaluated array to short-array syntax for `return $ITEMS$;`.
     *
     * @param array<string, mixed>|list<mixed> $items
     */
    public static function export(array $items, int $methodIndent = 8): string
    {
        $export = function_exists('array_export')
            ? (string) array_export($items, true)
            : var_export($items, true);

        return self::indentForMethodReturn($export, $methodIndent);
    }

    /**
     * Re-indent a `[...]` literal so it sits cleanly after `        return `.
     */
    public static function indentForMethodReturn(string $literal, int $methodIndent = 8): string
    {
        $literal = trim($literal);
        if ($literal === '') {
            return '[]';
        }

        $lines = preg_split("/\r\n|\n|\r/", $literal) ?: [$literal];
        $first = array_shift($lines);
        if ($lines === []) {
            return (string) $first;
        }

        $pad = str_repeat(' ', $methodIndent);
        $out = (string) $first;
        foreach ($lines as $line) {
            $out .= "\n" . $pad . $line;
        }

        return $out;
    }

    /**
     * Extra `use` lines inferred from array source (without trailing newlines).
     *
     * @return list<string>
     */
    public static function detectExtraUses(string $itemsCode): array
    {
        $uses = [];

        if (str_contains($itemsCode, 'Component::')
            || str_contains($itemsCode, '\\Unusualify\\Modularous\\View\\Component')) {
            $uses[] = 'use Unusualify\\Modularous\\View\\Component;';
        }

        return $uses;
    }
}
