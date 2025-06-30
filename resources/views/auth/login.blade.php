@extends("{$MODULARITY_VIEW_NAMESPACE}::auth.layout", [
    'pageTitle' => ___('authentication.login') . ' | ' . \Unusualify\Modularity\Facades\Modularity::pageTitle()
])
@section('appTypeClass', 'body--form')

@php

@endphp

@push('head_last_js')

@endpush

@push('post_js')

@endpush

@push('STORE')
    {{-- window['{{ modularityConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!} --}}
    {{-- window['{{ modularityConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!} --}}
@endpush



