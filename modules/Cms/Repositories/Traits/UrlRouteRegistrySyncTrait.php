<?php

namespace Modules\Cms\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\CmsAdminWarnings;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use Unusualify\Modularous\Repositories\Traits\TranslationsTrait;

/**
 * Keeps {@see UrlRoute} in sync for the **repository's own model** (any page-like entity with slugs).
 * Composes {@see PublicUrlRegistrySyncDispatchTrait}; handlers key off {@see $this->model}'s class — no per-entity static maps.
 *
 * Basename sorts after {@see TranslationsTrait} so slug + translation rows are persisted first.
 */
trait UrlRouteRegistrySyncTrait
{
    use PublicUrlRegistrySyncDispatchTrait;

    public function afterSaveUrlRouteRegistrySyncTrait($object, $fields): void
    {
        $this->afterSavePublicUrlRegistrySyncTrait($object, $fields);
    }

    public function afterDeleteUrlRouteRegistrySyncTrait($object): void
    {
        $this->afterDeletePublicUrlRegistrySyncTrait($object);
    }

    public function afterRestoreUrlRouteRegistrySyncTrait($object): void
    {
        $this->afterRestorePublicUrlRegistrySyncTrait($object);
    }

    /**
     * @return class-string<Model>
     */
    protected function urlRouteRegistryRepositoryModelClass(): string
    {
        /** @var Model $m */
        $m = $this->model;

        return $m::class;
    }

    protected function publicUrlRegistryAfterSaveHandlers(): array
    {
        $class = $this->urlRouteRegistryRepositoryModelClass();

        return [
            $class => function (object $object, array $fields) use ($class): void {
                if (! $object instanceof $class) {
                    return;
                }

                /** @var Model $object */
                app(CmsUrlRouteRegistry::class)->syncPublicPageRoutesForModel($object);

                if (method_exists($this, 'setCmsAdminWarningsBuffer')) {
                    $this->setCmsAdminWarningsBuffer(app(CmsAdminWarnings::class)->gather($object));
                }
            },
        ];
    }

    protected function publicUrlRegistryAfterDeleteHandlers(): array
    {
        $class = $this->urlRouteRegistryRepositoryModelClass();

        return [
            $class => function (object $object) use ($class): void {
                if (! $object instanceof $class) {
                    return;
                }

                /** @var Model $object */
                app(CmsUrlRouteRegistry::class)->removePublicPageRoutesForModel($object);
            },
        ];
    }
}
