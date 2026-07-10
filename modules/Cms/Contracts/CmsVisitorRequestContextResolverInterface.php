<?php

declare(strict_types=1);

namespace Modules\Cms\Contracts;

use Illuminate\Http\Request;

/**
 * Locale + normalized path resolution for public CMS middleware (no DB).
 */
interface CmsVisitorRequestContextResolverInterface
{
    public function shouldExcludeRequest(Request $request): bool;

    /**
     * @return array{0: string, 1: string, 2: bool}
     */
    public function resolveLocalePathKeyAndExplicitFlag(Request $request): array;
}
