@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@php

@endphp

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush


@section('content')
    <div class="pa-3">
        @foreach ($elements as $i => $context)
            <ue-recursive-stuff
                :configuration='@json($context)'
                />
        @endforeach
    </div>
@stop

@section('STORE')
    window['{{ modularityConfig('js_namespace') }}'].STORE.form = {}
@stop

