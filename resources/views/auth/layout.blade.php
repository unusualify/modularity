@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.base")

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-auth.js'])
    }}
@endpush

{{-- @dd(get_defined_vars()) --}}
@section('body')
    <div id="auth">

        <ue-auth
            title="'{{ __('authentication.create-an-account') ?? 'CREATE AN ACCOUNT' }}'"
            v-bind='@json($attributes ?? [])'
            @if(isset($taskState))
                no-divider
            @endif
        >
            @section('content')
                @if(!isset($taskState))
                    <ue-form v-bind='@json($formAttributes)'>
                        {{-- <template v-slot:submit="submitScope">
                            <v-btn block dense type="submit" :disabled="!submitScope.validForm" :loading="submitScope.loading">
                                @{{ submitScope.buttonDefaultText.toUpperCase() }}
                            </v-btn>
                        </template> --}}
                        @foreach( ($formSlots ?? []) as $slotName => $configuration)
                            <template v-slot:{{ $slotName }}="slotScope">
                                <ue-recursive-stuff
                                    :configuration='@json($configuration)'
                                    :bindData='slotScope'
                                />
                            </template>
                        @endforeach
                    </ue-form>
                @else
                    <ue-success v-bind='@json($taskState)'>
                    </ue-success>
                @endif

                @foreach( ($slots ?? []) as $slotName => $configuration)
                    <template v-slot:{{ $slotName }}="slotScope">
                        <ue-recursive-stuff
                            :configuration='@json($configuration)'
                            :bindData='slotScope'
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

