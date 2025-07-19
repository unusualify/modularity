<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

trait ApiVersioning
{
    /**
     * Available API versions
     *
     * @var array
     */
    protected $availableVersions = ['v1', 'v2'];

    /**
     * Get API version from request
     */
    protected function getApiVersion(): string
    {
        $version = $this->request->header('API-Version') ??
                   $this->request->get('version') ??
                   $this->apiVersion;

        if (in_array($version, $this->availableVersions)) {
            return $version;
        }

        return $this->availableVersions[0]; // Default to first version
    }

    /**
     * Check if API version is supported
     */
    protected function isVersionSupported(string $version): bool
    {
        return in_array($version, $this->availableVersions);
    }

    /**
     * Get version-specific resource class
     */
    protected function getVersionedResourceClass(string $baseClass): ?string
    {
        $version = $this->getApiVersion();
        $versionedClass = str_replace('Resource', ucfirst($version) . 'Resource', $baseClass);

        if (class_exists($versionedClass)) {
            return $versionedClass;
        }

        return $baseClass;
    }
}
