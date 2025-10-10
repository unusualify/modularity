window['{{ modularityConfig('js_namespace') }}'] = {
    version: '{{ modularityConfig('version') }}',
    LOCALE: '{{ modularityConfig('locale') }}',
    modularityLocalization: {!! json_encode($modularityLocalization) !!},

    STORE: {
        ambient: {
            isHot: @json(ModularityVite::useHotFile(public_path('modularity.hot'))->isRunningHot()),
            appName: '{{ env('APP_NAME') }}',
            appEmail: '{{ env('APP_EMAIL') }}',
            appEnv: '{{ env('APP_ENV') }}',
            appDebug: '{{ env('APP_DEBUG') }}',
            systemPackageVersions: {!! json_encode($SYSTEM_PACKAGE_VERSIONS) !!},
        },
        user: {},
        languages: {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!},
        config: {},
        datatable: {},
        form: {},
        browser: {
            selected: {}
        },
        medias: {
            types: [],
            config: {
                useWysiwyg: {{ modularityConfig('media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
                wysiwygOptions: {!! json_encode(modularityConfig('media_library.media_caption_wysiwyg_options')) !!}
            }
        },
    }
};
