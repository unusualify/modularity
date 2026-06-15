<?php

namespace Modules\Cms\Services\Stylesheet;

use Modules\Cms\Entities\StyleSheet;

/**
 * Resolves external stylesheet URLs (Bootstrap / Tailwind CDN, vendor copies, definition prelude links).
 *
 * Bootstrap {@code framework_source=vendor} or {@code build} (self-hosted / pipeline output): version →
 * {@code modularous.cms_stylesheets.bootstrap.vendor_css_by_version}, then {@code definition.framework.bootstrap_css_href},
 * then {@code modularous.cms_stylesheets.bootstrap.vendor_css_href_fallback}. {@code cdn} uses jsDelivr.
 *
 * Tailwind {@code framework_source=build} or {@code vendor}: {@code definition.framework.tailwind_css_href} (built CSS URL or root-relative path),
 * else optional {@code modularous.cms_stylesheets.tailwind.build_css_href_fallback}. CDN mode may use {@code cms_stylesheets.tailwind.placeholder_cdn_href}.
 *
 * Single {@code <link>} output: set {@code definition.framework.inline_vendor_bootstrap_into_bundle} to {@code true} to prepend the resolved Bootstrap CSS
 * into the persisted public bundle and drop the separate Bootstrap stylesheet href (local {@code /} paths only unless
 * {@code cms_stylesheets.allow_http_fetch_for_inline_merge} is enabled).
 */
final class FrameworkArtifactResolver
{
    /**
     * Ordered list of absolute or root-relative {@code <link href>} targets.
     *
     * @return list<string>
     */
    public function resolveStylesheetLinks(StyleSheet $sheet): array
    {
        $definition = is_array($sheet->definition) ? $sheet->definition : [];
        $framework = isset($definition['framework']) && is_array($definition['framework']) ? $definition['framework'] : [];

        /** @var list<string> $hrefs */
        $hrefs = [];

        foreach (['prelude', 'prepend'] as $key) {
            if (! isset($framework[$key]) || ! is_array($framework[$key])) {
                continue;
            }
            foreach ($framework[$key] as $href) {
                if (is_string($href) && $this->isAllowedHref($href)) {
                    $hrefs[] = $href;
                }
            }
        }

        $driver = (string) $sheet->driver;
        $source = (string) $sheet->framework_source;
        $version = $sheet->framework_version ? (string) $sheet->framework_version : null;

        if (in_array($driver, ['bootstrap', 'hybrid'], true)) {
            if ($source === 'cdn' && $version !== null && $version !== '') {
                $hrefs[] = sprintf(
                    'https://cdn.jsdelivr.net/npm/bootstrap@%s/dist/css/bootstrap.min.css',
                    $this->sanitizeVersion($version)
                );
            } elseif (in_array($source, ['vendor', 'build'], true)) {
                $mapped = $this->resolveBootstrapVendorHref($framework, $version);
                if ($mapped !== null) {
                    $hrefs[] = $mapped;
                }
            }
        }

        if (in_array($driver, ['tailwind', 'hybrid'], true)) {
            if ($source === 'cdn' && $version !== null && $version !== '') {
                // Documented limitation: there is no official full-utility Tailwind CDN for arbitrary versions.
                // Host apps may publish a prebuilt file and set framework_source=vendor or pass link_hrefs.
                $maybe = (string) (modularousConfig('cms_stylesheets.tailwind.placeholder_cdn_href') ?? '');
                if ($maybe !== '' && $this->isAllowedHref($maybe)) {
                    $hrefs[] = $maybe;
                }
            } elseif (in_array($source, ['build', 'vendor'], true)) {
                $tw = isset($framework['tailwind_css_href']) && is_string($framework['tailwind_css_href'])
                    ? $framework['tailwind_css_href']
                    : null;
                if ($tw !== null && $this->isAllowedHref($tw)) {
                    $hrefs[] = $tw;
                } else {
                    $fallback = (string) (modularousConfig('cms_stylesheets.tailwind.build_css_href_fallback') ?? '');
                    if ($fallback !== '' && $this->isAllowedHref($fallback)) {
                        $hrefs[] = $fallback;
                    }
                }
            }
        }

        if (isset($framework['link_hrefs']) && is_array($framework['link_hrefs'])) {
            foreach ($framework['link_hrefs'] as $href) {
                if (is_string($href) && $this->isAllowedHref($href)) {
                    $hrefs[] = $href;
                }
            }
        }

        if (isset($framework['append']) && is_array($framework['append'])) {
            foreach ($framework['append'] as $href) {
                if (is_string($href) && $this->isAllowedHref($href)) {
                    $hrefs[] = $href;
                }
            }
        }

        return array_values(array_unique($hrefs));
    }

    /**
     * Primary Bootstrap stylesheet {@code href} for the Bootstrap portion (CDN, or {@code vendor}/{@code build} local map —
     * not prelude / {@code link_hrefs}).
     */
    public function resolveBootstrapStylesheetHref(StyleSheet $sheet): ?string
    {
        $definition = is_array($sheet->definition) ? $sheet->definition : [];
        $framework = isset($definition['framework']) && is_array($definition['framework']) ? $definition['framework'] : [];

        $driver = (string) $sheet->driver;
        $source = (string) $sheet->framework_source;
        $version = $sheet->framework_version ? (string) $sheet->framework_version : null;

        if (! in_array($driver, ['bootstrap', 'hybrid'], true)) {
            return null;
        }

        if ($source === 'cdn' && $version !== null && $version !== '') {
            return sprintf(
                'https://cdn.jsdelivr.net/npm/bootstrap@%s/dist/css/bootstrap.min.css',
                $this->sanitizeVersion($version)
            );
        }

        if (in_array($source, ['vendor', 'build'], true)) {
            return $this->resolveBootstrapVendorHref($framework, $version);
        }

        return null;
    }

    private function sanitizeVersion(string $version): string
    {
        return preg_replace('/[^0-9A-Za-z._-]/', '', $version) ?? '';
    }

    /**
     * @param array<string, mixed> $framework {@code definition.framework}
     */
    private function resolveBootstrapVendorHref(array $framework, ?string $version): ?string
    {
        if ($version !== null && $version !== '') {
            $map = (array) modularousConfig('cms_stylesheets.bootstrap.vendor_css_by_version', []);
            $hit = $map[$version] ?? null;
            if (is_string($hit) && $this->isAllowedHref($hit)) {
                return $hit;
            }
        }

        $explicit = $framework['bootstrap_css_href'] ?? null;
        if (is_string($explicit) && $this->isAllowedHref($explicit)) {
            return $explicit;
        }

        $fallback = (string) (modularousConfig('cms_stylesheets.bootstrap.vendor_css_href_fallback') ?? '');
        if ($fallback !== '' && $this->isAllowedHref($fallback)) {
            return $fallback;
        }

        return null;
    }

    private function isAllowedHref(string $href): bool
    {
        $href = trim($href);
        if ($href === '' || mb_strlen($href) > 2048) {
            return false;
        }

        if (str_starts_with($href, 'https://') || str_starts_with($href, 'http://')) {
            return true;
        }

        return str_starts_with($href, '/') && ! str_starts_with($href, '//');
    }
}
