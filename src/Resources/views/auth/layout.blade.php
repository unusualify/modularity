<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ) }}">
    <head>
        @include("{$BASE_KEY}::partials.head")

        @if( app()->isProduction() )
            <link href="{{ unusualMix('core-auth.js') }}" rel="preload" as="script" crossorigin />
        @else
            <script src="{{ unusualMix('chunk-common.js')}}" defer></script>
            <script src="{{ unusualMix('chunk-vendors.js') }}" defer></script>
            <script type="text/javascript" src="{{ unusualMix('core-auth.js') }}" defer></script>
        @endif

        @stack('head_last_js')
    </head>
    <body>
        @include("{$BASE_KEY}::partials.icons.svg-sprite")
        {{-- @dd(get_defined_vars()) --}}
        <div id="auth">
            <ue-auth>
                @section('content')
                    <v-sheet>
                        <ue-form v-bind='@json($formAttributes)'>
                            <template v-slot:submit="object">
                                <v-btn block dense type="submit" :disabled="!object.validForm">
                                @{{ object.buttonDefaultText.toUpperCase() }}
                                </v-btn>
                            </template>
                        </ue-form>
                    </v-sheet>

                    @foreach( ($slots ?? []) as $slotName => $configuration)
                        <template v-slot:{{ $slotName }} >
                            <ue-recursive-shit
                                :configuration='@json($configuration)'
                            />
                        </template>
                        {{-- <template v-slot:bottom1 >
                            <ue-recursive-shit
                                :configuration='@json($configuration)'
                            />
                        </template> --}}
                    @endforeach

                @stop
                @yield('content')
            </ue-auth>
        </div>
        <script>
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'] = {};
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].LOCALE = '{{ config(unusualBaseKey() . '.locale') }}';
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].version = '{{ config(unusualBaseKey() . '.version') }}';
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].ENDPOINTS = {};
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE = {};

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.config = {
                // isMiniSidebar:  '{{ $isMiniSidebar ?? true }}',
                isMiniSidebar:  {!! json_encode($isMiniSidebar ?? true) !!},
            },

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.languages = {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!};

            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias = {};
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.types = [];
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.config = {
                useWysiwyg: {{ config(unusualBaseKey() . '.media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
                wysiwygOptions: {!! json_encode(config(unusualBaseKey() . '.media_library.media_caption_wysiwyg_options')) !!}
            };



            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].unusualLocalization = {!! json_encode($unusualLocalization) !!};

            // window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.datatable = {}
            window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.form = {}

            @section('STORE')
                window['{{ config(unusualBaseKey() . '.js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
                window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
            @stop
            @yield('STORE')
        </script>

        {{-- <script src="{{ unusualMix('chunk-common.js')}}" > </script>
        <script src="{{ unusualMix('chunk-vendors.js') }}"> </script>
        <script type="text/javascript" src="{{ unusualMix('core-auth.js') }}"></script> --}}

        @stack('post_js')

    </body>
</html>
