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

{{-- <div class="rounded">
</div> --}}
{{-- <v-sheet class="h-screen"> --}}

    <ue-new-table v-bind='@json($vBind)' />
    {{-- <ue-table-draggable v-bind='@json($vBind)'/> --}}
{{-- </v-sheet> --}}

