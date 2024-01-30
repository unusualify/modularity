@extends("$BASE_KEY::layouts.master")

@section('appTypeClass', 'body--listing')

@php
    $translate = $translate ?? false;
    $translateTitle = $translateTitle ?? $translate ?? false;
    $reorder = $reorder ?? false;
    $nested = $nested ?? false;
    $bulkEdit = $bulkEdit ?? true;
    $create = $create ?? false;
    $skipCreateModal = $skipCreateModal ?? false;
    $controlLanguagesPublication = $controlLanguagesPublication ?? true;
@endphp


@section('content')
    @include("$BASE_KEY::components.table", $tableAttributes ?? [])
@stop

@push('head_last_js')
    @if( app()->isProduction() )
        <link href="{{ unusualMix('core-index.js') }}" rel="preload" as="script" crossorigin />
    @else


    @endif
@endpush
@push('post_js')
    <script src="{{ unusualMix('core-index.js') }}"></script>
@endpush

@section('STORE')
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {
        inputs: {!! json_encode($formSchema) !!},
        fields: []
    }

    window['{{ unusualConfig('js_namespace') }}'].STORE.datatable = {
        baseUrl: '{{ rtrim(config('app.url'), '/') . '/' }}',
        headers: {!! json_encode($headers) !!},
        searchText: '{{ $searchText ?? '' }}',
        options: {!! json_encode($listOptions) !!},
        data: {!! json_encode($initialResource['data']) !!},
        total: '{{ $initialResource['total'] ?? 0 }}',
        mainFilters: {!! json_encode($tableMainFilters) !!},
        filter: { status: '{{ $filters['status'] ?? $defaultFilterSlug ?? 'all' }}' },
    }

@endsection



