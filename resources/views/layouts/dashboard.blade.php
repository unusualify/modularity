@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.master")

@php

@endphp

@push('head_last_js')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush
@push('post_js')

@endpush

@section('content')
    <div class="dashboard flex-grow-1 min-height-0 d-flex flex-column">
        <ue-blocks :items='@json($blockItems ?? [])'>
    </div>
@stop
