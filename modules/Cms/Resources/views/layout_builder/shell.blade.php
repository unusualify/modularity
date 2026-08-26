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
        $resolvedFavicon = SiteSettings::value('site.favicon.frontend') ?: asset('favicon.ico');
        $settingsOgImage = SiteSettings::value('seo.og_image.frontend');
        $pageOrSettingsOg = filled($ogImage ?? null) ? $ogImage : ($settingsOgImage ?: null);
        $resolvedOgImage = filled($pageOrSettingsOg) ? $pageOrSettingsOg : ($defaultOgImage ?? null);
        $resolvedTwitterImage = filled($twitterImage ?? null)
            ? $twitterImage
            : (filled($pageOrSettingsOg) ? $pageOrSettingsOg : ($defaultTwitterImage ?? $resolvedOgImage));
        $resolvedCanonicalUrl = $canonicalUrl ?? url()->current();
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

    <meta property="og:title" content="{{ $resolvedMetaTitle }}">
    <meta property="og:url" content="{{ $resolvedCanonicalUrl }}">
    <meta property="og:site_name" content="{{ $resolvedSiteName }}">
    <meta property="og:type" content="website">
    <meta property="og:description" content="{{ $resolvedMetaDescription }}">
    @if (filled($resolvedOgImage))
        <meta property="og:image" content="{{ $resolvedOgImage }}">
    @endif

    <meta name="twitter:card" content="{{ filled($resolvedTwitterImage) ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $resolvedMetaTitle }}">
    <meta name="twitter:description" content="{{ $resolvedMetaDescription }}">
    @if (filled($resolvedTwitterImage))
        <meta name="twitter:image" content="{{ $resolvedTwitterImage }}">
    @endif

    {{-- Optional CMP / consent (e.g. CookieFirst) must precede GTM --}}
    {!! $consentHeadHtml ?? '' !!}
    {!! app(\Modules\SystemSetting\Support\AnalyticsScripts::class)->headHtml() !!}
    {{-- Admin scripts.head: after CMP/GTM, before base/CSS/layout head (legacy metadata slot) --}}
    {!! app(\Modules\Cms\Support\CustomScripts::class)->headHtml() !!}

    <base href="{{ rtrim($siteAddress ?? url('/'), '/') }}/">
    <link rel="canonical" href="{{ $resolvedCanonicalUrl }}">

    <!-- Shell hreflang alternates -->
    @foreach ($hreflangAlternates ?? [] as $alternate)
        @if (! empty($alternate['hreflang']) && ! empty($alternate['href']))
            <link rel="alternate" hreflang="{{ $alternate['hreflang'] }}" href="{{ $alternate['href'] }}">
        @endif
    @endforeach

    <!-- Shell stylesheets -->
    @foreach ($stylesheetHrefs ?? [] as $href)
        <link rel="stylesheet" href="{{ $href }}">
    @endforeach

    <!-- Shell append head HTML -->
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
    {{-- Admin scripts.body: after layout footer (e.g. Statcounter), before host afterCustomBodyHtml (e.g. Zoho zcga) --}}
    {!! app(\Modules\Cms\Support\CustomScripts::class)->bodyHtml() !!}
    {!! $afterCustomBodyHtml ?? '' !!}
    @foreach ($stylesheetScriptSrcs ?? [] as $src)
        <script src="{{ $src }}"></script>
    @endforeach
</body>
</html>
