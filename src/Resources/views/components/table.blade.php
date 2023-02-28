<ue-datatable class="mx-5 my-5" 
    :headers='@json($headers)'
    :inputs='@json($inputs)' 
    {{-- list-end-point='{{ route("api.user.role.index") }}'
    store-end-point='{{  route("api.user.role.store" )}}' --}}
    list-end-point="{{ $listEndPoint }}"
    store-end-point="{{ $storeEndPoint }}"
    update-end-point="{{ $updateEndPoint }}"
    delete-end-point="{{ $deleteEndPoint }}"
    name="{{$name}}"
    :hide-default-header="false"
    :hide-default-footer="false"
    :page="{{request()->has('page') ? request()->query('page') : 1 }}"
    :items-per-page="{{request()->has('itemsPerPage') ? request()->query('itemsPerPage') : ($itemsPerPage ?? 2) }}"
    @if(request()->has('search'))
        :search-text="{{request()->query('search')}}"
    @endif
    >
</ue-datatable>
