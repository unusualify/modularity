<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

/**
 * Inspect result for a single module route.
 */
final class ModuleRouteInspectEntry
{
    /**
     * @param  array<string, array{present: bool, model: bool|null, repository: bool|null, extras?: array<string, mixed>}>  $features
     * @param  list<ModuleRouteInspectFinding>  $findings
     */
    public function __construct(
        public readonly string $module,
        public readonly string $route,
        public readonly bool $enabled,
        public readonly bool $parent,
        public readonly ?string $model,
        public readonly ?string $repository,
        public readonly array $features,
        public readonly array $findings,
        public readonly bool $inConfig = true,
        public readonly bool $inStatuses = true,
    ) {
    }

    public function hasFindings(): bool
    {
        return $this->findings !== [];
    }

    /**
     * @return list<string>
     */
    public function presentFeatureKeys(): array
    {
        $keys = [];

        foreach ($this->features as $key => $state) {
            if (($state['present'] ?? false) === true) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'module' => $this->module,
            'route' => $this->route,
            'enabled' => $this->enabled,
            'parent' => $this->parent,
            'model' => $this->model,
            'repository' => $this->repository,
            'in_config' => $this->inConfig,
            'in_statuses' => $this->inStatuses,
            'features' => $this->features,
            'findings' => array_map(
                static fn (ModuleRouteInspectFinding $finding): array => $finding->toArray(),
                $this->findings
            ),
        ];
    }
}
