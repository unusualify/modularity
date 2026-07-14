<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiConfigurationException;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiConfigurationExceptionTest extends TestCase
{
    public function test_disabled_factory_sets_message(): void
    {
        $exception = RemoteApiConfigurationException::disabled('BusinessPackage', 'Package');

        $this->assertSame(
            'Remote API connector is disabled for BusinessPackage::Package.',
            $exception->getMessage()
        );
    }

    public function test_missing_endpoint_factory_sets_message(): void
    {
        $exception = RemoteApiConfigurationException::missingEndpoint('BusinessPackage', 'Package');

        $this->assertSame(
            'Remote API endpoint is not configured for BusinessPackage::Package.',
            $exception->getMessage()
        );
    }

    public function test_missing_base_url_factory_sets_message(): void
    {
        $exception = RemoteApiConfigurationException::missingBaseUrl('BusinessPackage', 'Package');

        $this->assertSame(
            'Remote API base URL is not configured for BusinessPackage::Package.',
            $exception->getMessage()
        );
    }

    public function test_invalid_catalog_factory_sets_message(): void
    {
        $exception = RemoteApiConfigurationException::invalidCatalog('packages', 'BusinessPackage', 'Package');

        $this->assertSame(
            'Remote API catalog [packages] is not configured for BusinessPackage::Package.',
            $exception->getMessage()
        );
    }

    public function test_invalid_connector_class_factory_sets_message(): void
    {
        $exception = RemoteApiConfigurationException::invalidConnectorClass(
            'InvalidConnector',
            'BusinessPackage',
            'Package'
        );

        $this->assertSame(
            'Remote API connector class [InvalidConnector] is invalid for BusinessPackage::Package.',
            $exception->getMessage()
        );
    }
}
