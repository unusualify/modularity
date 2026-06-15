<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Services\CmsPageLayoutResolver;
use Modules\Cms\Entities\PageLayout;

/**
 * Wraps submodule {@code *.custom} (or mapped) presentation HTML in a {@see PageLayout} {@see LayoutBladeResolver} shell
 * when a binding exists for the model class — shared by panel preview ({@see \Unusualify\Modularous\Http\Controllers\Traits\ManagePreview})
 * and public CMS ({@see \Modules\Cms\Http\Controllers\Front\CmsController}).
 */
final class CmsPageLayoutPresentationWrapper
{
    /**
     * Full HTML document string, or {@code null} to fall back to rendering {@code $viewName} alone.
     *
     * @param  array<string, mixed>  $innerData  Passed to the inner view and merged into {@see LayoutBladeResolver} data
     *                                            (e.g. {@code item}, {@code seoTitle}, …).
     */
    public static function documentOrNull(Model $item, string $viewName, array $innerData): ?string
    {
        if (! (bool) modularousConfig('cms_features.enabled', true)) {
            return null;
        }

        if (! class_exists(CmsPageLayoutResolver::class) || ! class_exists(LayoutBladeResolver::class)) {
            return null;
        }

        $class = $item::class;
        if (method_exists($class, 'supportsPageLayoutBindings') && ! $class::supportsPageLayoutBindings()) {
            return null;
        }

        $resolver = app(CmsPageLayoutResolver::class);
        $pageLayout = $resolver->pageLayoutForModelClass($class);

        $moduleRouteContext = LayoutBladeResolver::moduleRouteContextFromPresentationViewName($viewName)
            ?? self::moduleRouteContextFromModel($item);

        $staticSegmentsEnabled = (bool) modularousConfig(
            'cms_page_layouts.filesystem_segments_without_db_binding_enabled',
            true,
        );
        $hasStaticSegments = $staticSegmentsEnabled
            && LayoutBladeResolver::hasFilesystemPageLayoutSegments($moduleRouteContext);

        $innerViewName = self::innerPresentationViewName($viewName, $moduleRouteContext, $hasStaticSegments);
        $innerHtml = $innerViewName !== null && View::exists($innerViewName)
            ? View::make($innerViewName, $innerData)->render()
            : '';
        $innerHtml = self::extractBodyFragmentForShell($innerHtml);

        if ($pageLayout === null && ! $hasStaticSegments && $innerHtml === '') {
            return null;
        }

        $merge = array_merge($innerData, [
            'previewBodyHtml' => $innerHtml,
        ]);

        $presentationViewName = self::presentationViewNameForContext($viewName, $moduleRouteContext);
        [$pageLayoutBladeSource, $pageLayoutAppends] = self::resolvePageLayoutShellLayer(
            $pageLayout,
            $hasStaticSegments,
        );

        $layoutBuilder = $resolver->layoutBuilderShellForModelClass($class);
        if ($layoutBuilder === null) {
            if ($pageLayout === null) {
                return null;
            }

            $layout = self::layoutFromPageLayout($pageLayout);
            if ($layout === null) {
                return null;
            }

            return LayoutBladeResolver::renderHtml($layout, $merge, $presentationViewName);
        }

        return LayoutBladeResolver::renderHtmlWithShellAppends(
            $layoutBuilder,
            $pageLayoutAppends,
            $merge,
            $presentationViewName,
            $pageLayoutBladeSource,
        );
    }

    /**
     * Mirrors enabled PageLayout resolution: DB appends vs filesystem overrides on the LayoutBuilder shell.
     *
     * @return array{0: string, 1: array{head:string,body:string,footer:string}|null}
     */
    private static function resolvePageLayoutShellLayer(?PageLayout $pageLayout, bool $hasStaticSegments): array
    {
        if ($pageLayout !== null) {
            $source = self::normalizePageLayoutBladeSource($pageLayout);

            return [
                $source,
                $source === 'db' ? self::normalizedPageLayoutSegments($pageLayout) : null,
            ];
        }

        if ($hasStaticSegments) {
            return ['filesystem', null];
        }

        return ['db', null];
    }

    /**
     * Presentation views are often full HTML documents; the layout shell already provides {@code html}/{@code head}/{@code body}.
     * Keep only the inner body markup for {@code previewBodyHtml}.
     */
    public static function extractBodyFragmentForShell(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (preg_match('~<body\b[^>]*>(.*)</body>\s*</html>\s*$~is', $html, $matches) === 1) {
            return trim((string) $matches[1]);
        }

        return $html;
    }

    /**
     * Prefer {@code module::route.custom} for inner HTML; skip when static page_layout segments own the body slot.
     *
     * @param  array{module: string, route: string, viewPrefix?: string}|null  $moduleRouteContext
     */
    private static function innerPresentationViewName(
        string $viewName,
        ?array $moduleRouteContext,
        bool $hasStaticSegments,
    ): ?string {
        if ($hasStaticSegments) {
            if ($moduleRouteContext !== null) {
                $custom = sprintf(
                    '%s::%s.custom',
                    $moduleRouteContext['module'],
                    $moduleRouteContext['route'],
                );
                if (View::exists($custom)) {
                    return $custom;
                }
            }

            return null;
        }

        if ($moduleRouteContext !== null) {
            $custom = sprintf(
                '%s::%s.custom',
                $moduleRouteContext['module'],
                $moduleRouteContext['route'],
            );
            if (View::exists($custom)) {
                return $custom;
            }
        }

        if (View::exists($viewName)) {
            return $viewName;
        }

        if (CmsPublicFrontViewName::informationalFallbackEnabled()) {
            $fallback = CmsPublicFrontViewName::informationalFallbackViewName();
            if ($fallback !== '' && View::exists($fallback)) {
                return $fallback;
            }
        }

        return null;
    }

    /**
     * @return array{module: string, route: string}|null
     */
    private static function moduleRouteContextFromModel(Model $item): ?array
    {
        $context = CmsPublicFrontViewName::moduleRouteContextForModel($item);
        if ($context === null) {
            return null;
        }

        return [
            'module' => $context['module'],
            'route' => $context['route'],
        ];
    }

    /**
     * Ensure {@see LayoutBladeResolver} receives a {@code module::route.*} name for filesystem segment resolution.
     *
     * @param  array{module: string, route: string, viewPrefix?: string}|null  $moduleRouteContext
     */
    private static function presentationViewNameForContext(string $viewName, ?array $moduleRouteContext): string
    {
        if ($moduleRouteContext === null) {
            return $viewName;
        }

        if (LayoutBladeResolver::moduleRouteContextFromPresentationViewName($viewName) !== null) {
            return $viewName;
        }

        return sprintf('%s::%s.custom', $moduleRouteContext['module'], $moduleRouteContext['route']);
    }

    private static function layoutFromPageLayout(PageLayout $pageLayout): ?LayoutBuilder
    {
        $source = self::normalizePageLayoutBladeSource($pageLayout);

        $layout = new LayoutBuilder();
        $layout->blade_source = $source;
        $layout->definition = null;
        $layout->style_sheet_id = null;
        $layout->style_sheet_slugs = [];
        $layout->setRelation('styleSheet', null);

        if ($source === 'db') {
            $layout->blade_segments = self::normalizedPageLayoutSegments($pageLayout);
        } else {
            $viewName = trim((string) ($pageLayout->blade_view_name ?? ''));
            if ($viewName !== '') {
                $layout->blade_view_name = $viewName;
            }
        }

        return $layout;
    }

    private static function normalizePageLayoutBladeSource(PageLayout $pageLayout): string
    {
        $source = (string) ($pageLayout->blade_source ?? modularousConfig('cms_layout_builder.default_blade_source', 'db'));

        return $source === 'filesystem' ? 'filesystem' : 'db';
    }

    private static function normalizedPageLayoutSegments(PageLayout $pageLayout): array
    {
        return LayoutSegmentAppends::normalize($pageLayout->blade_segments ?? null);
    }
}
