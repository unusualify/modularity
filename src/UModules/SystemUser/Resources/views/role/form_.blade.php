@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@section('appTypeClass', 'body--form')


@php
    $titleFormKey = $titleFormKey ?? 'name';
    $disableContentFieldset = $disableContentFieldset ?? false;
    // $editModalTitle = ($createWithoutModal ?? false) ? twillTrans('twill::lang.modal.create.title') : null;
    // dd(
    //     get_defined_vars()
    // );
    // dd($formAttributes)
    // dd($formAttributes, $formStore, $endpoints );

@endphp


@section('content')
    <v-sheet>
        {{-- <ue-stepper-form></ue-stepper-form> --}}
        <ue-form v-bind='@json($formAttributes)'/>

    </v-sheet>
@stop

@push('head_last_js')
    @if( app()->isProduction() )
        <link href="{{ unusualMix('core-form.js') }}" rel="preload" as="script" crossorigin />
    @else

    @endif
@endpush

@push('post_js')
    <script src="{{ unusualMix('core-form.js') }}"></script>
@endpush

@section('STORE')
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}

@endsection



