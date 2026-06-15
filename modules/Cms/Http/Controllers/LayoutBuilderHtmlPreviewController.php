<?php

declare(strict_types=1);

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Support\CmsLayoutShellPreviewPlaceholder;
use Modules\Cms\Support\LayoutBladeResolver;

/**
 * Authenticated CMS panel preview: full HTML using the same resolver as the front middleware contract.
 */
class LayoutBuilderHtmlPreviewController
{
    public function __invoke(LayoutBuilder $layoutBuilder): Response
    {
        if (! (bool) modularousConfig('cms_layout_builder.preview_enabled', true)) {
            abort(404);
        }

        $body = '<div class="cms-layout-preview-marker p-8 text-body-2">Layout preview marker (replace with hosted page content).</div>';

        $placeholder = CmsLayoutShellPreviewPlaceholder::mergeDataForShellPreview(null);


        dd($placeholder);
        $html = LayoutBladeResolver::renderHtml($layoutBuilder, array_merge($placeholder, [
            'previewBodyHtml' => $body,
        ]));

        dd(
            $html
        );

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
