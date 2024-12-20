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
    {{-- @if( app()->isProduction() )
        <link href="{{ unusualMix('core-free.js') }}" rel="preload" as="script" crossorigin />
    @else


    @endif --}}
@endpush
@push('post_js')
    {{-- <script src="{{ unusualMix('core-free.js') }}"></script> --}}
@endpush

{{-- @dd($elements) --}}
@section('content')
    <div class="pa-3">
        @foreach ($elements as $i => $context)
            <ue-recursive-stuff
                :configuration='@json($context)'
                />
        @endforeach
    </div>
    {{-- <v-row>
        @foreach ($forms as $form)
            <v-col
                v-bind='{
                    ...@json($form['col'])
                }'
                v-fit-grid
                >
                <v-sheet>
                    <ue-form
                        @isset($form['formTitle'])
                            :form-title='@json($form["formTitle"])'
                        @endisset
                        @isset($form['buttonText'])
                            :button-text='@json($form["buttonText"])'
                        @endisset
                        @if($form["editable"])
                            :model-value='@json($form["item"])'
                        @endif
                        @isset($form["actionUrl"])
                            :action-url='@json($form["actionUrl"])'
                        @endif
                        :has-submit="true"
                        :sticky-button="false"
                        :schema='@json($form["schema"])'
                        >
                    </ue-form>
                </v-sheet>
            </v-col>
        @endforeach
    </v-row> --}}

    {{-- <ue-stepper-form></ue-stepper-form> --}}
    {{-- <v-sheet>
        <ue-form
            :has-submit="true"
            :sticky-button="false"
            :schema='@json($formSchema)'
            @if($editable)
                :value='@json($item)'
            @endif
            >
        </ue-form>
    </v-sheet> --}}
@stop

@section('STORE')
    {{-- window['{{ unusualConfig('js_namespace') }}'].ENDPOINTS = {} --}}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {
        {{-- inputs: {!! json_encode($formSchema ?? new StdClass()) !!}, --}}
    }
@stop

