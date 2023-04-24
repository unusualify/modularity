<div class="mx-5 my-5">
    <ue-datatable
        title-key="{{ $titleKey }}"
        {{-- name="{{$name}}" --}}
        name="{{ $routeName }}"
        :hide-default-header="false"
        :hide-default-footer="false"
        :is-row-editing="@json($isRowEditing ?? false)"
        :create-on-modal="@json($createOnModal ?? true)"
        :edit-on-modal="@json($editOnModal ?? true)"
    >

        {{-- <ue-modal-create
            slot="FormDialog"
            route-name="{{ $routeName }}"
            inputs='@json($tableInputs)'
            >
        </ue-modal-create> --}}

    </ue-datatable>
</div>
