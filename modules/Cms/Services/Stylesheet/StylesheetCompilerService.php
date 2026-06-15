<?php

namespace Modules\Cms\Services\Stylesheet;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Entities\StyleSheet;

/**
 * Compiles JSON definition + optional SCSS into custom CSS; resolves framework {@code <link>} targets separately.
 */
final class StylesheetCompilerService
{
    public const COMPILER_VERSION = '1.0.0';

    public function __construct(
        private readonly RootVariablesEmitter $rootVariables,
        private readonly UtilityCssGenerator $utilities,
        private readonly FrameworkArtifactResolver $framework,
        private readonly FrameworkScriptResolver $frameworkScripts,
        private readonly ScssStylesheetCompiler $scss,
    ) {}

    public function compile(StyleSheet $sheet): StylesheetCompilationResult
    {
        $definition = is_array($sheet->definition) ? $sheet->definition : [];

        $root = isset($definition['root']) && is_array($definition['root']) ? $definition['root'] : [];
        $util = isset($definition['utilities']) && is_array($definition['utilities']) ? $definition['utilities'] : [];
        $raw = isset($definition['raw_css']) && is_string($definition['raw_css']) ? $definition['raw_css'] : '';

        $css = '';
        $css .= $this->rootVariables->emit($root);
        $css .= $this->utilities->generate($util);
        if ($raw !== '') {
            $css .= "\n" . $this->limitRawCss($raw) . "\n";
        }
        $css .= $this->scss->compileIfEnabled($sheet->scss_source);

        $links = $this->framework->resolveStylesheetLinks($sheet);
        $scripts = $this->frameworkScripts->resolveScriptSrcs($sheet);

        $css = trim($css);
        [$css, $links] = $this->maybeInlineVendorBootstrapCssIntoBundle($sheet, $css, $links);

        return new StylesheetCompilationResult($links, $css, $scripts);
    }

    /**
     * Writes {@see StylesheetCompilationResult::$inlineCss} to the configured disk and updates model columns quietly.
     */
    public function persistCompiled(StyleSheet $sheet): void
    {
        $result = $this->compile($sheet);

        $disk = (string) modularousConfig('cms_stylesheets.compiled_disk', 'local');
        $dir = trim((string) modularousConfig('cms_stylesheets.compiled_directory', 'modularous/cms/stylesheets'), '/');
        $path = $dir . '/' . $sheet->getKey() . '.css';

        Storage::disk($disk)->put($path, $result->inlineCss . "\n");
        $checksum = hash('sha256', $result->inlineCss);

        $sheet->forceFill([
            'compiled_disk' => $disk,
            'compiled_path' => $path,
            'compiled_checksum' => $checksum,
            'compiled_at' => now(),
            'compiler_version' => self::COMPILER_VERSION,
        ])->saveQuietly();
    }

    private function limitRawCss(string $raw): string
    {
        $max = (int) modularousConfig('cms_stylesheets.max_raw_css_bytes', 512_000);
        if (strlen($raw) > $max) {
            return mb_substr($raw, 0, $max);
        }

        return $raw;
    }

    /**
     * When {@code definition.framework.inline_vendor_bootstrap_into_bundle} is true, prepend the resolved Bootstrap
     * stylesheet body to the compiled bundle and remove that href from {@code $links} so the shell emits one CSS link
     * (the public bundle). Local root-relative {@code /} files only unless {@code cms_stylesheets.allow_http_fetch_for_inline_merge}.
     *
     * @param  list<string>  $links
     * @return array{0: string, 1: list<string>}
     */
    private function maybeInlineVendorBootstrapCssIntoBundle(StyleSheet $sheet, string $css, array $links): array
    {
        $definition = is_array($sheet->definition) ? $sheet->definition : [];
        $framework = isset($definition['framework']) && is_array($definition['framework']) ? $definition['framework'] : [];

        if (! ($framework['inline_vendor_bootstrap_into_bundle'] ?? false)) {
            return [$css, $links];
        }

        if (! in_array((string) $sheet->driver, ['bootstrap', 'hybrid'], true)) {
            return [$css, $links];
        }

        $href = $this->framework->resolveBootstrapStylesheetHref($sheet);
        if ($href === null) {
            return [$css, $links];
        }

        $hrefIndex = null;
        foreach ($links as $i => $h) {
            if ($h === $href) {
                $hrefIndex = $i;
                break;
            }
        }
        if ($hrefIndex === null) {
            return [$css, $links];
        }

        $body = $this->loadStylesheetBodyForInlineMerge($href);
        if ($body === '') {
            return [$css, $links];
        }

        $max = max(1024, (int) modularousConfig('cms_stylesheets.max_inline_vendor_css_bytes', 2_000_000));
        if (strlen($body) > $max) {
            $body = mb_substr($body, 0, $max);
        }

        $merged = trim($body) . "\n" . $css;
        unset($links[$hrefIndex]);
        $links = array_values($links);

        return [trim($merged), $links];
    }

    private function loadStylesheetBodyForInlineMerge(string $href): string
    {
        $href = trim($href);
        if ($href === '') {
            return '';
        }

        if (str_starts_with($href, '/') && ! str_starts_with($href, '//')) {
            $path = public_path(ltrim($href, '/'));
            if (is_file($path) && is_readable($path)) {
                $raw = @file_get_contents($path);

                return is_string($raw) ? $raw : '';
            }

            return '';
        }

        if ((str_starts_with($href, 'https://') || str_starts_with($href, 'http://'))
            && (bool) modularousConfig('cms_stylesheets.allow_http_fetch_for_inline_merge', false)) {
            try {
                $resp = Http::timeout(20)->get($href);
                if ($resp->successful()) {
                    return (string) $resp->body();
                }
            } catch (\Throwable) {
            }
        }

        return '';
    }
}
