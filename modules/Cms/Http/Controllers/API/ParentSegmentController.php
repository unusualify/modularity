<?php

namespace Modules\Cms\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Repositories\ParentSegmentRepository;
use Unusualify\Modularous\Http\Controllers\Controller;

/**
 * Optional JSON API for parent segment bindings (panel CRUD uses {@see \Modules\Cms\Http\Controllers\ParentSegmentController}).
 */
class ParentSegmentController extends Controller
{
    public function __construct(
        protected ParentSegmentRepository $parentSegmentRepository
    ) {}

    public function index(): JsonResponse
    {
        $rows = ParentSegment::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_model_class' => 'required|string|max:512',
            'locale' => 'nullable|string|max:12',
            'normalized_prefix' => 'nullable|string|max:2048',
            'admin_label' => 'nullable|string|max:255',
            'enabled' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $segment = $this->parentSegmentRepository->create($validated);

        return response()->json(['data' => $segment->fresh()], 201);
    }

    public function update(Request $request, ParentSegment $parent_segment): JsonResponse
    {
        $validated = $request->validate([
            'target_model_class' => 'sometimes|string|max:512',
            'locale' => 'nullable|string|max:12',
            'normalized_prefix' => 'sometimes|string|max:2048',
            'admin_label' => 'nullable|string|max:255',
            'enabled' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $this->parentSegmentRepository->update($parent_segment->getKey(), $validated);

        return response()->json(['data' => $parent_segment->fresh()]);
    }

    public function destroy(ParentSegment $parent_segment): JsonResponse
    {
        $this->parentSegmentRepository->delete($parent_segment->getKey());

        return response()->json([], 204);
    }
}
