<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ) }}">
    <head>
        {{-- <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title> {{ $title ?? 'Module Template' }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @yield('pre-scripts') --}}
        @include('base::partials.head')

        @stack('head_last_js')
    </head>
    <body>
        @include('base::partials.icons.svg-sprite')

        {{-- @dd( auth()->user() ) --}}
        @php

            $isMiniSidebar = false;
        @endphp
        {{-- @dd($sideMenu) --}}

        <div id="admin">
            <ue-main
                ref='main'
                :configuration='@json($configuration)'
                >
                <div id="ue-main-body">

                    @yield('content')

                    <div id="ue-bottom-content">
                        @if (config(getUnusualBaseKey() . '.enabled.media-library') || config(getUnusualBaseKey() . '.enabled.file-library'))
                            {{-- <ue-media
                                ref="mediaLibra"
                                :authorized="{{ json_encode(auth('twill_users')->user()->can('upload')) }}"
                                :extra-metadatas="{{ json_encode(array_values(config(getUnusualBaseKey() . '.media_library.extra_metadatas_fields', []))) }}"
                                :translatable-metadatas="{{ json_encode(array_values(config(getUnusualBaseKey() . '.media_library.translatable_metadatas_fields', []))) }}"
                            ></ue-media> --}}

                        @endif

                        <ue-alert ref='alert'
                        ></ue-alert>

                        {{-- <ue-modal-test></ue-modal-test> --}}
                    </div>
                </div>
            </ue-main>
        </div>
        {{-- <script src="{{ asset('js/admin.js') }}"></script> --}}

        {{-- @yield('initial-scripts') --}}
        <script>
            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'] = {};
            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].version = '{{ config(getUnusualBaseKey() . '.version') }}';
            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE = {};

            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.config = {
                // isMiniSidebar:  '{{ $isMiniSidebar ?? true }}',
                isMiniSidebar:  {!! json_encode($isMiniSidebar ?? true) !!},
            },

            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias = {};
            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias.types = [];
            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias.config = {
                useWysiwyg: {{ config(getUnusualBaseKey() . '.media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
                wysiwygOptions: {!! json_encode(config(getUnusualBaseKey() . '.media_library.media_caption_wysiwyg_options')) !!}
            };

            @if (config(getUnusualBaseKey() . '.enabled.media-library'))
                window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias.types.push({
                    value: 'image',
                    text: '{{ unusualTrans("base::lang.media-library.images") }}',
                    total: {{ \OoBook\CRM\Base\Entities\Media::count() }},
                    endpoint: '{{ route('media-library.medias.index') }}',
                    tagsEndpoint: '{{ route('media-library.medias.tags') }}',
                    uploaderConfig: {!! json_encode($mediasUploaderConfig) !!}
                });
                window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias.showFileName = !!'{{ config(getUnusualBaseKey() . '.media_library.show_file_name') }}';
            @endif

            @if (config(getUnusualBaseKey() . '.enabled.file-library'))
                window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias.types.push({
                    value: 'file',
                    text: '{{ twillTrans("twill::lang.media-library.files") }}',
                    total: {{ \OoBook\CRM\Base\Entities\File::count() }},
                    endpoint: '{{ route('file-library.files.index') }}',
                    tagsEndpoint: '{{ route('file-library.files.tags') }}',
                    uploaderConfig: {!! json_encode($filesUploaderConfig) !!}
                });
            @endif
            // window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias.crops = {!! json_encode(([]) + config(getUnusualBaseKey() . '.block_editor.crops', []) + (config(getUnusualBaseKey() . '.settings.crops') ?? [])) !!}
            // window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.medias.selected = {}

            window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].unusualLocalization = {!! json_encode($unusualLocalization) !!};

            @yield('STORE')
        </script>

        <script src="{{ unusualMix('chunk-common.js')}}" > </script>
        <script src="{{ unusualMix('chunk-vendors.js') }}"> </script>

        @stack('post_js')

    </body>
</html>
