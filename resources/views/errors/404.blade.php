@extends($MODULAROUS_VIEW_NAMESPACE . '::layouts.master')

@push('head_last_js')
    {{
        ModularousVite::useHotFile(public_path('modularous.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush

@section('content')
    <v-container class="fill-height d-flex align-center flex-wrap">
        <v-row class="justify-center align-center fill-height">
            <v-col cols="12" md="8" lg="6">
                <ue-error-card
                    icon="mdi-alert-circle-outline"
                    status-code="404"
                    status-text="Page Not Found"
                    description="Sorry, the page you are looking for could not be found."
                    alert-text="This is a custom 404 page for modularous authenticated users."
                    alert="error"
                >
            </v-col>
        </v-row>
    </v-container>
@stop
