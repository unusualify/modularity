@extends("{$MODULARITY_VIEW_NAMESPACE}::layouts.base")

@section('body')
    @php
        $navigation ??= [];
        $impersonation ??= [];
        $authorization ??= [];

        $_mainConfiguration = [
            'navigation' => $navigation,
            'impersonation' => $impersonation,
            'authorization' => $authorization
        ];
    @endphp
    <div id="admin">
        <ue-main
            ref='main'
            v-bind='@json($_mainConfiguration)'

            >
            @if(auth()->check() && auth()->user()->invalidCompany)
                <template v-slot:main-top>
                    <v-alert
                        density="compact"
                        type="warning"
                        text="{{ ___('messages.invalid-company') }}"
                    ></v-alert>
                </template>
            @endif
            <div id="ue-main-body" class="ue--main-container pa-3 h-100">

                @yield('content')

                <div id="ue-bottom-content">
                    @if (modularityConfig('enabled.media-library') || modularityConfig('enabled.file-library'))
                        {{-- <ue-media
                            ref="mediaLibra"
                            :authorized="{{ json_encode(auth('twill_users')->user()->can('upload')) }}"
                            :extra-metadatas="{{ json_encode(array_values(modularityConfig('media_library.extra_metadatas_fields', []))) }}"
                            :translatable-metadatas="{{ json_encode(array_values(modularityConfig('media_library.translatable_metadatas_fields', []))) }}"
                        ></ue-media> --}}

                    @endif

                    {{-- <ue-alert ref='alert'></ue-alert> --}}
                    {{-- <ue-modal-test></ue-modal-test> --}}
                </div>
            </div>
        </ue-main>
    </div>

@endsection

@push('STORE')
    window['{{ modularityConfig('js_namespace') }}'].TIMEZONE = '{{ modularityConfig('timezone') }}';
    window['{{ modularityConfig('js_namespace') }}'].AUTHORIZATION = @json($authorization);

    window['{{ modularityConfig('js_namespace') }}'].ENDPOINTS = {!! json_encode($endpoints ?? new StdClass()) !!}
    window['{{ modularityConfig('js_namespace') }}'].STORE.config = {
        test: false,
        profileMenu: {!! json_encode($navigation['profileMenu']) !!},
        sidebarOptions: {!! json_encode(modularityConfig('ui_settings.sidebar')) !!},
        secondarySidebarOptions : {!! json_encode(modularityConfig('ui_settings.secondarySidebar')) !!},
    },
    window['{{ modularityConfig('js_namespace') }}'].STORE.user = {
        profile: {!! json_encode($currentUser) !!},
        profileRoute: '{{ route(Route::hasAdmin('profile.update')) }}',
        profileShortcutModel: {!! json_encode($profileShortcutModel) !!},
        profileShortcutSchema: {!! json_encode($profileShortcutSchema) !!}
    },

    @if (modularityConfig('enabled.media-library'))
        window['{{ modularityConfig('js_namespace') }}'].STORE.medias.types.push({
            value: 'image',
            text: '{{ modularityTrans("media-library.images") }}',
            total: {{ \Unusualify\Modularity\Entities\Media::query()->authorized()->count() }},
            endpoint: '{{ route(Route::hasAdmin('media-library.media.index')) }}',
            tagsEndpoint: '{{ route(Route::hasAdmin('media-library.media.tags')) }}',
            uploaderConfig: {!! json_encode($mediasUploaderConfig) !!}
        });
        window['{{ modularityConfig('js_namespace') }}'].STORE.medias.showFileName = !!'{{ modularityConfig('media_library.show_file_name') }}';
    @endif

    @if (modularityConfig('enabled.file-library'))
        window['{{ modularityConfig('js_namespace') }}'].STORE.medias.types.push({
            value: 'file',
            text: '{{ modularityTrans("media-library.files") }}',
            total: {{ \Unusualify\Modularity\Entities\File::query()->authorized()->count() }},
            endpoint: '{{ route(Route::hasAdmin('file-library.file.index')) }}',
            tagsEndpoint: '{{ route(Route::hasAdmin('file-library.file.tags')) }}',
            uploaderConfig: {!! json_encode($filesUploaderConfig) !!}
        });
    @endif
@endpush
