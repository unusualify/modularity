<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Support;

use Modules\Cms\Services\SiteSettingsService;

/**
 * Builds GTM / optional GA4 snippets from SiteSettings analytics keys
 * (CmsSettings on frontend with SystemSettings fallback).
 *
 * Effective enablement: DB `analytics.enabled` when set; otherwise
 * {@see modularousConfig('system_settings.analytics_enabled_default')}.
 */
final class AnalyticsScripts
{
    public function __construct(
        protected SiteSettingsService $settings,
    ) {}

    public function isEnabled(): bool
    {
        $db = $this->settings->get('analytics.enabled');

        if ($db === null || $db === '') {
            return (bool) modularousConfig(
                'system_settings.analytics_enabled_default',
                env('MODULAROUS_ANALYTICS_ENABLED', false)
            );
        }

        return filter_var($db, FILTER_VALIDATE_BOOLEAN);
    }

    public function gtmId(): ?string
    {
        return $this->normalizeId(
            $this->settings->get('analytics.gtm_id'),
            '/^GTM-[A-Z0-9]+$/i'
        );
    }

    public function gaMeasurementId(): ?string
    {
        return $this->normalizeId(
            $this->settings->get('analytics.ga_measurement_id'),
            '/^G-[A-Z0-9]+$/i'
        );
    }

    /**
     * GTM bootstrap + optional gtag.js for GA4 (only when measurement ID is set).
     */
    public function headHtml(): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        $parts = [];

        $gtmId = $this->gtmId();
        if ($gtmId !== null) {
            $escaped = e($gtmId);
            $parts[] = <<<HTML
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$escaped}');</script>
<!-- End Google Tag Manager -->
HTML;
        }

        $gaId = $this->gaMeasurementId();
        if ($gaId !== null) {
            $escaped = e($gaId);
            $parts[] = <<<HTML
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$escaped}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$escaped}');
</script>
HTML;
        }

        return implode("\n", $parts);
    }

    /**
     * GTM noscript iframe for immediately after the opening body tag.
     */
    public function bodyOpenHtml(): string
    {
        if (! $this->isEnabled()) {
            return '';
        }

        $gtmId = $this->gtmId();
        if ($gtmId === null) {
            return '';
        }

        $escaped = e($gtmId);

        return <<<HTML
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$escaped}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
HTML;
    }

    protected function normalizeId(mixed $value, string $pattern): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $id = trim((string) $value);
        if ($id === '' || ! preg_match($pattern, $id)) {
            return null;
        }

        return mb_strtoupper($id);
    }
}
