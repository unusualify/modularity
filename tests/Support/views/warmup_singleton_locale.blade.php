@php
    $locale = app()->getLocale();
    $title = is_array($item->banner_section_title ?? null)
        ? ($item->banner_section_title[$locale] ?? '')
        : (string) ($item->banner_section_title ?? '');
@endphp
<div data-locale="{{ $locale }}" data-banner-title="{{ $title }}">warmup-singleton-locale-body</div>
