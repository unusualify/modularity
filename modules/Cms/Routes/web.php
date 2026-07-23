<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\API\PromotionController;
use Modules\Cms\Http\Controllers\LayoutBuilderHtmlPreviewController;
use Modules\Cms\Http\Controllers\LayoutBuilderShellDraftPreviewController;
use Modules\Cms\Http\Controllers\PromotionToolController;
use Modules\Cms\Http\Controllers\SignedPublicPreviewMintController;
use Modules\Cms\Http\Controllers\SitemapController;
use Modules\Cms\Http\Controllers\SiteSeoToolController;
use Unusualify\Modularous\Facades\ModularousRoutes;

/*
|--------------------------------------------------------------------------
| CMS panel web routes (Inertia + session-backed promotion actions)
|--------------------------------------------------------------------------
|
| Dry-run / execute are registered here so the panel can POST with the web
| session. The api.php routes use api.auth and may not authenticate the
| panel user, which leads to Authenticate resolving route('login').
|
*/

Route::middleware(ModularousRoutes::webPanelMiddlewares())->group(function () {
    if (modularousConfig('cms_layout_builder.preview_enabled', true)) {
        Route::get('layout-builders/{layout_builder}/preview-html', LayoutBuilderHtmlPreviewController::class)
            ->whereNumber('layout_builder')
            ->name('layout_builder.preview_html');
    }

    if (
        (bool) modularousConfig('cms_layout_builder.preview_enabled', true)
        || (bool) modularousConfig('cms_page_layouts.layout_appends_modal_preview_enabled', true)
    ) {
        Route::post('layout-builders/shell-draft-preview', LayoutBuilderShellDraftPreviewController::class)
            ->name('layout_builder.shell_draft_preview');
    }

    if (modularousConfig('cms_routing.signed_preview.enabled', true)) {
        Route::get('signed-public-preview/{module}/{route}/{id}', SignedPublicPreviewMintController::class)
            ->where([
                'module' => '[A-Za-z][A-Za-z0-9]*',
                'route' => '[A-Za-z][A-Za-z0-9]*',
                'id' => '[0-9]+',
            ])
            ->name('signed_public_preview.mint');
    }

    Route::get('promotion', PromotionToolController::class)
        ->name('promotion.tool');

    // Route::get('redirects/bulk', [RedirectController::class, 'bulkSheetTool'])
    //     ->name('redirects.bulk.tool');

    Route::get('site-seo', SiteSeoToolController::class)
        ->name('siteSeo.tool');

    $stepUpEnabled = modularousConfig('cms_features.register_middlewares', true) && modularousConfig('security.enabled', false);

    $promotionStepUpMiddleware = $stepUpEnabled
        ? 'modularous.security.step_up:promotion.execute'
        : null;

    $redirectBulkStepUpMiddleware = $stepUpEnabled
        ? 'modularous.security.step_up:redirect.bulk_import'
        : null;

    $siteSeoStepUpMiddleware = $stepUpEnabled
        ? 'modularous.security.step_up:site_seo.edit'
        : null;

    // SİTEMAP PANEL STEP-UP MIDDLEWARE
    $sitemapCommitAbility = (string) modularousConfig('cms_sitemap.panel.step_up_ability.commit', 'sitemap.commit');
    $sitemapCommitStepUpMiddleware = $stepUpEnabled
        ? 'modularous.security.step_up:' . $sitemapCommitAbility
        : null;


    $sitemapDryRun = Route::post('sitemap/dry-run', [SitemapController::class, 'dryRun'])
        ->name('sitemap.dryRun.web');

    $sitemapCommit = Route::post('sitemap/commit', [SitemapController::class, 'commit'])
        ->name('sitemap.commit.web');

    if ($sitemapCommitStepUpMiddleware) {
        $sitemapDryRun->middleware($sitemapCommitStepUpMiddleware);
        $sitemapCommit->middleware($sitemapCommitStepUpMiddleware);
    }

    Route::post('sitemap/item', [SitemapController::class, 'upsertItem'])
        ->name('sitemap.item.upsert.web');

    // PROMOTION PANEL STEP-UP MIDDLEWARE
    $dryRunRoute = Route::post('promotion/dry-run', [PromotionController::class, 'dryRun'])
        ->name('promotion.dryRun.web');

    $executeRoute = Route::post('promotion/execute', [PromotionController::class, 'execute'])
        ->name('promotion.execute.web');

    if ($promotionStepUpMiddleware) {
        $dryRunRoute->middleware($promotionStepUpMiddleware);
        $executeRoute->middleware($promotionStepUpMiddleware);
    }
});
