{{-- @include('twill::partials.toaster') --}}
<footer class="footer">
    {{-- <div class="container">
        <span class="footer__copyright"><a href="https://twill.io" target="_blank" class="f--light-hover" tabindex="0">Made with Twill</a></span>
        <span class="footer__version">{{ twillTrans('twill::lang.footer.version') . ' ' . modularityConfig('version', '2.0') }}</span>
    </div> --}}
</footer>

@stack('post_js')

<script>
    window['{{ modularityConfig('js_namespace') }}'] = {};
    window['{{ modularityConfig('js_namespace') }}'].version = '{{ modularityConfig('version') }}';
    window['{{ modularityConfig('js_namespace') }}'].LOCALE = '{{ modularityConfig('locale') }}';
    window['{{ modularityConfig('js_namespace') }}'].modularityLocalization = {!! json_encode($modularityLocalization) !!};

    // STORE MODULES
    window['{{ modularityConfig('js_namespace') }}'].STORE = {};
    window['{{ modularityConfig('js_namespace') }}'].STORE.ambient = {
        isHot: @json(ModularityVite::useHotFile(public_path('modularity.hot'))->isRunningHot()),
        appName: '{{ env('APP_NAME') }}',
        appEmail: '{{ env('APP_EMAIL') }}',
        appEnv: '{{ env('APP_ENV') }}',
        appDebug: '{{ env('APP_DEBUG') }}',
        systemPackageVersions: {!! json_encode($SYSTEM_PACKAGE_VERSIONS) !!},
    };
    window['{{ modularityConfig('js_namespace') }}'].STORE.user = {};
    window['{{ modularityConfig('js_namespace') }}'].STORE.languages = {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!};
    window['{{ modularityConfig('js_namespace') }}'].STORE.config = {}
    window['{{ modularityConfig('js_namespace') }}'].STORE.datatable = {}
    window['{{ modularityConfig('js_namespace') }}'].STORE.form = {}
    window['{{ modularityConfig('js_namespace') }}'].STORE.browser = {
        selected: {}
    }
    window['{{ modularityConfig('js_namespace') }}'].STORE.medias = {};
    window['{{ modularityConfig('js_namespace') }}'].STORE.medias.types = [];
    window['{{ modularityConfig('js_namespace') }}'].STORE.medias.config = {
        useWysiwyg: {{ modularityConfig('media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
        wysiwygOptions: {!! json_encode(modularityConfig('media_library.media_caption_wysiwyg_options')) !!}
    };

    @stack('STORE')
</script>
