@extends("{$BASE_KEY}::layouts.master", [
    'title' => 'Module User'
])

@section('content')
    @dd($sideMenu)
    <main-component :menu-configuration='@json($menuConfiguration)' >
        <div >

            <ue-datatable class="mx-5 my-5"
                :headers='@json($headers)'
                :inputs='@json($inputs)'
                list-end-point='{{ route("api.user.permission.index") }}'
                store-end-point='{{  route("api.user.permission.store" )}}'
                name="Permission"
                :hide-default-header="false"
                :hide-default-footer="false"
                :page="{{request()->has('page') ? request()->query('page') : 1 }}"
                >

            </ue-datatable>

            {{-- <footer-component :show='footerDisplay' :items="footerLinks" class="bottom: 0; position:fixed;">

            </footer-component> --}}
        </div>

    </main-component>
@endsection

@section('initial-scripts')

    <script src="{{ asset('js/admin.js') }}"></script>

@endsection
