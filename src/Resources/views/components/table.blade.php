@once
    @php
        $default_attributes = [
            'class' => 'h-100',
            'name' => $routeName ?? 'Item',
            'titleKey' => $titleKey ?? 'name',
            'hideDefaultFooter' => false,
            'createOnModal' => true,
            'editOnModal' => true,
            'embeddedForm' => true
        ];
    @endphp
@endonce

@php
    $vBind = array_merge_recursive_preserve($default_attributes, $_attributes ?? []);
@endphp

{{-- <div class="rounded">
</div> --}}
{{-- <v-sheet class="h-screen"> --}}
    <ue-table v-bind='@json($vBind)' />
    {{-- <ue-table-draggable v-bind='@json($vBind)'/> --}}
{{-- </v-sheet> --}}
