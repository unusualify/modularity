<?php

declare(strict_types=1);

namespace Unusualify\Modularous;

use Illuminate\Support\Collection;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;
use Unusualify\Modularous\Services\ModuleRouteInspect\FeatureDetector;

/**
 * Builds {@see ModuleRoute} instances for a module from config ∪ status keys.
 */
final class ModuleRouteRegistry
{
    /** @var array<string, ModuleRoute> */
    private array $instances = [];

    /** @var list<string>|null */
    private ?array $unionCache = null;

    /** @var array<string, true>|null */
    private ?array $unionSet = null;

    /** @var array<string, true>|null */
    private ?array $configSet = null;

    /** @var array<string, true>|null */
    private ?array $statusSet = null;

    public function __construct(
        private readonly Module $module,
        private readonly ModuleRouteStatusStoreInterface $statusStore,
        private readonly FeatureDetector $featureDetector,
    ) {
    }

    /**
     * @return Collection<string, ModuleRoute> keyed by Studly route name
     */
    public function all(): Collection
    {
        $routes = collect();

        foreach ($this->unionNames() as $name) {
            $routes->put($name, $this->make($name));
        }

        return $routes->sortKeys();
    }

    public function find(string $routeName): ?ModuleRoute
    {
        $wanted = studlyName($routeName);

        if (isset($this->instances[$wanted])) {
            return $this->instances[$wanted];
        }

        // Hot path: config-listed routes resolve without reading status∪config union.
        if ($this->hasConfigName($wanted) || $this->hasStatusName($wanted)) {
            return $this->make($wanted);
        }

        return null;
    }

    public function get(string $routeName): ModuleRoute
    {
        $route = $this->find($routeName);

        if ($route === null) {
            throw new \InvalidArgumentException(
                "Route [{$routeName}] not found on module [{$this->module->getStudlyName()}]."
            );
        }

        return $route;
    }

    /**
     * @return list<string> Studly names
     */
    public function names(): array
    {
        $names = $this->unionNames();
        sort($names);

        return $names;
    }

    /**
     * @return Collection<string, ModuleRoute>
     */
    public function enabled(): Collection
    {
        return $this->all()->filter(
            static fn (ModuleRoute $route): bool => $route->isEnabled()
        );
    }

    private function make(string $name): ModuleRoute
    {
        return $this->instances[$name] ??= new ModuleRoute(
            $this->module,
            $name,
            $this->statusStore,
            $this->featureDetector,
        );
    }

    /**
     * @return list<string>
     */
    private function unionNames(): array
    {
        return $this->unionCache ??= array_values(array_unique(array_merge(
            array_map(
                static fn (string|int $key): string => studlyName((string) $key),
                array_keys($this->statusStore->getStatuses($this->module->getStudlyName()))
            ),
            $this->configNames(),
        )));
    }

    private function hasName(string $studlyName): bool
    {
        $this->unionSet ??= array_fill_keys($this->unionNames(), true);

        return isset($this->unionSet[$studlyName]);
    }

    private function hasConfigName(string $studlyName): bool
    {
        $this->configSet ??= array_fill_keys($this->configNames(), true);

        return isset($this->configSet[$studlyName]);
    }

    private function hasStatusName(string $studlyName): bool
    {
        $this->statusSet ??= array_fill_keys(
            array_map(
                static fn (string|int $key): string => studlyName((string) $key),
                array_keys($this->statusStore->getStatuses($this->module->getStudlyName()))
            ),
            true,
        );

        return isset($this->statusSet[$studlyName]);
    }

    /**
     * Route names from raw config only — avoid processed getRouteConfigs on every lookup.
     *
     * @return list<string>
     */
    private function configNames(): array
    {
        $names = [];
        $configs = $this->module->getRawRouteConfigs(null, true) ?: [];

        foreach ($configs as $key => $config) {
            if (! is_array($config)) {
                continue;
            }

            $name = $config['name'] ?? null;
            if (is_string($name) && $name !== '') {
                $names[] = studlyName($name);
            } elseif (is_string($key) && $key !== '') {
                $names[] = studlyName($key);
            }
        }

        return array_values(array_unique($names));
    }
}
