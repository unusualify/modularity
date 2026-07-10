<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Unusualify\Modularous\Services\Assets;
use Unusualify\Modularous\Tests\TestCase;

class AssetsTest extends TestCase
{
    private string $publicDir;

    private string $manifestPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicDir = sys_get_temp_dir() . '/modularous-assets-' . uniqid('', true);
        mkdir($this->publicDir, 0777, true);

        $this->manifestPath = $this->publicDir . '/unusual-manifest.json';
        file_put_contents($this->manifestPath, json_encode([
            'app.js' => '/unusual/js/app.js',
            'app.css' => '/unusual/css/app.css',
        ], JSON_THROW_ON_ERROR));

        config()->set('modularous.public_dir', 'unusual');
        config()->set('modularous.manifest', 'unusual-manifest.json');
        config()->set('modularous.is_development', false);
    }

    protected function tearDown(): void
    {
        if (is_file($this->manifestPath)) {
            @unlink($this->manifestPath);
        }
        if (is_dir($this->publicDir)) {
            @rmdir($this->publicDir);
        }

        parent::tearDown();
    }

    public function test_prod_asset_reads_manifest_entry(): void
    {
        $service = new class extends Assets
        {
            public function exposeManifestPath(string $path): void
            {
                $this->manifestPathOverride = $path;
            }

            public function getManifestFilename()
            {
                return $this->manifestPathOverride ?? parent::getManifestFilename();
            }

            private ?string $manifestPathOverride = null;
        };

        $service->exposeManifestPath($this->manifestPath);

        $this->assertSame('/unusual/js/app.js', $service->prodAsset('app.js'));
    }

    public function test_prod_asset_falls_back_to_public_dir_when_manifest_entry_missing(): void
    {
        $service = new class extends Assets
        {
            public function exposeManifestPath(string $path): void
            {
                $this->manifestPathOverride = $path;
            }

            public function getManifestFilename()
            {
                return $this->manifestPathOverride ?? parent::getManifestFilename();
            }

            private ?string $manifestPathOverride = null;
        };

        $service->exposeManifestPath($this->manifestPath);

        $this->assertSame('/unusual/missing.js', $service->prodAsset('missing.js'));
    }

    public function test_dev_asset_returns_null_outside_development(): void
    {
        config()->set('modularous.is_development', false);
        app()['env'] = 'production';

        $service = new Assets;

        $this->assertNull($service->devAsset('app.js'));
    }

    public function test_asset_prefers_production_manifest_path(): void
    {
        $service = new class extends Assets
        {
            public function exposeManifestPath(string $path): void
            {
                $this->manifestPathOverride = $path;
            }

            public function getManifestFilename()
            {
                return $this->manifestPathOverride ?? parent::getManifestFilename();
            }

            private ?string $manifestPathOverride = null;
        };

        $service->exposeManifestPath($this->manifestPath);

        $this->assertSame('/unusual/js/app.js', $service->asset('app.js'));
    }
}
