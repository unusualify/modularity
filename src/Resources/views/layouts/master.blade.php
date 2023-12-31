<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ) }}">
    <head>
        {{-- <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title> {{ $title ?? 'Module Template' }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @yield('pre-scripts') --}}
        @include("{$BASE_KEY}::partials.head")

        @stack('head_last_js')
    </head>
    <body>
        @include("{$BASE_KEY}::partials.icons.svg-sprite")

        {{-- @dd( auth()->user() ) --}}
        @php
            $isMiniSidebar = false;

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
                <div id="ue-main-body" class="ue--main-container" style="min-height: 100vh">

                    @yield('content')

                    <div id="ue-bottom-content">
                        @if (config(unusualBaseKey() . '.enabled.media-library') || config(unusualBaseKey() . '.enabled.file-library'))
                            {{-- <ue-media
                                ref="mediaLibra"
                                :authorized="{{ json_encode(auth('twill_users')->user()->can('upload')) }}"
                                :extra-metadatas="{{ json_encode(array_values(config(unusualBaseKey() . '.media_library.extra_metadatas_fields', []))) }}"
                                :translatable-metadatas="{{ json_encode(array_values(config(unusualBaseKey() . '.media_library.translatable_metadatas_fields', []))) }}"
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
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'] = {};
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].version = '{{ config(unusualBaseKey() . '.version') }}';
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].LOCALE = '{{ config(unusualBaseKey() . '.locale') }}';
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].TIMEZONE = '{{ config(unusualBaseKey() . '.timezone') }}';
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].AUTHORIZATION = @json($authorization);

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].ENDPOINTS = {};
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE = {};

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.config = {
                // isMiniSidebar:  '{{ $isMiniSidebar ?? true }}',
                isMiniSidebar:  {!! json_encode($isMiniSidebar ?? true) !!},
            },

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias = {};
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.types = [];
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.config = {
                useWysiwyg: {{ config(unusualBaseKey() . '.media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
                wysiwygOptions: {!! json_encode(config(unusualBaseKey() . '.media_library.media_caption_wysiwyg_options')) !!}
            };

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.languages = {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!};

            @if (config(unusualBaseKey() . '.enabled.media-library'))
                window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.types.push({
                    value: 'image',
                    text: '{{ unusualTrans("media-library.images") }}',
                    total: {{ \Unusualify\Modularity\Entities\Media::count() }},
                    endpoint: '{{ route('media-library.medias.index') }}',
                    tagsEndpoint: '{{ route('media-library.medias.tags') }}',
                    uploaderConfig: {!! json_encode($mediasUploaderConfig) !!}
                });
                window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.showFileName = !!'{{ config(unusualBaseKey() . '.media_library.show_file_name') }}';
            @endif

            @if (config(unusualBaseKey() . '.enabled.file-library'))
                window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.types.push({
                    value: 'file',
                    text: '{{ unusualTrans("media-library.files") }}',
                    total: {{ \Unusualify\Modularity\Entities\File::count() }},
                    endpoint: '{{ route('file-library.files.index') }}',
                    tagsEndpoint: '{{ route('file-library.files.tags') }}',
                    uploaderConfig: {!! json_encode($filesUploaderConfig) !!}
                });
            @endif
            // window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.crops = {!! json_encode(([]) + config(unusualBaseKey() . '.block_editor.crops', []) + (config(unusualBaseKey() . '.settings.crops') ?? [])) !!}
            // window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.selected = {}

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].unusualLocalization = {!! json_encode($unusualLocalization) !!};

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.datatable = {}
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.form = {}
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.browser = {
                selected: {}
            }
            // console.log(
            //     window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.types
            // )
            @yield('STORE')
        </script>

        <script src="{{ unusualMix('chunk-common.js')}}" > </script>
        <script src="{{ unusualMix('chunk-vendors.js') }}"> </script>

        @stack('post_js')

    </body>
</html>
