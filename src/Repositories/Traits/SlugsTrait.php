<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Unusualify\Modularous\Entities\Model;

trait SlugsTrait
{
    /**
     * When true, {@see RevisionsTrait::bypassAfterSaves} may set `passAfterSaveSlugsTrait` during pending-only
     * revision saves so {@see afterSaveSlugsTrait} is skipped.
     */
    protected bool $pendingBypassRevisionSlugsTrait = true;

    /**
     * Skip {@see HasSlug}'s automatic {@see setSlugs()} on {@see Model::saved} when the client posts explicit
     * per-locale slug payloads (see `$fields['slugs']`) or when {@see prepareFieldsBeforeSaveSlugsTrait} merged
     * slug input from nested request shapes. Otherwise {@see setSlugs()} may still run from {@see $slugAttributes}.
     */
    public function beforeSaveSlugsTrait(Model $object, array $fields): void
    {
        if (! property_exists($this->model, 'slugAttributes')) {
            return;
        }

        $object->modularousSkipAutomaticSlugSync = $this->shouldSkipAutomaticSlugSyncOnSave($object, $fields);
    }

    /**
     * @param array<string, mixed> $fields Raw request fields (before {@see prepareFieldsBeforeSave} transforms).
     */
    protected function shouldSkipAutomaticSlugSyncOnSave(Model $object, array $fields): bool
    {
        if ($this->requestArrayContainsEditorSlugPayload($fields['slugs'] ?? null)) {
            return true;
        }

        if ($this->requestArrayContainsEditorSlugPayload($fields['translations']['slugs'] ?? null)) {
            return true;
        }

        foreach ($object->getSlugAttributes() as $attr) {
            if (array_key_exists($attr, $fields)) {
                return false;
            }

            if (isset($fields['translations']) && is_array($fields['translations']) && array_key_exists($attr, $fields['translations'])) {
                return false;
            }
        }

        return true;
    }

    protected function requestArrayContainsEditorSlugPayload(mixed $slugByLocale): bool
    {
        if (! is_array($slugByLocale)) {
            return false;
        }

        foreach (getLocales() as $locale) {
            if ($this->slugLocalePayloadIsEditorProvided($slugByLocale[$locale] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Canonical slug payloads for {@see afterSaveSlugsTrait} live under `$fields['slugs'][locale]`.
     * Never copy {@see HasSlug::$slugAttributes} (derivation sources) into `$fields['slugs']`; those inform
     * {@see HasSlug::setSlugs()} on save when no explicit slug input is posted.
     * Only merges alternate request shapes into `$fields['slugs']` (translations layout / per-locale bucket).
     *
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function prepareFieldsBeforeSaveSlugsTrait($object, $fields)
    {
        if (! property_exists($this->model, 'slugAttributes')) {
            return $fields;
        }

        if ($object->getSlugAttributes() === []) {
            return $fields;
        }

        $fields['slugs'] = isset($fields['slugs']) && is_array($fields['slugs']) ? $fields['slugs'] : [];

        foreach (getLocales() as $locale) {
            if ($this->slugInputPayloadIsPresent($fields['slugs'][$locale] ?? null)) {
                continue;
            }

            $fromTranslationsNested = $fields['translations']['slugs'][$locale] ?? null;
            $fromLocaleBucket = isset($fields[$locale]) && is_array($fields[$locale])
                ? ($fields[$locale]['slugs'] ?? null)
                : null;

            foreach ([$fromTranslationsNested, $fromLocaleBucket] as $candidate) {
                if ($this->slugInputPayloadIsPresent($candidate)) {
                    $fields['slugs'][$locale] = $candidate;

                    break;
                }
            }
        }

        if ($this->mergedSlugPayloadRequestsExplicitManagement($fields)) {
            $object->modularousSkipAutomaticSlugSync = true;
        }

        return $fields;
    }

    /**
     * After merge, at least one locale carries slug input the editor controls (non-empty slug and/or explicit `active`).
     */
    protected function mergedSlugPayloadRequestsExplicitManagement(array $fields): bool
    {
        if (! isset($fields['slugs']) || ! is_array($fields['slugs'])) {
            return false;
        }

        foreach (getLocales() as $locale) {
            if ($this->slugLocalePayloadIsEditorProvided($fields['slugs'][$locale] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when this locale's slug bucket should run {@see afterSaveSlugsTrait} and should not be treated as empty.
     */
    protected function slugLocalePayloadIsEditorProvided(mixed $payload): bool
    {
        if ($payload === null || $payload === '') {
            return false;
        }

        if (is_array($payload)) {
            $hasSlug = array_key_exists('slug', $payload)
                && $payload['slug'] !== null
                && $payload['slug'] !== '';

            return $hasSlug || array_key_exists('active', $payload);
        }

        return true;
    }

    /**
     * Used when merging alternate request shapes into `$fields['slugs']` (slug text must be non-empty).
     */
    protected function slugInputPayloadIsPresent(mixed $payload): bool
    {
        if ($payload === null || $payload === '') {
            return false;
        }

        if (is_array($payload)) {
            return array_key_exists('slug', $payload) && $payload['slug'] !== null && $payload['slug'] !== '';
        }

        return true;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return void
     */
    public function afterSaveSlugsTrait($object, $fields)
    {
        if (property_exists($this->model, 'slugAttributes')) {
            foreach (getLocales() as $locale) {
                if (
                    isset($fields['slugs'][$locale])
                    && $this->slugLocalePayloadIsEditorProvided($fields['slugs'][$locale])
                ) {
                    $slugValue = $fields['slugs'][$locale];
                    $isArray = is_array($slugValue);
                    $object->disableLocaleSlugs($locale);
                    $currentSlug = [];
                    $currentSlug['slug'] = $isArray ? ($slugValue['slug'] ?? '') : $slugValue;
                    $currentSlug['locale'] = $locale;
                    $slugPayloadCanForceActive =
                        $this->model->isTranslatable()
                        && method_exists($object, 'slugPrimaryAttributeIsTranslated')
                        && $object->slugPrimaryAttributeIsTranslated()
                        && isset($object->translations)
                        && count($object->translations) > 0
                        && ! ($isArray && array_key_exists('active', $slugValue));

                    $currentSlug['active'] = $slugPayloadCanForceActive
                        ? true
                        : ($isArray && array_key_exists('active', $slugValue)
                            ? $object->normalizeSlugActiveRequestValue($slugValue['active'])
                            : true);
                    $currentSlug = $this->getSlugParameters($object, $fields, $currentSlug);

                    $object->updateOrNewSlug($currentSlug);
                }
            }
        }
    }

    /**
     * @param Model $object
     * @return void
     */
    public function afterDeleteSlugsTrait($object)
    {
        $object->slugs()->delete();
    }

    /**
     * @param Model $object
     * @return void
     */
    public function afterRestoreSlugsTrait($object)
    {
        $object->slugs()->restore();
    }

    /**
     * @param Model $object
     * @param array $fields
     * @return array
     */
    public function getFormFieldsSlugsTrait($object, $fields)
    {
        unset($fields['slugs']);

        if (! property_exists($this->model, 'slugAttributes')) {
            return $fields;
        }

        $slugAttributes = $object->getSlugAttributes();

        if ($slugAttributes === []) {
            return $fields;
        }

        $object->loadMissing('slugs');

        if ($object->slugs === null || $object->slugs->isEmpty()) {
            return $fields;
        }

        foreach ($object->slugs as $slug) {
            $hasOtherActiveForLocale = $object->slugs->where('locale', $slug->locale)->where('active', true)->isNotEmpty();
            if ($slug->active || ! $hasOtherActiveForLocale) {
                $fields['slugs'][$slug->locale] = [
                    'slug' => $slug->slug,
                    'active' => (bool) $slug->active,
                ];
            }
        }

        return $fields;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @param array $slug
     * @return array
     */
    public function getSlugParameters($object, $fields, $slug)
    {
        $slugParams = $this->getSlugParams($object, $slug['locale']);

        foreach ($object->getSlugAttributes() as $param) {
            if (isset($slugParams[$param]) && isset($fields[$param])) {
                $slug[$param] = $fields[$param];
            } elseif (isset($slugParams[$param])) {
                $slug[$param] = $slugParams[$param];
            }
        }

        return $slug;
    }

    /**
     * @param string $slug
     * @param array $with
     * @param array $withCount
     * @param array $scopes
     * @return Model|null
     */
    public function existsSlug($slug, $with = [], $withCount = [], $scopes = [])
    {
        $query = $this->model->where($scopes)->scopes(['published', 'visible']);

        foreach (class_uses_recursive(get_called_class()) as $trait) {
            if (method_exists(get_called_class(), $method = 'getPublishedScopes' . class_basename($trait))) {
                $query->scopes($this->$method());
            }
        }

        $item = (clone $query)->existsSlug($slug)->with($with)->withCount($withCount)->first();

        if (! $item && $item = (clone $query)->orWhere(function ($query) use ($slug) {
            return $query->existsInactiveSlug($slug);
        })->first()) {
            $item->redirect = true;
        }

        if (! $item && config('translatable.use_property_fallback', false)
        && config('translatable.fallback_locale') != config('app.locale')) {
            $item = (clone $query)->orWhere(function ($query) {
                return $query->withActiveTranslations(config('translatable.fallback_locale'));
            })->existsFallbackLocaleSlug($slug)->first();

            if ($item) {
                $item->redirect = true;
            }
        }

        return $item;
    }

    /**
     * @param string $slug
     * @param array $with
     * @param array $withCount
     * @return Model
     */
    public function existsSlugPreview($slug, $with = [], $withCount = [])
    {
        return $this->model->existsInactiveSlug($slug)->with($with)->withCount($withCount)->first();
    }

    protected function getSlugParams($object, $locale)
    {
        return $object->getSlugParams($locale);
    }
}
