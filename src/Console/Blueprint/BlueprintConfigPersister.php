<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Blueprint;

/**
 * Persists nested Blueprint `index` / `form` class leaves into Config/config.php.
 */
final class BlueprintConfigPersister
{
    public function __construct(
        private readonly BlueprintConfigSourceExtractor $extractor = new BlueprintConfigSourceExtractor,
    ) {}

    /**
     * @param list<array{nested: string, fqcn: string, legacy: string}> $wires
     * @return array{
     *     ok: bool,
     *     message: string,
     *     content: string,
     *     commented_flats: list<string>,
     *     surfaces: list<string>
     * }
     */
    public function build(
        string $configContents,
        string $routeSnake,
        array $wires,
        bool $commentLegacyFlats = true,
    ): array {
        $surfaces = $this->groupBySurface($wires);
        $rootLeaves = $this->groupRootLeaves($wires);
        if ($surfaces === [] && $rootLeaves === []) {
            return [
                'ok' => false,
                'message' => 'No Blueprint wires to persist.',
                'content' => $configContents,
                'commented_flats' => [],
                'surfaces' => [],
            ];
        }

        $route = $this->extractor->locateRouteArray($configContents, $routeSnake);
        if ($route === null) {
            return [
                'ok' => false,
                'message' => "Route [{$routeSnake}] not found in config.",
                'content' => $configContents,
                'commented_flats' => [],
                'surfaces' => [],
            ];
        }

        $routeLiteral = $route['literal'];
        $inner = mb_substr($routeLiteral, 1, -1); // strip [ ]
        $indent = $this->detectInnerIndent($inner);

        $commented = [];
        if ($commentLegacyFlats) {
            [$inner, $commented] = $this->commentLegacyFlats($inner, $wires, $indent);
        }

        $touched = [];
        foreach ($surfaces as $surface => $leaves) {
            $inner = $this->upsertSurface($inner, $surface, $leaves, $indent);
            $touched[] = $surface;
        }

        foreach ($rootLeaves as $key => $classExpr) {
            $inner = $this->upsertRootClassLeaf($inner, $key, $classExpr, $indent);
            $touched[] = $key;
        }

        $newRouteLiteral = '[' . $inner . ']';
        $content = mb_substr($configContents, 0, $route['value_start'])
            . $newRouteLiteral
            . mb_substr($configContents, $route['value_end']);

        return [
            'ok' => true,
            'message' => sprintf(
                'Updated routes.%s (%s).',
                $routeSnake,
                implode(', ', $touched)
            ),
            'content' => $content,
            'commented_flats' => $commented,
            'surfaces' => $touched,
        ];
    }

    /**
     * @param list<array{nested: string, fqcn: string, legacy: string}> $wires
     * @return array{
     *     ok: bool,
     *     message: string,
     *     content: string,
     *     commented_flats: list<string>,
     *     surfaces: list<string>
     * }
     */
    public function persist(
        string $configPath,
        string $routeSnake,
        array $wires,
        bool $commentLegacyFlats = true,
    ): array {
        if (! is_file($configPath)) {
            return [
                'ok' => false,
                'message' => 'Config file not found.',
                'content' => '',
                'commented_flats' => [],
                'surfaces' => [],
            ];
        }

        $original = (string) file_get_contents($configPath);
        $result = $this->build($original, $routeSnake, $wires, $commentLegacyFlats);

        if ($result['ok'] && $result['content'] !== $original) {
            file_put_contents($configPath, $result['content']);
        }

        return $result;
    }

    /**
     * @param list<array{nested: string, fqcn: string, legacy: string}> $wires
     * @return array<string, array<string, string>> surface => [leaf => \Fqcn::class]
     */
    private function groupBySurface(array $wires): array
    {
        $out = [];
        foreach ($wires as $wire) {
            $nested = $wire['nested'];
            if (! str_contains($nested, '.')) {
                continue;
            }
            [$surface, $leaf] = explode('.', $nested, 2);
            if (! in_array($surface, ['index', 'form'], true)) {
                continue;
            }
            $out[$surface][$leaf] = '\\' . ltrim($wire['fqcn'], '\\') . '::class';
        }

        return $out;
    }

    /**
     * Route-root class leaves (nested_key === legacy, e.g. bulk_sheet).
     *
     * @param list<array{nested: string, fqcn: string, legacy: string}> $wires
     * @return array<string, string> key => \Fqcn::class
     */
    private function groupRootLeaves(array $wires): array
    {
        $out = [];
        foreach ($wires as $wire) {
            $nested = $wire['nested'];
            if (str_contains($nested, '.')) {
                continue;
            }
            $out[$nested] = '\\' . ltrim($wire['fqcn'], '\\') . '::class';
        }

        return $out;
    }

    private function upsertRootClassLeaf(string $inner, string $key, string $classExpr, string $indent): string
    {
        $assignment = "'{$key}' => {$classExpr}";
        $entry = $this->extractor->locateKeyedEntry($inner, $key);
        if ($entry === null) {
            return "\n{$indent}{$assignment}," . $inner;
        }

        return mb_substr($inner, 0, $entry['key_start'])
            . $assignment
            . mb_substr($inner, $entry['value_end']);
    }

    /**
     * @param array<string, string> $leaves
     */
    private function upsertSurface(string $inner, string $surface, array $leaves, string $indent): string
    {
        $hit = $this->extractor->locateKeyedArray($inner, $surface);
        if ($hit === null) {
            $block = $this->formatSurfaceBlock($surface, $leaves, $indent);

            return "\n{$indent}{$block}," . $inner;
        }

        $existingLeaves = $this->readExistingClassLeaves($hit['literal']);
        $merged = array_merge($existingLeaves, $leaves);
        $replacement = $this->formatSurfaceBlock($surface, $merged, $indent);

        $entry = $this->extractor->locateKeyedEntry($inner, $surface);
        if ($entry === null) {
            return $inner;
        }

        return mb_substr($inner, 0, $entry['key_start'])
            . $replacement
            . mb_substr($inner, $entry['value_end']);
    }

    /**
     * @return array<string, string> leaf => class expr
     */
    private function readExistingClassLeaves(string $surfaceLiteral): array
    {
        $inner = mb_substr(trim($surfaceLiteral), 1, -1);
        $leaves = [];
        $candidates = [
            'options',
            'columns',
            'filters',
            'advanced_filters',
            'actions',
            'row_actions',
            'with',
            'appends',
            'inputs',
        ];

        foreach ($candidates as $leaf) {
            $entry = $this->extractor->locateKeyedEntry($inner, $leaf);
            if ($entry === null) {
                continue;
            }
            $value = trim($entry['value']);
            if (str_ends_with($value, '::class')) {
                $leaves[$leaf] = $value;
            }
        }

        return $leaves;
    }

    /**
     * @param array<string, string> $leaves
     */
    private function formatSurfaceBlock(string $surface, array $leaves, string $indent): string
    {
        $leafIndent = $indent . '    ';
        $lines = ["'{$surface}' => ["];
        foreach ($leaves as $leaf => $classExpr) {
            $lines[] = "{$leafIndent}'{$leaf}' => {$classExpr},";
        }
        $lines[] = "{$indent}]";

        return implode("\n", $lines);
    }

    /**
     * @param list<array{nested: string, fqcn: string, legacy: string}> $wires
     * @return array{0: string, 1: list<string>}
     */
    private function commentLegacyFlats(string $inner, array $wires, string $indent): array
    {
        $spans = [];
        foreach ($wires as $wire) {
            // Root class leaves replace the same key — do not comment them away.
            if (! str_contains($wire['nested'], '.') || $wire['legacy'] === $wire['nested']) {
                continue;
            }
            $legacy = $wire['legacy'];
            $entry = $this->extractor->locateKeyedEntry($inner, $legacy);
            if ($entry === null) {
                continue;
            }
            $start = $this->lineStartOffset($inner, $entry['key_start']);
            $end = $entry['value_end'];
            if (isset($inner[$end]) && $inner[$end] === ',') {
                $end++;
            } elseif (preg_match('/\G\s*,/', $inner, $m, 0, $end) === 1) {
                $end += mb_strlen($m[0]);
            }
            $spans[] = [$start, $end, $legacy];
        }

        // Dedupe by legacy key
        $byLegacy = [];
        foreach ($spans as $span) {
            $byLegacy[$span[2]] = $span;
        }
        $spans = array_values($byLegacy);

        usort($spans, static fn (array $a, array $b): int => $b[0] <=> $a[0]);

        $commented = [];
        foreach ($spans as [$start, $end, $legacy]) {
            $chunk = mb_substr($inner, $start, $end - $start);
            $inner = mb_substr($inner, 0, $start)
                . $this->commentChunk($chunk, $indent)
                . mb_substr($inner, $end);
            $commented[] = $legacy;
        }

        return [$inner, array_values(array_reverse($commented))];
    }

    private function lineStartOffset(string $src, int $pos): int
    {
        $lineStart = mb_strrpos(mb_substr($src, 0, $pos), "\n");

        return $lineStart === false ? 0 : $lineStart + 1;
    }

    private function commentChunk(string $chunk, string $indent): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $chunk) ?: [$chunk];
        $baseIndent = $indent;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            if (preg_match('/^(\s*)/', str_replace("\t", '    ', $line), $m) === 1) {
                $baseIndent = $m[1];
            }

            break;
        }

        $baseLen = mb_strlen($baseIndent);
        $out = [];

        foreach ($lines as $i => $line) {
            if ($i === 0 && $line === '') {
                continue;
            }

            $normalized = str_replace("\t", '    ', $line);

            if (trim($normalized) === '') {
                $out[] = '';

                continue;
            }

            // Already commented: re-align to base so nested // stay column-aligned.
            if (str_starts_with(ltrim($normalized), '//')) {
                $trimmed = ltrim($normalized);
                $out[] = $baseIndent . $trimmed;

                continue;
            }

            $relative = $normalized;
            if ($baseLen > 0 && str_starts_with($normalized, $baseIndent)) {
                $relative = mb_substr($normalized, $baseLen);
            } else {
                $relative = ltrim($normalized);
            }

            $out[] = $baseIndent . '// ' . $relative;
        }

        return implode("\n", $out);
    }

    private function detectInnerIndent(string $inner): string
    {
        foreach (preg_split("/\r\n|\n|\r/", $inner) ?: [] as $line) {
            if (trim($line) === '' || str_starts_with(ltrim($line), '//')) {
                continue;
            }
            if (preg_match('/^(\s+)/', $line, $m) === 1) {
                return str_replace("\t", '    ', $m[1]);
            }
        }

        return '            ';
    }
}
