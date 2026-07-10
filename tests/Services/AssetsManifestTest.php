<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Services\Assets;
use Unusualify\Modularous\Tests\TestCase;

class AssetsManifestTest extends TestCase
{
    public function test_get_manifest_filename_falls_back_to_vendor_dist_path(): void
    {
        Config::set('modularous.public_dir', 'unusual');
        Config::set('modularous.manifest', 'unusual-manifest.json');
        Config::set('modularous.vendor_path', 'vendor/unusualify/modularous');

        $path = (new Assets)->getManifestFilename();

        $this->assertStringEndsWith(
            'vendor/unusualify/modularous/vue/dist/unusual/unusual-manifest.json',
            str_replace('\\', '/', $path)
        );
    }
}
