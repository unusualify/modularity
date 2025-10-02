{{-- @include('twill::partials.toaster') --}}
<footer class="footer">
    {{-- <div class="container">
        <span class="footer__copyright"><a href="https://twill.io" target="_blank" class="f--light-hover" tabindex="0">Made with Twill</a></span>
        <span class="footer__version">{{ twillTrans('twill::lang.footer.version') . ' ' . modularityConfig('version', '2.0') }}</span>
    </div> --}}
</footer>

@stack('post_js')

<script>
    @include("{$MODULARITY_VIEW_NAMESPACE}::partials.default-store")

    @stack('STORE')
</script>
