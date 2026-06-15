<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?? ('Preview — ' . ($item->title ?? 'Page')) }}</title>
    @if(! empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif
    @if(! empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif
    @if(! empty($robotsMeta))
        <meta name="robots" content="{{ $robotsMeta }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    @php
        $locale = app()->getLocale();
        $item->loadMissing(['files', 'medias', 'fileponds']);

        $translatedMedias = modularousConfig('media_library.translated_form_fields', false);

        $documentFiles = $item->files->filter(function ($file) use ($locale) {
            return $file->pivot->role === 'documents' && $file->pivot->locale === $locale;
        });

        $photoMedias = $item->medias->filter(function ($media) use ($locale, $translatedMedias) {
            if ($media->pivot->role !== 'photos' || $media->pivot->crop !== 'default') {
                return false;
            }
            if ($translatedMedias) {
                return $media->pivot->locale === $locale;
            }

            return true;
        });

        $attachmentFileponds = $item->fileponds->filter(function ($fp) use ($locale) {
            return $fp->role === 'attachments' && $fp->locale === $locale;
        });

        $filepondPreviewRoute = \Illuminate\Support\Facades\Route::has('filepond.preview');

        $sessions = $item->repeaters->where('role', 'sessions')->first()?->content ?? [];
    @endphp
    <div class="container py-4">
        @include('cms::components.responsive-title', ['title' => $item->title ?? null])

        <div class="card border-secondary shadow-sm mb-4">
            <div class="card-header bg-secondary text-white small fw-semibold">
                {{ __('Files') }} <span class="text-white-50">(documents)</span>
            </div>
            <div class="card-body">
                @forelse ($documentFiles as $file)
                    @php $docUrl = $item->file('documents', $locale, $file); @endphp
                    <div class="mb-2">
                        <a href="{{ $docUrl }}" class="link-primary text-break" target="_blank" rel="noopener noreferrer">{{ $file->filename }}</a>
                    </div>
                @empty
                    <p class="text-muted mb-0 small">{{ __('No files for this locale.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="card border-secondary shadow-sm mb-4">
            <div class="card-header bg-secondary text-white small fw-semibold">
                {{ __('Images') }} <span class="text-white-50">(photos)</span>
            </div>
            <div class="card-body">
                @forelse ($photoMedias as $media)
                    @php
                        $src = $item->image('photos', 'default', [], false, false, $media, $locale);
                        $alt = $item->imageAltText('photos', $media);
                    @endphp
                    <figure class="figure mb-3">
                        <img
                            src="{{ $src }}"
                            alt="{{ $alt }}"
                            class="figure-img img-fluid rounded border"
                            style="max-height: 320px; width: auto;"
                            loading="lazy"
                        >
                        @if ($caption = $item->imageCaption('photos', $media))
                            <figcaption class="figure-caption">{{ $caption }}</figcaption>
                        @endif
                    </figure>
                @empty
                    <p class="text-muted mb-0 small">{{ __('No images for this locale.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="card border-secondary shadow-sm mb-4">
            <div class="card-header bg-secondary text-white small fw-semibold">
                {{ __('Attachments') }} <span class="text-white-50">(filepond)</span>
            </div>
            <div class="card-body">
                @forelse ($attachmentFileponds as $fp)
                    <div class="mb-2">
                        @if ($filepondPreviewRoute)
                            <img src="{{ $fp->mediableFormat()['source'] }}" alt="{{ $fp->file_name }}" class="img-fluid rounded border" style="max-height: 100px; width: auto;">
                            {{-- <a href="{{ route('filepond.preview', ['uuid' => $fp->uuid]) }}" class="link-primary text-break" target="_blank" rel="noopener noreferrer">{{ $fp->file_name }}</a> --}}
                        @else
                            <span class="text-muted small">{{ $fp->file_name }} — {{ __('Preview route not registered (filepond.preview).') }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0 small">{{ __('No attachments for this locale.') }}</p>
                @endforelse
            </div>
        </div>

        @forelse ($sessions as $session)
            <div class="card border-secondary shadow-sm mb-4">
                <div class="card-header bg-secondary text-white small fw-semibold">
                    {{ $session['session_title'] }} <span class="text-white-50">(title)</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0 small">{{ $session['session_description'] }}</p>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0 small">{{ __('No sessions for this locale.') }}</p>
        @endforelse
    </div>
</body>
</html>
