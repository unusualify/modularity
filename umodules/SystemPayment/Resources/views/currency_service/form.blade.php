@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@section('appTypeClass', 'body--form')

@php
    $titleFormKey = $titleFormKey ?? 'name';
    $disableContentFieldset = $disableContentFieldset ?? false;
@endphp

@section('content')
    <ue-form v-bind='@json($formAttributes)'/>

@stop

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-index.js'])
    }}
@endpush

@push('post_js')

@endpush

@section('STORE')
    window['{{ modularityConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ modularityConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
@endsection



