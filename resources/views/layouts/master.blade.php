<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ) }}">
    <head>
        {{-- <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title> {{ $title ?? 'Module Template' }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @yield('pre-scripts') --}}
        @include("{$MODULARITY_VIEW_NAMESPACE}::partials.head")

        @stack('head_last_js')
    </head>
    <body>
        {{-- @if(!unusualConfig('is_development', false))
            @include("{$MODULARITY_VIEW_NAMESPACE}::partials.icons.svg-sprite")
        @endif --}}
        @if(!ModularityVite::useHotFile(public_path('modularity.hot'))->isRunningHot())
            @include("{$MODULARITY_VIEW_NAMESPACE}::partials.icons.svg-sprite")
        @endif

        @php
            $_mainConfiguration = [
                'navigation' => $navigation,
                'impersonation' => $impersonation,
                'authorization' => $authorization
            ];
        @endphp
        {{-- @dd($sideMenu) --}}
        {{-- @dd($currentUser->isImpersonating(), get_defined_vars()) --}}

        <div id="admin">
            <ue-main
                ref='main'
                v-bind='@json($_mainConfiguration)'

                >
                @if(auth()->user()->invalidCompany)
                    <template v-slot:main-top>
                        <v-alert
                            density="compact"
                            type="warning"
                            text="{{ ___('messages.invalid-company') }}"
                        ></v-alert>
                    </template>
                @endif
                <div id="ue-main-body" class="ue--main-container" style="height: 100dvh;">

                    @yield('content')

                    <div id="ue-bottom-content">
                        @if (unusualConfig('enabled.media-library') || unusualConfig('enabled.file-library'))
                            {{-- <ue-media
                                ref="mediaLibra"
                                :authorized="{{ json_encode(auth('twill_users')->user()->can('upload')) }}"
                                :extra-metadatas="{{ json_encode(array_values(unusualConfig('media_library.extra_metadatas_fields', []))) }}"
                                :translatable-metadatas="{{ json_encode(array_values(unusualConfig('media_library.translatable_metadatas_fields', []))) }}"
                            ></ue-media> --}}

                        @endif

                        {{-- <ue-alert ref='alert'></ue-alert> --}}
                        {{-- <ue-modal-test></ue-modal-test> --}}
                    </div>
                </div>
            </ue-main>
        </div>
        {{-- <script src="{{ asset('js/admin.js') }}"></script> --}}

        {{-- @yield('initial-scripts') --}}
        <script>
            window['{{ unusualConfig('js_namespace') }}'] = {};
            window['{{ unusualConfig('js_namespace') }}'].version = '{{ unusualConfig('version') }}';
            window['{{ unusualConfig('js_namespace') }}'].LOCALE = '{{ unusualConfig('locale') }}';
            window['{{ unusualConfig('js_namespace') }}'].TIMEZONE = '{{ unusualConfig('timezone') }}';
            window['{{ unusualConfig('js_namespace') }}'].AUTHORIZATION = @json($authorization);

            window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
            window['{{ unusualConfig('js_namespace') }}'].STORE = {};

            window['{{ unusualConfig('js_namespace') }}'].STORE.config = {
                test: false,
                sideBarOpt: {!! json_encode(unusualConfig('ui_settings.sidebar')) !!},
                secondarySideBar : {!! json_encode(unusualConfig('ui_settings.secondarySidebar')) !!},
                profileMenu: {!! json_encode($navigation['profileMenu']) !!},
                currentUser: {!! json_encode($currentUser->only(['name', 'email'])) !!}
            },

            window['{{ unusualConfig('js_namespace') }}'].STORE.medias = {};
            window['{{ unusualConfig('js_namespace') }}'].STORE.medias.types = [];
            window['{{ unusualConfig('js_namespace') }}'].STORE.medias.config = {
                useWysiwyg: {{ unusualConfig('media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
                wysiwygOptions: {!! json_encode(unusualConfig('media_library.media_caption_wysiwyg_options')) !!}
            };

            window['{{ unusualConfig('js_namespace') }}'].STORE.languages = {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!};

            @if (unusualConfig('enabled.media-library'))
                window['{{ unusualConfig('js_namespace') }}'].STORE.medias.types.push({
                    value: 'image',
                    text: '{{ unusualTrans("media-library.images") }}',
                    total: {{ \Unusualify\Modularity\Entities\Media::query()->authorized()->count() }},
                    endpoint: '{{ route(Route::hasAdmin('media-library.media.index')) }}',
                    tagsEndpoint: '{{ route(Route::hasAdmin('media-library.media.tags')) }}',
                    uploaderConfig: {!! json_encode($mediasUploaderConfig) !!}
                });
                window['{{ unusualConfig('js_namespace') }}'].STORE.medias.showFileName = !!'{{ unusualConfig('media_library.show_file_name') }}';
            @endif

            @if (unusualConfig('enabled.file-library'))
                window['{{ unusualConfig('js_namespace') }}'].STORE.medias.types.push({
                    value: 'file',
                    text: '{{ unusualTrans("media-library.files") }}',
                    total: {{ \Unusualify\Modularity\Entities\File::query()->authorized()->count() }},
                    endpoint: '{{ route(Route::hasAdmin('file-library.file.index')) }}',
                    tagsEndpoint: '{{ route(Route::hasAdmin('file-library.file.tags')) }}',
                    uploaderConfig: {!! json_encode($filesUploaderConfig) !!}
                });
            @endif
            // window['{{ unusualConfig('js_namespace') }}'].STORE.medias.crops = {!! json_encode(([]) + unusualConfig('block_editor.crops', []) + (unusualConfig('settings.crops', []) ?? [])) !!}
            // window['{{ unusualConfig('js_namespace') }}'].STORE.medias.selected = {}

            window['{{ unusualConfig('js_namespace') }}'].unusualLocalization = {!! json_encode($unusualLocalization) !!};

            window['{{ unusualConfig('js_namespace') }}'].STORE.datatable = {}
            window['{{ unusualConfig('js_namespace') }}'].STORE.form = {}
            window['{{ unusualConfig('js_namespace') }}'].STORE.browser = {
                selected: {}
            }
            // console.log(
            //     window['{{ unusualConfig('js_namespace') }}'].STORE.medias.types
            // )
            @yield('STORE')
        </script>

        {{-- <script src="{{ unusualMix('chunk-common.js')}}" > </script>
        <script src="{{ unusualMix('chunk-vendors.js') }}"> </script> --}}

        @stack('post_js')

    </body>
</html>
