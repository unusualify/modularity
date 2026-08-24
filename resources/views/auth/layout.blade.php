@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.base")

@push('head_last_js')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-auth.js'])
    }}
@endpush

@php
    $attributes = $attributes ?? [];
    $formAttributes = $formAttributes ?? [];
    $formSlots = $formSlots ?? [];
    $slots = $slots ?? [];
    $authComponentName = modularousConfig('auth_pages.component_name', 'ue-auth');
@endphp

@section('body')
    <div id="auth">
        {{-- All $attributes are passed to the auth component. Custom auth components can declare any props (e.g. bannerDescription, redirectButtonText) and receive them from auth_pages.attributes. --}}
        <{{ $authComponentName }}
            v-bind='@json($attributes)'
            @if(isset($taskState))
                no-divider
            @endif
        >
            @section('content')
                @if(!isset($taskState))
                    <ue-form :border="false" v-bind='@json($formAttributes)'>
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
                @endforeach
            @stop
            @yield('content')
        </{{ $authComponentName }}>
    </div>
@endsection

@push('STORE')
    window['{{ modularousConfig('js_namespace') }}'].STORE.config = { test: false };
    window['{{ modularousConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new stdClass()) !!};
    window['{{ modularousConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new stdClass()) !!};
    window['{{ modularousConfig('js_namespace') }}'].AUTH_COMPONENT = {!! json_encode(modularousConfig('auth_component', [])) !!};
    window.__MODULAROUS_AUTH_CONFIG__ = {!! json_encode(modularousConfig('auth_component', [])) !!};
@endpush

