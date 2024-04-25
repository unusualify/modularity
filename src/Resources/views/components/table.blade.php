@once
    @php
        $defaultTableAttributes = [
            // 'class' => 'ue-table',
            'name' => $routeName ?? 'Item',
            'titleKey' => $titleKey ?? 'name',
            'hideDefaultFooter' => false,
            'createOnModal' => true,
            'editOnModal' => true,
            'embeddedForm' => true,
            'formWidth' => '60%',
            // 'showSelect' => true,
        ];
    @endphp
@endonce

@php
    $vBind = array_merge_recursive_preserve($defaultTableAttributes, $tableAttributes ?? []);
    // dd($vBind)
@endphp

{{-- <div class="rounded">
</div> --}}
{{-- <v-sheet class="h-screen"> --}}
    <ue-new-table v-bind='@json($vBind)' />
    {{-- <ue-table-draggable v-bind='@json($vBind)'/> --}}
{{-- </v-sheet> --}}

@section('STORE')
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {
        inputs: {!! json_encode($formSchema ?? []) !!},
        fields: []
    }


    window['{{ unusualConfig('js_namespace') }}'].STORE.datatable = {
        baseUrl: '{{ rtrim(config('app.url'), '/') . '/' }}',
        headers: {!! json_encode($headers ?? '') !!},
        searchText: '{{ $searchText ?? '' }}',
        options: {!! json_encode($listOptions ?? ['itemsPerPage' => 10,])  !!},
        data: {!! json_encode($initialResource['data'] ?? []) !!},
        total: '{{ $initialResource['total'] ?? 0 }}',
        mainFilters: {!! json_encode($tableMainFilters ?? []) !!},
        filter: { status: '{{ $filters['status'] ?? $defaultFilterSlug ?? 'all' }}' },
    }
@endsection
