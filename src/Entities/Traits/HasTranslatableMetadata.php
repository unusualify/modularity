<?php

namespace Unusualify\Modularous\Entities\Traits;

use Unusualify\Modularous\Repositories\Traits\TranslatableMetadataTrait;
use Unusualify\Modularous\Support\TranslatableMetadata;

/**
 * Opt-in translatable metadata (SEO + robots + sitemap flag) for models using {@see HasTranslation}.
 *
 * Use **after** {@see HasTranslation} on the model. Merge {@see TranslatableMetadata::TRANSLATED_ATTRIBUTES}
 * into {@see $translatedAttributes}.
 *
 * Migrations: {@see createTranslatableMetadataFields()}.
 *
 * **Repository companion:** also add {@see TranslatableMetadataTrait}
 * on the model's repository — admin SEO/metadata form inputs are appended there.
 */
trait HasTranslatableMetadata
{
    /**
     * @return list<string>
     */
    public static function translatableMetadataAttributeNames(): array
    {
        return TranslatableMetadata::TRANSLATED_ATTRIBUTES;
    }

    public function initializeHasTranslatableMetadata()
    {
        if (classHasTrait($this, HasTranslation::class)) {
            $this->translatedAttributes = array_unique(array_merge($this->translatedAttributes, $this->translatableMetadataAttributeNames()));
        } elseif (classHasTrait($this, IsSingular::class)) {
            $this->mergeFillable($this->translatableMetadataAttributeNames());
        }
    }
}
