@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.master")

@php
    // $emptyMessage = $emptyMessage ?? twillTrans('twill::lang.dashboard.empty-message');
    // $isDashboard = true;
    // $translate = true;
    // dd($blocks);
@endphp

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush
@push('post_js')

@endpush

@section('content')
    <div class="dashboard">
        <ue-dashboard :blocks='@json($blocks ?? [])'>
    </div>
@stop

@section('STORE')

@stop

