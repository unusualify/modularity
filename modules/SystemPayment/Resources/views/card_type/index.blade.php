@extends("$MODULARITY_VIEW_NAMESPACE::layouts.master")

@section('appTypeClass', 'body--listing')

@php
    $customTableAttributes = [];
@endphp

@section('content')
    @include("$MODULARITY_VIEW_NAMESPACE::components.table", array_merge($tableAttributes ?? [], $customTableAttributes))
@stop

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-index.js'])
    }}
@endpush

@push('STORE')
    window['{{ modularityConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ modularityConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
    window['{{ modularityConfig('js_namespace') }}'].STORE.datatable = {!! json_encode($tableStore ?? new StdClass()) !!}
@endpush



