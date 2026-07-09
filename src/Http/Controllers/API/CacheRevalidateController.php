<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularous\Jobs\Cache\RevalidatePresentationCacheJob;

final class CacheRevalidateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string'],
            'route' => ['required', 'string'],
            'id' => ['nullable', 'integer'],
            'action' => ['required', 'string', 'in:purge,warm,both'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string', 'in:counts,index,record,formItem,formattedItem,presentationItem'],
            'locales' => ['nullable', 'array'],
            'locales.*' => ['string'],
        ]);

        RevalidatePresentationCacheJob::dispatch(
            moduleName: $validated['module'],
            moduleRouteName: $validated['route'],
            action: $validated['action'],
            recordId: isset($validated['id']) ? (int) $validated['id'] : null,
            types: $validated['types'] ?? null,
            locales: $validated['locales'] ?? null,
        );

        return response()->json([
            'queued' => true,
            'action' => $validated['action'],
            'module' => $validated['module'],
            'route' => $validated['route'],
        ]);
    }
}
