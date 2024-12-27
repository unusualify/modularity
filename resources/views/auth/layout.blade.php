<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ) }}">
    <head>
        @include("{$MODULARITY_VIEW_NAMESPACE}::partials.head")

        {{-- @if( app()->isProduction() )
            <link href="{{ unusualMix('core-auth.js') }}" rel="preload" as="script" crossorigin />
        @else
            <script src="{{ unusualMix('chunk-common.js')}}" defer></script>
            <script src="{{ unusualMix('chunk-vendors.js') }}" defer></script>
            <script type="text/javascript" src="{{ unusualMix('core-auth.js') }}" defer></script>
        @endif --}}
        {{
            ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-auth.js'])
        }}
        @stack('head_last_js')
    </head>
    <body>
        @include("{$MODULARITY_VIEW_NAMESPACE}::partials.icons.svg-sprite")
        {{-- @dd(get_defined_vars()) --}}
        <div id="auth">
            {{-- @dd(__('authentication.create-an-account')) --}}
            {{-- @dd($formAttributes) --}}
            {{-- @dd(!isset($taskState)) --}}

            <ue-auth
                title="'{{ __('authentication.create-an-account') ?? 'CREATE AN ACCOUNT' }}'"
                @if(isset($taskState))
                    no-divider
                @endif
            >
                @section('content')
                    @if(!isset($taskState))
                        <ue-form v-bind='@json($formAttributes)'>
                            <template v-slot:submit="object">
                                <v-btn block dense type="submit" :disabled="!object.validForm">
                                @{{ object.buttonDefaultText.toUpperCase() }}
                                </v-btn>
                            </template>
                            {{-- @dd($formSlots) --}}
                            @foreach( ($formSlots ?? []) as $slotTestName => $testconf)
                            {{-- @dd($slotName, $configuration) --}}
                                <template v-slot:{{ $slotTestName }} >
                                    <ue-recursive-stuff
                                        :configuration='@json($testconf)'
                                    />
                                </template>
                            @endforeach
                        </ue-form>
                    @else
                        <ue-success v-bind='@json($taskState)'>
                        </ue-success>
                    @endif

                    @foreach( ($slots ?? []) as $slotName => $configuration)
                        <template v-slot:{{ $slotName }} >
                            <ue-recursive-stuff
                                :configuration='@json($configuration)'
                            />
                        </template>
                        {{-- <template v-slot:bottom1 >
                            <ue-recursive-stuff
                                :configuration='@json($configuration)'
                            />
                        </template> --}}
                    @endforeach
                @stop
                @yield('content')
            </ue-auth>
        </div>
        <script>
            window['{{ unusualConfig('js_namespace') }}'] = {};
            window['{{ unusualConfig('js_namespace') }}'].LOCALE = '{{ unusualConfig('locale') }}';
            window['{{ unusualConfig('js_namespace') }}'].version = '{{ unusualConfig('version') }}';
            window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {
                languages: @json(route('api.languages.index'))
            };
            window['{{ unusualConfig('js_namespace') }}'].STORE = {};

            window['{{ unusualConfig('js_namespace') }}'].STORE.config = {
                test: false,
                // isMiniSidebar:  '{{ $isMiniSidebar ?? true }}',
                isMiniSidebar:  {!! json_encode($isMiniSidebar ?? true) !!},
            },

            window['{{ unusualConfig('js_namespace') }}'].STORE.languages = {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!};

            window['{{ unusualConfig('js_namespace') }}'].STORE.medias = {};
            window['{{ unusualConfig('js_namespace') }}'].STORE.medias.types = [];
            window['{{ unusualConfig('js_namespace') }}'].STORE.medias.config = {
                useWysiwyg: {{ unusualConfig('media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
                wysiwygOptions: {!! json_encode(unusualConfig('media_library.media_caption_wysiwyg_options')) !!}
            };



            window['{{ unusualConfig('js_namespace') }}'].unusualLocalization = {!! json_encode($unusualLocalization) !!};

            // window['{{ unusualConfig('js_namespace') }}'].STORE.datatable = {}
            window['{{ unusualConfig('js_namespace') }}'].STORE.form = {}

            @section('STORE')
                window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
                window['{{ unusualConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
            @stop

            window['{{ unusualConfig('js_namespace') }}'].STORE.browser = {
                selected: {}
            }
            @yield('STORE')
        </script>

        {{-- <script src="{{ unusualMix('chunk-common.js')}}" > </script>
        <script src="{{ unusualMix('chunk-vendors.js') }}"> </script>
        <script type="text/javascript" src="{{ unusualMix('core-auth.js') }}"></script> --}}

        @stack('post_js')

    </body>
</html>
