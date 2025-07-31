@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.base")

@push('head_css')
    <style>
        .ue-loading-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 1);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: opacity 1s ease-in-out;
        }

        @keyframes opacity {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }

        .ue-loading-spinner .ue-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(var(--v-theme-primary), 1);
            border-top: 4px solid #fff;
            border-radius: 50%;
            animation: spin 0.3s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .ue-loading-spinner .ue-loading-text {
            color: rgba(var(--v-theme-primary), 1);
            font-size: 16px;
            font-weight: bold;
        }
    </style>
@endpush

@push('head_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingSpinner = document.getElementById('loading-spinner');
            if (loadingSpinner) {
                loadingSpinner.style.opacity = '0';

                setTimeout(() => {
                    loadingSpinner.style.display = 'none';
                }, 700);
            }
        });
    </script>
@endpush

@section('body')
    @php
        $defaultMainNavigationConfiguration = [
            'profileMenu' => [],
            'breadcrumbs' => [],
            'sidebar' => [],
        ];
        $navigation = array_merge(
            $defaultMainNavigationConfiguration,
            $navigation ?? []
        );
        $impersonation ??= [];
        $authorization ??= [];
        $headerTitle ??= config('app.name');

        $_mainConfiguration = array_merge_recursive_preserve([
            'headerTitle' => $headerTitle,
            'hideDefaultSidebar' => $hideDefaultSidebar ?? false,
            'fixedAppBar' => $fixedAppBar ?? false,
            'appBarOrder' => $appBarOrder ?? 0,

            'navigation' => $navigation,
            'impersonation' => $impersonation,
            'authorization' => $authorization,
        ], $_mainConfiguration ?? []);
    @endphp
    <div id="admin">
        <ue-main
            ref='main'
            v-bind='@json($_mainConfiguration)'
            >
            <div id="ue-main-body" class="ue--main-container pa-3 h-100">

                @yield('content')

                <div id="ue-bottom-content">
                    @if (modularityConfig('enabled.media-library') || modularityConfig('enabled.file-library'))
                        {{-- <ue-media
                            ref="mediaLibra"
                            :authorized="{{ json_encode(auth('twill_users')->user()->can('upload')) }}"
                            :extra-metadatas="{{ json_encode(array_values(modularityConfig('media_library.extra_metadatas_fields', []))) }}"
                            :translatable-metadatas="{{ json_encode(array_values(modularityConfig('media_library.translatable_metadatas_fields', []))) }}"
                        ></ue-media> --}}

                    @endif

                    {{-- <ue-alert ref='alert'></ue-alert> --}}
                    {{-- <ue-modal-test></ue-modal-test> --}}
                </div>
            </div>

            @yield('slots')

            @if(view()->exists('modularity::layouts.slots'))
                @include('modularity::layouts.slots')
            @endif
        </ue-main>
    </div>

    <div class="ue-loading-spinner" id="loading-spinner">
        <div class="ue-spinner"></div>
    </div>

@endsection

@push('STORE')
    window['{{ modularityConfig('js_namespace') }}'].TIMEZONE = '{{ modularityConfig('timezone') }}';
    window['{{ modularityConfig('js_namespace') }}'].AUTHORIZATION = @json($authorization);

    window['{{ modularityConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ modularityConfig('js_namespace') }}'].STORE.config = {
        test: false,
        profileMenu: {!! json_encode($navigation['profileMenu']) !!},
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
@endpush
