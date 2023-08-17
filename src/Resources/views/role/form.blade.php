@extends("{$BASE_KEY}::layouts.master")

@section('appTypeClass', 'body--form')


@php
    $titleFormKey = $titleFormKey ?? 'title';
    $disableContentFieldset = $disableContentFieldset ?? false;
    // $editModalTitle = ($createWithoutModal ?? false) ? twillTrans('twill::lang.modal.create.title') : null;
    // dd(
    //     get_defined_vars()
    // );
    // dd($formAttributes)
@endphp


@section('content')
    <v-sheet>
        {{-- <ue-stepper-form></ue-stepper-form> --}}
        <ue-form v-bind='@json($formAttributes)'>
        </ue-form>
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
    window['{{ config(unusualBaseKey() . '.js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ config(unusualBaseKey() . '.js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}

@endsection



