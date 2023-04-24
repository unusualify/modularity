@extends('base::layouts.master', [
    'title' => 'Role Management'
])

@section('content')
    {{-- @dd($menuConfiguration) --}}
    {{-- <main-component :menu-configuration='@json($menuConfiguration)' >
        <div > --}}
            @if(false)

            @endif
            {{-- {!! $repository->renderDataTable() !!} --}}
            {{-- <x-table    
                :headers="$headers"
                :inputs="$inputs">

            </x-table> --}}
            {{-- @include('base::components.table',[
                'headers' => $headers,
                'inputs' => $inputs
            ]) --}}

            {{-- <footer-component :show='footerDisplay' :items="footerLinks" class="bottom: 0; position:fixed;">
    
            </footer-component> --}}
        {{-- </div>
    </main-component> --}}
@endsection

@push('post_js')
    {{-- <script src="{{ unusualMix('vendor~utils-1.js') }}"></script> --}}
    <script src="{{ unusualMix('manifest.js') }}"></script>
    <script src="{{ unusualMix('vendor.js') }}"></script>
    <script src="{{ unusualMix('core-index.js') }}"></script>
    {{-- <script src="{{ asset('js/admin.js') }}"></script> --}}
@endpush