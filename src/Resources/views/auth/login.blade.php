@extends("{$MODULARITY_VIEW_NAMESPACE}::auth.layout", [
    'pageTitle' => ___('authentication.login')
])
@section('appTypeClass', 'body--form')

@php

@endphp

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
        {{-- <template v-slot:[@json($slotName)] > --}}
        <template v-slot:{{ $slotName }} >
            <ue-recursive-stuff
                :configuration='@json($configuration)'
            />
        </template>
    @endforeach
@stop

@push('head_last_js')

@endpush

@push('post_js')

@endpush

@section('STORE')
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
@endsection



