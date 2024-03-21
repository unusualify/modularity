@extends("{$BASE_KEY}::layouts.master")

@section('appTypeClass', 'body--form')


@php
    // dd( get_defined_vars() );

    // $editor = $editor ?? false;
    // $translate = $translate ?? false;
    // $translateTitle = $translateTitle ?? $translate ?? false;
    $titleFormKey = $titleFormKey ?? 'name';
    // $customForm = $customForm ?? false;
    // $controlLanguagesPublication = $controlLanguagesPublication ?? true;
    $disableContentFieldset = $disableContentFieldset ?? false;
    // $editModalTitle = ($createWithoutModal ?? false) ? twillTrans('twill::lang.modal.create.title') : null;
    // dd($formSchema);
    // $formSchema = $formSchema + [
    //     "treeview" => [
    //         "type" => "treeview",
    //         "col" => 6,
    //         "open" => [],
    //         "model" => [],
    //         "activatable"=> true,
    //         "selectable"=> true,
    //         "multipleActive"=> true,
    //         "slot" => [
    //             "prepend",
    //             "label"
    //         ]
    //     ]
    // ];
@endphp


@section('content')
    <v-sheet>
        {{-- <ue-stepper-form></ue-stepper-form> --}}
        <ue-form
            v-bind='@json($formAttributes)'
            >
        </ue-form>
    </v-sheet>
@stop

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-index.js'])
    }}
    {{-- @if( app()->isProduction() )
        <link href="{{ unusualMix('core-form.js') }}" rel="preload" as="script" crossorigin />
    @else

    @endif --}}
@endpush

@push('post_js')
    {{-- <script src="{{ unusualMix('core-form.js') }}"></script> --}}
@endpush

@section('STORE')
    {{-- window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = @json($endpoints) --}}
    window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {!! json_encode($formStore ?? new StdClass()) !!}
    {{-- window['{{ unusualConfig('js_namespace') }}'].STORE.form = { --}}
        {{-- inputs: {!! json_encode($formSchema ?? new StdClass()) !!}, --}}

        {{-- baseUrl: '{{ $baseUrl ?? '' }}',
        saveUrl: '{{ $saveUrl }}',

        previewUrl: '{{ $previewUrl ?? '' }}',
        restoreUrl: '{{ $restoreUrl ?? '' }}',

        blockPreviewUrl: '{{ $blockPreviewUrl ?? '' }}',
        fields: [],
        editor: {{ $editor ? 'true' : 'false' }},
        isCustom: {{ $customForm ? 'true' : 'false' }},
        reloadOnSuccess: {{ ($reloadOnSuccess ?? false) ? 'true' : 'false' }}, --}}
    {{-- } --}}
@endsection



