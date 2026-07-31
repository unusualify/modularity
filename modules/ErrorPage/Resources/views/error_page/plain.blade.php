{{-- Minimal document for host/theme body overrides when no LayoutBuilder shell is bound. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="{{ $robotsMeta ?? 'noindex, nofollow' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle ?? config('app.name') }}</title>
    @if (! empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif
</head>
<body style="margin:0;background:#f9fafb;">
{!! $bodyHtml !!}
</body>
</html>
