<?php

namespace Unusualify\Modularity\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'modularity::layouts.app-inertia';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],

            'config' => [
                'app_name' => config('app.name'),
                'js_namespace' => modularityConfig('js_namespace'),
                'timezone' => modularityConfig('timezone'),
            ],
            'endpoints' => fn () => $request->attributes->get('endpoints', new \StdClass()),

            'authorization' => fn () => $this->getAuthorizationData($request),
            'storeData' => fn () => $this->getStoreData($request),
        ]);
    }

    /**
     * Get authorization data for the current user
     */
    protected function getAuthorizationData(Request $request): array
    {
        $user = $request->user();

        if (!$user) {
            return [];
        }

        return [
            'isSuperAdmin' => $user->hasRole('superadmin') ?? false,
            'isClient' => $user->isClient() ?? false,
            'hasRestorable' => method_exists($user, 'hasRestorable') ? $user->hasRestorable() : false,
            'hasBulkable' => method_exists($user, 'hasBulkable') ? $user->hasBulkable() : false,
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray() ?? [],
            'roles' => $user->roles->pluck('name')->toArray() ?? [],
        ];
    }

    /**
     * Get store data for Vuex initialization
     */
    protected function getStoreData(Request $request): array
    {
        $user = $request->user();

        return [
            'config' => [
                'test' => app()->environment('testing'),
                'profileMenu' => [],
                'sidebarOptions' => modularityConfig('ui_settings.sidebar'),
                'secondarySidebarOptions' => modularityConfig('ui_settings.secondarySidebar'),
            ],
            'user' => [
                'isGuest' => !$user,
                'profile' => $user ? $user->toArray() : [],
                'profileRoute' => $user ? route('admin.profile.update') : '',
                'profileShortcutModel' => new \StdClass(),
                'profileShortcutSchema' => new \StdClass(),
                'loginShortcutModel' => new \StdClass(),
                'loginShortcutSchema' => new \StdClass(),
                'loginRoute' => route('admin.login'),
            ],
            'medias' => [
                'crops' => [],
                'showFileName' => modularityConfig('media_library.show_file_name', false),
                'types' => $this->getMediaTypes(),
                'selected' => [],
                'config' => ['useWysiwyg' => false, 'wysiwygOptions' => []],
            ],
            'languages' => [
                'all' => $this->getLanguages(),
                'active' => $this->getActiveLanguage(),
            ],
            'form' => [
                'baseUrl' => '',
                'inputs' => [],
            ],
            'datatable' => [
                'advancedFilters' => [],
                'customModal' => false,
            ],
            'ambient' => [
                'isHot' => app()->environment('local'),
                'appEnv' => app()->environment(),
                'appName' => config('app.name'),
                'appEmail' => config('mail.from.address'),
                'appDebug' => config('app.debug'),
                'test' => app()->environment('testing'),
                'systemPackageVersions' => [
                    'APP_VERSION' => env('APP_VERSION', 'v0.0.1'),
                    'MODULARITY_VERSION' => env('MODULARITY_VERSION', 'Not Found'),
                    'PAYABLE_VERSION' => env('PAYABLE_VERSION', 'Not Found'),
                    'SNAPSHOT_VERSION' => env('SNAPSHOT_VERSION', 'Not Found'),
                    'COMPOSER' => env('COMPOSER', 'Not Found'),
                ],
            ],
        ];
    }

    /**
     * Get available media types
     */
    protected function getMediaTypes(): array
    {
        $types = [];

        if (modularityConfig('enabled.media-library')) {
            $types[] = [
                'value' => 'image',
                'text' => 'Images',
                'total' => \Unusualify\Modularity\Entities\Media::query()->authorized()->count(),
                'endpoint' => route('admin.media-library.media.index'),
                'tagsEndpoint' => route('admin.media-library.media.tags'),
                'uploaderConfig' => []
            ];
        }

        if (modularityConfig('enabled.file-library')) {
            $types[] = [
                'value' => 'file',
                'text' => 'Files',
                'total' => \Unusualify\Modularity\Entities\File::query()->authorized()->count(),
                'endpoint' => route('admin.file-library.file.index'),
                'tagsEndpoint' => route('admin.file-library.file.tags'),
                'uploaderConfig' => []
            ];
        }

        return $types;
    }

    /**
     * Get available languages
     */
    protected function getLanguages(): array
    {
        // This should be implemented based on your language system
        return [];
    }

    /**
     * Get active language
     */
    protected function getActiveLanguage(): array
    {
        // This should be implemented based on your language system
        return [];
    }
}
