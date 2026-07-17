<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Cms\Http\Requests\SiteSeoSettingsRequest;

/**
 * @deprecated Global robots.txt is edited under System Settings → General.
 */
class SiteSeoSettingsController extends Controller
{
    public function update(SiteSeoSettingsRequest $request): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'message' => __('Global robots.txt is managed under System Settings → General.'),
        ], 410);
    }
}
