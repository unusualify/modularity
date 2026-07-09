<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Http\Middleware\LoadLocalizedConfig;
use Unusualify\Modularous\Tests\TestCase;

class LoadLocalizedConfigTest extends TestCase
{
    public function test_merges_project_modularous_config_overrides(): void
    {
        $baseKey = modularousBaseKey();
        $configDir = base_path('modularous');

        if (! is_dir($configDir)) {
            mkdir($configDir, 0777, true);
        }

        $overridePath = $configDir . '/custom-feature.php';
        file_put_contents($overridePath, '<?php return ["enabled" => true, "label" => "from-project"];');

        Config::set("{$baseKey}.custom-feature", [
            'enabled' => false,
            'label' => 'from-package',
            'keep' => true,
        ]);

        $middleware = new LoadLocalizedConfig;
        $request = Request::create('/admin', 'GET');

        $middleware->handle($request, fn () => response('loaded'));

        $merged = config("{$baseKey}.custom-feature");
        $this->assertTrue($merged['enabled']);
        $this->assertSame('from-project', $merged['label']);
        $this->assertTrue($merged['keep']);

        unlink($overridePath);
    }
}
