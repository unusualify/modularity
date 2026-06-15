<?php

namespace Modules\Cms\Contracts;

use Modules\Cms\Entities\UrlRoute;

interface CanonicalUrlResolverInterface
{
    public function resolve(?string $host, string $path, ?string $locale = null, array $options = []): array;

    public function normalizePath(string $path): string;

    /**
     * Values to match against {@see UrlRoute::normalized_path} (legacy rows may omit a leading slash).
     *
     * @return list<string>
     */
    public function normalizedPathRegistryLookupVariants(string $pathKey): array;
}
