<?php

declare(strict_types=1);

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Support\CmsLayoutShellPreviewPlaceholder;
use Modules\Cms\Support\LayoutBladeResolver;
use Modules\Cms\Support\LayoutSegmentAppends;
use Modules\Cms\Support\LayoutSegmentAppendsValidator;

/**
 * Panel-only HTML preview merging unsaved Blade segments/appends via {@see LayoutBladeResolver} (same shell as the front).
 */
final class LayoutBuilderShellDraftPreviewController
{
    public function __invoke(Request $request): Response
    {
        if (! $this->accepts($request)) {
            abort(404);
        }

        $draft = $request->string('draft')->toString();

        if ($draft !== 'layout_builder') {
            throw ValidationException::withMessages([
                'draft' => [__('Invalid preview draft mode.')],
            ]);
        }

        return $this->layoutBuilderDraft($request);
    }

    private function accepts(Request $request): bool
    {
        return (bool) modularousConfig('cms_layout_builder.preview_enabled', true);
    }

    private function layoutBuilderDraft(Request $request): Response
    {
        /** @var array<string, mixed> $validated */
        $layoutBuildersTable = modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');
        $styleSheetsTable = modularousConfig('tables.cms_style_sheets', 'um_cms_style_sheets');
        $validated = $request->validate([
            'draft' => 'required|in:layout_builder',
            'layout_builder_id' => [
                'nullable',
                Rule::exists($layoutBuildersTable, 'id'),
            ],
            'blade_segments' => 'nullable|array',
            'blade_segments.head' => 'nullable|string',
            'blade_segments.body' => 'nullable|string',
            'blade_segments.footer' => 'nullable|string',
            'blade_source' => 'nullable|string|in:db,filesystem',
            'blade_view_name' => 'nullable|string|max:512',
            'style_sheet_id' => 'nullable|integer|exists:' . $styleSheetsTable . ',id',
            'style_sheet_slugs' => 'nullable|array',
            'style_sheet_slugs.*' => 'nullable|string',
            'target_model_class' => 'nullable|string|max:512',
        ]);

        $segments = LayoutSegmentAppends::normalize($validated['blade_segments'] ?? null);
        LayoutSegmentAppendsValidator::enforceByteLimit($segments);

        $hasId = isset($validated['layout_builder_id']) && $validated['layout_builder_id'] !== null && $validated['layout_builder_id'] !== '';

        if ($hasId) {
            $base = LayoutBuilder::query()->with(['styleSheet'])->findOrFail((int) $validated['layout_builder_id']);
            $layout = tap($base->replicate(), function (LayoutBuilder $copy) use ($base): void {
                $copy->setRelation('styleSheet', $base->relationLoaded('styleSheet') ? $base->styleSheet : null);
            });
        } else {
            $layout = new LayoutBuilder;
            $layout->blade_source = (string) modularousConfig('cms_layout_builder.default_blade_source', 'db');
        }

        if (($validated['blade_source'] ?? '') !== '') {
            $layout->blade_source = (string) $validated['blade_source'];
        }

        if (array_key_exists('blade_view_name', $validated)) {
            $layout->blade_view_name = $validated['blade_view_name'];
        }

        if (array_key_exists('style_sheet_slugs', $validated) && \is_array($validated['style_sheet_slugs'])) {
            $layout->style_sheet_slugs = $validated['style_sheet_slugs'];
        }

        if (array_key_exists('style_sheet_id', $validated)) {
            $layout->style_sheet_id = $validated['style_sheet_id'] ? (int) $validated['style_sheet_id'] : null;
        }

        $effectiveBladeSource = (string) ($layout->blade_source ?: modularousConfig('cms_layout_builder.default_blade_source', 'db'));
        $layout->blade_source = $effectiveBladeSource;

        if ($effectiveBladeSource === 'filesystem') {
            return $this->filesystemPreviewUnavailableResponse($layout);
        }

        if ($effectiveBladeSource === 'db') {
            $layout->blade_segments = [
                'head' => $segments['head'],
                'body' => $segments['body'],
                'footer' => $segments['footer'],
            ];
        }

        $layout->unsetRelation('styleSheet');
        $layout->loadMissing('styleSheet');

        $marker = '<div class="cms-layout-preview-marker p-8 text-body-2">Layout preview marker (hosted page replaces this).</div>';

        $placeholder = CmsLayoutShellPreviewPlaceholder::mergeDataForShellPreview(
            isset($validated['target_model_class']) ? (string) $validated['target_model_class'] : null
        );

        $html = LayoutBladeResolver::renderHtml($layout, array_merge($placeholder, [
            'previewBodyHtml' => $marker,
        ]));

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function filesystemPreviewUnavailableResponse(LayoutBuilder $layout): Response
    {
        $document = view('cms::layout_builder.filesystem_preview_unavailable', [
            'bladeViewName' => trim((string) ($layout->blade_view_name ?? '')),
            'filesystemSlug' => trim((string) ($layout->slug ?? '')),
        ])->render();

        return response($document, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
