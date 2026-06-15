<?php

namespace Modules\Cms\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Entities\StyleSheet;
use Modules\Cms\Services\Stylesheet\StylesheetCompilerService;

/**
 * Contract for LayoutBuilder: attach {@see LayoutBuilder::$style_sheet_id} (or future slugs[]) and resolve
 * framework {@code <link>} / {@code <script src>} targets at render time.
 *
 * Usage in Blade (after {@see StyleSheet} is loaded for the layout):
 *
 * {@code @foreach (StylesheetManager::linkHrefsForSheet($sheet) as $href)}
 * {@code   <link rel="stylesheet" href="{{ $href }}">}
 * {@code @endforeach}
 */
final class StylesheetManager
{
    /**
     * Full ordered list of stylesheet hrefs: framework CDNs / vendor URLs first, then the compiled custom bundle URL
     * when the sheet produces non-empty custom CSS (empty bundles are not linked).
     *
     * @return list<string>
     */
    public static function linkHrefsForSheet(?StyleSheet $sheet): array
    {
        if ($sheet === null) {
            return [];
        }

        $compiler = app(StylesheetCompilerService::class);
        $result = $compiler->compile($sheet);

        $hrefs = $result->frameworkLinkHrefs;
        $bundle = self::compiledBundleUrl($sheet, self::fingerprintForInlineCss($result->inlineCss));
        if ($bundle !== null && trim($result->inlineCss) !== '') {
            $hrefs[] = $bundle;
        }

        return array_values(array_unique($hrefs));
    }

    /**
     * Optional framework script {@code src} values (e.g. Bootstrap bundle for modals / dropdowns).
     *
     * @return list<string>
     */
    public static function scriptSrcsForSheet(?StyleSheet $sheet): array
    {
        if ($sheet === null) {
            return [];
        }

        $result = app(StylesheetCompilerService::class)->compile($sheet);

        return array_values(array_unique($result->frameworkScriptSrcs));
    }

    /**
     * Merges the primary FK sheet (if set) followed by extras from {@see LayoutBuilder::$style_sheet_slugs}.
     * Each sheet is compiled once; duplicate URLs are stripped while keeping the first occurrence.
     *
     * @return array{linkHrefs: list<string>, scriptSrcs: list<string>}
     */
    public static function frameworkAssetsForLayoutBuilder(?LayoutBuilder $layout): array
    {
        if ($layout === null) {
            return ['linkHrefs' => [], 'scriptSrcs' => []];
        }

        $layout->loadMissing('styleSheet');

        $compiler = app(StylesheetCompilerService::class);
        $mergedHrefs = [];
        $mergedScripts = [];

        foreach (self::layoutBuilderSheetsInMergeOrder($layout) as $sheet) {
            $result = $compiler->compile($sheet);
            foreach ($result->frameworkLinkHrefs as $href) {
                $mergedHrefs[] = $href;
            }
            foreach ($result->frameworkScriptSrcs as $src) {
                $mergedScripts[] = $src;
            }

            $bundle = self::compiledBundleUrl($sheet, self::fingerprintForInlineCss($result->inlineCss));
            if ($bundle !== null && trim($result->inlineCss) !== '') {
                $mergedHrefs[] = $bundle;
            }
        }

        return [
            'linkHrefs' => array_values(array_unique($mergedHrefs)),
            'scriptSrcs' => array_values(array_unique($mergedScripts)),
        ];
    }

    /**
     * @return list<string>
     */
    public static function linkHrefsForLayoutBuilder(?LayoutBuilder $layout): array
    {
        return self::frameworkAssetsForLayoutBuilder($layout)['linkHrefs'];
    }

    /**
     * @return list<string>
     */
    public static function scriptSrcsForLayoutBuilder(?LayoutBuilder $layout): array
    {
        return self::frameworkAssetsForLayoutBuilder($layout)['scriptSrcs'];
    }

    /**
     * Sheets to merge: FK first, then slugs listed in JSON (skipped if same as FK or duplicate slug).
     *
     * @return list<StyleSheet>
     */
    private static function layoutBuilderSheetsInMergeOrder(LayoutBuilder $layout): array
    {
        $out = [];
        $seenIds = [];

        $primary = $layout->styleSheet;
        if ($primary !== null) {
            $out[] = $primary;
            $seenIds[$primary->getKey()] = true;
        }

        $slugs = $layout->style_sheet_slugs;
        if (! is_array($slugs)) {
            return $out;
        }

        foreach ($slugs as $slug) {
            if (! is_string($slug) || $slug === '') {
                continue;
            }

            /** @var StyleSheet|null $extra */
            $extra = StyleSheet::query()->where('slug', $slug)->first();
            if ($extra === null) {
                continue;
            }

            if (isset($seenIds[$extra->getKey()])) {
                continue;
            }

            $seenIds[$extra->getKey()] = true;
            $out[] = $extra;
        }

        return $out;
    }

    /**
     * @param string|null $inlineCssFingerprint Hex SHA-256 of {@see StylesheetCompilationResult::$inlineCss} when non-empty;
     *                                          appended as a query param for CDN cache busting (see {@code cms_stylesheets.public_route.cache_bust_query}).
     */
    public static function compiledBundleUrl(StyleSheet $sheet, ?string $inlineCssFingerprint = null): ?string
    {
        if (! (bool) modularousConfig('cms_stylesheets.public_route.enabled', true)) {
            return null;
        }

        if (! Route::has('cms.public.stylesheet')) {
            return null;
        }

        $url = route('cms.public.stylesheet', ['slug' => $sheet->slug], absolute: true);

        return self::appendCacheBustQuery($url, $inlineCssFingerprint);
    }

    /**
     * Stable fingerprint for the custom bundle body (same bytes as served after persist when in sync).
     */
    private static function fingerprintForInlineCss(string $inlineCss): ?string
    {
        if (trim($inlineCss) === '') {
            return null;
        }

        return hash('sha256', $inlineCss);
    }

    private static function appendCacheBustQuery(string $url, ?string $fingerprint): string
    {
        if ($fingerprint === null || $fingerprint === '') {
            return $url;
        }

        if (! (bool) modularousConfig('cms_stylesheets.public_route.cache_bust_query', true)) {
            return $url;
        }

        $param = (string) modularousConfig('cms_stylesheets.public_route.cache_bust_param', 'v');
        $param = preg_replace('/[^A-Za-z0-9_-]/', '', $param) ?: 'v';
        $sep = str_contains($url, '?') ? '&' : '?';

        return $url . $sep . rawurlencode($param) . '=' . rawurlencode($fingerprint);
    }

    /**
     * Stream compiled CSS from disk (falls back to empty string file).
     */
    public static function compiledBundleContents(StyleSheet $sheet): string
    {
        $disk = $sheet->compiled_disk;
        $path = $sheet->compiled_path;
        if (! is_string($disk) || ! is_string($path) || $disk === '' || $path === '') {
            return '';
        }

        if (! Storage::disk($disk)->exists($path)) {
            return '';
        }

        return Storage::disk($disk)->get($path);
    }
}
