@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@section('appTypeClass', 'body--listing')

@php

@endphp

@section('content')
    {{-- @include("{$MODULARITY_VIEW_NAMESPACE}::components.datatable", $tableAttributes ?? []) --}}
    @include("{$MODULARITY_VIEW_NAMESPACE}::components.table", $tableAttributes ?? [])
@stop

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-index.js'])
    }}
@endpush

@push('post_js')
    <script>

    </script>
@endpush

@push('STORE')
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.datatable = {!! json_encode($tableStore ?? new StdClass()) !!}
@endpush
