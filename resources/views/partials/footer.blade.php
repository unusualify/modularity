{{-- @include('twill::partials.toaster') --}}
<footer class="footer">
    {{-- <div class="container">
        <span class="footer__copyright"><a href="https://twill.io" target="_blank" class="f--light-hover" tabindex="0">Made with Twill</a></span>
        <span class="footer__version">{{ twillTrans('twill::lang.footer.version') . ' ' . unusualConfig('version', '2.0') }}</span>
    </div> --}}
</footer>

@stack('post_js')

<script>
    window['{{ unusualConfig('js_namespace') }}'] = {};
    window['{{ unusualConfig('js_namespace') }}'].version = '{{ unusualConfig('version') }}';
    window['{{ unusualConfig('js_namespace') }}'].LOCALE = '{{ unusualConfig('locale') }}';
    window['{{ unusualConfig('js_namespace') }}'].unusualLocalization = {!! json_encode($unusualLocalization) !!};

    // STORE MODULES
    window['{{ unusualConfig('js_namespace') }}'].STORE = {};
    window['{{ unusualConfig('js_namespace') }}'].STORE.ambient = {
        isHot: @json(ModularityVite::useHotFile(public_path('modularity.hot'))->isRunningHot()),
        appName: '{{ env('APP_NAME') }}',
        appEmail: '{{ env('APP_EMAIL') }}',
        appEnv: '{{ env('APP_ENV') }}',
        appDebug: '{{ env('APP_DEBUG') }}',
        systemPackageVersions: {!! json_encode($SYSTEM_PACKAGE_VERSIONS) !!},
    };
    window['{{ unusualConfig('js_namespace') }}'].STORE.user = {};
    window['{{ unusualConfig('js_namespace') }}'].STORE.languages = {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!};
    window['{{ unusualConfig('js_namespace') }}'].STORE.config = {}
    window['{{ unusualConfig('js_namespace') }}'].STORE.datatable = {}
    window['{{ unusualConfig('js_namespace') }}'].STORE.form = {}
    window['{{ unusualConfig('js_namespace') }}'].STORE.browser = {
        selected: {}
    }
    window['{{ unusualConfig('js_namespace') }}'].STORE.medias = {};
    window['{{ unusualConfig('js_namespace') }}'].STORE.medias.types = [];
    window['{{ unusualConfig('js_namespace') }}'].STORE.medias.config = {
        useWysiwyg: {{ unusualConfig('media_library.media_caption_use_wysiwyg') ? 'true' : 'false' }},
        wysiwygOptions: {!! json_encode(unusualConfig('media_library.media_caption_wysiwyg_options')) !!}
    };

    @stack('STORE')
</script>
