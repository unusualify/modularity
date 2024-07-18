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
@endphp

<ue-table v-bind='@json($vBind)' />
{{-- <ue-table-binder component-name="{{$tableComponent}}" :table-attributes='@json($vBind)'/> --}}

