@extends('error_page::error_page.standalone')

@section('content')
    <v-container class="fill-height d-flex align-center flex-wrap py-12" style="min-height:100vh;">
        <v-row class="justify-center align-center fill-height">
            <v-col cols="12" sm="10" md="7" lg="5">
                <v-card class="mx-auto text-center" elevation="2" rounded="lg">
                    <v-card-text class="pa-8 pa-md-12">
                        <v-icon color="error" size="96" class="mb-6">mdi-alert-circle-outline</v-icon>
                        <div class="text-display-medium font-weight-bold text-error mb-2">404</div>
                        <div class="text-headline-medium font-weight-medium mb-4">{{ __('error_page::messages.404.title') }}</div>
                        <p class="text-body-large text-medium-emphasis mb-8">
                            {{ __('error_page::messages.404.description') }}
                        </p>
                        <v-btn
                            color="primary"
                            size="large"
                            variant="flat"
                            prepend-icon="mdi-home"
                            href="{{ $homeUrl ?? url('/') }}"
                        >
                            {{ __('error_page::messages.home') }}
                        </v-btn>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
@endsection
