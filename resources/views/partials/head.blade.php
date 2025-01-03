<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="robots" content="noindex,nofollow" />

    {{-- <title>{{ config('app.name') }} {{ unusualConfig('admin_app_title_suffix') }}</title> --}}
    <title> {{ $pageTitle ?? 'Module Template' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Fonts -->

    @stack('head_css')
    @stack('head_js')

    <script>
        const TRANSLATIONS = @json(get_translations());
        const URLS = @json($urls);
    </script>

    @stack('head_last_js')

</head>


