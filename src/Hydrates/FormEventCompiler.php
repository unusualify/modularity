<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Hydrates;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Compiles input `formEvents` (canonical) or `ext` (deprecated event DSL) into
 * frontend schema `formEvents[]` + legacy `event` pipe string.
 */
final class FormEventCompiler
{
    /** @var list<string> */
    public const TYPE_ALIASES = [
        'date',
        'time',
        'price',
        'softCurrency',
        'scroll',
        'number',
        'relationship',
        'morphTo',
    ];

    /** @var list<string> */
    public const EVENT_METHODS = [
        'permalinkPrefix',
        'lock',
        'permalink',
        'filter',
        'preview',
        'set',
        'update',
        'clearModel',
        'resetItems',
        'prependSchema',
        'removeValue',
        'toggleInput',
    ];

    /** @var array<string, true> */
    private static array $warnedExtFields = [];

    public static function isTypeAlias(mixed $ext): bool
    {
        return is_string($ext) && in_array($ext, self::TYPE_ALIASES, true);
    }

    public static function applyTypeAliasDefaults(array &$input): void
    {
        if (! isset($input['ext']) || ! is_string($input['ext'])) {
            return;
        }

        match ($input['ext']) {
            'date' => $input['default'] ??= date('Y-m-d'),
            'time' => $input['default'] ??= date('H:i'),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed>|null $data
     * @param list<mixed> $inputs
     * @param (callable(array): array)|null $hydrateSchema
     * @param (callable(string, mixed): mixed)|null $configFields
     */
    public static function apply(array &$input, ?array &$data, mixed &$arrayable, array $inputs, ?object $host = null, ?callable $hydrateSchema = null, ?callable $configFields = null): void
    {
        self::applyTypeAliasDefaults($input);

        if (self::alreadyCompiled($input['formEvents'] ?? null)) {
            return;
        }

        $resolved = self::resolveSource($input);
        $patterns = $resolved['patterns'] ?? [];
        $fromExt = $resolved['fromExt'] ?? false;

        $patterns = array_merge($patterns, self::relationshipInjectedPatterns($input, $inputs, $host));

        if ($patterns === []) {
            return;
        }

        if ($fromExt) {
            self::warnExtFallback($input);
        }

        $compiled = [];
        $extraInputs = [];

        foreach ($patterns as $pattern) {
            $result = self::compilePattern($pattern, $input, $data, $inputs, $extraInputs, $host, $hydrateSchema, $configFields);
            if ($result !== null) {
                $compiled[] = $result;
            }
        }

        if ($compiled === []) {
            return;
        }

        $compiled = array_values(array_unique($compiled));
        $existing = [];
        if (isset($data['event']) && is_string($data['event']) && $data['event'] !== '') {
            $existing = explode('|', $data['event']);
        } elseif (isset($input['event']) && is_string($input['event']) && $input['event'] !== '') {
            $existing = explode('|', $input['event']);
        }

        $merged = array_values(array_unique([...$existing, ...$compiled]));
        $input['formEvents'] = $merged;
        $input['event'] = implode('|', $merged);
        if (is_array($data)) {
            $data['formEvents'] = $merged;
            $data['event'] = $input['event'];
        }

        if ($extraInputs !== []) {
            $arrayable = true;
            $_input = (array) ($data ?? $input);
            unset($_input['formEvents']);
            $data = self::hydrateExtraHostSchema($_input, $hydrateSchema) + $extraInputs;
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{patterns: list<mixed>, fromExt: bool}
     */
    public static function resolveSource(array $input): array
    {
        if (self::hasFormEventsKey($input)) {
            return [
                'patterns' => self::normalizePatterns($input['formEvents']),
                'fromExt' => false,
            ];
        }

        if (! isset($input['ext']) || self::isTypeAlias($input['ext'])) {
            return ['patterns' => [], 'fromExt' => false];
        }

        $patterns = self::normalizePatterns($input['ext']);

        if ($patterns === [] || ! self::patternsLookLikeEvents($patterns)) {
            return ['patterns' => [], 'fromExt' => false];
        }

        return ['patterns' => $patterns, 'fromExt' => true];
    }

    /**
     * @return list<mixed>
     */
    public static function normalizePatterns(mixed $raw): array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return [];
        }

        if (is_string($raw)) {
            return array_values(array_filter(array_map('trim', explode('|', $raw)), fn (string $p) => $p !== ''));
        }

        if (! is_array($raw)) {
            return [];
        }

        return array_values($raw);
    }

    /**
     * @param list<mixed> $patterns
     */
    public static function patternsLookLikeEvents(array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $method = self::patternMethod($pattern);
            if ($method !== null && in_array($method, self::EVENT_METHODS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<mixed> $inputs
     * @return list<string>
     */
    private static function relationshipInjectedPatterns(array $input, array $inputs, ?object $host): array
    {
        if ($host === null || ! method_exists($host, 'getConfigFieldsByRoute')) {
            return [];
        }

        $additional = [];

        foreach ($inputs as $_input) {
            if (is_array($_input)) {
                $_input = (object) $_input;
            }

            if (! isset($_input->type) || $_input->type !== 'relationship') {
                continue;
            }

            $moduleName = $host->moduleName ?? null;
            if (! is_string($moduleName) || $moduleName === '') {
                continue;
            }

            $module = Modularous::find($moduleName);
            if (! $module) {
                continue;
            }

            $routeName = $host->routeName ?? (method_exists($host, 'getRouteName') ? $host->getRouteName() : '');
            $camel = method_exists($host, 'getCamelCase') ? $host->getCamelCase(...) : fn ($s) => $s;

            $foreignKeyRow = collect($module->getRawRouteConfig(studlyName($_input->name) . '.inputs'))
                ->filter(fn ($_i) => $camel($_i['name'] ?? '') === $camel((string) $routeName) . 'Id')
                ->toArray()[1] ?? [];
            $foreignKeyExt = $foreignKeyRow['formEvents'] ?? $foreignKeyRow['ext'] ?? '';

            if (is_array($foreignKeyExt)) {
                $foreignKeyExt = implode('|', array_map(
                    fn ($p) => is_array($p) ? implode(':', $p) : (string) $p,
                    $foreignKeyExt
                ));
            }

            foreach (explode('|', (string) $foreignKeyExt) as $pattern) {
                [$methodName, $formattedInput, $parentColumnName] = array_pad(explode(':', $pattern), 3, null);

                switch ($methodName) {
                    case 'lock':
                        if (isset($input['name']) && $parentColumnName === $input['name']) {
                            $additional[] = $methodName . ':' . pluralize($camel($_input->name)) . '.' . $formattedInput . ':' . $parentColumnName;
                        }

                        break;
                    case 'permalinkPrefix':
                        if (isset($input['name']) && $input['name'] === 'name') {
                            $additional[] = $methodName . ':' . pluralize($camel($_input->name)) . '.' . $formattedInput;
                        }

                        break;
                }
            }
        }

        return $additional;
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed>|null $data
     * @param list<mixed> $inputs
     * @param array<string, mixed> $extraInputs
     */
    private static function compilePattern(
        mixed $pattern,
        array &$input,
        ?array &$data,
        array $inputs,
        array &$extraInputs,
        ?object $host,
        ?callable $hydrateSchema,
        ?callable $configFields = null,
    ): ?string {
        $args = $pattern;
        if (is_string($pattern)) {
            $pattern = trim($pattern);
            $args = explode(':', $pattern);
        }

        if (! is_array($args) || $args === []) {
            return null;
        }

        $methodName = array_shift($args);
        $targetInputName = array_shift($args);

        return match ($methodName) {
            'permalinkPrefix' => self::compilePermalinkPrefix($targetInputName, $input, $host, $configFields),
            'lock' => self::compileLock($targetInputName, $args),
            'permalink' => self::compilePermalink($targetInputName, $input, $inputs, $extraInputs, $host, $hydrateSchema),
            'filter' => self::compileFilter($targetInputName, $args, $input, $data, $host),
            'preview' => self::compilePreview($targetInputName, $args),
            'set', 'update' => self::compileSetOrUpdate($methodName, $targetInputName, $args),
            'clearModel' => $targetInputName ? "formatClearModel:{$targetInputName}" : null,
            'resetItems' => $targetInputName ? "formatResetItems:{$targetInputName}" : null,
            'prependSchema' => self::compilePrependSchema($targetInputName, $args),
            'removeValue' => $targetInputName ? "formatRemoveValue:{$targetInputName}" : null,
            'toggleInput' => self::compileToggleInput($targetInputName, $args),
            default => null,
        };
    }

    private static function compilePermalinkPrefix(?string $targetInputName, array $input, ?object $host, ?callable $configFields): ?string
    {
        if (! $targetInputName) {
            return null;
        }

        $routeSnake = '';
        if (is_object($host) && method_exists($host, 'getRouteName') && method_exists($host, 'getSnakeCase')) {
            $routeSnake = $host->getSnakeCase($host->getRouteName());
        }

        if (isset($input['repository']) && $configFields !== null) {
            foreach ($configFields('inputs') ?? [] as $_input) {
                $_input = is_array($_input) ? (object) $_input : $_input;
                $siblingExt = $_input->formEvents ?? $_input->ext ?? '';
                $first = is_string($siblingExt) ? explode(':', $siblingExt)[0] : (is_array($siblingExt) ? ($siblingExt[0][0] ?? $siblingExt[0] ?? '') : '');
                if (in_array($first, ['permalink'], true)) {
                    return 'formatPermalinkPrefix:' . $targetInputName . ':' . self::snakeNameFromForeignKey((string) ($input['name'] ?? ''));
                }
            }

            return null;
        }

        return 'formatPermalinkPrefix:' . $targetInputName . ':' . $routeSnake;
    }

    /**
     * @param list<mixed> $args
     */
    private static function compileLock(?string $targetInputName, array $args): ?string
    {
        $parentColumnName = array_shift($args);

        if (! $targetInputName) {
            return null;
        }

        return "formatLock:{$targetInputName}:{$parentColumnName}";
    }

    /**
     * @param list<mixed> $inputs
     * @param array<string, mixed> $extraInputs
     */
    private static function compilePermalink(
        ?string $targetInputName,
        array &$input,
        array $inputs,
        array &$extraInputs,
        ?object $host,
        ?callable $hydrateSchema,
    ): ?string {
        if (! $targetInputName) {
            return null;
        }

        if ($hydrateSchema === null) {
            return 'formatPermalink:' . $targetInputName;
        }

        $permalinkPrefix = getHost() . '/';
        $permalinkPrefixFormat = getHost() . '/';

        foreach ($inputs as $_input) {
            $row = is_array($_input) ? $_input : (array) $_input;
            $type = $row['type'] ?? null;
            $hasRepo = isset($row['repository']);
            $hasEventDsl = isset($row['ext']) || isset($row['formEvents']);

            if (in_array($type, ['select', 'combobox', 'hidden'], true) && $hasRepo && $hasEventDsl) {
                $permalinkPrefixFormat .= ':' . self::snakeNameFromForeignKey((string) ($row['name'] ?? '')) . '/';
            }
        }

        $extraInputs += $hydrateSchema([
            'type' => 'text',
            'name' => 'slug',
            'ref' => 'permalink',
            'label' => 'Permalink',
            'prefix' => $permalinkPrefix,
            'prefixFormat' => $permalinkPrefixFormat,
            'readonly' => true,
        ]);
        unset($input['ext']);

        return 'formatPermalink:' . $targetInputName;
    }

    /**
     * @param list<mixed> $args
     * @param array<string, mixed> $input
     * @param array<string, mixed>|null $data
     */
    private static function compileFilter(?string $targetInputName, array $args, array &$input, ?array &$data, ?object $host): ?string
    {
        $targetPropName = array_shift($args) ?? 'inputs';
        $filterEndpoint = $input['filterEndpoint'] ?? null;

        if (! $filterEndpoint && isset($input['schema']) && $host && method_exists($host, 'getStudlyName')) {
            $filterEndpoint = Collection::make($input['schema'])->mapWithKeys(function ($r) use ($host) {
                $routeName = $host->getStudlyName($r['name'] ?? '');
                $targetModuleName = $host->getStudlyName($r['_moduleName'] ?? ($host->moduleName ?? ''));
                $targetModule = Modularous::find($targetModuleName);

                return [$r['name'] => $targetModule?->getRouteActionUrl($routeName, 'show')];
            });
        }

        if (! $filterEndpoint && isset($input['_routeName']) && $host && method_exists($host, 'getStudlyName')) {
            $routeName = $host->getStudlyName($input['_routeName']);
            $targetModuleName = $host->getStudlyName($input['_moduleName'] ?? ($host->moduleName ?? ''));
            $targetModule = Modularous::find($targetModuleName);
            if ($targetModule) {
                $filterEndpoint = $targetModule->getRouteActionUrl($routeName, 'show');
            }
        }

        if (! $filterEndpoint) {
            return null;
        }

        if ($data) {
            $data['filterEndpoint'] = $filterEndpoint;
        } else {
            $input['filterEndpoint'] = $filterEndpoint;
        }

        return 'formatFilter:' . implode(':', [$targetInputName, $targetPropName, ...$args]);
    }

    /**
     * @param list<mixed> $args
     */
    private static function compilePreview(?string $targetInputName, array $args): ?string
    {
        $previewFieldPatterns = array_shift($args) ?? null;
        if ($previewFieldPatterns) {
            $previewFieldPatterns = ':' . $previewFieldPatterns;
        }

        return $targetInputName ? "formatPreview:{$targetInputName}{$previewFieldPatterns}" : null;
    }

    /**
     * @param list<mixed> $args
     */
    private static function compileSetOrUpdate(string $methodName, ?string $targetInputName, array $args): ?string
    {
        $targetPropName = array_shift($args) ?? null;
        $setProp = array_shift($args) ?? "items.*.{$targetPropName}";
        $tail = array_values(array_filter($args, static fn ($part) => $part !== null && $part !== ''));

        if (! $targetInputName || ! $targetPropName) {
            return null;
        }

        $eventName = 'format' . studlyName($methodName);

        return implode(':', [$eventName, $targetInputName, $targetPropName, $setProp, ...$tail]);
    }

    /**
     * @param list<mixed> $args
     */
    private static function compilePrependSchema(?string $targetInputName, array $args): ?string
    {
        $prependKey = array_shift($args) ?? null;
        $setterSchemaKey = array_shift($args) ?? null;
        $orderKey = array_shift($args) ?? 'false';

        if (! $targetInputName || ! $prependKey || ! $setterSchemaKey) {
            return null;
        }

        return "formatPrependSchema:{$targetInputName}:{$prependKey}:{$setterSchemaKey}:{$orderKey}";
    }

    /**
     * @param list<mixed> $args
     */
    private static function compileToggleInput(?string $targetInputName, array $args): ?string
    {
        $toggleValue = array_shift($args) ?? 'toggleValue';
        $toggleLevel = array_shift($args) ?? -1;

        return $targetInputName ? "formatToggleInput:{$targetInputName}:{$toggleValue}:{$toggleLevel}" : null;
    }

    private static function hasFormEventsKey(array $input): bool
    {
        if (! array_key_exists('formEvents', $input)) {
            return false;
        }

        $formEvents = $input['formEvents'];

        return $formEvents !== null && $formEvents !== '' && $formEvents !== [];
    }

    private static function patternMethod(mixed $pattern): ?string
    {
        if (is_string($pattern)) {
            $parts = explode(':', trim($pattern));

            return $parts[0] !== '' ? $parts[0] : null;
        }

        if (is_array($pattern) && isset($pattern[0]) && is_string($pattern[0])) {
            return $pattern[0];
        }

        return null;
    }

    private static function snakeNameFromForeignKey(string $foreignKey): string
    {
        if (preg_match('/(.*)(_id)/', $foreignKey, $matches)) {
            return Str::snake($matches[1]);
        }

        return '';
    }

    public static function alreadyCompiled(mixed $events): bool
    {
        foreach (self::normalizePatterns($events) as $pattern) {
            $method = self::patternMethod($pattern);
            if (is_string($method) && str_starts_with($method, 'format')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param (callable(array): array)|null $hydrateSchema
     */
    private static function hydrateExtraHostSchema(array $input, ?callable $hydrateSchema): array
    {
        if ($hydrateSchema !== null) {
            return $hydrateSchema($input);
        }

        return $input;
    }

    /**
     * @param array<string, mixed> $input
     */
    private static function warnExtFallback(array $input): void
    {
        if (function_exists('app')) {
            try {
                if (app()->runningUnitTests()) {
                    return;
                }
            } catch (\Throwable) {
                // app() may be unbound outside Laravel
            }
        }

        $name = (string) ($input['name'] ?? spl_object_hash((object) $input));
        if (isset(self::$warnedExtFields[$name])) {
            return;
        }
        self::$warnedExtFields[$name] = true;

        if (function_exists('trigger_deprecation')) {
            trigger_deprecation(
                'unusualify/modularous',
                '13.0',
                'Input event DSL on ext is deprecated; use formEvents. ext event fallback is removed in 14.0 (field: %s).',
                $input['name'] ?? '(unnamed)'
            );
        }
    }
}
