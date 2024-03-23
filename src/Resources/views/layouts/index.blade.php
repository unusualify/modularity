@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

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
    // dd(get_defined_vars());
@endphp

@section('content')
    @include("{$MODULARITY_VIEW_NAMESPACE}::components.datatable", $tableAttributes ?? [])
@stop

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-index.js'])
    }}
@endpush
@push('post_js')
    {{-- <script src="{{ unusualMix('core-index.js') }}"></script> --}}
@endpush

@section('STORE')

    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    {{-- window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {
        index:  '{{ $indexEndpoint }}',
        create: '{{ $createEndpoint ?? $indexEndpoint."/create" }}',
        edit:   '{{ $editEndpoint ?? $indexEndpoint."/:id/edit" }}',
        store:  '{{ $indexEndpoint }}',
        update: '{{ $indexEndpoint . "/:id" }}',
        delete: '{{ $indexEndpoint . "/:id" }}',

        index: @if(isset($indexUrl)) '{{ $indexUrl }}' @else window.location.href.split('?')[0] @endif,
        publish: '{{ $publishUrl }}',
        bulkPublish: '{{ $bulkPublishUrl }}',
        restore: '{{ $restoreUrl }}',
        bulkRestore: '{{ $bulkRestoreUrl }}',
        forceDelete: '{{ $forceDeleteUrl }}',
        bulkForceDelete: '{{ $bulkForceDeleteUrl }}',
        reorder: '{{ $reorderUrl }}',
        create: '{{ $createUrl ?? '' }}',
        feature: '{{ $featureUrl }}',
        bulkFeature: '{{ $bulkFeatureUrl }}',
        bulkDelete: '{{ $bulkDeleteUrl }}'
    } --}}

    {{-- dd($inputs); --}}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {
        {{-- inputs: {!! json_encode($tableInputs) !!}, --}}
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

        {{-- inputs: {!! json_encode($inputs) !!}, --}}
        {{-- initialAsync: '{{ count($tableData['data']) ? true : false }}', --}}
        {{-- name: '{{ $routeName}}', --}}
        {{-- columns: {!! json_encode($tableColumns) !!}, --}}
    }

@endsection



