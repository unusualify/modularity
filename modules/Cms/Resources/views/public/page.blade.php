<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?? (optional($translation)->seo_title ?? optional($translation)->title ?? 'Page') }}</title>
    @if(! empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @elseif(!empty(optional($translation)->seo_description))
        <meta name="description" content="{{ $translation->seo_description }}">
    @endif
    @if(! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    @if(! empty($robotsMeta))
        <meta name="robots" content="{{ $robotsMeta }}">
    @endif
</head>
<body>
    <main>
        <h1>{{ optional($translation)->title ?? '' }}</h1>
        @if(!empty(optional($translation)->excerpt))
            <p class="excerpt">{{ $translation->excerpt }}</p>
        @endif
        <div class="content">
            {!! optional($translation)->content ?? '' !!}
        </div>
    </main>
</body>
</html>
