@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@php

@endphp

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush
@push('post_js')

@endpush

@section('content')
    <div class="dashboard pa-3 h-100">
        <ue-dashboard :blocks='@json($blocks ?? [])'>
    </div>
@stop

@section('STORE')

@stop

