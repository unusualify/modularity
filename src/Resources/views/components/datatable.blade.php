@once
    @php
        $defaultTableAttributes = [
            'hideDefaultFooter' => false,
            'createOnModal' => true,
            'editOnModal' => true,
        ];
    @endphp
@endonce

@php
    $vBind = array_merge_recursive_preserve($defaultTableAttributes, $tableAttributes ?? []);
@endphp

<ue-datatable v-bind='@json($vBind)' />
{{-- <div class="mx-5 my-5">
    <ue-datatable
        title-key="{{ $titleKey }}"
        name="{{ $routeName }}"
        :hide-default-header="false"
        :hide-default-footer="false"
        :is-row-editing="@json($isRowEditing ?? false)"
        :create-on-modal="@json($createOnModal ?? true)"
        :edit-on-modal="@json($editOnModal ?? true)"
    >
    </ue-datatable>
</div> --}}
