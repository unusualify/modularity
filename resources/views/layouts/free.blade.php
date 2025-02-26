@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@push('extra_js_head')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush

@section('content')
    @foreach ($elements as $i => $context)
        <ue-recursive-stuff :configuration='@json($context)'/>
    @endforeach
@stop

@push('STORE')
    window['{{ modularityConfig('js_namespace') }}'].STORE.medias.crops = {!! json_encode(modularityConfig('settings.crops') ?? []) !!}
    window['{{ modularityConfig('js_namespace') }}'].STORE.medias.selected = {}

    window['{{ modularityConfig('js_namespace') }}'].STORE.browser = {}
    window['{{ modularityConfig('js_namespace') }}'].STORE.browser.selected = {}
@endpush
