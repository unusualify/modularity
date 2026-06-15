<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\API\CmsRoutingMetaController;
use Modules\Cms\Http\Controllers\API\PageLayoutController;
use Modules\Cms\Http\Controllers\API\ParentSegmentController;
use Modules\Cms\Http\Controllers\API\PromotionController;
use Modules\Cms\Http\Controllers\API\StyleSheetController;
use Modules\Cms\Http\Controllers\RedirectController;
use Unusualify\Modularous\Facades\ModularousRoutes;

Route::middleware(['api.auth', ...ModularousRoutes::defaultMiddlewares()])->group(function () {
    $promotionStepUpMiddleware = (modularousConfig('cms_features.register_middlewares', true) && modularousConfig('security.enabled', false))
        ? 'modularous.security.step_up:promotion.execute'
        : null;

    $redirectBulkStepUpMiddleware = (modularousConfig('cms_features.register_middlewares', true) && modularousConfig('security.enabled', false))
        ? 'modularous.security.step_up:redirect.bulk_import'
        : null;

    Route::prefix('cms')->name('cms.')->group(function () use ($promotionStepUpMiddleware, $redirectBulkStepUpMiddleware) {
        Route::get('routing-meta', CmsRoutingMetaController::class)->name('routingMeta');

        Route::apiResource('parent-segments', ParentSegmentController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::apiResource('page-layouts', PageLayoutController::class)->only(['index', 'store', 'update', 'destroy']);

        $dryRunRoute = Route::post('promotion/dry-run', [PromotionController::class, 'dryRun'])
            ->name('promotion.dryRun');

        $executeRoute = Route::post('promotion/execute', [PromotionController::class, 'execute'])
            ->name('promotion.execute');

        if ($promotionStepUpMiddleware) {
            $dryRunRoute->middleware($promotionStepUpMiddleware);
            $executeRoute->middleware($promotionStepUpMiddleware);
        }

        $redirectBulkDryRun = Route::post('redirects/bulk/dry-run', [RedirectController::class, 'bulkSheetDryRun'])
            ->name('redirects.bulk.dryRun');

        $redirectBulkCommit = Route::post('redirects/bulk/commit', [RedirectController::class, 'bulkSheetCommit'])
            ->name('redirects.bulk.commit');

        if ($redirectBulkStepUpMiddleware) {
            $redirectBulkDryRun->middleware($redirectBulkStepUpMiddleware);
            $redirectBulkCommit->middleware($redirectBulkStepUpMiddleware);
        }

        Route::get('redirects/bulk/export', [RedirectController::class, 'bulkSheetExport'])
            ->name('redirects.bulk.export');

        Route::post('style-sheets/{style_sheet}/recompile', [StyleSheetController::class, 'recompile'])
            ->whereNumber('style_sheet')
            ->name('style_sheets.recompile');
    });
});
