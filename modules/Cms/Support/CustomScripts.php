<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Modules\Cms\Services\SiteSettingsService;

/**
 * Global head/body HTML from SiteSettings {@code scripts.head} / {@code scripts.body}
 * (CMS when filled, otherwise System). Emitted unescaped by the public layout shell:
 * head after CMP/GTM and before base/CSS; body after the footer slot (before extra layout JS).
 */
final class CustomScripts
{
    public function __construct(
        protected SiteSettingsService $settings,
    ) {}

    public function headHtml(): string
    {
        return $this->html('scripts.head');
    }

    public function bodyHtml(): string
    {
        return $this->html('scripts.body');
    }

    private function html(string $key): string
    {
        $value = $this->settings->forFrontend()->get($key, '');

        if (! is_string($value)) {
            return '';
        }

        return trim($value);
    }
}
