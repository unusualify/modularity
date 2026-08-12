<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRoutePresentation;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteTableOptionsProvider;
use Unusualify\Modularous\ModuleRoute;

/**
 * Resolves route presentation/blueprint fields from config / class / database.
 *
 * Config payload preference: nested `index.*` / `form.*` first, then legacy flat keys.
 * Class / driver meta may live under nested leaves or under `blueprint` / `presentation`.
 *
 * @see docs/src/pages/system-reference/adr-module-route-blueprint.md
 */
final class ModuleRoutePresentationResolver
{
    public const FIELD_INPUTS = 'inputs';

    public const FIELD_HEADERS = 'headers';

    public const FIELD_TABLE_OPTIONS = 'table_options';

    public const FIELD_TABLE_FILTERS = 'table_filters';

    public const FIELD_ADVANCED_FILTERS = 'filters';

    public const FIELD_TABLE_ACTIONS = 'table_actions';

    public const FIELD_TABLE_ROW_ACTIONS = 'table_row_actions';

    public const FIELD_INDEX_WITH = 'index_with';

    public const FIELD_INDEX_APPENDS = 'index_appends';

    public const FIELD_FORM_OPTIONS = 'form_options';

    public const FIELD_FORM_WITH = 'form_with';

    public const FIELD_FORM_APPENDS = 'form_appends';

    public const FIELD_FORM_ACTIONS = 'form_actions';

    public const FIELD_BULK_SHEET = 'bulk_sheet';

    /**
     * Legacy config key => [surface folder, class suffix after {Route}].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const FIELD_BLUEPRINT = [
        self::FIELD_INPUTS => ['Form', 'FormInputs'],
        self::FIELD_HEADERS => ['Index', 'IndexColumns'],
        self::FIELD_TABLE_OPTIONS => ['Index', 'IndexOptions'],
        self::FIELD_TABLE_FILTERS => ['Index', 'IndexFilters'],
        self::FIELD_ADVANCED_FILTERS => ['Index', 'IndexAdvancedFilters'],
        self::FIELD_TABLE_ACTIONS => ['Index', 'IndexActions'],
        self::FIELD_TABLE_ROW_ACTIONS => ['Index', 'IndexRowActions'],
        self::FIELD_INDEX_WITH => ['Index', 'IndexWith'],
        self::FIELD_INDEX_APPENDS => ['Index', 'IndexAppends'],
        self::FIELD_FORM_OPTIONS => ['Form', 'FormOptions'],
        self::FIELD_FORM_WITH => ['Form', 'FormWith'],
        self::FIELD_FORM_APPENDS => ['Form', 'FormAppends'],
        self::FIELD_FORM_ACTIONS => ['Form', 'FormActions'],
        self::FIELD_BULK_SHEET => ['BulkSheet', 'BulkSheet'],
    ];

    /**
     * Legacy flat key => canonical nested config path (ADR).
     *
     * @var array<string, string>
     */
    private const FIELD_NESTED = [
        self::FIELD_INPUTS => 'form.inputs',
        self::FIELD_HEADERS => 'index.columns',
        self::FIELD_TABLE_OPTIONS => 'index.options',
        self::FIELD_TABLE_FILTERS => 'index.filters',
        self::FIELD_ADVANCED_FILTERS => 'index.advanced_filters',
        self::FIELD_TABLE_ACTIONS => 'index.actions',
        self::FIELD_TABLE_ROW_ACTIONS => 'index.row_actions',
        self::FIELD_INDEX_WITH => 'index.with',
        self::FIELD_INDEX_APPENDS => 'index.appends',
        self::FIELD_FORM_OPTIONS => 'form.options',
        self::FIELD_FORM_WITH => 'form.with',
        self::FIELD_FORM_APPENDS => 'form.appends',
        self::FIELD_FORM_ACTIONS => 'form.actions',
        self::FIELD_BULK_SHEET => 'bulk_sheet',
    ];

    private const FIELD_INTERFACE = [
        self::FIELD_INPUTS => ModuleRouteInputsProvider::class,
        self::FIELD_HEADERS => ModuleRouteHeadersProvider::class,
        self::FIELD_TABLE_OPTIONS => ModuleRouteTableOptionsProvider::class,
        self::FIELD_TABLE_FILTERS => ModuleRouteBlueprintProvider::class,
        self::FIELD_ADVANCED_FILTERS => ModuleRouteBlueprintProvider::class,
        self::FIELD_TABLE_ACTIONS => ModuleRouteBlueprintProvider::class,
        self::FIELD_TABLE_ROW_ACTIONS => ModuleRouteBlueprintProvider::class,
        self::FIELD_INDEX_WITH => ModuleRouteBlueprintProvider::class,
        self::FIELD_INDEX_APPENDS => ModuleRouteBlueprintProvider::class,
        self::FIELD_FORM_OPTIONS => ModuleRouteBlueprintProvider::class,
        self::FIELD_FORM_WITH => ModuleRouteBlueprintProvider::class,
        self::FIELD_FORM_APPENDS => ModuleRouteBlueprintProvider::class,
        self::FIELD_FORM_ACTIONS => ModuleRouteBlueprintProvider::class,
        self::FIELD_BULK_SHEET => ModuleRouteBlueprintProvider::class,
    ];

    /**
     * Legacy keys handled via ModuleRoute in CoreController::getConfigFieldsByRoute.
     *
     * @return list<string>
     */
    public static function routeConfigFields(): array
    {
        return array_keys(self::FIELD_BLUEPRINT);
    }

    /**
     * Canonical nested config path for a legacy field (e.g. headers → index.columns).
     */
    public static function nestedConfigKey(string $field): string
    {
        $field = strtolower(trim($field));

        if (! isset(self::FIELD_NESTED[$field])) {
            throw new \InvalidArgumentException(
                "Unknown presentation field [{$field}]. Expected one of: "
                . implode(', ', array_keys(self::FIELD_NESTED))
            );
        }

        return self::FIELD_NESTED[$field];
    }

    public function __construct(
        private readonly Container $app,
    ) {
    }

    /**
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function resolve(ModuleRoute $route, string $field): array
    {
        $field = $this->normalizeField($field);
        $driver = $this->driverFor($route, $field);

        return match ($driver) {
            'class' => $this->fromClass($route, $field),
            'database' => $this->fromDatabase($route, $field),
            default => $this->fromConfig($route, $field),
        };
    }

    public function driverFor(ModuleRoute $route, string $field): string
    {
        $field = $this->normalizeField($field);
        $meta = $this->fieldMeta($route, $field);

        if (is_string($meta) && $meta !== '') {
            return 'class';
        }

        if (is_array($meta) && isset($meta['driver']) && is_string($meta['driver']) && $meta['driver'] !== '') {
            return strtolower($meta['driver']);
        }

        if (is_array($meta) && isset($meta['class']) && is_string($meta['class']) && $meta['class'] !== '') {
            return 'class';
        }

        $root = $this->driverRoot($route);
        if (isset($root['driver']) && is_string($root['driver']) && $root['driver'] !== '') {
            return strtolower($root['driver']);
        }

        return strtolower((string) modularousConfig('module_route_presentation.driver', 'config'));
    }

    /**
     * Prefer nested `index.*` / `form.*`; fall back to legacy flat keys.
     *
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function fromConfig(ModuleRoute $route, string $field): array
    {
        $field = $this->normalizeField($field);
        $value = $this->readConfigPayload($route, $field);

        return is_array($value) ? $value : [];
    }

    /**
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function fromClass(ModuleRoute $route, string $field): array
    {
        $field = $this->normalizeField($field);
        $class = $this->resolveClass($route, $field);

        if ($class === null) {
            return $this->fromConfig($route, $field);
        }

        $provider = $this->app->make($class);
        $expected = self::FIELD_INTERFACE[$field];

        if (! $provider instanceof $expected && ! is_callable($provider)) {
            throw new \InvalidArgumentException(
                "Presentation class [{$class}] must implement {$expected} or be callable."
            );
        }

        /** @var list<array<string, mixed>>|array<string, mixed> $result */
        $result = $provider($route);

        return is_array($result) ? $result : [];
    }

    /**
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function fromDatabase(ModuleRoute $route, string $field): array
    {
        return $this->fromConfig($route, $field);
    }

    public function conventionClass(ModuleRoute $route, string $field): string
    {
        $field = $this->normalizeField($field);
        [$surface, $classSuffix] = self::FIELD_BLUEPRINT[$field];

        $moduleNs = $route->module()->getBaseNamespace();
        $routeStudly = $route->name();
        $folder = modularousConfig('module_route_presentation.path', 'Blueprint');
        $folderNs = str_replace('/', '\\', trim((string) $folder, '/\\'));

        return "{$moduleNs}\\{$folderNs}\\{$routeStudly}\\{$surface}\\{$routeStudly}{$classSuffix}";
    }

    public function resolveClass(ModuleRoute $route, string $field): ?string
    {
        $field = $this->normalizeField($field);
        $meta = $this->fieldMeta($route, $field);

        if (is_string($meta) && $meta !== '' && class_exists($meta)) {
            return $meta;
        }

        if (is_array($meta)) {
            $explicit = $meta['class'] ?? null;
            if (is_string($explicit) && $explicit !== '' && class_exists($explicit)) {
                return $explicit;
            }
        }

        if (! (bool) modularousConfig('module_route_presentation.class_convention', true)) {
            return null;
        }

        $convention = $this->conventionClass($route, $field);

        return class_exists($convention) ? $convention : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function driverRoot(ModuleRoute $route): array
    {
        $config = $route->config();

        foreach (['blueprint', 'presentation'] as $key) {
            $root = $config[$key] ?? null;
            if (is_array($root)) {
                return $root;
            }
        }

        return [];
    }

    /**
     * Provider / driver meta for a field.
     *
     * Precedence:
     * 1. Nested leaf (`index.columns` / `form.inputs`) when it is a class string or driver meta array
     * 2. `blueprint` / `presentation` keyed by legacy flat field name
     * 3. Compat: nested surfaces under `blueprint` / `presentation` (`blueprint.form.inputs`, …)
     *
     * @return array<string, mixed>|string|null
     */
    private function fieldMeta(ModuleRoute $route, string $field): array|string|null
    {
        $config = $route->config();
        $nestedKey = self::FIELD_NESTED[$field];

        if (Arr::has($config, $nestedKey)) {
            $nested = data_get($config, $nestedKey);
            if (self::looksLikeProviderMeta($nested)) {
                return $nested;
            }
        }

        $root = $this->driverRoot($route);
        if ($root === []) {
            return null;
        }

        // ADR: blueprint / presentation keyed by legacy field name (inputs, headers, …).
        $meta = $root[$field] ?? null;

        if (is_string($meta) || is_array($meta)) {
            return $meta;
        }

        // Compat: mis-nested surfaces under blueprint (blueprint.form.inputs, blueprint.index.columns).
        $nestedUnderRoot = data_get($root, $nestedKey);
        if (self::looksLikeProviderMeta($nestedUnderRoot)) {
            return $nestedUnderRoot;
        }

        return null;
    }

    /**
     * Nested array payload first; legacy flat key second. Provider meta is not a payload.
     */
    private function readConfigPayload(ModuleRoute $route, string $field): mixed
    {
        $config = $route->config();
        $nestedKey = self::FIELD_NESTED[$field];

        if (Arr::has($config, $nestedKey)) {
            $nested = data_get($config, $nestedKey);
            if (self::looksLikeProviderMeta($nested)) {
                return [];
            }

            return is_array($nested) ? $nested : [];
        }

        if (Arr::has($config, $field)) {
            $legacy = $config[$field];
            if (self::looksLikeProviderMeta($legacy)) {
                return [];
            }

            return is_array($legacy) ? $legacy : [];
        }

        return [];
    }

    /**
     * Nested-first then legacy flat from a raw route config array (no ModuleRoute).
     *
     * Provider-meta leaves (class FQCN / driver meta) yield [] — resolve those via ModuleRoute.
     *
     * @param  array<string, mixed>  $routeConfig
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public static function readConfigPayloadFromArray(array $routeConfig, string $field): array
    {
        $field = strtolower(trim($field));

        if (! isset(self::FIELD_NESTED[$field])) {
            $legacy = $routeConfig[$field] ?? null;

            return is_array($legacy) ? $legacy : [];
        }

        $nestedKey = self::FIELD_NESTED[$field];

        if (Arr::has($routeConfig, $nestedKey)) {
            $nested = data_get($routeConfig, $nestedKey);
            if (self::looksLikeProviderMeta($nested)) {
                return [];
            }

            return is_array($nested) ? $nested : [];
        }

        if (Arr::has($routeConfig, $field)) {
            $legacy = $routeConfig[$field];
            if (self::looksLikeProviderMeta($legacy)) {
                return [];
            }

            return is_array($legacy) ? $legacy : [];
        }

        return [];
    }

    /**
     * Class FQCN string, or meta array/object with driver/class keys.
     */
    public static function looksLikeProviderMeta(mixed $value): bool
    {
        if (is_string($value) && $value !== '') {
            return true;
        }

        if (is_array($value)) {
            return isset($value['driver']) || isset($value['class']);
        }

        if (is_object($value)) {
            return isset($value->driver) || isset($value->class);
        }

        return false;
    }

    private function normalizeField(string $field): string
    {
        $field = strtolower(trim($field));

        if (! isset(self::FIELD_BLUEPRINT[$field])) {
            throw new \InvalidArgumentException(
                "Unknown presentation field [{$field}]. Expected one of: "
                . implode(', ', array_keys(self::FIELD_BLUEPRINT))
            );
        }

        return $field;
    }
}
