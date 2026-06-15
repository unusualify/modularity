<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\Navigation;

if (! function_exists('getLocales')) {
    /**
     * @return string[]
     */
    function getLocales()
    {
        $locales = collect(config('translatable.locales', ['en']))->map(function ($locale, $index) {
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
        return Cache::rememberForever('timezones_list_collection', function () {
            $timestamp = time();
            foreach (timezone_identifiers_list(DateTimeZone::ALL) as $key => $value) {
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
            ? array_merge_recursive_preserve(modularousConfig("form_drafts.{$name}", []), $overwrites)
            : array_merge(modularousConfig("form_drafts.{$name}", []), $overwrites);

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
        return Modularous::getAdminRouteNamePrefix();
    }
}

if (! function_exists('adminUrlPrefix')) {

    function adminUrlPrefix()
    {
        return Modularous::getAdminUrlPrefix();
    }
}

if (! function_exists('systemUrlPrefix')) {

    function systemUrlPrefix()
    {
        return modularousConfig('system_prefix', 'system-settings');
    }
}

if (! function_exists('systemRouteNamePrefix')) {

    function systemRouteNamePrefix()
    {
        return snakeCase(studlyName(systemUrlPrefix()));
    }
}

if (! function_exists('builtInModularousThemes')) {

    function builtInModularousThemes()
    {
        return collect(array_filter(
            File::glob(Modularous::getVendorPath('vue/src/sass/themes') . '/*', GLOB_ONLYDIR),
            fn ($dir) => File::isDirectory($dir) && ! preg_match('/customs/', $dir)
        ))->mapWithKeys(function ($dir) {
            $info = pathinfo($dir);

            return [$info['filename'] => Str::headline($info['filename'])];
        });
    }
}

if (! function_exists('customModularousThemes')) {

    function customModularousThemes()
    {
        return collect(array_filter(
            File::glob(resource_path('vendor/modularous/themes/*'), GLOB_ONLYDIR),
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
        $cache_key = 'modularous-languages';

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
        $cache_key = 'modularous-languages';

        Cache::forget($cache_key);
    }
}

if (! function_exists('get_modularous_navigation_config')) {
    function get_modularous_navigation_config()
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
            } elseif (count($user->roles) > 0 && $user->is_client) {
                $sidebarKey = 'client';
                $profileMenuKey = 'client';
                $sidebarBottomKey = 'client';
            }
        }

        $sidebarConfigKey = 'modularous.navigation.sidebar.' . $sidebarKey;
        $profileMenuConfigKey = 'modularous.navigation.profileMenu.' . $profileMenuKey;
        $sidebarBottomConfigKey = 'modularous.navigation.sidebarBottom.' . $sidebarBottomKey;

        $navigation = [
            'current_url' => url()->current(),
            'sidebar' => array_values(Navigation::formatSidebarMenu(config($sidebarConfigKey, config('modularous-navigation.sidebar.' . $sidebarKey, [])))),
            'breadcrumbs' => [],
            'profileMenu' => array_values(Navigation::formatSidebarMenu(config($profileMenuConfigKey, config('modularous-navigation.profileMenu.' . $profileMenuKey, [])))),
            'sidebarBottom' => array_values(Navigation::formatSidebarMenu(config($sidebarBottomConfigKey, config('modularous-navigation.sidebarBottom.' . $sidebarBottomKey, [])))),
        ];

        return $navigation;
    }
}

if (! function_exists('get_modularous_authorization_config')) {
    function get_modularous_authorization_config()
    {
        $user = Auth::user();

        $permissions = Arr::mapWithKeys(Gate::abilities(), function ($closure, $key) {
            return [$key => Gate::allows($key)];
        });

        $roles = Arr::map($user?->roles?->toArray() ?? [], function ($role) {
            return $role['name'];
        });

        return [
            'isSuperAdmin' => $user?->is_superadmin ?? false,
            'is_superadmin' => $user?->is_superadmin ?? false,
            'isClient' => $user?->is_client ?? false,
            'is_client' => $user?->is_client ?? false,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}

if (! function_exists('get_modularous_impersonation_config')) {
    function get_modularous_impersonation_config()
    {
        $activeUser = null;
        $canFetchUsers = false;

        if (Auth::check()) {
            $activeUser = Auth::user();
            $canFetchUsers = $activeUser->is_superadmin || $activeUser->isImpersonating();
        }

        $userRepository = app()->make(UserRepository::class);

        $defaultInput = modularousConfig('default_input');

        $isActive = $activeUser ? $activeUser->is_superadmin || $activeUser->isImpersonating() : false;
        $isImpersonating = $activeUser ? $activeUser->isImpersonating() : false;

        return [
            'active' => $isActive,
            'impersonated' => $isImpersonating,
            'stopRoute' => route(Route::hasAdmin('impersonate.stop')),
            'route' => route(Route::hasAdmin('impersonate'), ['id' => ':id']),

            'fetchEndpoint' => route(Route::hasAdmin('admin.system.user.index', [
                'eager' => ['roles'], 'appends' => ['company_name'],
                'column' => ['id', 'name', 'email', 'company_id'],
                'appends' => ['email_with_company'],
            ])),
            'density' => $defaultInput['density'] ?? 'comfortable',
            'variant' => $defaultInput['variant'] ?? 'outlined',
            'itemTitle' => 'email_with_company',
            'searchKeys' => ['name', 'email', 'company.name'],
            'recent' => ($isActive && ! $isImpersonating)
                ? get_modularous_recent_impersonations($userRepository)
                : [],
        ];
    }
}

if (! function_exists('get_modularous_recent_impersonations')) {
    /**
     * Hydrate the recently impersonated user ids stored in the session into a
     * lightweight collection matching the shape returned by the impersonate
     * search endpoint, preserving the stack order (newest first).
     *
     * @return array<int, array<string, mixed>>
     */
    function get_modularity_recent_impersonations(UserRepository $userRepository): array
    {
        $ids = \array_values(\array_filter(
            (array) session('impersonate_recent', []),
            fn ($value) => \is_numeric($value)
        ));

        if (empty($ids)) {
            return [];
        }

        $users = $userRepository->getModel()
            ->whereIn('id', $ids)
            ->select(['id', 'name', 'email', 'company_id'])
            ->get()
            ->keyBy('id');

        return \array_values(\array_filter(\array_map(
            fn ($id) => $users->get((int) $id)?->only([
                'id', 'name', 'email', 'company_id', 'company_name', 'email_with_company',
            ]),
            $ids
        )));
    }
}

if (! function_exists('get_modularous_localization_config')) {
    function get_modularous_localization_config()
    {
        // $currentLang = Lang::get("{$name}::lang", [], modularousConfig('locale'));
        $currentLang = Lang::get('*', [], modularousConfig('locale'));

        // $fallbackLang = Lang::get("{$name}::lang", [], modularousConfig('fallback_locale', 'en'));
        $fallbackLang = Lang::get('*', [], modularousConfig('fallback_locale', 'en'));

        $lang = array_replace_recursive($fallbackLang, $currentLang);

        return [
            'locale' => modularousConfig('locale'),
            'fallback_locale' => modularousConfig('fallback_locale', 'en'),
            'lang' => $lang,
        ];
    }
}

if (! function_exists('get_modularous_head_layout_config')) {
    function get_modularous_head_layout_config(array $data)
    {
        return array_merge([
            'pageTitle' => $data['pageTitle'] ?? Modularous::pageTitle(),
        ], $data['_headLayoutData'] ?? []);
    }
}

if (! function_exists('get_modularous_ui_preferences')) {
    /**
     * Get merged UI preferences: PHP config defaults + user DB preferences.
     *
     * @return array<string, mixed>
     */
    function get_modularous_ui_preferences(): array
    {
        $defaults = [
            'sidebar' => modularousConfig('ui_settings.sidebar', []),
            'topbar' => modularousConfig('ui_settings.topbar', []),
            'bottomNavigation' => modularousConfig('ui_settings.bottomNavigation', []),
        ];

        if (Auth::guest()) {
            return $defaults;
        }

        $userPrefs = Auth::user()->ui_preferences ?? [];

        return [
            'sidebar' => array_replace_recursive($defaults['sidebar'] ?? [], $userPrefs['sidebar'] ?? []),
            'topbar' => array_replace_recursive($defaults['topbar'] ?? [], $userPrefs['topbar'] ?? []),
            'bottomNavigation' => array_replace_recursive($defaults['bottomNavigation'] ?? [], $userPrefs['bottomNavigation'] ?? []),
        ];
    }
}

if (! function_exists('get_modularous_inertia_main_configuration')) {
    function get_modularous_inertia_main_configuration(array $data)
    {
        $locale = app()->getLocale();
        $sidebarLogoSymbol = modularousConfig('ui_settings.sidebar.logoSymbol', 'mini-logo-dark');

        $sidebarLogoSymbol = get_modularous_logo_symbol([
            "{$sidebarLogoSymbol}-{$locale}",
            $sidebarLogoSymbol,
            'main-logo',
        ]);

        return array_merge([
            'headerTitle' => $data['headerTitle'] ?? config('app.name'),
            'hideDefaultSidebar' => $data['hideDefaultSidebar'] ?? false,
            'fixedAppBar' => $data['fixedAppBar'] ?? false,
            'appBarOrder' => $data['appBarOrder'] ?? 0,
            'sidebarAttributes' => [
                'logoSymbol' => $sidebarLogoSymbol,
            ],

            'navigation' => get_modularous_navigation_config(),
            'impersonation' => get_modularous_impersonation_config(),
            'authorization' => get_modularous_authorization_config(),
        ], $data['_mainConfiguration'] ?? []);
    }
}

if (! function_exists('get_user_currency_vat_rates')) {
    function get_user_currency_vat_rates(): Collection
    {
        return tap(Collection::make(), function ($collection) {
            if ((($guard = Auth::guard('modularous')) !== null) && $guard->check()) {
                $user = $guard->user();

                if ($user->is_client && $user->validCompany) {
                    $company = $user->company;
                    $paymentCountry = $company->paymentCountry;
                    $collection->push(...$paymentCountry->currencyVatRates);
                    // if($company->isCorporateCompany) {
                    // }
                }
            }
        });
    }
}

if (! function_exists('get_user_payment_country_currencies')) {
    function get_user_payment_country_currencies(): Collection
    {
        return tap(Collection::make(), function ($collection) {
            get_user_currency_vat_rates()->each(function ($currencyVatRate) use ($collection) {
                $collection->push($currencyVatRate->paymentCurrency);
            });
        });
    }
}
