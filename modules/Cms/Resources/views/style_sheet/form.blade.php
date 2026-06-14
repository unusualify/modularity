@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.master")

@section('appTypeClass', 'body--form')

@php

@endphp

@push('head_last_js')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-form.js'])
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
    window['{{ modularousConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ modularousConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
@endpush
