@extends("{$BASE_KEY}::layouts.master")

@push('extra_js_head')
    @if(app()->isProduction())
        <link href="{{ unusualAsset('core-free.js')}}" rel="preload" as="script" crossorigin/>
    @endif
@endpush

@section('content')
    @foreach ($elements as $i => $context)
        <ue-recursive-stuff :configuration='@json($context)'/>
    @endforeach
@stop

@section('initialStore')
    window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.crops = {!! json_encode(config('unusual.settings.crops') ?? []) !!}
    window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.medias.selected = {}

    window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.browser = {}
    window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.browser.selected = {}
@stop

{{-- @push('extra_js')
    <script src="{{ unusualAsset('core-free.js') }}" crossorigin></script>
@endpush --}}
