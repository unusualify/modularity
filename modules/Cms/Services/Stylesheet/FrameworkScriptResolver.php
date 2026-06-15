<?php

namespace Modules\Cms\Services\Stylesheet;

use Modules\Cms\Entities\StyleSheet;

/**
 * Resolves optional framework {@code <script src>} URLs (Bootstrap bundle includes Popper).
 *
 * Tailwind utilities CSS does not ship a required JS bundle; add arbitrary scripts via
 * {@code definition.framework.script_prepend} / {@code script_append}.
 */
final class FrameworkScriptResolver
{
    /**
     * Ordered list of absolute or root-relative script {@code src} values (deduped by caller).
     *
     * @return list<string>
     */
    public function resolveScriptSrcs(StyleSheet $sheet): array
    {
        $definition = is_array($sheet->definition) ? $sheet->definition : [];
        $framework = isset($definition['framework']) && is_array($definition['framework']) ? $definition['framework'] : [];

        /** @var list<string> $srcs */
        $srcs = [];

        foreach (['script_prepend'] as $key) {
            if (! isset($framework[$key]) || ! is_array($framework[$key])) {
                continue;
            }
            foreach ($framework[$key] as $src) {
                if (is_string($src) && $this->isAllowedSrc($src)) {
                    $srcs[] = $src;
                }
            }
        }

        $driver = (string) $sheet->driver;
        $source = (string) $sheet->framework_source;
        $version = $sheet->framework_version ? (string) $sheet->framework_version : null;

        $includeBootstrapJs = ! isset($framework['include_bootstrap_js']) || $framework['include_bootstrap_js'] !== false;

        if ($includeBootstrapJs && in_array($driver, ['bootstrap', 'hybrid'], true)) {
            if ($source === 'cdn' && $version !== null && $version !== '') {
                $srcs[] = sprintf(
                    'https://cdn.jsdelivr.net/npm/bootstrap@%s/dist/js/bootstrap.bundle.min.js',
                    $this->sanitizeVersion($version)
                );
            } elseif ($source === 'vendor' || $source === 'build') {
                $mapped = $this->resolveBootstrapBundleJsSrc($framework, $version);
                if ($mapped !== null) {
                    $srcs[] = $mapped;
                }
            }
        }

        foreach (['script_append'] as $key) {
            if (! isset($framework[$key]) || ! is_array($framework[$key])) {
                continue;
            }
            foreach ($framework[$key] as $src) {
                if (is_string($src) && $this->isAllowedSrc($src)) {
                    $srcs[] = $src;
                }
            }
        }

        return array_values(array_unique($srcs));
    }

    private function sanitizeVersion(string $version): string
    {
        return preg_replace('/[^0-9A-Za-z._-]/', '', $version) ?? '';
    }

    /**
     * @param  array<string, mixed>  $framework  {@code definition.framework}
     */
    private function resolveBootstrapBundleJsSrc(array $framework, ?string $version): ?string
    {
        if ($version !== null && $version !== '') {
            $map = (array) modularousConfig('cms_stylesheets.bootstrap.vendor_bundle_js_by_version', []);
            $hit = $map[$version] ?? null;
            if (is_string($hit) && $this->isAllowedSrc($hit)) {
                return $hit;
            }
        }

        $explicit = $framework['bootstrap_bundle_js_href'] ?? null;
        if (is_string($explicit) && $this->isAllowedSrc($explicit)) {
            return $explicit;
        }

        $fallback = (string) (modularousConfig('cms_stylesheets.bootstrap.vendor_bundle_js_href_fallback') ?? '');
        if ($fallback !== '' && $this->isAllowedSrc($fallback)) {
            return $fallback;
        }

        return null;
    }

    private function isAllowedSrc(string $src): bool
    {
        $src = trim($src);
        if ($src === '' || strlen($src) > 2048) {
            return false;
        }

        if (str_starts_with($src, 'https://') || str_starts_with($src, 'http://')) {
            return true;
        }

        return str_starts_with($src, '/') && ! str_starts_with($src, '//');
    }
}
