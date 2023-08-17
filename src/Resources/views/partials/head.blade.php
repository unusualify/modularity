<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="robots" content="noindex,nofollow" />

{{-- <title>{{ config('app.name') }} {{ config('twill.admin_app_title_suffix') }}</title> --}}
<title> {{ $pageTitle ?? 'Module Template' }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Fonts -->

@if(app()->isProduction())
    <link href="{{ unusualMix('Inter-Regular.woff2') }}" rel="preload" as="font" type="font/woff2" crossorigin>
    <link href="{{ unusualMix('Inter-Medium.woff2') }}" rel="preload" as="font" type="font/woff2" crossorigin>
@endif

<!-- CSS -->
@if(app()->isProduction())
    {{-- <link href="{{ unusualMix('chunk-common.css') }}" rel="preload" as="style" crossorigin/> --}}
    <link href="{{ unusualMix('chunk-vendors.css') }}" rel="preload" as="style" crossorigin/>
@endif

@unless(config(unusualBaseKey() . '.is_development', false))
    {{-- <link href="{{ unusualMix('chunk-common.css') }}" rel="stylesheet" crossorigin/> --}}
    <link href="{{ unusualMix('chunk-vendors.css' )}}" rel="stylesheet" crossorigin/>
@endunless


{{-- @yield('pre-scripts') --}}
@stack('head_css')
@stack('head_js')

