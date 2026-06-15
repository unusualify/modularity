<?php

namespace Modules\Cms\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Modules\Cms\Entities\StyleSheet;
use Modules\Cms\Repositories\StyleSheetRepository;
use Unusualify\Modularous\Http\Controllers\Controller;

/**
 * JSON helpers for {@see StyleSheet} (panel CRUD uses {@see \Modules\Cms\Http\Controllers\StyleSheetController}).
 */
class StyleSheetController extends Controller
{
    public function __construct(
        protected StyleSheetRepository $repository
    ) {
        parent::__construct();
    }

    /**
     * Forces a CSS compile and storage write for the given sheet.
     */
    public function recompile(StyleSheet $style_sheet): JsonResponse
    {
        $this->repository->recompile($style_sheet);

        return response()->json(['data' => $style_sheet->fresh()]);
    }
}
