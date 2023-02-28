@extends('base::layouts.master')

@section('appTypeClass', 'body--form')


@php
    // dd( get_defined_vars() );

    // $editor = $editor ?? false;
    // $translate = $translate ?? false;
    // $translateTitle = $translateTitle ?? $translate ?? false;
    $titleFormKey = $titleFormKey ?? 'title';
    // $customForm = $customForm ?? false;
    // $controlLanguagesPublication = $controlLanguagesPublication ?? true;
    $disableContentFieldset = $disableContentFieldset ?? false;
    // $editModalTitle = ($createWithoutModal ?? false) ? twillTrans('twill::lang.modal.create.title') : null;

    $new_form = true;
    $old_form = false;

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

    {{-- @dd( get_defined_vars() ) --}}

    @if($old_form)

        <ue-form
            :async='@json($async)'
            :has-submit="true"
            :sticky-button="true"
            :inputs='@json($inputs)'
            @isset($defaultItem)
                :defaultItem='@json($defaultItem)'
            @endisset
            >
            {{-- <template v-slot:body="{ attrs }">
                <v-row>
                    @foreach ($inputs as $i => $input)
                        <v-col
                            key="{{ $i }}"
                            index='{{ $i }}'
                            cols="{{ $input['cols'] }}"
                            md="{{ $input['md'] }}"
                            sm="{{ $input['sm'] }}"
                        >
                            <component
                                is="{{ "ue-input-" . $input['type']}}"
                                :attributes='@json($input)'
                                />
                        </v-col>
                    @endforeach
                </v-row>
            </template> --}}
        </ue-form>
    @endif

    @if($new_form)
        <ue-form-base
            :has-submit="true"
            :sticky-button="true"
            :schema='@json($formSchema)'
            @if($editable)
                :value='@json($item)'
            @endif
            >

        </ue-form-base>
    @endif
@stop

@push('post_js')
    <script src="{{ unusualMix('runtime.js') }}"></script>
    <script src="{{ unusualMix('vendor.js') }}"></script>
    <script src="{{ unusualMix('core-form.js') }}"></script>
@endpush

@section('STORE')
    window['{{ config('base.js_namespace') }}'].ENDPOINTS = {
        @if($editable)
            update: '{{ $actionUrl }}',
        @else
            store:  '{{ $actionUrl }}',
        @endif

    }
    window['{{ config('base.js_namespace') }}'].STORE.form = {

        inputs: {!! json_encode($formSchema ?? new StdClass()) !!},

        {{-- baseUrl: '{{ $baseUrl ?? '' }}',
        saveUrl: '{{ $saveUrl }}',

        previewUrl: '{{ $previewUrl ?? '' }}',
        restoreUrl: '{{ $restoreUrl ?? '' }}',

        blockPreviewUrl: '{{ $blockPreviewUrl ?? '' }}',
        fields: [],
        editor: {{ $editor ? 'true' : 'false' }},
        isCustom: {{ $customForm ? 'true' : 'false' }},
        reloadOnSuccess: {{ ($reloadOnSuccess ?? false) ? 'true' : 'false' }}, --}}

    }
@endsection



