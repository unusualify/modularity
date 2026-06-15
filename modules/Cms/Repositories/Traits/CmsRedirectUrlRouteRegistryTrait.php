<?php

namespace Modules\Cms\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Repositories\RedirectRepository;
use Modules\Cms\Services\CmsUrlRouteRegistry;

/**
 * Registers redirect {@see UrlRoute} source rows for the repository's own {@see Redirect} model.
 * Use on {@see RedirectRepository} only — separate from {@see UrlRouteRegistrySyncTrait}.
 */
trait CmsRedirectUrlRouteRegistryTrait
{
    use PublicUrlRegistrySyncDispatchTrait;

    public function afterSaveCmsRedirectUrlRouteRegistryTrait($object, $fields): void
    {
        $this->afterSavePublicUrlRegistrySyncTrait($object, $fields);
    }

    public function afterDeleteCmsRedirectUrlRouteRegistryTrait($object): void
    {
        $this->afterDeletePublicUrlRegistrySyncTrait($object);
    }

    public function afterRestoreCmsRedirectUrlRouteRegistryTrait($object): void
    {
        $this->afterRestorePublicUrlRegistrySyncTrait($object);
    }

    /**
     * @return class-string<Model>
     */
    protected function cmsRedirectUrlRouteRegistryModelClass(): string
    {
        /** @var Model $m */
        $m = $this->model;

        return $m::class;
    }

    protected function publicUrlRegistryAfterSaveHandlers(): array
    {
        $class = $this->cmsRedirectUrlRouteRegistryModelClass();

        return [
            $class => function (object $object, array $fields) use ($class): void {
                if (! $object instanceof $class) {
                    return;
                }

                /** @var Model $object */
                app(CmsUrlRouteRegistry::class)->syncRedirectSourceRoute($object);
            },
        ];
    }

    protected function publicUrlRegistryAfterDeleteHandlers(): array
    {
        $class = $this->cmsRedirectUrlRouteRegistryModelClass();

        return [
            $class => function (object $object) use ($class): void {
                if (! $object instanceof $class) {
                    return;
                }

                /** @var Model $object */
                app(CmsUrlRouteRegistry::class)->removeRedirectSourceRoute($object);
            },
        ];
    }
}
