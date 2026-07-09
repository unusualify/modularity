@php
    $locale = app()->getLocale();
    $title = $item->translate($locale, false)?->banner_title ?? '';
@endphp
<div data-locale="{{ $locale }}" data-banner-title="{{ $title }}">warmup-translatable-body</div>
