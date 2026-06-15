<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Entities\Traits\Publishable;
use Unusualify\Modularous\Support\PublishableMetadata;

trait PublishableTrait
{
    public function prependFormSchemaPublishableTrait($scope = []): array
    {
        if ( ! classHasTrait($this->getModel(), Publishable::class)) {
            return [];
        }

        return PublishableMetadata::defaultFormInputs($this->translatedPublishableAttributes());
    }

    /**
     * Publishable fields that live on the translation model for this repository's entity.
     *
     * @return list<string>
     */
    protected function translatedPublishableAttributes(): array
    {
        $model = $this->getModel();

        if (! classHasTrait($model, HasTranslation::class)) {
            return [];
        }

        $instance = is_string($model) ? new $model : $model;

        if (! method_exists($instance, 'getTranslatedAttributes')) {
            return [];
        }

        return PublishableMetadata::normalizeTranslatedFields($instance->getTranslatedAttributes());
    }
}
