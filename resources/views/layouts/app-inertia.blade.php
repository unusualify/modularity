<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta name="robots" content="noindex,nofollow" />

        {{-- <title> {{ $pageTitle ?? \Unusualify\Modularity\Facades\Modularity::pageTitle() }}</title> --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            const TRANSLATIONS = @json(get_translations());
            // const URLS = @json($urls);
        </script>

        <!-- Scripts -->
        @routes()
        @stack('head_css')
        @stack('head_js')

        {{
            ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-inertia.js'])
        }}

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @if(!ModularityVite::useHotFile(public_path('modularity.hot'))->isRunningHot())
            @include("{$MODULARITY_VIEW_NAMESPACE}::partials.icons.svg-sprite")
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
            @include("{$MODULARITY_VIEW_NAMESPACE}::partials.default-store")

            window['{{ modularityConfig('js_namespace') }}'].TIMEZONE = '{{ modularityConfig('timezone') }}';
            window['{{ modularityConfig('js_namespace') }}'].AUTHORIZATION = @json($authorization ?? []);

            window['{{ modularityConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
            window['{{ modularityConfig('js_namespace') }}'].STORE.config = {
                isInertia: {{ json_encode(\Unusualify\Modularity\Facades\Modularity::shouldUseInertia()) }},
                test: false,
                profileMenu: {!! json_encode($navigation['profileMenu'] ?? []) !!},
                sidebarOptions: {!! json_encode(modularityConfig('ui_settings.sidebar')) !!},
                secondarySidebarOptions : {!! json_encode(modularityConfig('ui_settings.secondarySidebar')) !!},
            },
            window['{{ modularityConfig('js_namespace') }}'].STORE.user = {
                isGuest: {{ json_encode(auth()->guest()) }},
                profile: {!! json_encode($currentUser) !!},
                profileRoute: '{{ route(Route::hasAdmin('profile.update')) }}',
                profileShortcutModel: {!! json_encode($profileShortcutModel ?? new StdClass()) !!},
                profileShortcutSchema: {!! json_encode($profileShortcutSchema ?? new StdClass()) !!},

                loginShortcutModel: {!! json_encode($loginShortcutModel ?? new StdClass()) !!},
                loginShortcutSchema: {!! json_encode($loginShortcutSchema ?? new StdClass()) !!},
                loginRoute: '{{ route('admin.login') }}',
            },

            @if (modularityConfig('enabled.media-library'))
                window['{{ modularityConfig('js_namespace') }}'].STORE.medias.types.push({
                    value: 'image',
                    text: '{{ modularityTrans("media-library.images") }}',
                    total: {{ \Unusualify\Modularity\Entities\Media::query()->authorized()->count() }},
                    endpoint: '{{ route(Route::hasAdmin('media-library.media.index')) }}',
                    tagsEndpoint: '{{ route(Route::hasAdmin('media-library.media.tags')) }}',
                    uploaderConfig: {!! json_encode($mediasUploaderConfig) !!}
                });
                window['{{ modularityConfig('js_namespace') }}'].STORE.medias.showFileName = !!'{{ modularityConfig('media_library.show_file_name') }}';
            @endif

            @if (modularityConfig('enabled.file-library'))
                window['{{ modularityConfig('js_namespace') }}'].STORE.medias.types.push({
                    value: 'file',
                    text: '{{ modularityTrans("media-library.files") }}',
                    total: {{ \Unusualify\Modularity\Entities\File::query()->authorized()->count() }},
                    endpoint: '{{ route(Route::hasAdmin('file-library.file.index')) }}',
                    tagsEndpoint: '{{ route(Route::hasAdmin('file-library.file.tags')) }}',
                    uploaderConfig: {!! json_encode($filesUploaderConfig) !!}
                });
            @endif

            @stack('STORE')
        </script>
    </body>
</html>
