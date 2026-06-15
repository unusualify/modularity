<?php

namespace Modules\Cms\Services\Stylesheet;

/**
 * Emits a {@code :root { ... }} block from a flat map of CSS custom property names → values.
 */
final class RootVariablesEmitter
{
    /**
     * @param  array<string, string|int|float|null>  $root
     */
    public function emit(array $root): string
    {
        if ($root === []) {
            return '';
        }

        $lines = [];
        foreach ($root as $name => $value) {
            $prop = $this->normalizePropertyName((string) $name);
            if ($prop === '') {
                continue;
            }
            if ($value === null || $value === '') {
                continue;
            }
            $lines[] = sprintf('  %s: %s;', $prop, $this->escapeValue((string) $value));
        }

        if ($lines === []) {
            return '';
        }

        return ":root {\n" . implode("\n", $lines) . "\n}\n";
    }

    private function normalizePropertyName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        if ($name[0] !== '-') {
            $name = '--' . ltrim($name, '-');
        }

        return preg_match('/^--[a-zA-Z0-9_-]+$/', $name) ? $name : '';
    }

    private function escapeValue(string $value): string
    {
        $value = str_replace(["\n", "\r"], '', $value);

        return trim($value);
    }
}
