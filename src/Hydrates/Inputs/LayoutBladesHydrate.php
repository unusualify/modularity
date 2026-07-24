<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\Route;

/**
 * Head / Body / Footer Blade snippets for CMS LayoutBuilder ({@code db} storage mode).
 */
class LayoutBladesHydrate extends InputHydrate
{
    /**
     * @var array<string, mixed>
     */
    public $requirements = [
        'textareaRows' => 12,
        'variant' => 'outlined',
        'density' => 'comfortable',
        /** @var string Shown above tabs */
        'layoutBladesSubtitle' => 'Applied when Blade source is Database. Filesystem layouts ignore saved segments.',
        /** @var string layout_builder|page_layout_appends */
        'shellPreviewMode' => 'layout_builder',
    ];

    public function hydrate(): array
    {
        $input = $this->input;

        $defaultCol = [
            'cols' => 12,
        ];
        $input['col'] = array_merge_recursive_preserve($defaultCol, $input['col'] ?? []);
        $input['type'] = 'input-layout-blades';

        $url = $this->resolveShellDraftPreviewUrl();

        if ($url !== null) {
            $input['shellDraftPreviewUrl'] = $url;
        }

        return $input;
    }

    private function shouldExposeShellDraftPreview(): bool
    {
        if ((bool) modularousConfig('cms_layout_builder.preview_enabled', true)) {
            return true;
        }

        $mode = (string) ($this->input['shellPreviewMode'] ?? 'layout_builder');

        return $mode === 'page_layout_appends'
            && (bool) modularousConfig('cms_page_layouts.layout_appends_modal_preview_enabled', true);
    }

    private function resolveShellDraftPreviewUrl(): ?string
    {
        if (! $this->shouldExposeShellDraftPreview()) {
            return null;
        }

        foreach ($this->shellDraftPreviewRouteCandidates() as $name) {
            if (Route::has($name)) {
                return route($name);
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function shellDraftPreviewRouteCandidates(): array
    {
        $names = [];
        $module = $this->module;

        if ($module !== null && \is_object($module) && method_exists($module, 'panelRouteNamePrefix')) {
            $names[] = $module->panelRouteNamePrefix() . 'layout_builder.shell_draft_preview';
        }

        $names[] = 'layout_builder.shell_draft_preview';

        return $names;
    }
}
