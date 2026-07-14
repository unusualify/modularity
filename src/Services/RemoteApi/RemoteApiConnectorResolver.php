<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiAdapterInterface;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiConnectorInterface;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiConfigurationException;

class RemoteApiConnectorResolver
{
    /**
     * @var array<string, class-string>
     */
    private array $defaults = [
        'connector' => DefaultRemoteApiConnector::class,
        'adapter' => ConfigurableRemoteApiAdapter::class,
        'dto' => RemoteApiRecordDto::class,
    ];

    public function configuration(string $moduleName, string $routeName): RemoteApiConfiguration
    {
        $module = Modularous::findOrFail($moduleName);
        $routeName = snakeCase($routeName);
        $connectorClass = $this->resolveConnectorClass($module, $routeName);

        if ($connectorClass !== DefaultRemoteApiConnector::class) {
            return RemoteApiConfiguration::fromConnectorClass($module, $routeName, $connectorClass);
        }

        return $this->configurationFromRouteConfig($module, $routeName);
    }

    public function resolve(string $moduleName, string $routeName): RemoteApiConnectorInterface
    {
        return app(RemoteApiConnectorFactory::class)->make($moduleName, $routeName);
    }

    public function resolveAdapter(
        Module $module,
        string $routeName,
        RemoteApiConfiguration $configuration,
        RemoteApiFieldMapper $fieldMapper,
    ): RemoteApiAdapterInterface {
        $class = $configuration->classFor('adapter')
            ?? $this->resolveClass($module, $routeName, 'adapter');

        return new $class($configuration, $fieldMapper);
    }

    /**
     * @return class-string
     */
    public function resolveClass(Module $module, string $routeName, string $type): string
    {
        $routeName = snakeCase($routeName);

        if ($type !== 'connector') {
            $fromConnector = $this->resolveClassFromConnectorConfig($module, $routeName, $type);

            if ($fromConnector !== null) {
                return $fromConnector;
            }
        }

        $routeConfig = $module->getRouteConfig($routeName);
        $configured = $routeConfig['api_connector']['classes'][$type] ?? null;

        if (is_string($configured) && $configured !== '' && class_exists($configured)) {
            return $configured;
        }

        $studlyRoute = studlyName($routeName);
        $studlyType = studlyName($type);
        $convention = $module->getStudlyName() . '\\RemoteApi\\' . $studlyRoute . 'RemoteApi' . $studlyType;
        $fqcn = config('modules.namespace', 'Modules') . '\\' . $convention;

        if (class_exists($fqcn)) {
            return $fqcn;
        }

        return $this->defaults[$type] ?? RemoteApiRecordDto::class;
    }

    /**
     * @return class-string<RemoteApiConnectorInterface>
     */
    public function resolveConnectorClass(Module $module, string $routeName): string
    {
        $class = $this->resolveClass($module, $routeName, 'connector');

        if (! is_subclass_of($class, RemoteApiConnectorInterface::class) && $class !== DefaultRemoteApiConnector::class) {
            return DefaultRemoteApiConnector::class;
        }

        return $class;
    }

    private function configurationFromRouteConfig(Module $module, string $routeName): RemoteApiConfiguration
    {
        $routeConfig = $module->getRouteConfig($routeName);
        $connectorConfig = (array) ($routeConfig['api_connector'] ?? []);

        return RemoteApiConfiguration::fromRouteConfig($module, $routeName, $connectorConfig);
    }

    /**
     * @return class-string|null
     */
    private function resolveClassFromConnectorConfig(Module $module, string $routeName, string $type): ?string
    {
        $connectorClass = $this->resolveClass($module, $routeName, 'connector');

        if ($connectorClass === DefaultRemoteApiConnector::class) {
            return null;
        }

        try {
            /** @var array<string, mixed> $config */
            $config = $connectorClass::remoteApiConfiguration();
            $configured = $config['classes'][$type] ?? null;

            if (is_string($configured) && $configured !== '' && class_exists($configured)) {
                return $configured;
            }
        } catch (RemoteApiConfigurationException) {
            return null;
        }

        return null;
    }
}
