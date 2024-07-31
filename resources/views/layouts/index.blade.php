@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@section('appTypeClass', 'body--listing')

@php
    // $translate = $translate ?? false;
    // $translateTitle = $translateTitle ?? $translate ?? false;
    // $reorder = $reorder ?? false;
    // $nested = $nested ?? false;
    // $bulkEdit = $bulkEdit ?? true;
    // $create = $create ?? false;
    // $skipCreateModal = $skipCreateModal ?? false;

    // $controlLanguagesPublication = $controlLanguagesPublication ?? true;
    // dd($tableMainFilters);
    // dd(get_defined_vars());
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

@section('STORE')
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.datatable = {!! json_encode($tableStore ?? new StdClass()) !!}
@endsection
