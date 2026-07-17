@php
    // Cache-busting helper: appends the file's last-modified time so browsers
    // fetch the new icon as soon as the file changes (no hard refresh needed).
    $faviconVersion = fn (string $path) => file_exists(public_path($path))
        ? asset($path) . '?v=' . filemtime(public_path($path))
        : asset($path);

    // Panel/admin always uses SystemSettings (not SiteSettings / CmsSettings).
    $dbFavicon = SystemSettings::value('site.favicon.original');
@endphp

@if (! empty($dbFavicon))
    <link rel="icon" href="{{ $dbFavicon }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $dbFavicon }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $dbFavicon }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $dbFavicon }}">
@else
    <link rel="icon" href="{{ $faviconVersion('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconVersion('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconVersion('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ $faviconVersion('favicon-96x96.png') }}">
    @if (file_exists(public_path('favicon.svg')))
        <link rel="icon" type="image/svg+xml" href="{{ $faviconVersion('favicon.svg') }}">
    @endif
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $faviconVersion('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ $faviconVersion('site.webmanifest') }}">
@endif
