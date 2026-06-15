<?php

namespace Unusualify\Modularous\Tests\Services\Cms\Stylesheet;

use Modules\Cms\Entities\StyleSheet;
use Modules\Cms\Services\Stylesheet\FrameworkArtifactResolver;
use Modules\Cms\Services\Stylesheet\FrameworkScriptResolver;
use Modules\Cms\Services\Stylesheet\RootVariablesEmitter;
use Modules\Cms\Services\Stylesheet\ScssStylesheetCompiler;
use Modules\Cms\Services\Stylesheet\StylesheetCompilerService;
use Modules\Cms\Services\Stylesheet\UtilityCssGenerator;
use Unusualify\Modularous\Tests\TestCase;

final class StylesheetCompilerServiceTest extends TestCase
{
    private function compilerService(): StylesheetCompilerService
    {
        return new StylesheetCompilerService(
            new RootVariablesEmitter,
            new UtilityCssGenerator,
            new FrameworkArtifactResolver,
            new FrameworkScriptResolver,
            new ScssStylesheetCompiler,
        );
    }

    public function test_bootstrap_hybrid_adds_framework_href_when_version_set(): void
    {
        $service = $this->compilerService();

        $sheet = new StyleSheet;
        $sheet->forceFill([
            'driver' => 'hybrid',
            'framework_source' => 'cdn',
            'framework_version' => '5.3.3',
            'definition' => [
                'root' => [],
                'utilities' => [],
            ],
            'scss_source' => null,
        ]);

        $compiled = $service->compile($sheet);

        $this->assertMatchesRegularExpression(
            '#cdn\\.jsdelivr\\.net/npm/bootstrap@5\\.3\\.3/dist/css/bootstrap\\.min\\.css#',
            implode("\n", $compiled->frameworkLinkHrefs)
        );
        $this->assertSame('', $compiled->inlineCss);
    }

    public function test_custom_sheet_emits_definitions(): void
    {
        $service = $this->compilerService();

        $sheet = new StyleSheet;
        $sheet->forceFill([
            'driver' => 'custom',
            'framework_source' => 'cdn',
            'definition' => [
                'root' => ['--brand' => 'red'],
                'utilities' => [
                    'spacing' => [
                        'prefix' => 'x-',
                        'scale' => ['0' => '0'],
                        'properties' => ['margin'],
                    ],
                ],
                'raw_css' => 'main{display:block}',
            ],
        ]);

        $compiled = $service->compile($sheet);

        $this->assertSame([], $compiled->frameworkLinkHrefs);
        $this->assertStringContainsString('--brand: red;', $compiled->inlineCss);
        $this->assertStringContainsString('.x-m-0{margin:0;}', $compiled->inlineCss);
        $this->assertStringContainsString('main{display:block}', $compiled->inlineCss);
    }
}
