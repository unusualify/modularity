<?php

namespace Modules\Cms\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Services\CmsPromotionService;
use Unusualify\Modularous\Http\Controllers\Controller;

class PromotionController extends Controller
{
    public function __construct(
        protected CmsPromotionService $promotionService,
    ) {
        parent::__construct();
    }

    public function dryRun(Request $request): JsonResponse
    {
        if (! modularousConfig('cms_promotion.enabled', false)) {
            return response()->json(['ok' => false, 'message' => 'CMS promotion is disabled in configuration.'], 403);
        }

        return response()->json($this->promotionService->promote([
            'scope' => (array) $request->get('scope', []),
            'dry_run' => true,
        ], $request->user()));
    }

    public function execute(Request $request): JsonResponse
    {
        if (! modularousConfig('cms_promotion.enabled', false)) {
            return response()->json(['ok' => false, 'message' => 'CMS promotion is disabled in configuration.'], 403);
        }

        return response()->json($this->promotionService->promote([
            'scope' => (array) $request->get('scope', []),
            'dry_run' => (bool) $request->boolean('dry_run', false),
        ], $request->user()));
    }
}
