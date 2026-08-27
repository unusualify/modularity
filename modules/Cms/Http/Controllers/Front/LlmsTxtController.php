<?php

namespace Modules\Cms\Http\Controllers\Front;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Cms\Services\CmsSiteSeoSettingsService;
use Modules\Cms\Support\CmsPublicSeo;

/**
 * Serves global llms.txt at GET /llms.txt when the route is enabled.
 *
 * Resolution order: {@see CmsSiteSeoSettingsService::resolvedLlmsTxtBody()} (DB when enabled), then env/config.
 *
 * @see https://llmstxt.org/
 */
class LlmsTxtController extends Controller
{
    public function __invoke(CmsSiteSeoSettingsService $siteSeo): Response
    {
        $body = self::resolvedBody($siteSeo);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Normalized body (single trailing newline) for tests and reuse.
     *
     * Uses {@see CmsSiteSeoSettingsService} when the container can resolve it; otherwise env/config only
     * (e.g. lightweight testbench without CMS bindings).
     */
    public static function resolvedBody(?CmsSiteSeoSettingsService $siteSeo = null): string
    {
        if ($siteSeo instanceof CmsSiteSeoSettingsService) {
            return $siteSeo->resolvedLlmsTxtBody();
        }

        if (function_exists('app') && app()->bound(CmsSiteSeoSettingsService::class)) {
            return app(CmsSiteSeoSettingsService::class)->resolvedLlmsTxtBody();
        }

        return self::resolvedBodyFromConfigOnly();
    }

    /**
     * Env/config fallback when DB-backed service is unavailable.
     */
    public static function resolvedBodyFromConfigOnly(): string
    {
        if (($staging = CmsPublicSeo::resolvedStagingLlmsTxtBody()) !== null) {
            return $staging;
        }

        $default = CmsPublicSeo::LLMS_TXT_DEFAULT;
        $raw = trim((string) modularousConfig('cms_seo.llms.global_llms_txt', $default));

        if ($raw === '') {
            $raw = trim($default);
        }

        return $raw . "\n";
    }
}
