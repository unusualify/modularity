@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.base")

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-auth.js'])
    }}
@endpush

@section('body')
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
@endsection

@push('STORE')
    window['{{ modularityConfig('js_namespace') }}'].STORE.config = {
        test: false,
    };
    window['{{ modularityConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ modularityConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
@endpush

