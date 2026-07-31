<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $resolvedSiteName = SiteSettings::get('site.name', config('app.name'));
        $resolvedTitle = filled($title ?? null) ? $title : null;
        $resolvedMetaTitle = filled($seoTitle ?? null) ? $seoTitle : (filled($resolvedTitle) ? $resolvedTitle : SiteSettings::get('seo.default_meta_title', $resolvedSiteName));
        $resolvedTitle = filled($resolvedTitle) ? $resolvedTitle : $resolvedMetaTitle;
        $resolvedMetaDescription = filled($seoDescription ?? null) ? $seoDescription : SiteSettings::get('seo.default_meta_description', '');
        $resolvedFavicon = SiteSettings::value('site.favicon.original') ?: asset('favicon.ico');
    @endphp

    <title>{{ $resolvedTitle }}</title>

    <link rel="icon" href="{{ $resolvedFavicon }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ $resolvedFavicon }}" type="image/x-icon">
    <link rel="icon" href="{{ $resolvedFavicon }}" type="image/png" sizes="16x16">
    <link rel="icon" href="{{ $resolvedFavicon }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ $resolvedFavicon }}" sizes="180x180">

    <meta name="title" content="{{ $resolvedMetaTitle }}">
    <meta name="description" content="{{ $resolvedMetaDescription }}">
    <meta name="robots" content="{{ $robotsMeta ?? \Modules\Cms\Support\CmsPublicSeo::defaultRobotsMeta() }}">

    {{-- Optional CMP / consent (e.g. CookieFirst) must precede GTM --}}
    {!! $consentHeadHtml ?? '' !!}
    {!! app(\Modules\SystemSetting\Support\AnalyticsScripts::class)->headHtml() !!}

    <base href="{{ rtrim($siteAddress ?? url('/'), '/') }}/">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">

    @foreach ($hreflangAlternates ?? [] as $alternate)
        @if (! empty($alternate['hreflang']) && ! empty($alternate['href']))
            <link rel="alternate" hreflang="{{ $alternate['hreflang'] }}" href="{{ $alternate['href'] }}">
        @endif
    @endforeach

    @foreach ($stylesheetHrefs ?? [] as $href)
        <link rel="stylesheet" href="{{ $href }}">
    @endforeach

    {!! $headHtml ?? '' !!}
    @if (! empty($cmsLayoutUseInjectionMarkers))
        {!! $cmsLayoutMarkerHeadAppend ?? '' !!}
    @endif
</head>
<body>
    {!! app(\Modules\SystemSetting\Support\AnalyticsScripts::class)->bodyOpenHtml() !!}
    {!! $bodyHtml ?? '' !!}
    @if (! empty($cmsLayoutUseInjectionMarkers))
        {!! $cmsLayoutMarkerBeforeFooter ?? '' !!}
    @endif
    {!! $footerHtml ?? '' !!}
    @foreach ($stylesheetScriptSrcs ?? [] as $src)
        <script src="{{ $src }}"></script>
    @endforeach
</body>
</html>
