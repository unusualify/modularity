<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\Navigation;

if (! function_exists('getLocales')) {
    /**
     * @return string[]
     */
    function getLocales()
    {
        $locales = collect(config('translatable.locales', ['tr']))->map(function ($locale, $index) {
            return collect($locale)->map(function ($country) use ($locale, $index) {
                return is_numeric($index)
                    ? $locale
                    : "$index-$country";
            });
        })->flatten()->toArray();

        if (blank($locales)) {
            $locales = [config('app.locale')];
        }

        return $locales;
    }
}

if (! function_exists('getTimezoneList')) {

    function getTimeZoneList()
    {
        return \Cache::rememberForever('timezones_list_collection', function () {
            $timestamp = time();
            foreach (timezone_identifiers_list(\DateTimeZone::ALL) as $key => $value) {
                date_default_timezone_set($value);
                $timezone[$value] = $value . ' (UTC ' . date('P', $timestamp) . ')';
            }

            return collect($timezone)->sortKeys();
        });
    }
}

if (! function_exists('getFormDraft')) {

    function getFormDraft($name, $overwrites = [], $excludes = [], $preserve = true)
    {

        $draft = $preserve
            ? array_merge_recursive_preserve(modularityConfig("form_drafts.{$name}", []), $overwrites)
            : array_merge(modularityConfig("form_drafts.{$name}", []), $overwrites);

        if (count($excludes)) {

            $draft = array_filter($draft, function ($value, $key) use ($excludes) {
                return ! in_array($key, $excludes);
            }, ARRAY_FILTER_USE_BOTH);
        }

        return $draft;
    }
}

if (! function_exists('adminRouteNamePrefix')) {

    function adminRouteNamePrefix()
    {
        return Modularity::getAdminRouteNamePrefix();
    }
}

if (! function_exists('adminUrlPrefix')) {

    function adminUrlPrefix()
    {
        return Modularity::getAdminUrlPrefix();
    }
}

if (! function_exists('systemUrlPrefix')) {

    function systemUrlPrefix()
    {
        return modularityConfig('system_prefix', 'system-settings');
    }
}

if (! function_exists('systemRouteNamePrefix')) {

    function systemRouteNamePrefix()
    {
        return snakeCase(studlyName(systemUrlPrefix()));
    }
}

if (! function_exists('builtInModularityThemes')) {

    function builtInModularityThemes()
    {
        return collect(array_filter(
            File::glob(Modularity::getVendorPath('vue/src/sass/themes') . '/*', GLOB_ONLYDIR),
            fn ($dir) => File::isDirectory($dir) && ! preg_match('/customs/', $dir)
        ))->mapWithKeys(function ($dir) {
            $info = pathinfo($dir);

            return [$info['filename'] => Str::headline($info['filename'])];
        });
    }
}

if (! function_exists('customModularityThemes')) {

    function customModularityThemes()
    {
        return collect(array_filter(
            File::glob(resource_path('vendor/modularity/themes/*'), GLOB_ONLYDIR),
            fn ($dir) => File::isDirectory($dir)
        ))->mapWithKeys(function ($dir) {
            $info = pathinfo($dir);

            return [$info['filename'] => Str::headline($info['filename'])];
        });
    }
}

if (! function_exists('get_translations')) {

    function get_translations(): array
    {
        $cache_key = 'modularity-languages';

        $cache = Cache::store('file');

        if ($cache->has($cache_key) && false) {
            return $cache->get($cache_key);
        }

        $translations = app('translator')->getTranslations();

        $cache->set($cache_key, json_encode($translations), 600);

        return $translations;
    }
}

if (! function_exists('clear_translations')) {

    function clear_translations(): void
    {
        $cache_key = 'modularity-languages';

        Cache::forget($cache_key);
    }
}

if (! function_exists('get_modularity_navigation_config')) {
    function get_modularity_navigation_config()
    {
        $sidebarKey = 'default';
        $profileMenuKey = 'default';
        $sidebarBottomKey = 'default';

        if (Auth::guest()) {
            $sidebarKey = 'guest';
            $profileMenuKey = 'guest';
            $sidebarBottomKey = 'guest';
        } else {
            $user = Auth::user();

            if ($user->hasRole('superadmin')) {
                $sidebarKey = 'superadmin';
                $profileMenuKey = 'superadmin';
                $sidebarBottomKey = 'superadmin';
            } elseif (count($user->roles) > 0 && $user->isClient()) {
                $sidebarKey = 'client';
                $profileMenuKey = 'client';
                $sidebarBottomKey = 'client';
            }
        }

        $sidebarConfigKey = 'modularity-navigation.sidebar.' . $sidebarKey;
        $profileMenuConfigKey = 'modularity-navigation.profileMenu.' . $profileMenuKey;
        $sidebarBottomConfigKey = 'modularity-navigation.sidebarBottom.' . $sidebarBottomKey;

        $navigation = [
            'current_url' => url()->current(),
            'sidebar' => array_values(Navigation::formatSidebarMenu(config($sidebarConfigKey, []))),
            'breadcrumbs' => [],
            'profileMenu' => array_values(Navigation::formatSidebarMenu(config($profileMenuConfigKey, []))),
            'sidebarBottom' => array_values(Navigation::formatSidebarMenu(config($sidebarBottomConfigKey, []))),
        ];

        return $navigation;
    }
}

if (! function_exists('get_modularity_authorization_config')) {
    function get_modularity_authorization_config()
    {
        $user = Auth::user();

        $permissions = Arr::mapWithKeys(Gate::abilities(), function ($closure, $key) {
            return [$key => Gate::allows($key)];
        });

        $roles = Arr::map($user?->roles?->toArray() ?? [], function ($role) {
            return $role['name'];
        });

        return [
            'isSuperAdmin' => $user?->isSuperAdmin() ?? false,
            'isClient' => $user?->isClient() ?? false,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}

if (! function_exists('get_modularity_impersonation_config')) {
    function get_modularity_impersonation_config()
    {
        $activeUser = null;
        $canFetchUsers = false;

        if (Auth::check()) {
            $activeUser = Auth::user();
            $canFetchUsers = $activeUser->isSuperAdmin() || $activeUser->isImpersonating();
        }

        $userRepository = app()->make(\Modules\SystemUser\Repositories\UserRepository::class);

        return [
            'active' => $activeUser ? $activeUser->isSuperAdmin() || $activeUser->isImpersonating() : false,
            'users' => $canFetchUsers ? $userRepository->whereNot(fn ($query) => $query->role(['superadmin']))->get(['id', 'name', 'email', 'company_id'])->toArray() : [],
            'impersonated' => $activeUser ? $activeUser->isImpersonating() : false,
            'stopRoute' => route(Route::hasAdmin('impersonate.stop')),
            'route' => route(Route::hasAdmin('impersonate'), ['id' => ':id']),
        ];
    }
}

if (! function_exists('get_modularity_localization_config')) {
    function get_modularity_localization_config()
    {
        // $currentLang = Lang::get("{$name}::lang", [], modularityConfig('locale'));
        $currentLang = Lang::get('*', [], modularityConfig('locale'));

        // $fallbackLang = Lang::get("{$name}::lang", [], modularityConfig('fallback_locale', 'en'));
        $fallbackLang = Lang::get('*', [], modularityConfig('fallback_locale', 'en'));

        $lang = array_replace_recursive($fallbackLang, $currentLang);

        return [
            'locale' => modularityConfig('locale'),
            'fallback_locale' => modularityConfig('fallback_locale', 'en'),
            'lang' => $lang,
        ];
    }
}

if (! function_exists('get_modularity_head_layout_config')) {
    function get_modularity_head_layout_config(array $data)
    {
        return array_merge([
            'pageTitle' => $data['pageTitle'] ?? Modularity::pageTitle(),
        ], $data['_headLayoutData'] ?? []);
    }
}

if (! function_exists('get_modularity_inertia_main_configuration')) {
    function get_modularity_inertia_main_configuration(array $data)
    {
        return array_merge([
            'headerTitle' => $data['headerTitle'] ?? config('app.name'),
            'hideDefaultSidebar' => $data['hideDefaultSidebar'] ?? false,
            'fixedAppBar' => $data['fixedAppBar'] ?? false,
            'appBarOrder' => $data['appBarOrder'] ?? 0,

            'navigation' => get_modularity_navigation_config(),
            'impersonation' => get_modularity_impersonation_config(),
            'authorization' => get_modularity_authorization_config(),
        ], $data['_mainConfiguration'] ?? []);
    }
}


