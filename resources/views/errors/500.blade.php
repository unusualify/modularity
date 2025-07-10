@extends($MODULARITY_VIEW_NAMESPACE . '::layouts.master')

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush

@section('content')
    <v-container class="fill-height">
        <v-row justify="center" align="center" class="fill-height">
            <v-col cols="12" md="8" lg="6">
                <ue-error-card
                    icon="mdi-server-network-off"
                    status-code="500"
                    status-text="Internal Server Error"
                    description="Something went wrong on our end. We're working to fix it."
                    alert="error"
                    alert-text="The server encountered an unexpected condition that prevented it from fulfilling your request."
                >
                </ue-error-card>
            </v-col>
        </v-row>
    </v-container>
@endsection
