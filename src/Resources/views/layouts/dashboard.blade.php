@extends("{$BASE_KEY}::layouts.master")

@php
    // $emptyMessage = $emptyMessage ?? twillTrans('twill::lang.dashboard.empty-message');
    // $isDashboard = true;
    // $translate = true;
    // dd($blocks);
@endphp

@push('head_last_js')
    @if( app()->isProduction() )
        <link href="{{ unusualMix('core-free.js') }}" rel="preload" as="script" crossorigin />
    @else


    @endif
@endpush
@push('post_js')
    <script src="{{ unusualMix('core-free.js') }}"></script>
@endpush

@section('content')
    <div class="dashboard">
        <ue-dashboard :blocks='@json($blocks ?? [])'>
    </div>
@stop

@section('STORE')

@stop

