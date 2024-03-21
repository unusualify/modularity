@extends("{$BASE_KEY}::layouts.master")

@push('extra_js_head')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
    {{-- @if(app()->isProduction())
        <link href="{{ unusualAsset('core-free.js')}}" rel="preload" as="script" crossorigin/>
    @endif --}}
@endpush

@section('content')
    @foreach ($elements as $i => $context)
        <ue-recursive-stuff :configuration='@json($context)'/>
    @endforeach
@stop

@section('initialStore')
    window['{{ unusualConfig('js_namespace') }}'].STORE.medias.crops = {!! json_encode(config('unusual.settings.crops') ?? []) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.medias.selected = {}

    window['{{ unusualConfig('js_namespace') }}'].STORE.browser = {}
    window['{{ unusualConfig('js_namespace') }}'].STORE.browser.selected = {}
@stop

{{-- @push('extra_js')
    <script src="{{ unusualAsset('core-free.js') }}" crossorigin></script>
@endpush --}}
