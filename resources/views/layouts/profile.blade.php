@extends("{$MODULAROUS_VIEW_NAMESPACE}::layouts.master")

@php

@endphp

@push('head_last_js')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush


@section('content')
    <div class="profile d-flex flex-column flex-grow-1 min-height-0">
        @foreach ($elements as $i => $context)
            <ue-recursive-stuff
                :configuration='@json($context)'
            />
        @endforeach
    </div>
@stop

@section('STORE')
    window['{{ modularousConfig('js_namespace') }}'].STORE.form = {}
@stop

