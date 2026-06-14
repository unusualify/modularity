<?php

namespace Modules\Cms\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Repositories\PageLayoutRepository;
use Modules\Cms\Support\LayoutSegmentAppends;
use Modules\Cms\Support\LayoutSegmentAppendsValidator;
use Unusualify\Modularous\Http\Controllers\Controller;

class PageLayoutController extends Controller
{
    public function __construct(
        protected PageLayoutRepository $pageLayoutRepository
    ) {}

    public function index(): JsonResponse
    {
        $rows = PageLayout::query()
            ->with(['layoutBuilder:id,name,slug'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_model_class' => 'required|string|max:512|unique:' . (new PageLayout)->getTable() . ',target_model_class',
            'admin_label' => 'nullable|string|max:255',
            'enabled' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
            'layout_builder_id' => 'nullable|integer|exists:layout_builders,id',
            'blade_source' => 'sometimes|string|in:db,filesystem',
            'blade_segments' => 'nullable|array',
            'blade_segments.head' => 'nullable|string',
            'blade_segments.body' => 'nullable|string',
            'blade_segments.footer' => 'nullable|string',
            'blade_view_name' => 'nullable|string|max:512',
        ]);

        $this->validateBladeStorageFields($validated);

        $row = $this->pageLayoutRepository->create($validated);

        return response()->json(['data' => $row->fresh(['layoutBuilder:id,name,slug'])], 201);
    }

    public function update(Request $request, PageLayout $page_layout): JsonResponse
    {
        $validated = $request->validate([
            'target_model_class' => 'sometimes|string|max:512|unique:' . (new PageLayout)->getTable() . ',target_model_class,' . $page_layout->getKey(),
            'admin_label' => 'nullable|string|max:255',
            'enabled' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
            'layout_builder_id' => 'nullable|integer|exists:layout_builders,id',
            'blade_source' => 'sometimes|string|in:db,filesystem',
            'blade_segments' => 'nullable|array',
            'blade_segments.head' => 'nullable|string',
            'blade_segments.body' => 'nullable|string',
            'blade_segments.footer' => 'nullable|string',
            'blade_view_name' => 'nullable|string|max:512',
        ]);

        $this->validateBladeStorageFields($validated);

        $this->pageLayoutRepository->update($page_layout->getKey(), $validated);

        return response()->json(['data' => $page_layout->fresh(['layoutBuilder:id,name,slug'])]);
    }

    public function destroy(PageLayout $page_layout): JsonResponse
    {
        $this->pageLayoutRepository->delete($page_layout->getKey());

        return response()->json([], 204);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function validateBladeStorageFields(array $validated): void
    {
        $source = (string) ($validated['blade_source'] ?? modularousConfig('cms_layout_builder.default_blade_source', 'db'));
        $source = $source === 'filesystem' ? 'filesystem' : 'db';

        $segments = LayoutSegmentAppends::normalize($validated['blade_segments'] ?? null);
        LayoutSegmentAppendsValidator::enforceByteLimit($segments);

        if ($source !== 'filesystem') {
            return;
        }

        $viewName = trim((string) ($validated['blade_view_name'] ?? ''));
        if ($viewName === '') {
            throw ValidationException::withMessages([
                'blade_view_name' => [__('Filesystem layouts require a view name.')],
            ]);
        }

        if (trim($segments['head'] . $segments['body'] . $segments['footer']) !== '') {
            throw ValidationException::withMessages([
                'blade_segments' => [__('Database segments cannot be saved while Blade source is Filesystem.')],
            ]);
        }
    }
}
