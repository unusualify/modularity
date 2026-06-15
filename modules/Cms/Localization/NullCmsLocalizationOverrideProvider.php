<?php

namespace Modules\Cms\Localization;

use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Contracts\CmsLocalizationOverrideProviderInterface;

/**
 * No DB overrides; inner {@see CmsLocalizationContract} values are used as-is.
 */
final class NullCmsLocalizationOverrideProvider implements CmsLocalizationOverrideProviderInterface
{
    public function pathSegmentLocales(): ?array
    {
        return null;
    }

    public function defaultLocale(): ?string
    {
        return null;
    }

    public function hideDefaultLocaleInUrl(): ?bool
    {
        return null;
    }

    public function supportedLocalesMeta(): ?array
    {
        return null;
    }
}
