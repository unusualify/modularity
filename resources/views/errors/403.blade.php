@extends($MODULARITY_VIEW_NAMESPACE . '::layouts.master')

@section('appTypeClass', 'body--listing')


@section('content')
    <v-container class="fill-height">
        <v-row justify="center" align="center" class="fill-height">
            <v-col cols="12" md="8" lg="6">
                <ue-error-card
                    icon="mdi-lock-outline"
                    status-code="403"
                    status-text="Access Forbidden"
                    description="You don't have permission to access this resource."
                    alert-text="This action is restricted for modularity authenticated users."
                    alert="warning"
                >
                </ue-error-card>
            </v-col>
        </v-row>
    </v-container>
@stop

@push('head_last_js')
    {{
        ModularityVite::useHotFile(public_path('modularity.hot'))->withEntryPoints(['src/js/core-free.js'])
    }}
@endpush


@push('STORE')

@endpush