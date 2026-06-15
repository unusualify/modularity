<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Modules\Cms\Entities\StyleSheet;
use Modules\Cms\Services\Stylesheet\StylesheetCompilerService;
use Modules\Cms\Support\StylesheetManager;

/**
 * Serves the compiled custom CSS bundle for a {@see StyleSheet} (by slug).
 */
final class PublicStyleSheetAssetController
{
    public function __construct(
        private readonly StylesheetCompilerService $compilerService,
    ) {}

    public function show(string $slug): Response
    {
        if (! (bool) modularousConfig('cms_stylesheets.public_route.enabled', true)) {
            abort(404);
        }

        $slug = Str::lower(trim($slug));

        /** @var StyleSheet|null $sheet */
        $sheet = StyleSheet::query()
            ->where('slug', $slug)
            ->first();

        if ($sheet === null) {
            abort(404);
        }

        $body = StylesheetManager::compiledBundleContents($sheet);
        if ($body === '') {
            $this->compilerService->persistCompiled($sheet);
            $sheet->refresh();
            $body = StylesheetManager::compiledBundleContents($sheet);
        }

        $etag = '"' . ($sheet->compiled_checksum ?: hash('sha256', $body)) . '"';

        $maxAge = max(0, (int) modularousConfig('cms_stylesheets.public_route.max_age_seconds', 3600));

        return response($body, 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=' . $maxAge,
            'ETag' => $etag,
        ]);
    }
}
