<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') . " Locale Settings" }}</title>
    <link rel="stylesheet" href="{{ asset('/vendor/translation/css/main.css') }}">
</head>
<body>

    <div id="app">

        @include('translation::nav')
        @include('translation::notifications')

        @yield('body')

    </div>

    <script src="{{ asset('/vendor/translation/js/app.js') }}"></script>
</body>
</html>

{{-- @extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@push('head_css')
    <link rel="stylesheet" href="{{ asset('/vendor/translation/css/main.css') }}">
@endpush

@push('extra_js_head')
    @if(app()->isProduction())
        <link href="{{ unusualMix('core-free.js')}}" rel="preload" as="script" crossorigin/>
    @endif
@endpush

@section('content')
    <div id="app">

        @include('translation::nav')
        @include('translation::notifications')

        @yield('body')

    </div>
@stop

@section('initialStore')
    window['{{ unusualConfig('js_namespace') }}'].STORE.medias.crops = {!! json_encode(config('unusual.settings.crops') ?? []) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.medias.selected = {}

    window['{{ unusualConfig('js_namespace') }}'].STORE.browser = {}
    window['{{ unusualConfig('js_namespace') }}'].STORE.browser.selected = {}
@stop

@push('post_js')
    <script src="{{ unusualMix('core-free.js') }}" crossorigin></script>
    <script src="{{ asset('/vendor/translation/js/app.js') }}"></script>
@endpush --}}

