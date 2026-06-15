<?php

namespace Modules\Cms\Repositories\Traits;

use Modules\Cms\Contracts\PublicUrlRegistryContract;
use Modules\Cms\Entities\UrlRoute;

/**
 * Dispatches {@code afterSave} / {@code afterDelete} / {@code afterRestore} to callables keyed by model class.
 * Used by CMS repositories whose entities sync into {@see PublicUrlRegistryContract} (typically {@see UrlRoute} rows).
 *
 * Implement {@see publicUrlRegistryAfterSaveHandlers()} / {@see publicUrlRegistryAfterDeleteHandlers()} in the repository trait that uses this.
 */
trait PublicUrlRegistrySyncDispatchTrait
{
    public function afterSavePublicUrlRegistrySyncTrait(object $object, array $fields): void
    {
        if (property_exists($this, 'passAfterSaveSlugsTrait') && $this->passAfterSaveSlugsTrait === true) {
            return;
        }

        foreach ($this->publicUrlRegistryAfterSaveHandlers() as $class => $handler) {
            if ($object instanceof $class) {
                $handler($object, $fields);

                return;
            }
        }
    }

    public function afterDeletePublicUrlRegistrySyncTrait(object $object): void
    {
        foreach ($this->publicUrlRegistryAfterDeleteHandlers() as $class => $handler) {
            if ($object instanceof $class) {
                $handler($object);

                return;
            }
        }
    }

    public function afterRestorePublicUrlRegistrySyncTrait(object $object): void
    {
        $this->afterSavePublicUrlRegistrySyncTrait($object, []);
    }

    /**
     * @return array<class-string, callable(object, array):void>
     */
    abstract protected function publicUrlRegistryAfterSaveHandlers(): array;

    /**
     * @return array<class-string, callable(object):void>
     */
    abstract protected function publicUrlRegistryAfterDeleteHandlers(): array;
}
