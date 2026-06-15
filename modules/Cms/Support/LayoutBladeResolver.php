<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Cms\Entities\LayoutBuilder;

/**
 * Renders a {@see LayoutBuilder} as HTML: DB segments use the package shell; filesystem mode delegates to {@code view()}.
 * Optional Blade append fragments extend each shell slot (default: concatenated after that slot's base template).
 *
 * Nested DB body (LayoutBuilder wraps PageLayout body): set {@code LayoutBuilder::$definition['cms_page_layout_body_compose']}
 * to {@code wrap}. The PageLayout {@code body} append is compiled first, then passed as {@code $cmsPageLayoutBodyHtml} while
 * compiling the LayoutBuilder {@code body} segment (output is only that compiled base — no trailing concat of the append).
 *
 * Merge view data commonly includes {@code previewBodyHtml} (injected into the DB shell body slot: compiled into
 * {@code PageLayout} body appends and/or used as fallback when base+append are empty), {@code item}, and SEO keys
 * ({@code seoTitle}, …) for {@see \Illuminate\Support\Facades\Blade::render} on DB segments; panel preview uses
 * {@see CmsLayoutShellPreviewPlaceholder::mergeDataForShellPreview()}.
 */
final class LayoutBladeResolver
{
    /**
     * @param  array<string, mixed>  $mergeViewData
     */
    public static function renderView(LayoutBuilder $layout, array $mergeViewData = [], ?string $presentationViewName = null): View
    {
        return self::renderViewWithShellAppends($layout, null, $mergeViewData, $presentationViewName, null);
    }

    /**
     * @param  array<string, mixed>  $mergeViewData
     */
    public static function renderHtml(LayoutBuilder $layout, array $mergeViewData = [], ?string $presentationViewName = null): string
    {
        return self::renderView($layout, $mergeViewData, $presentationViewName)->render();
    }

    /**
     * @param  array{head:string,body:string,footer:string}|null  $fragmentAppends
     * @param  array<string, mixed>                               $mergeViewData
     */
    public static function renderHtmlWithShellAppends(LayoutBuilder $layout, ?array $fragmentAppends, array $mergeViewData = [], ?string $presentationViewName = null, ?string $pageLayoutBladeSource = null): string
    {
        return self::renderViewWithShellAppends($layout, $fragmentAppends, $mergeViewData, $presentationViewName, $pageLayoutBladeSource)->render();
    }

    /**
     * Backward-compatible alias registered when ParentSegment still owned shell fragments.
     *
     * @deprecated Use {@see self::renderHtmlWithShellAppends}.
     *
     * @param  array<string, mixed>  $mergeViewData
     */
    public static function renderHtmlWithParentSegmentAppends(LayoutBuilder $layout, ?array $parentSegmentAppends, array $mergeViewData = [], ?string $pageLayoutBladeSource = null): string
    {
        return self::renderHtmlWithShellAppends($layout, $parentSegmentAppends, $mergeViewData, null, $pageLayoutBladeSource);
    }

    /**
     * @param  array{head:string,body:string,footer:string}|null  $fragmentAppends
     * @param  array<string, mixed>                               $mergeViewData
     */
    public static function renderViewWithShellAppends(LayoutBuilder $layout, ?array $fragmentAppends, array $mergeViewData = [], ?string $presentationViewName = null, ?string $pageLayoutBladeSource = null): View
    {
        $layout->loadMissing('styleSheet');

        $layoutBladeSource = (string) ($layout->blade_source ?: modularousConfig('cms_layout_builder.default_blade_source', 'db'));
        $allowLayoutBuilderOverrides = $layoutBladeSource !== 'db';

        $assets = StylesheetManager::frameworkAssetsForLayoutBuilder($layout);

        $baseData = [
            'cmsLayout' => $layout,
            'stylesheetHrefs' => $assets['linkHrefs'],
            'stylesheetScriptSrcs' => $assets['scriptSrcs'],
        ];

        $data = array_merge($baseData, $mergeViewData);
        /** @var array{head:string,body:string,footer:string} */
        $appends = LayoutSegmentAppends::normalize($fragmentAppends);
        $pageLayoutBladeSource = $pageLayoutBladeSource === 'db' ? 'db' : 'filesystem';
        $allowPageLayoutOverrides = $pageLayoutBladeSource === 'filesystem';
        $moduleRouteContext = self::presentationViewModuleRoute($presentationViewName);

        if ($layoutBladeSource === 'filesystem') {
            $slug = self::filesystemOverrideSlug($layout);

            if ($slug !== '' && self::hasFilesystemSlugOverride($slug)) {
                $html = self::renderFilesystemSlugOverride($slug, $data);
            } else {
                $viewName = trim((string) ($layout->blade_view_name ?? ''));
                if ($viewName !== '' && ViewFacade::exists($viewName)) {
                    $html = view($viewName, $data)->render();
                } else {
                    $html = self::renderPageLayoutFilesystemDocument($data, $moduleRouteContext);
                }
            }

            if (self::appendsContainBlade($appends) || $allowPageLayoutOverrides) {
                $html = self::injectFilesystemAppendsIntoDocument($html, $appends, $data, $moduleRouteContext);
            }

            return view('cms::layout_builder.inline_document', ['document' => $html]);
        }

        /** @var array<string, mixed> */
        $segments = is_array($layout->blade_segments) ? $layout->blade_segments : [];

        $headBase = trim((string) ($segments['head'] ?? ''));
        $bodyBase = trim((string) ($segments['body'] ?? ''));
        $footerBase = trim((string) ($segments['footer'] ?? ''));

        $compiledHeadBase = self::compileSegmentBladeWithOverrides(
            $headBase,
            $data,
            $moduleRouteContext,
            'layout_builder',
            'head',
            false,
            $allowLayoutBuilderOverrides
        );
        $compiledFooterBase = self::compileSegmentBladeWithOverrides(
            $footerBase,
            $data,
            $moduleRouteContext,
            'layout_builder',
            'footer',
            false,
            $allowLayoutBuilderOverrides
        );

        $compiledAppendHead = self::compileSegmentBladeWithOverrides(
            $appends['head'],
            $data,
            $moduleRouteContext,
            'page_layout',
            'head',
            $allowPageLayoutOverrides
        );
        $compiledAppendBody = self::compileSegmentBladeWithOverrides(
            $appends['body'],
            $data,
            $moduleRouteContext,
            'page_layout',
            'body',
            $allowPageLayoutOverrides
        );
        $compiledAppendFooter = self::compileSegmentBladeWithOverrides(
            $appends['footer'],
            $data,
            $moduleRouteContext,
            'page_layout',
            'footer',
            $allowPageLayoutOverrides
        );

        if (self::wrapPageLayoutBodyIntoLayoutBuilderBody($layout)) {
            $dataForBodyBase = array_merge($data, [
                'cmsPageLayoutBodyHtml' => $compiledAppendBody,
            ]);

            $compiledBodyBase = self::compileSegmentBladeWithOverrides(
                $bodyBase,
                $dataForBodyBase,
                $moduleRouteContext,
                'layout_builder',
                'body',
                false,
                $allowLayoutBuilderOverrides
            );
            $bodyCombined = $compiledBodyBase;

            if ($compiledAppendBody !== '') {
                $trimmedBodyBase = trim($bodyCombined);
                if ($trimmedBodyBase === '') {
                    $bodyCombined = $compiledAppendBody;
                } elseif (strpos($bodyCombined, $compiledAppendBody) === false) {
                    $bodyCombined .= $compiledAppendBody;
                }
            }
        } else {
            $compiledBodyBase = self::compileSegmentBladeWithOverrides(
                $bodyBase,
                $data,
                $moduleRouteContext,
                'layout_builder',
                'body',
                false,
                $allowLayoutBuilderOverrides
            );
            $bodyCombined = $compiledBodyBase . $compiledAppendBody;
        }

        if (trim($bodyCombined) === '' && isset($data['previewBodyHtml'])) {
            $preview = trim((string) $data['previewBodyHtml']);
            if ($preview !== '') {
                $bodyCombined = (string) $data['previewBodyHtml'];
            }
        }

        $segmentData = array_merge($data, [
            'headHtml' => $compiledHeadBase . $compiledAppendHead,
            'bodyHtml' => $bodyCombined,
            'footerHtml' => $compiledFooterBase . $compiledAppendFooter,
        ]);

        unset($segmentData['previewBodyHtml']);

        return view('cms::layout_builder.shell', $segmentData);
    }

    /**
     * @deprecated Use {@see self::renderViewWithShellAppends}.
     *
     * @param  array<string, mixed>  $mergeViewData
     */
    public static function renderViewWithParentSegmentAppends(LayoutBuilder $layout, ?array $parentSegmentAppends, array $mergeViewData = [], ?string $pageLayoutBladeSource = null): View
    {
        return self::renderViewWithShellAppends($layout, $parentSegmentAppends, $mergeViewData, null, $pageLayoutBladeSource);
    }

    /**
     * @param  array{head:string,body:string,footer:string}  $appends
     * @param  array<string, mixed>                          $data
     */
    private static function injectFilesystemAppendsIntoDocument(string $html, array $appends, array $data, ?array $moduleRouteContext): string
    {
        $compiledHead = self::compileSegmentBladeWithOverrides(
            $appends['head'],
            $data,
            $moduleRouteContext,
            'page_layout',
            'head',
            true
        );
        if ($compiledHead !== '') {
            $html = self::injectHtmlBeforeClosingTag($html, 'head', $compiledHead);
        }

        $compiledBody = self::compileSegmentBladeWithOverrides(
            $appends['body'],
            $data,
            $moduleRouteContext,
            'page_layout',
            'body',
            true
        );
        if ($compiledBody === '' && isset($data['previewBodyHtml'])) {
            $preview = trim((string) $data['previewBodyHtml']);
            if ($preview !== '') {
                $compiledBody = $preview;
            }
        }
        $compiledFooter = self::compileSegmentBladeWithOverrides(
            $appends['footer'],
            $data,
            $moduleRouteContext,
            'page_layout',
            'footer',
            true
        );
        $combinedBodyFoot = $compiledBody . $compiledFooter;

        if ($combinedBodyFoot !== '') {
            $html = self::insertAppendsBeforeLayoutBuilderFooter($html, $combinedBodyFoot);
        }

        return $html;
    }

    /**
     * @param  array{head:string,body:string,footer:string}  $appends
     */
    private static function appendsContainBlade(array $appends): bool
    {
        return $appends['head'] !== '' || $appends['body'] !== '' || $appends['footer'] !== '';
    }

    private static function injectHtmlBeforeClosingTag(string $html, string $tagName, string $inject): string
    {
        $inject = trim($inject);
        if ($inject === '') {
            return $html;
        }

        $pattern = '~</' . preg_quote($tagName, '~') . '\s*>~i';
        if (preg_match($pattern, $html) !== 1) {
            return $html . "\n" . $inject . "\n";
        }

        return (string) preg_replace($pattern, $inject . '$0', $html, 1);
    }

    private static function insertAppendsBeforeLayoutBuilderFooter(string $html, string $insert): string
    {
        $insert = trim($insert);
        if ($insert === '') {
            return $html;
        }

        $footerBlock = self::extractLastFooterBlock($html);
        if ($footerBlock !== null) {
            [$prefix, $footerHtml, $suffix] = $footerBlock;

            return $prefix . $insert . $footerHtml . $suffix;
        }

        return self::injectHtmlBeforeClosingTag($html, 'body', $insert);
    }

    private static function extractLastFooterBlock(string $html): ?array
    {
        if (preg_match_all('~<footer\b[^>]*>.*?</footer>~is', $html, $matches, PREG_OFFSET_CAPTURE)) {
            $last = end($matches[0]);
            $start = $last[1];
            $footerHtml = $last[0];
            $end = $start + strlen($footerHtml);

            return [
                substr($html, 0, $start),
                $footerHtml,
                substr($html, $end),
            ];
        }

        return null;
    }

    /**
     * When {@code true}, the PageLayout {@code body} append is compiled first and exposed as {@code cmsPageLayoutBodyHtml}
     * while compiling the LayoutBuilder {@code body} segment; the append is not concatenated again afterward.
     *
     * Opt-in via {@see LayoutBuilder::$definition}{@code ['cms_page_layout_body_compose'] === 'wrap'}.
     */
    private static function wrapPageLayoutBodyIntoLayoutBuilderBody(LayoutBuilder $layout): bool
    {
        $definition = $layout->definition;
        if (! is_array($definition)) {
            return false;
        }

        $mode = $definition['cms_page_layout_body_compose'] ?? null;

        return $mode === 'wrap';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function compileSegmentBlade(string $template, array $data): string
    {
        if ($template === '') {
            return '';
        }

        return Blade::render($template, $data);
    }

    private static function filesystemOverrideSlug(LayoutBuilder $layout): string
    {
        return trim((string) ($layout->slug ?? ''));
    }

    private static function hasFilesystemSlugOverride(string $slug): bool
    {
        if ($slug === '') {
            return false;
        }

        foreach (['shell', 'head', 'body', 'footer'] as $segment) {
            if (self::firstExistingFilesystemSlugView($slug, $segment) !== null) {
                return true;
            }
        }

        return false;
    }

    private static function renderFilesystemSlugOverride(string $slug, array $data): string
    {
        $headHtml = self::renderFilesystemSlugSegment($slug, 'head', $data);
        $bodyHtml = self::renderFilesystemSlugSegment($slug, 'body', $data);
        $footerHtml = self::renderFilesystemSlugSegment($slug, 'footer', $data);

        $shellView = self::firstExistingFilesystemSlugView($slug, 'shell');

        if ($shellView !== null) {
            return view($shellView, array_merge($data, [
                'headHtml' => $headHtml,
                'bodyHtml' => $bodyHtml,
                'footerHtml' => $footerHtml,
            ]))->render();
        }

        return $headHtml . $bodyHtml . $footerHtml;
    }

    private static function renderFilesystemSlugSegment(string $slug, string $segment, array $data): string
    {
        $viewName = self::firstExistingFilesystemSlugView($slug, $segment);

        if ($viewName === null) {
            return '';
        }

        return view($viewName, $data)->render();
    }

    private static function firstExistingFilesystemSlugView(string $slug, string $segment): ?string
    {
        foreach (self::filesystemSlugViewCandidates($slug, $segment) as $viewName) {
            if (ViewFacade::exists($viewName)) {
                return $viewName;
            }
        }

        return null;
    }

    /**
     * Returns the ordered list of view names where a layout builder segment can be resolved.
     *
     * @param  string  $slug
     * @param  string  $segment
     * @return list<string>
     */
    private static function filesystemSlugViewCandidates(string $slug, string $segment): array
    {
        $candidates = [];

        if ($segment === 'shell') {
            if ($slug !== '') {
                $candidates[] = 'cms.layout_builder.' . $slug . '.shell';
                $candidates[] = 'cms::layout_builder.' . $slug . '.shell';
                $candidates[] = 'vendor.modularous.layout_builder.' . $slug . '.shell';
            }

            return $candidates;
        }

        if ($slug !== '') {
            $candidates[] = 'cms.layout_builder.' . $slug . '.' . $segment;
            $candidates[] = 'cms::layout_builder.' . $slug . '.' . $segment;
            $candidates[] = 'vendor.modularous.layout_builder.' . $slug . '.' . $segment;
        }

        $candidates[] = 'cms.layout_builder.' . $segment;
        $candidates[] = 'cms::layout_builder.' . $segment;

        return $candidates;
    }

    private static function compileSegmentBladeWithOverrides(
        string $template,
        array $data,
        ?array $moduleRouteContext,
        string $context,
        string $segment,
        bool $allowPageLayoutOverride = false,
        bool $allowLayoutBuilderOverride = true,
    ): string {
        $override = null;

        if ($context === 'layout_builder' && $allowLayoutBuilderOverride) {
            $override = self::renderLayoutBuilderSegmentOverride($segment, $data);
        } elseif ($context === 'page_layout' && $allowPageLayoutOverride) {
            $override = self::renderPageLayoutSegmentOverride($moduleRouteContext, $segment, $data);
        }

        if ($override !== null) {
            return $override;
        }

        return self::compileSegmentBlade($template, $data);
    }

    private static function renderLayoutBuilderSegmentOverride(string $segment, array $data): ?string
    {
        foreach (self::layoutBuilderViewCandidates($segment) as $viewName) {
            if (ViewFacade::exists($viewName)) {
                return view($viewName, $data)->render();
            }
        }

        return null;
    }

    /**
     * Override blades may be dropped inside the published path so projects can provide
     * module/route aware fallbacks. The filesystem layout is:
     * resources/views/vendor/modularous/modules/{module}/{route}/{context}/{segment}.blade.php
     * where {context} is one of layout_builder|page_layout and {segment} is head|body|footer.
     */
    private static function renderPageLayoutSegmentOverride(?array $moduleRouteContext, string $segment, array $data): ?string
    {
        foreach (self::pageLayoutViewCandidates($moduleRouteContext, $segment) as $viewName) {
            if (ViewFacade::exists($viewName)) {
                return view($viewName, $data)->render();
            }
        }

        return null;
    }

    private static function pageLayoutViewCandidates(?array $moduleRouteContext, string $segment): array
    {
        $candidates = [];

        if (
            ! empty($moduleRouteContext)
            && isset($moduleRouteContext['module'], $moduleRouteContext['route'])
        ) {
            $module = $moduleRouteContext['module'];
            $route = $moduleRouteContext['route'];

            if ($module !== '' && $route !== '') {
                $candidates[] = sprintf('vendor.modularous.modules.%s.%s.page_layout.%s', $module, $route, $segment);
                $candidates[] = sprintf('modularous::modules.%s.%s.page_layout.%s', $module, $route, $segment);
                $candidates[] = sprintf('%s::%s.page_layout.%s', $module, $route, $segment);
            }
        }

        return $candidates;
    }

    private static function renderPageLayoutFilesystemDocument(array $data, ?array $moduleRouteContext): string
    {
        $headHtml = self::renderPageLayoutFallbackSegment($moduleRouteContext, 'head', $data);
        $bodyHtml = self::renderPageLayoutFallbackSegment($moduleRouteContext, 'body', $data);
        $footerHtml = self::renderPageLayoutFallbackSegment($moduleRouteContext, 'footer', $data);

        return view('cms::layout_builder.shell', array_merge($data, [
            'headHtml' => $headHtml,
            'bodyHtml' => $bodyHtml,
            'footerHtml' => $footerHtml,
        ]))->render();
    }

    private static function renderPageLayoutFallbackSegment(?array $moduleRouteContext, string $segment, array $data): string
    {
        foreach (self::pageLayoutFallbackViewCandidates($moduleRouteContext, $segment) as $viewName) {
            if (ViewFacade::exists($viewName)) {
                return view($viewName, $data)->render();
            }
        }

        return '';
    }

    private static function pageLayoutFallbackViewCandidates(?array $moduleRouteContext, string $segment): array
    {
        $candidates = [
            'cms.page.page_layout.' . $segment,
        ];

        if (
            ! empty($moduleRouteContext)
            && isset($moduleRouteContext['module'], $moduleRouteContext['route'])
        ) {
            $module = $moduleRouteContext['module'];
            $route = $moduleRouteContext['route'];

            if ($module !== '' && $route !== '') {
                $candidates[] = sprintf('vendor.modularous.modules.%s.%s.page_layout.%s', $module, $route, $segment);
                $candidates[] = sprintf('modularous::modules.%s.%s.page_layout.%s', $module, $route, $segment);
                $candidates[] = sprintf('%s::%s.page_layout.%s', $module, $route, $segment);
            }
        }

        $candidates[] = 'cms::page.page_layout.' . $segment;

        return array_unique($candidates);
    }

    private static function layoutBuilderViewCandidates(string $segment): array
    {
        return [
            'cms.layout_builder.' . $segment,
            'cms::layout_builder.' . $segment,
        ];
    }

    private static function presentationViewModuleRoute(?string $presentationViewName): ?array
    {
        return self::moduleRouteContextFromPresentationViewName($presentationViewName);
    }

    /**
     * @return array{module: string, route: string}|null
     */
    public static function moduleRouteContextFromPresentationViewName(?string $presentationViewName): ?array
    {
        if ($presentationViewName === null) {
            return null;
        }

        if (! str_contains($presentationViewName, '::')) {
            return null;
        }

        [$modulePart, $rest] = explode('::', $presentationViewName, 2);
        if ($modulePart === '') {
            return null;
        }

        $routePart = Str::before($rest, '.');
        if ($routePart === '') {
            return null;
        }

        return [
            'module' => Str::snake($modulePart),
            'route' => Str::snake($routePart),
        ];
    }

    /**
     * @param  array{module: string, route: string}|null  $moduleRouteContext
     */
    public static function hasFilesystemPageLayoutSegments(?array $moduleRouteContext): bool
    {
        if ($moduleRouteContext === null) {
            return false;
        }

        foreach (['head', 'body', 'footer'] as $segment) {
            foreach (self::pageLayoutViewCandidates($moduleRouteContext, $segment) as $viewName) {
                if (ViewFacade::exists($viewName)) {
                    return true;
                }
            }
        }

        return false;
    }
}
