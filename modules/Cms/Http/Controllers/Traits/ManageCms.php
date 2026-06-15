<?php

namespace Modules\Cms\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Modules\Cms\Services\CmsSignedPreviewTargetResolver;
use Modules\Cms\Services\CmsSignedPreviewUrlGenerator;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use Modules\Cms\Support\CmsFrontPath;
use Modules\Cms\Support\CmsPublicSiteUrl;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Entities\Traits\IsSingular;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Support\ModularousFlashWarnings;

trait ManageCms
{
    protected $fieldsPermissions = [];

    /**
     * @return void
     */
    protected function __beforeConstructManageCms($app, $request)
    {
        if (modularousConfig('security.enabled', false)) {
            $this->fieldsPermissions = [
                'canonical_url' => modularousConfig('security.critical_field_permissions.canonical_url', 'page_edit'),
                'robots_index' => modularousConfig('security.critical_field_permissions.robots_index', 'page_edit'),
                'robots_follow' => modularousConfig('security.critical_field_permissions.robots_follow', 'page_edit'),
            ];
        }
    }

    protected function handleResponseManageCms($response)
    {
        $repo = $this->repository;
        $warnings = is_object($repo) && method_exists($repo, 'pullCmsAdminWarnings')
            ? $repo->pullCmsAdminWarnings()
            : [];

        if ($warnings === []) {
            return $response;
        }

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            if (is_array($data)) {
                $existing = $data['warnings'] ?? [];
                $data['warnings'] = array_values(array_merge(
                    is_array($existing) ? $existing : [],
                    $warnings
                ));
                $response->setData($data);
            }

            return $response;
        }

        if ($response instanceof RedirectResponse) {
            ModularousFlashWarnings::merge($warnings);
        }

        return $response;
    }

    /**
     * Metadata for minting a signed public preview URL (any submodule with {@see HasParentSegment} + CMS front stack).
     *
     * @return array{fetchUrl: string, expiresInMinutes: int}|null
     */
    protected function signedPublicPreviewFormPayload($itemId): ?array
    {
        if ($itemId === null || $itemId === '') {
            return null;
        }

        if (! modularousConfig('cms_routing.signed_preview.enabled', true)) {
            return null;
        }

        if (! class_exists(CmsSignedPreviewTargetResolver::class)) {
            return null;
        }

        $model = $this->repository->getModel();
        if (! classHasTrait($model, HasParentSegment::class)) {
            return null;
        }

        $target = app(CmsSignedPreviewTargetResolver::class)
            ->resolve((string) $this->moduleName, (string) $this->routeName);
        if ($target === null) {
            return null;
        }

        $cmsModule = Collection::make(Modularous::allEnabled())->first(
            fn ($m) => studlyName($m->getName()) === 'Cms'
        );
        if ($cmsModule === null) {
            return null;
        }

        $mintRouteKey = $cmsModule->panelRouteNamePrefix() . '.signed_public_preview.mint';
        if (! Route::has($mintRouteKey)) {
            return null;
        }

        return [
            'fetchUrl' => route($mintRouteKey, [
                'module' => $this->moduleName,
                'route' => $this->routeName,
                'id' => $itemId,
            ], false),
            'expiresInMinutes' => app(CmsSignedPreviewUrlGenerator::class)->ttlMinutes(),
        ];
    }

    /**
     * Public site URLs for the edited item (per locale), for the admin form permalink row.
     *
     * @return list<array{locale: string, path: string, url: string}>|null
     */
    protected function localizedPublicPermalinksForFormItem($item): ?array
    {
        if (! is_object($item) || $item->getKey() === null) {
            return null;
        }

        if (! modularousConfig('cms_routing.public_pages_enabled', true)) {
            return null;
        }

        $modelClass = get_class($item);

        if (! classHasTrait($modelClass, HasParentSegment::class)
          || ! (classHasTrait($modelClass, HasSlug::class) || classHasTrait($modelClass, IsSingular::class))
        ) {
            return null;
        }

        if (! class_exists(CmsUrlRouteRegistry::class)) {
            return null;
        }

        $registry = app(CmsUrlRouteRegistry::class);

        if (! $registry->tableReady()) {
            return null;
        }

        $byLocale = $registry->publicPagePathsByLocale($item);

        if ($byLocale === []) {
            return null;
        }

        $out = [];

        foreach ($byLocale as $locale => $path) {
            $locale = (string) $locale;
            $path = (string) $path;

            if (trim($path) === '') {
                continue;
            }

            $browserPath = CmsFrontPath::publicBrowserPathForLocaleAndRegistryPath($locale, $path);

            $out[] = [
                'locale' => $locale,
                'path' => $browserPath,
                'url' => CmsPublicSiteUrl::absoluteUrlForPath($browserPath),
            ];
        }

        return $out === [] ? null : $out;
    }
}
