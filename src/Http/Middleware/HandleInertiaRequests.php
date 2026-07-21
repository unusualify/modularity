<?php

namespace Unusualify\Modularous\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use Unusualify\Modularous\Entities\File;
use Unusualify\Modularous\Entities\Media;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\BroadcastManager;
use Unusualify\Modularous\Support\ModularousFlashWarnings;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'modularous::layouts.app-inertia';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $this->modularousUser($request),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                /** Stacked non-blocking warnings (session {@see ModularousFlashWarnings::SESSION_KEY}). */
                'warnings' => fn () => $request->session()->pull(ModularousFlashWarnings::SESSION_KEY, []),
            ],

            'config' => [
                'app_name' => config('app.name'),
                'js_namespace' => modularousConfig('js_namespace'),
                'timezone' => modularousConfig('timezone'),
            ],
            'endpoints' => fn () => $request->attributes->get('endpoints', new \StdClass),

            'authorization' => fn () => $this->getAuthorizationData($request),
            'storeData' => fn () => $this->getStoreData($request),
        ]);
    }

    /**
     * Resolve the Modularous panel user (not the default `web` guard).
     */
    protected function modularousUser(Request $request): mixed
    {
        return $request->user(Modularous::getAuthGuardName());
    }

    /**
     * Get authorization data for the current user
     */
    protected function getAuthorizationData(Request $request): array
    {
        $user = $this->modularousUser($request);

        if (! $user) {
            return [];
        }

        return [
            'isSuperAdmin' => $user->is_superadmin ?? false,
            'isClient' => $user->isClient() ?? false,
            'is_client' => $user->is_client ?? false,
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
        $user = $this->modularousUser($request);

        return [
            'config' => [
                'test' => app()->environment('testing'),
                'profileMenu' => [],
                'sidebarOptions' => modularousConfig('ui_settings.sidebar'),
                'secondarySidebarOptions' => modularousConfig('ui_settings.secondarySidebar'),
                'topbarOptions' => modularousConfig('ui_settings.topbar'),
                'bottomNavigationOptions' => modularousConfig('ui_settings.bottomNavigation'),
                'uiPreferences' => $user ? get_modularous_ui_preferences() : [],
                'uiPreferencesEndpoint' => Route::has('admin.profile.ui-preferences') ? route('admin.profile.ui-preferences') : '',
            ],
            'user' => [
                'isGuest' => ! $user,
                'profile' => $user ? $user->toArray() : [],
                'profileRoute' => $user ? route('admin.profile.update') : '',
                'profileShortcutModel' => new \StdClass,
                'profileShortcutSchema' => new \StdClass,
                'loginShortcutModel' => new \StdClass,
                'loginShortcutSchema' => new \StdClass,
                'loginRoute' => route('admin.login'),
            ],
            'medias' => [
                'crops' => [],
                'showFileName' => modularousConfig('media_library.show_file_name', false),
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
                    'MODULAROUS_VERSION' => env('MODULAROUS_VERSION', 'Not Found'),
                    'PAYABLE_VERSION' => env('PAYABLE_VERSION', 'Not Found'),
                    'SNAPSHOT_VERSION' => env('SNAPSHOT_VERSION', 'Not Found'),
                    'COMPOSER' => env('COMPOSER', 'Not Found'),
                ],
            ],
            'broadcast' => BroadcastManager::panelConfig($user),
        ];
    }

    /**
     * Get available media types
     */
    protected function getMediaTypes(): array
    {
        $types = [];

        if (modularousConfig('enabled.media-library')) {
            $types[] = [
                'value' => 'image',
                'text' => 'Images',
                'total' => Media::query()->authorized()->count(),
                'endpoint' => route('admin.media-library.media.index'),
                'tagsEndpoint' => route('admin.media-library.media.tags'),
                'uploaderConfig' => [],
            ];
        }

        if (modularousConfig('enabled.file-library')) {
            $types[] = [
                'value' => 'file',
                'text' => 'Files',
                'total' => File::query()->authorized()->count(),
                'endpoint' => route('admin.file-library.file.index'),
                'tagsEndpoint' => route('admin.file-library.file.tags'),
                'uploaderConfig' => [],
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
