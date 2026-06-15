<?php

namespace Unusualify\Modularous\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Facades\Modularous;

/**
 * Validates slug strings for admin form inputs (uniqueness against slug tables, optional locale scope).
 */
class SlugInputValidationService
{
    /**
     * Resolve module route to model and validate slug value.
     *
     * @return array{valid: bool, message: ?string, normalized: string}
     */
    public function validate(
        string $moduleName,
        string $routeName,
        string $value,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        $modelClass = $this->resolveModelClass($moduleName, $routeName);

        return $this->validateModelSlug($modelClass, $value, $locale, $localeScoped, $excludeId);
    }

    /**
     * Validate slug for a concrete model class (used by HTTP layer and tests).
     *
     * @param class-string<Model> $modelClass
     * @return array{valid: bool, message: ?string, normalized: string}
     */
    public function validateModelSlug(
        string $modelClass,
        string $value,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        if (! in_array(HasSlug::class, class_uses_recursive($modelClass), true)) {
            return [
                'valid' => false,
                'message' => __('This entity does not support slug validation.'),
                'normalized' => '',
            ];
        }

        $model = new $modelClass;

        $locale = $locale ?? app()->getLocale();

        $normalized = $this->normalizeSlugForModel($model, $value, $locale);

        if ($normalized === '') {
            return [
                'valid' => false,
                'message' => __('The slug field is required.'),
                'normalized' => '',
            ];
        }

        $slugModelClass = $model->getSlugModelClass();
        $foreignKey = $model->getForeignKey();

        $query = $slugModelClass::query()
            ->where('slug', $normalized);

        if ($localeScoped) {
            $query->where('locale', $locale);
        }

        if ($excludeId !== null) {
            $query->where($foreignKey, '!=', $excludeId);
        }

        if ($query->exists()) {
            return [
                'valid' => false,
                'message' => __('This slug is already taken.'),
                'normalized' => $normalized,
            ];
        }

        return [
            'valid' => true,
            'message' => null,
            'normalized' => $normalized,
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resolveModelClass(string $moduleName, string $routeName): string
    {
        $module = Modularous::find($moduleName);

        if ($module === null) {
            throw new InvalidArgumentException(__('The specified module was not found.'));
        }

        return $module->getRouteClass($routeName, 'model');
    }

    /**
     * Propose a unique slug for admin inputs: normalize like {@see HasSlug}, then validate with {@see validateModelSlug}
     * (including uniqueness + optional registry checks in CMS). On conflict, tries {@code base-2}, {@code base-3}, …
     *
     * @return array{slug: string, normalized: string, suffixed: bool}
     */
    public function proposeUniqueSlug(
        string $moduleName,
        string $routeName,
        string $source,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        $modelClass = $this->resolveModelClass($moduleName, $routeName);

        return $this->proposeUniqueSlugForModel($modelClass, $source, $locale, $localeScoped, $excludeId);
    }

    /**
     * @param class-string<Model> $modelClass
     * @return array{slug: string, normalized: string, suffixed: bool}
     */
    public function proposeUniqueSlugForModel(
        string $modelClass,
        string $source,
        ?string $locale = null,
        bool $localeScoped = true,
        ?int $excludeId = null,
    ): array {
        if (! in_array(HasSlug::class, class_uses_recursive($modelClass), true)) {
            throw new InvalidArgumentException(__('This entity does not support slug validation.'));
        }

        /** @var Model $model */
        $model = new $modelClass;
        $locale = $locale ?? app()->getLocale();
        $trimmedSource = trim($source);

        if ($trimmedSource === '') {
            throw new InvalidArgumentException(__('Enter a title or source text to generate a slug.'));
        }

        $base = $this->normalizeSlugForModel($model, $trimmedSource, $locale);
        if ($base === '') {
            throw new InvalidArgumentException(__('The slug field is required.'));
        }

        for ($attempt = 0; $attempt < 500; $attempt++) {
            $candidate = $attempt === 0 ? $base : $base . '-' . ($attempt + 1);
            $result = $this->validateModelSlug($modelClass, $candidate, $locale, $localeScoped, $excludeId);
            if (($result['valid'] ?? false) === true) {
                $normalized = (string) ($result['normalized'] ?? $candidate);

                return [
                    'slug' => $normalized,
                    'normalized' => $normalized,
                    'suffixed' => $attempt > 0,
                ];
            }
        }

        throw new InvalidArgumentException(__('Could not find an available slug.'));
    }

    /**
     * Match {@see HasSlug} slug generation for the given locale.
     */
    protected function normalizeSlugForModel($model, string $raw, string $locale): string
    {
        $trimmed = trim($raw);

        if ($trimmed === '') {
            return '';
        }

        if (in_array($locale, modularousConfig('slug_utf8_languages', []), true)) {
            return $model->getUtf8Slug($trimmed);
        }

        return Str::slug($trimmed);
    }
}
