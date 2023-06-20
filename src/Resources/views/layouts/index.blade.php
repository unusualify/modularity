@extends("{$BASE_KEY}::layouts.master")

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

    // dd($tableMainFilters);
    // dd($formSchema, $headers);
    // dd(get_defined_vars());
@endphp


@section('content')
    @include("{$BASE_KEY}::components.datatable")
@stop

@push('head_last_js')
    {{-- <script src="{{ unusualMix('runtime.js') }}"></script>
    <script src="{{ unusualMix('vendor.js') }}"></script>
    <script src="{{ unusualMix('core-index.js') }}"></script> --}}
    @if( app()->isProduction() )
        <link href="{{ unusualMix('core-index.js') }}" rel="preload" as="script" crossorigin />
    @else


    @endif
@endpush
@push('post_js')
    <script src="{{ unusualMix('core-index.js') }}"></script>
@endpush

@section('STORE')
    {{-- @dd(
        get_defined_vars(),
        $tableData,
        $tableInputs
    ) --}}
    window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].ENDPOINTS = {
        index:  '{{ $indexEndpoint }}',
        create: '{{ $createEndpoint ?? $indexEndpoint."/create" }}',
        edit:   '{{ $editEndpoint ?? $indexEndpoint."/:id/edit" }}',
        store:  '{{ $indexEndpoint }}',
        update: '{{ $indexEndpoint . "/:id" }}',
        delete: '{{ $indexEndpoint . "/:id" }}',

        {{-- index: @if(isset($indexUrl)) '{{ $indexUrl }}' @else window.location.href.split('?')[0] @endif, --}}
        {{-- publish: '{{ $publishUrl }}',
        bulkPublish: '{{ $bulkPublishUrl }}',
        restore: '{{ $restoreUrl }}',
        bulkRestore: '{{ $bulkRestoreUrl }}',
        forceDelete: '{{ $forceDeleteUrl }}',
        bulkForceDelete: '{{ $bulkForceDeleteUrl }}',
        reorder: '{{ $reorderUrl }}',
        create: '{{ $createUrl ?? '' }}',
        feature: '{{ $featureUrl }}',
        bulkFeature: '{{ $bulkFeatureUrl }}',
        bulkDelete: '{{ $bulkDeleteUrl }}' --}}
    }

    {{-- dd($inputs); --}}
    window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.form = {
        {{-- inputs: {!! json_encode($tableInputs) !!}, --}}
        inputs: {!! json_encode($formSchema) !!},
        fields: []
    }

    window['{{ config(getUnusualBaseKey() . '.js_namespace') }}'].STORE.datatable = {
        baseUrl: '{{ rtrim(config('app.url'), '/') . '/' }}',

        name: '{{ $routeName}}',
        headers: {!! json_encode($headers) !!},
        {{-- inputs: {!! json_encode($inputs) !!}, --}}
        searchText: '{{ $searchText ?? '' }}',
        options: {!! json_encode($listOptions) !!},
        actions: {!! json_encode($actions) !!},
        actionsType: '{{ $actionsType ?? 'inline' }}',
        {{-- initialAsync: '{{ count($tableData['data']) ? true : false }}', --}}
        data: {!! json_encode($initialResource['data']) !!},
        total: '{{ $initialResource['total'] ?? 0 }}',

        mainFilters: {!! json_encode($tableMainFilters) !!},
        filter: { status: '{{ $filters['status'] ?? $defaultFilterSlug ?? 'all' }}' },

        {{-- columns: {!! json_encode($tableColumns) !!}, --}}
    }

@endsection



