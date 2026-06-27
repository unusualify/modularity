<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiConfigurationException;

class RemoteApiConfiguration
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        public readonly Module $module,
        public readonly string $routeName,
        public readonly array $config,
    ) {
    }

    /**
     * @param class-string<AbstractRemoteApiConnector> $connectorClass
     */
    public static function fromConnectorClass(Module $module, string $routeName, string $connectorClass): self
    {
        $routeName = snakeCase($routeName);

        if (! is_a($connectorClass, AbstractRemoteApiConnector::class, true)) {
            throw RemoteApiConfigurationException::invalidConnectorClass($connectorClass, $module->getName(), $routeName);
        }

        /** @var array<string, mixed> $config */
        $config = $connectorClass::remoteApiConfiguration();

        $config['classes'] = array_merge(
            ['connector' => $connectorClass],
            (array) ($config['classes'] ?? []),
        );

        if (! ($config['enabled'] ?? false)) {
            throw RemoteApiConfigurationException::disabled($module->getName(), $routeName);
        }

        $configuration = new self($module, $routeName, $config);
        $configuration->baseUrl();
        $configuration->endpoint();

        return $configuration;
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromRouteConfig(Module $module, string $routeName, array $config): self
    {
        $routeName = snakeCase($routeName);

        if (! ($config['enabled'] ?? false)) {
            throw RemoteApiConfigurationException::disabled($module->getName(), $routeName);
        }

        $configuration = new self($module, $routeName, $config);
        $configuration->baseUrl();
        $configuration->endpoint();

        return $configuration;
    }

    public function moduleName(): string
    {
        return $this->module->getName();
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    public function baseUrl(): string
    {
        $baseUrl = $this->config['base_url']
            ?? config('modularous.remote_api.base_url')
            ?? '';

        $baseUrl = rtrim((string) $baseUrl, '/');

        if ($baseUrl === '') {
            throw RemoteApiConfigurationException::missingBaseUrl($this->moduleName(), $this->routeName);
        }

        return $baseUrl;
    }

    public function token(): ?string
    {
        $token = $this->config['token'] ?? config('modularous.remote_api.token');

        return filled($token) ? (string) $token : null;
    }

    public function timeout(): int
    {
        return (int) (
            $this->config['http']['timeout']
            ?? $this->config['timeout']
            ?? config('modularous.remote_api.timeout', 30)
        );
    }

    public function endpoint(): string
    {
        $endpoint = (string) ($this->config['endpoint'] ?? '');

        if ($endpoint === '') {
            throw RemoteApiConfigurationException::missingEndpoint($this->moduleName(), $this->routeName);
        }

        return ltrim($endpoint, '/');
    }

    public function showEndpoint(int|string $remoteId): string
    {
        $pattern = (string) ($this->config['show_endpoint'] ?? $this->endpoint() . '/{id}');

        return ltrim(str_replace('{id}', (string) $remoteId, $pattern), '/');
    }

    public function remoteIdColumn(): string
    {
        return (string) ($this->config['remote_id_column'] ?? 'remote_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function httpQuery(): array
    {
        return (array) ($this->config['http']['query'] ?? []);
    }

    /**
     * @return array<int, string>
     */
    public function includes(): array
    {
        return array_values((array) ($this->config['http']['includes'] ?? []));
    }

    /**
     * Lightweight HTTP query for admin catalog pickers (remote_id combobox).
     *
     * @return array<string, mixed>
     */
    public function catalogHttpQuery(): array
    {
        return (array) ($this->config['catalog_http']['query'] ?? [
            'per_page' => 100,
        ]);
    }

    public function listPath(): string
    {
        return (string) ($this->config['response']['list_path'] ?? 'data.data');
    }

    public function itemPath(): string
    {
        return (string) ($this->config['response']['item_path'] ?? 'data');
    }

    public function metaPath(): string
    {
        return (string) ($this->config['response']['meta_path'] ?? 'data');
    }

    public function catalogListPath(?string $catalogKey = null): string
    {
        if ($catalogKey !== null) {
            $catalog = $this->catalog($catalogKey);

            if (isset($catalog['list_path'])) {
                return (string) $catalog['list_path'];
            }
        }

        return (string) ($this->config['response']['catalog_list_path'] ?? 'data.data');
    }

    /**
     * @return array<string, string>
     */
    public function mapping(): array
    {
        return (array) ($this->config['mapping'] ?? []);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function fields(): array
    {
        return (array) ($this->config['fields'] ?? []);
    }

    /**
     * @return array<int, string>
     */
    public function preserveLocalFields(): array
    {
        return array_values((array) ($this->config['sync']['preserve_local_fields'] ?? []));
    }

    public function importNewFromRemoteList(): bool
    {
        return (bool) ($this->config['sync']['import_new_from_list'] ?? true);
    }

    public function cacheEnabled(): bool
    {
        return (bool) ($this->config['cache']['enabled'] ?? true);
    }

    public function cacheTtl(): int
    {
        return (int) (
            $this->config['cache']['ttl']
            ?? config('modularous.remote_api.cache_ttl', 3600)
        );
    }

    public function cacheTag(): string
    {
        $default = sprintf(
            'remote-api.%s.%s',
            snakeCase($this->moduleName()),
            snakeCase($this->routeName)
        );

        return (string) ($this->config['cache']['tag'] ?? $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function catalog(string $catalogKey): array
    {
        $catalogs = (array) ($this->config['catalogs'] ?? []);

        if (! isset($catalogs[$catalogKey]) || ! is_array($catalogs[$catalogKey])) {
            throw RemoteApiConfigurationException::invalidCatalog($catalogKey, $this->moduleName(), $this->routeName);
        }

        return $catalogs[$catalogKey];
    }

    public function catalogEndpoint(string $catalogKey): string
    {
        $catalog = $this->catalog($catalogKey);
        $endpoint = (string) ($catalog['endpoint'] ?? '');

        if ($endpoint === '') {
            throw RemoteApiConfigurationException::invalidCatalog($catalogKey, $this->moduleName(), $this->routeName);
        }

        return ltrim($endpoint, '/');
    }

    /**
     * @return array<int, string>
     */
    public function actions(): array
    {
        return array_values((array) ($this->config['actions'] ?? []));
    }

    public function previewResponseDisplay(): string
    {
        $display = (string) ($this->config['preview']['display'] ?? 'fields');

        return in_array($display, ['fields', 'raw'], true) ? $display : 'fields';
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function previewResponseFields(): array
    {
        $fields = $this->config['preview']['fields'] ?? null;

        if (! is_array($fields) || $fields === []) {
            return [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'description', 'label' => 'Description'],
            ];
        }

        $normalized = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $key = (string) ($field['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $normalized[] = [
                'key' => $key,
                'label' => (string) ($field['label'] ?? $key),
            ];
        }

        return $normalized !== [] ? $normalized : [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'description', 'label' => 'Description'],
        ];
    }

    public function classFor(string $type): ?string
    {
        $class = $this->config['classes'][$type] ?? null;

        return is_string($class) && $class !== '' ? $class : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSummaryArray(): array
    {
        return [
            'module' => $this->moduleName(),
            'route' => $this->routeName,
            'base_url' => $this->baseUrl(),
            'endpoint' => $this->endpoint(),
            'remote_id_column' => $this->remoteIdColumn(),
            'includes' => $this->includes(),
            'cache_enabled' => $this->cacheEnabled(),
            'token_configured' => filled($this->token()),
        ];
    }

    /**
     * @return list<string>
     */
    public function catalogKeys(): array
    {
        return array_keys((array) ($this->config['catalogs'] ?? []));
    }
}
