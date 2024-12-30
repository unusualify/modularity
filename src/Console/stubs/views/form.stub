@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@section('appTypeClass', 'body--form')

@php

@endphp

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-form.js'])
    }}
@endpush

@section('content')
    <ue-form v-bind='@json($formAttributes)'/>
@stop

@push('post_js')
    <script>

    </script>
@endpush

@push('STORE')
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
@endpush
