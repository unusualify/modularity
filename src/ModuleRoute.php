<?php

declare(strict_types=1);

namespace Unusualify\Modularous;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\Traits\HasRemoteApiSource;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Http\Controllers\Traits\ManageResourceCache;
use Unusualify\Modularous\Repositories\Logic\ResourceCacheActionsTrait;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RemoteApiSourceTrait;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;
use Unusualify\Modularous\Services\ModuleRouteInspect\FeatureDetector;
use Unusualify\Modularous\Services\ModuleRouteInspect\ModuleRouteInspector;
use Unusualify\Modularous\Services\ModuleRoutePresentation\ModuleRoutePresentationResolver;

/**
 * First-class representation of a single module route.
 *
 * Aggregates config.php route slice, status-store enablement, and (lazily)
 * model/repository/feature metadata. Inspection findings stay in
 * {@see ModuleRouteInspector}.
 */
final class ModuleRoute
{
    private readonly string $name;

    private ?array $rawConfig = null;

    private ?array $config = null;

    private ?array $cachedFeatures = null;

    /** @var array<string, array<string, mixed>|list<array<string, mixed>>> */
    private array $presentationCache = [];

    private ?string $resolvedModelClass = null;

    private bool $modelClassResolved = false;

    private ?string $resolvedRepositoryClass = null;

    private bool $repositoryClassResolved = false;

    private ?string $resolvedControllerClass = null;

    private bool $controllerClassResolved = false;

    private ?bool $cachedIsSingleton = null;

    public function __construct(
        private readonly Module $module,
        string $name,
        private readonly ModuleRouteStatusStoreInterface $statusStore,
        private readonly FeatureDetector $featureDetector,
    ) {
        $this->name = studlyName($name);
    }

    public function module(): Module
    {
        return $this->module;
    }

    /**
     * Canonical Studly route name (e.g. Page, StyleSheet).
     */
    public function name(): string
    {
        return $this->name;
    }

    public function laravelRouteName(): string
    {
        $configured = $this->rawConfig('route_name');

        return (is_string($configured) && $configured !== '')
            ? $configured
            : snakeCase($this->name);
    }

    /**
     * Snake config key (e.g. page, style_sheet).
     */
    public function snakeName(): string
    {
        return snakeCase($this->name);
    }

    /**
     * Merged/processed route config from module config.php (may be empty).
     *
     * @return ($notation is null ? array<string, mixed> : mixed)
     */
    public function config($notation = null, $default = null): mixed
    {
        if ($this->config === null) {
            $loaded = $this->module->getRouteConfig($this->name);
            $this->config = is_array($loaded) ? $loaded : [];
        }

        if ($notation === null) {
            return $this->config;
        }

        return data_get($this->config, $notation, $default);
    }

    /**
     * Raw route config from module config.php (may be empty).
     *
     * Prefer this on hot paths (sidebar, headlines, parent flag).
     *
     * @return ($notation is null ? array<string, mixed> : mixed)
     */
    public function rawConfig($notation = null, $default = null): mixed
    {
        if ($this->rawConfig === null) {
            $loaded = $this->module->getRawRouteConfig($this->name);
            $this->rawConfig = is_array($loaded) ? $loaded : [];
        }

        if ($notation === null) {
            return $this->rawConfig;
        }

        return data_get($this->rawConfig, $notation, $default);
    }

    public function headline(): string
    {
        $configured = $this->rawConfig('headline');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return $this->isSingleton()
            ? singularize(headline($this->name))
            : pluralize(headline($this->name));
    }

    public function inConfig(): bool
    {
        return $this->config() !== [] || $this->rawConfig() !== [];
    }

    /**
     * Form input definitions (config / class / database via presentation resolver).
     *
     * @return list<array<string, mixed>>
     */
    public function inputs(): array
    {
        /** @var list<array<string, mixed>> $inputs */
        $inputs = $this->presentation(ModuleRoutePresentationResolver::FIELD_INPUTS);

        return array_is_list($inputs) ? $inputs : array_values($inputs);
    }

    /**
     * Table header definitions.
     *
     * @return list<array<string, mixed>>
     */
    public function headers(): array
    {
        /** @var list<array<string, mixed>> $headers */
        $headers = $this->presentation(ModuleRoutePresentationResolver::FIELD_HEADERS);

        return array_is_list($headers) ? $headers : array_values($headers);
    }

    /**
     * Table options map.
     *
     * @return array<string, mixed>
     */
    public function tableOptions(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_TABLE_OPTIONS);
    }

    /**
     * Chip / status table filters (legacy: table_filters).
     *
     * @return array<string, mixed>
     */
    public function tableFilters(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_TABLE_FILTERS);
    }

    /**
     * Advanced Vuetify filters panel (legacy: filters).
     *
     * @return array<string, mixed>
     */
    public function advancedFilters(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_ADVANCED_FILTERS);
    }

    /**
     * Index toolbar actions (legacy: table_actions).
     *
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function tableActions(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_TABLE_ACTIONS);
    }

    /**
     * Per-row table actions (legacy: table_row_actions).
     *
     * @return array<string, mixed>
     */
    public function tableRowActions(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_TABLE_ROW_ACTIONS);
    }

    /**
     * @return array<string, mixed>|list<string>
     */
    public function indexWith(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_INDEX_WITH);
    }

    /**
     * @return list<string>|array<string, mixed>
     */
    public function indexAppends(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_INDEX_APPENDS);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_FORM_OPTIONS);
    }

    /**
     * @return array<string, mixed>|list<string>
     */
    public function formWith(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_FORM_WITH);
    }

    /**
     * @return list<string>|array<string, mixed>
     */
    public function formAppends(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_FORM_APPENDS);
    }

    /**
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function formActions(): array
    {
        return $this->presentation(ModuleRoutePresentationResolver::FIELD_FORM_ACTIONS);
    }

    /**
     * @return list<array<string, mixed>>|array<string, mixed>
     */
    public function presentation(string $field): array
    {
        $field = mb_strtolower($field);

        if (! array_key_exists($field, $this->presentationCache)) {
            $this->presentationCache[$field] = $this->presentationResolver()->resolve($this, $field);
        }

        return $this->presentationCache[$field];
    }

    public function presentationDriver(string $field): string
    {
        return $this->presentationResolver()->driverFor($this, $field);
    }

    private function presentationResolver(): ModuleRoutePresentationResolver
    {
        return app(ModuleRoutePresentationResolver::class);
    }

    public function inStatuses(): bool
    {
        return array_key_exists($this->name, $this->statusStore->getStatuses($this->module->getStudlyName()));
    }

    public function isEnabled(): bool
    {
        // Prefer Module hot-path (filesystem activator) over status-store findOrFail.
        return $this->module->isEnabledModuleRoute($this->name);
    }

    public function enable(): void
    {
        $this->module->enableModuleRoute($this->name);
    }

    public function disable(): void
    {
        $this->module->disableModuleRoute($this->name);
    }

    public function isParent(): bool
    {
        $flag = $this->rawConfig('parent');
        if ($flag !== null) {
            return (bool) $flag;
        }

        return $this->module->isParentRoute($this->name);
    }

    public function isSingleton(): bool
    {
        // Share Module memo — single class-string implementation (hot-path ADR).
        return $this->cachedIsSingleton ??= $this->module->isSingleton($this->name);
    }

    public function hasRemoteApiSource(): bool
    {
        $repositoryClass = $this->repositoryClass();
        $modelClass = $this->modelClass();

        if ($repositoryClass === null || $modelClass === null) {
            return false;
        }

        return classHasTrait($repositoryClass, RemoteApiSourceTrait::class)
            && classHasTrait($modelClass, HasRemoteApiSource::class);
    }

    public function isResourceCacheEnabled(): bool
    {
        $repositoryClass = $this->repositoryClass();
        $controllerClass = $this->controllerClass();

        if ($repositoryClass === null || $controllerClass === null) {
            return false;
        }

        if (! in_array(ResourceCacheActionsTrait::class, class_uses_recursive($repositoryClass), true)) {
            return false;
        }

        if (! in_array(ManageResourceCache::class, class_uses_recursive($controllerClass), true)) {
            return false;
        }

        return ModularousCache::hasAdminCacheActions($this->module->getName(), $this->name);
    }

    public function modelClass(): ?string
    {
        if (! $this->modelClassResolved) {
            $this->resolvedModelClass = $this->safeRouteClass('model');
            $this->modelClassResolved = true;
        }

        return $this->resolvedModelClass;
    }

    public function repositoryClass(): ?string
    {
        if (! $this->repositoryClassResolved) {
            $this->resolvedRepositoryClass = $this->safeRouteClass('repository');
            $this->repositoryClassResolved = true;
        }

        return $this->resolvedRepositoryClass;
    }

    public function controllerClass(): ?string
    {
        if (! $this->controllerClassResolved) {
            try {
                $class = $this->module->getTargetClassNamespace(
                    'controller',
                    studlyName($this->name) . 'Controller'
                );
                $this->resolvedControllerClass = (is_string($class) && $class !== '' && class_exists($class))
                    ? $class
                    : null;
            } catch (\Throwable) {
                $this->resolvedControllerClass = null;
            }
            $this->controllerClassResolved = true;
        }

        return $this->resolvedControllerClass;
    }

    public function model(bool $asInstance = true): Model|string|null
    {
        $class = $this->modelClass();
        if ($class === null) {
            return null;
        }

        if (! $asInstance) {
            return $class;
        }

        try {
            return $this->module->getModel($this->name, true);
        } catch (\Throwable) {
            return null;
        }
    }

    public function repository(bool $asInstance = true): Repository|string|null
    {
        $class = $this->repositoryClass();
        if ($class === null) {
            return null;
        }

        if (! $asInstance) {
            return $class;
        }

        try {
            $repo = $this->module->getRepository($this->name, true);

            return $repo instanceof Repository ? $repo : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function controller(bool $asInstance = true): Controller|string|null
    {
        $class = $this->controllerClass();
        if ($class === null) {
            return null;
        }

        if (! $asInstance) {
            return $class;
        }

        try {
            return $this->module->getController($this->name, true);
        } catch (\Throwable) {
            return null;
        }
    }

    public function hasTable(): bool
    {
        $repository = $this->repository(true);
        if (! $repository instanceof Repository) {
            return false;
        }

        try {
            $model = $repository->getModel();
            $tableName = is_string($model) ? (new $model)->getTable() : $model->getTable();

            return Schema::hasTable($tableName);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Feature matrix from {@see FeatureDetector} (no findings).
     *
     * @return array<string, array{present: bool, model: bool|null, repository: bool|null}>
     */
    public function features(): array
    {
        if ($this->cachedFeatures === null) {
            $detected = $this->featureDetector->detect(
                $this->modelClass(),
                $this->repositoryClass()
            );
            $this->cachedFeatures = $detected['features'];
        }

        return $this->cachedFeatures;
    }

    public function hasFeature(string $featureKey): bool
    {
        $key = mb_strtolower($featureKey);

        return ($this->features()[$key]['present'] ?? false) === true;
    }

    /**
     * @return array<string, string>
     */
    public function urls(): array
    {
        try {
            return $this->module->getRouteUrls($this->name);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    public function panelUrls(bool $withoutNamePrefix = false, ?string $modelBindingValue = null): array
    {
        try {
            return $this->module->getRoutePanelUrls($this->name, $withoutNamePrefix, $modelBindingValue);
        } catch (\Throwable) {
            return [];
        }
    }

    public function actionUrl(
        string $action,
        array $replacements = [],
        bool $absolute = false,
        bool $isPanel = true
    ): string {
        return $this->module->getRouteActionUrl(
            $this->name,
            $action,
            $replacements,
            $absolute,
            $isPanel
        );
    }

    /**
     * Route name prefix with system prefix (delegates to {@see Module::fullRouteNamePrefix()}).
     */
    public function fullRouteNamePrefix(?bool $isParent = null): string
    {
        return $this->module->fullRouteNamePrefix($isParent ?? $this->isParent());
    }

    /**
     * Route name prefix with panel (admin) prefix (delegates to {@see Module::panelRouteNamePrefix()}).
     *
     * Includes a trailing dot, matching Module.
     */
    public function panelRouteNamePrefix(?bool $isParent = null): string
    {
        return $this->module->panelRouteNamePrefix($isParent ?? $this->isParent());
    }

    /**
     * Build the Laravel route-name prefix used by panel controllers.
     *
     * Mirrors historical PanelController::generateRoutePrefix behaviour, including
     * nested parent segments. Does not include a trailing dot.
     */
    public function generateRoutePrefix(
        bool $noNested = false,
        bool $isNested = false,
        ?string $nestedParentName = null,
        ?bool $isParent = null,
    ): string {
        $isParent ??= $this->isParent();
        $routePrefixes = [];

        if ($adminRoutePrefix = adminRouteNamePrefix()) {
            $routePrefixes[] = $adminRoutePrefix;
        }

        if ($this->module->hasSystemPrefix()) {
            $routePrefixes[] = $this->module->systemRouteNamePrefix();
        }

        if (! $isParent || ($isNested && ! $noNested)) {
            $routePrefixes[] = $this->module->routeNamePrefix();
        }

        if ($isNested && ! $noNested && filled($nestedParentName)) {
            $routePrefixes[] = $nestedParentName;
            $routePrefixes[] = 'nested';
        }

        return implode('.', $routePrefixes);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'module' => $this->module->getStudlyName(),
            'route' => $this->name,
            'snake' => $this->snakeName(),
            'enabled' => $this->isEnabled(),
            'parent' => $this->isParent(),
            'in_config' => $this->inConfig(),
            'in_statuses' => $this->inStatuses(),
            'model' => $this->modelClass(),
            'repository' => $this->repositoryClass(),
            'controller' => $this->controllerClass(),
            'has_table' => $this->hasTable(),
            'features' => $this->features(),
        ];
    }

    private function safeRouteClass(string $target): ?string
    {
        try {
            $class = $this->module->getRouteClass($this->name, $target);
        } catch (\Throwable) {
            return null;
        }

        if (! is_string($class) || $class === '' || ! class_exists($class)) {
            return null;
        }

        return $class;
    }
}
