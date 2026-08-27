<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Http\Controllers\Front\LlmsTxtController;
use Modules\Cms\Support\CmsPublicSeo;
use Unusualify\Modularous\Tests\TestCase;

class LlmsTxtControllerTest extends TestCase
{
    public function test_resolved_body_uses_config_and_trailing_newline(): void
    {
        config([
            'modularous.cms_seo.llms.global_llms_txt' => "# B2Press\n\n> Press distribution",
        ]);

        $body = LlmsTxtController::resolvedBody();

        $this->assertStringEndsWith("\n", $body);
        $this->assertStringContainsString('# B2Press', $body);
    }

    public function test_resolved_body_falls_back_when_empty_config(): void
    {
        config(['modularous.cms_seo.llms.global_llms_txt' => '   ']);

        $body = LlmsTxtController::resolvedBody();

        $this->assertSame(CmsPublicSeo::LLMS_TXT_DEFAULT . "\n", $body);
    }

    public function test_staging_force_noindex_serves_staging_llms_txt(): void
    {
        config([
            'modularous.cms_seo.staging.force_noindex' => true,
            'modularous.cms_seo.llms.global_llms_txt' => '# Production',
        ]);

        $body = LlmsTxtController::resolvedBodyFromConfigOnly();

        $this->assertSame(CmsPublicSeo::LLMS_TXT_STAGING . "\n", $body);
    }
}
