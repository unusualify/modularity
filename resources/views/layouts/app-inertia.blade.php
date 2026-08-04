<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta name="robots" content="noindex,nofollow" />

        {{-- <title> {{ $pageTitle ?? \Unusualify\Modularous\Facades\Modularous::pageTitle() }}</title> --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include("{$MODULAROUS_VIEW_NAMESPACE}::partials.favicons")

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            (function () {
                try {
                    var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                    if (!timezone) {
                        return;
                    }

                    var secure = location.protocol === 'https:' ? ';Secure' : '';
                    document.cookie = 'timezone=' + encodeURIComponent(timezone)
                        + ';path=/;max-age=31536000;SameSite=Lax' + secure;

                    window.__MODULAROUS_BROWSER_TIMEZONE__ = timezone;
                } catch (e) {}
            })();
        </script>

        <script>
            const TRANSLATIONS = @json(get_translations());
            // const URLS = @json($urls);
        </script>

        <!-- Scripts -->
        @routes()
        @stack('head_css')
        @stack('head_js')

        {{
            ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-inertia.js'])
        }}

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @if(!ModularousVite::useHotFile(public_path('modularous.hot'))->isRunningHot())
            @include("{$MODULAROUS_VIEW_NAMESPACE}::partials.icons.svg-sprite")
        @endif

        @php
            // $_mainConfiguration = array_merge_recursive_preserve([
            //     'headerTitle' => $headerTitle ?? config('app.name'),
            //     'hideDefaultSidebar' => $hideDefaultSidebar ?? false,
            //     'fixedAppBar' => $fixedAppBar ?? false,
            //     'appBarOrder' => $appBarOrder ?? 0,

            //     'navigation' => array_merge(
            //         [
            //             'profileMenu' => [],
            //             'breadcrumbs' => [],
            //             'sidebar' => [],
            //         ],
            //         $navigation ?? []
            //     ),
            //     'impersonation' => $impersonation ?? [],
            //     'authorization' => $authorization ?? [],
            // ], $_mainConfiguration ?? []);
        @endphp

        @inertia

        @stack('post_js')

        <script>
            @include("{$MODULAROUS_VIEW_NAMESPACE}::partials.default-store")

            window['{{ modularousConfig('js_namespace') }}'].TIMEZONE = window.__MODULAROUS_BROWSER_TIMEZONE__ || '{{ modularousConfig('timezone') }}';
            window['{{ modularousConfig('js_namespace') }}'].AUTHORIZATION = @json($authorization ?? []);

            window['{{ modularousConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
            window['{{ modularousConfig('js_namespace') }}'].STORE.config = {
                isInertia: {{ json_encode(\Unusualify\Modularous\Facades\Modularous::shouldUseInertia()) }},
                useCountryBasedVatRates: {{ json_encode(\Unusualify\Modularous\Facades\Modularous::shouldUseCountryBasedVatRates()) }},
                test: false,
                profileMenu: {!! json_encode($navigation['profileMenu'] ?? []) !!},
                sidebarOptions: {!! json_encode(modularousConfig('ui_settings.sidebar')) !!},
                secondarySidebarOptions : {!! json_encode(modularousConfig('ui_settings.secondarySidebar')) !!},
                topbarOptions: {!! json_encode(modularousConfig('ui_settings.topbar')) !!},
                bottomNavigationOptions: {!! json_encode(modularousConfig('ui_settings.bottomNavigation')) !!},
                uiPreferences: {!! json_encode(get_modularous_ui_preferences()) !!},
                uiPreferencesEndpoint: '{{ \Illuminate\Support\Facades\Route::hasAdmin("profile.ui-preferences") ? route(\Illuminate\Support\Facades\Route::hasAdmin("profile.ui-preferences")) : "" }}',
            },
            @php
                $modularousGuardUser = auth(\Unusualify\Modularous\Facades\Modularous::getAuthGuardName())->user();
            @endphp
            window['{{ modularousConfig('js_namespace') }}'].STORE.user = {
                isGuest: {{ json_encode($modularousGuardUser === null) }},
                profile: {!! json_encode($currentUser) !!},
                profileRoute: '{{ route(Route::hasAdmin('profile.update')) }}',
                profileShortcutModel: {!! json_encode($profileShortcutModel ?? new StdClass()) !!},
                profileShortcutSchema: {!! json_encode($profileShortcutSchema ?? new StdClass()) !!},

                loginShortcutModel: {!! json_encode($loginShortcutModel ?? new StdClass()) !!},
                loginShortcutSchema: {!! json_encode($loginShortcutSchema ?? new StdClass()) !!},
                loginRoute: '{{ route('admin.login') }}',
            },
            window['{{ modularousConfig('js_namespace') }}'].STORE.broadcast = {!! json_encode(\Unusualify\Modularous\Services\BroadcastManager::panelConfig($modularousGuardUser)) !!},

            @if (modularousConfig('enabled.media-library'))
                window['{{ modularousConfig('js_namespace') }}'].STORE.medias.types.push({
                    value: 'image',
                    text: '{{ modularousTrans("media-library.images") }}',
                    total: {{ \Unusualify\Modularous\Entities\Media::query()->authorized()->count() }},
                    endpoint: '{{ route(Route::hasAdmin('media-library.media.index')) }}',
                    tagsEndpoint: '{{ route(Route::hasAdmin('media-library.media.tags')) }}',
                    uploaderConfig: {!! json_encode($mediasUploaderConfig) !!}
                });
                window['{{ modularousConfig('js_namespace') }}'].STORE.medias.showFileName = !!'{{ modularousConfig('media_library.show_file_name') }}';
            @endif

            @if (modularousConfig('enabled.file-library'))
                window['{{ modularousConfig('js_namespace') }}'].STORE.medias.types.push({
                    value: 'file',
                    text: '{{ modularousTrans("media-library.files") }}',
                    total: {{ \Unusualify\Modularous\Entities\File::query()->authorized()->count() }},
                    endpoint: '{{ route(Route::hasAdmin('file-library.file.index')) }}',
                    tagsEndpoint: '{{ route(Route::hasAdmin('file-library.file.tags')) }}',
                    uploaderConfig: {!! json_encode($filesUploaderConfig) !!}
                });
            @endif

            @stack('STORE')
        </script>
    </body>
</html>
