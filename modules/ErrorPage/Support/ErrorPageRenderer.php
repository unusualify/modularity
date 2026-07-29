<?php

declare(strict_types=1);

namespace Modules\ErrorPage\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Services\CmsPageLayoutResolver;
use Modules\Cms\Services\CmsPublicModelResolver;
use Modules\Cms\Support\CmsPathLocale;
use Modules\Cms\Support\CmsPublicSeo;
use Modules\Cms\Support\LayoutBladeResolver;
use Modules\ErrorPage\Entities\ErrorPage;
use Throwable;

/**
 * Renders HTTP error pages from CMS records and/or builtin Blade fallbacks.
 *
 * Resolution (highest gate first):
 * 1. Feature flag {@code cms_features.error_pages_enabled} off → Laravel default
 * 2. Unpublished {@see ErrorPage} for the code → Laravel default (CMS intentionally off)
 * 3. Published record → host/package body override in LayoutBuilder (when shell + override),
 *    else package builtin full document ({@code @extends} standalone). HTML may be served from
 *    model-scoped {@see ErrorPagePresentationCache} when {@code cms_features.error_pages_cache_enabled}.
 * 4. No record (or table missing) → host override in plain document, else builtin full document
 *    (builtin path is not presentation-cached).
 *
 * LayoutBuilder body fragments (highest wins; package builtins are not fragments):
 * 1. {@code cms.layout_builder.{layoutSlug}.{code}} — app theme body override
 * 2. {@code cms::layout_builder.{layoutSlug}.{code}} — package Cms fallback
 *
 * Package builtin (no usable override):
 * {@code error_page::error_page.{code}} → full HTML via {@code @extends('error_page::error_page.standalone')}
 */
final class ErrorPageRenderer
{
    public const STRATEGY_CMS = 'cms';

    public const STRATEGY_BUILTIN = 'builtin';

    public function __construct(
        private readonly CmsPageLayoutResolver $pageLayoutResolver,
    ) {}

    public function toResponse(int $status, Request $request): ?Response
    {
        if (! $this->enabled()) {
            return null;
        }

        $this->syncLocaleFromPath($request);

        $errorCode = (string) $status;
        $record = $this->findPageByCode($errorCode);
        $strategy = $this->resolveStrategy($record !== null ? (bool) $record->published : null);

        if ($strategy === null) {
            return null;
        }

        try {
            if ($strategy === self::STRATEGY_CMS) {
                if ($record === null) {
                    return null;
                }

                $html = ErrorPagePresentationCache::remember(
                    $record,
                    app()->getLocale(),
                    fn (): ?string => $this->renderDocument($record, $errorCode, $request),
                );
            } else {
                $html = $this->renderBuiltinFallback($errorCode, $request);
            }
        } catch (Throwable $e) {
            if (! app()->runningUnitTests()) {
                report($e);
            }

            return null;
        }

        if ($html === null || $html === '') {
            return null;
        }

        return response($html, $status)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function enabled(): bool
    {
        return (bool) modularousConfig('cms_features.error_pages_enabled', true);
    }

    public function cacheEnabled(): bool
    {
        return ErrorPagePresentationCache::enabled();
    }

    /**
     * Render published CMS error HTML (same document path as {@see self::STRATEGY_CMS}).
     * Used by admin / job warmup — no HTTP exception required.
     */
    public function renderPublishedHtml(ErrorPage $item, ?Request $request = null): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        if ($this->resolveStrategy((bool) $item->published) !== self::STRATEGY_CMS) {
            return null;
        }

        $request ??= Request::create('/');
        $errorCode = (string) $item->error_code;

        return $this->renderDocument($item, $errorCode, $request);
    }

    /**
     * Map DB presence / publish state to a render strategy.
     *
     * @param  bool|null  $published  {@code null} = no row; {@code true}/{@code false} = row state
     * @return self::STRATEGY_CMS|self::STRATEGY_BUILTIN|null  {@code null} → Laravel default
     */
    public function resolveStrategy(?bool $published): ?string
    {
        if ($published === false) {
            return null;
        }

        if ($published === true) {
            return self::STRATEGY_CMS;
        }

        return self::STRATEGY_BUILTIN;
    }

    public function moduleReady(): bool
    {
        try {
            return Schema::hasTable((new ErrorPage)->getTable());
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Any row for the error code (published or not). Skips lookup when the table is missing.
     */
    public function findPageByCode(string $errorCode): ?ErrorPage
    {
        if (! $this->moduleReady()) {
            return null;
        }

        try {
            /** @var ErrorPage|null $page */
            $page = ErrorPage::query()->where('error_code', $errorCode)->first();

            return $page;
        } catch (Throwable) {
            return null;
        }
    }

    public function findPublishedPage(string $errorCode): ?ErrorPage
    {
        if (! $this->moduleReady()) {
            return null;
        }

        try {
            $query = ErrorPage::query()->where('error_code', $errorCode);
            CmsPublicModelResolver::applyPublishedVisibilityScopes($query, ErrorPage::class);

            if (! method_exists(ErrorPage::class, 'scopePublished')) {
                $query->where('published', true);
            }

            /** @var ErrorPage|null $page */
            $page = $query->first();

            return $page;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Ordered LayoutBuilder body fragment candidates (host/package overrides only).
     *
     * Package builtins are full documents via {@see ErrorPageDefaults::builtinViewName()} and are
     * not listed here.
     *
     * @return list<string>
     */
    public function bodyViewCandidates(string $errorCode, ?string $layoutSlug = null): array
    {
        return $this->overrideBodyViewCandidates($errorCode, $layoutSlug);
    }

    /**
     * Theme/layout body overrides only (no module builtin).
     *
     * @return list<string>
     */
    public function overrideBodyViewCandidates(string $errorCode, ?string $layoutSlug = null): array
    {
        $slug = $layoutSlug ?? $this->resolveLayoutSlug();
        if ($slug === '') {
            return [];
        }

        return [
            ErrorPageDefaults::layoutBuilderBodyOverrideViewName($slug, $errorCode),
            'cms::layout_builder.'.$slug.'.'.$errorCode,
        ];
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    public function resolveBodyHtml(string $errorCode, ?ErrorPage $item, array $viewData, ?string $layoutSlug = null): ?string
    {
        return $this->resolveFirstExistingView(
            $this->bodyViewCandidates($errorCode, $layoutSlug),
            $errorCode,
            $item,
            $viewData,
        );
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    public function resolveOverrideBodyHtml(string $errorCode, array $viewData, ?string $layoutSlug = null): ?string
    {
        return $this->resolveFirstExistingView(
            $this->overrideBodyViewCandidates($errorCode, $layoutSlug),
            $errorCode,
            null,
            $viewData,
        );
    }

    private function renderDocument(ErrorPage $item, string $errorCode, Request $request): ?string
    {
        $viewData = $this->baseViewData($errorCode, $request, $item);
        $overrideHtml = $this->resolveOverrideBodyHtml($errorCode, $viewData);
        $layoutBuilder = $this->pageLayoutResolver->layoutBuilderShellForModelClass(ErrorPage::class);

        if ($layoutBuilder !== null && $overrideHtml !== null && trim($overrideHtml) !== '') {
            return LayoutBladeResolver::renderHtmlWithShellAppends(
                $layoutBuilder,
                null,
                array_merge($viewData, [
                    'cmsPageLayoutBodyHtml' => $overrideHtml,
                ]),
                'error_page::error_page.custom',
                'db',
            );
        }

        if ($overrideHtml !== null && trim($overrideHtml) !== '') {
            return $this->plainDocument($overrideHtml, $viewData);
        }

        // No host/package body override: package builtin is a full standalone document.
        return $this->builtinStandaloneDocument($errorCode, $viewData);
    }

    /**
     * No CMS row: app/theme override as a plain document, else package builtin full page.
     */
    private function renderBuiltinFallback(string $errorCode, Request $request): ?string
    {
        $viewData = $this->baseViewData($errorCode, $request);
        $layoutSlug = $this->resolveLayoutSlug();

        $overrideHtml = $this->resolveOverrideBodyHtml($errorCode, $viewData, $layoutSlug);
        if ($overrideHtml !== null && trim($overrideHtml) !== '') {
            return $this->plainDocument($overrideHtml, $viewData);
        }

        return $this->builtinStandaloneDocument($errorCode, $viewData);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseViewData(string $errorCode, Request $request, ?ErrorPage $item = null): array
    {
        $data = [
            'errorCode' => $errorCode,
            'homeUrl' => url('/'),
            'seoTitle' => $this->metaTitle($errorCode),
            'seoDescription' => $this->metaDescription($errorCode),
            'robotsMeta' => CmsPublicSeo::ROBOTS_NOINDEX_NOFOLLOW,
            'recaptchaScript' => false,
            'canonicalUrl' => $request->url(),
        ];

        if ($item !== null) {
            $data['item'] = $item;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    private function plainDocument(string $bodyHtml, array $viewData): string
    {
        return View::make('error_page::error_page.plain', array_merge($viewData, [
            'bodyHtml' => $bodyHtml,
        ]))->render();
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    private function builtinStandaloneDocument(string $errorCode, array $viewData): ?string
    {
        $view = ErrorPageDefaults::builtinViewName($errorCode);
        if (! View::exists($view)) {
            return null;
        }

        return View::make($view, $viewData)->render();
    }

    private function resolveLayoutSlug(): string
    {
        $layout = $this->pageLayoutResolver->layoutBuilderShellForModelClass(ErrorPage::class)
            ?? $this->pageLayoutResolver->defaultLayoutBuilder();

        if ($layout instanceof LayoutBuilder) {
            $slug = trim((string) ($layout->slug ?? ''));
            if ($slug !== '') {
                return $slug;
            }
        }

        return ErrorPageDefaults::configuredLayoutSlug();
    }

    /**
     * @param  list<string>  $viewNames
     * @param  array<string, mixed>  $viewData
     */
    private function resolveFirstExistingView(
        array $viewNames,
        string $errorCode,
        ?ErrorPage $item,
        array $viewData,
    ): ?string {
        foreach ($viewNames as $viewName) {
            if (! View::exists($viewName)) {
                continue;
            }

            $data = array_merge($viewData, [
                'errorCode' => $errorCode,
            ]);

            if ($item !== null) {
                $data['item'] = $item;
            }

            return View::make($viewName, $data)->render();
        }

        return null;
    }

    private function syncLocaleFromPath(Request $request): void
    {
        $first = $request->segment(1);
        if (! is_string($first) || $first === '') {
            return;
        }

        $locales = CmsPathLocale::pathSegmentLocales();
        if (in_array($first, $locales, true)) {
            app()->setLocale($first);
        }
    }

    private function metaTitle(string $errorCode): string
    {
        $key = 'error_page::messages.'.$errorCode.'.meta_title';

        return trans()->has($key) ? __($key) : ('Error '.$errorCode);
    }

    private function metaDescription(string $errorCode): string
    {
        $key = 'error_page::messages.'.$errorCode.'.description';

        return trans()->has($key) ? __($key) : '';
    }
}
