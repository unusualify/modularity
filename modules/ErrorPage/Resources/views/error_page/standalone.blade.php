{{--
    Builtin error document: ModularousVite + core-free.js mounts Vue/Vuetify on #admin
    (same entry/mount as free panel layouts). STORE must be defined before the deferred
    module evaluates — language.js reads STORE.languages.all[0].
--}}
@php
    $jsNamespace = modularousConfig('js_namespace');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="{{ $robotsMeta ?? 'noindex, nofollow' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle ?? config('app.name') }}</title>
    @if (! empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif

    <script>
        @php
            try {
                $errorPageTranslations = function_exists('get_translations') ? get_translations() : [];
            } catch (\Throwable) {
                $errorPageTranslations = [];
            }
        @endphp
        const TRANSLATIONS = @json($errorPageTranslations);
        {{-- Mirror default-store shape so core-free Vuex modules do not throw on import. --}}
        window['{{ $jsNamespace }}'] = {
            version: '{{ modularousConfig('version') }}',
            LOCALE: '{{ app()->getLocale() }}',
            STORE: {
                ambient: {
                    isHot: @json(ModularousVite::useHotFile(public_path('modularous.hot'))->isRunningHot()),
                    appName: @json(config('app.name')),
                    appEnv: @json(config('app.env')),
                    appDebug: @json((bool) config('app.debug')),
                    systemPackageVersions: {},
                },
                user: { isGuest: true },
                languages: { all: [], active: {} },
                config: { isInertia: false, test: false },
                datatable: {},
                form: {},
                browser: { selected: {} },
                medias: { types: [], config: { useWysiwyg: false, wysiwygOptions: {} } },
            },
            ENDPOINTS: {},
        };
    </script>

    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
</head>
<body style="margin:0;">
@if (! ModularousVite::useHotFile(public_path('modularous.hot'))->isRunningHot())
    @includeIf('modularous::partials.icons.svg-sprite')
@endif

{{-- core-free.js: app.mount('#admin') — in-DOM Vuetify tags compile via vue.esm-bundler --}}
<div id="admin">
    <v-app>
        <v-main>
            @yield('content')
        </v-main>
    </v-app>
</div>
</body>
</html>
