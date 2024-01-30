@extends("{$BASE_KEY}::layouts.master")

@section('appTypeClass', 'body--form')

@php
    $titleFormKey = $titleFormKey ?? 'name';
    $disableContentFieldset = $disableContentFieldset ?? false;
@endphp

@section('content')
    <ue-form v-bind='@json($formAttributes)'/>

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



