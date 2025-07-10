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
            <v-card
                elevation="8"
                class="mx-auto text-center"
                rounded="lg"
            >
                <v-card-text class="pa-8">
                    <v-icon
                        size="120"
                        color="warning"
                        class="mb-6"
                    >
                        mdi-lock-outline
                    </v-icon>

                    <h1 class="text-h1 font-weight-bold text-warning mb-4">
                        403
                    </h1>

                    <h2 class="text-h4 font-weight-medium mb-6">
                        Access Forbidden
                    </h2>

                    <p class="text-h6 text-medium-emphasis mb-4">
                        You don't have permission to access this resource.
                    </p>

                    <v-alert
                        type="warning"
                        variant="tonal"
                        class="mb-6"
                    >
                        This action is restricted for modularity authenticated users.
                    </v-alert>

                    <v-btn
                        color="primary"
                        size="large"
                        variant="elevated"
                        prepend-icon="mdi-arrow-left"
                        @click="window.history.back()"
                        class="me-4"
                    >
                        Go Back
                    </v-btn>

                    <v-btn
                        color="secondary"
                        size="large"
                        variant="outlined"
                        prepend-icon="mdi-home"
                        href="/"
                    >
                        Home
                    </v-btn>
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>
</v-container>
@endsection
