<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

use Illuminate\Support\Str;
use Unusualify\Modularous\Module;

/**
 * Detects route features from config/merges/traits.php against model/repository classes.
 */
final class FeatureDetector
{
    private const MODEL_TRAIT_NAMESPACE = 'Unusualify\\Modularous\\Entities\\Traits\\';

    private const REPOSITORY_TRAIT_NAMESPACE = 'Unusualify\\Modularous\\Repositories\\Traits\\';

    /**
     * @return array<string, array{option: string, model: ?string, repository: ?string}>
     */
    public function definitions(): array
    {
        /** @var array<string, array{model?: mixed, repository?: mixed}> $traits */
        $traits = (array) config('modularous.traits', []);
        $definitions = [];

        foreach ($traits as $optionKey => $meta) {
            if (! is_string($optionKey) || ! str_starts_with($optionKey, 'add')) {
                continue;
            }

            if (! is_array($meta)) {
                continue;
            }

            $featureKey = $this->optionKeyToFeatureKey($optionKey);
            $definitions[$featureKey] = [
                'option' => $optionKey,
                'model' => $this->resolveTraitClass($meta['model'] ?? null, self::MODEL_TRAIT_NAMESPACE),
                'repository' => $this->resolveTraitClass($meta['repository'] ?? null, self::REPOSITORY_TRAIT_NAMESPACE),
            ];
        }

        return $definitions;
    }

    /**
     * @return array{
     *     features: array<string, array{present: bool, model: bool|null, repository: bool|null}>,
     *     findings: list<ModuleRouteInspectFinding>
     * }
     */
    public function detect(?string $modelClass, ?string $repositoryClass): array
    {
        $features = [];
        $findings = [];

        foreach ($this->definitions() as $featureKey => $definition) {
            $modelTrait = $definition['model'];
            $repositoryTrait = $definition['repository'];

            $modelHas = $modelTrait !== null && $modelClass !== null && class_exists($modelClass)
                ? classHasTrait($modelClass, $modelTrait)
                : ($modelTrait === null ? null : false);

            $repositoryHas = $repositoryTrait !== null && $repositoryClass !== null && class_exists($repositoryClass)
                ? classHasTrait($repositoryClass, $repositoryTrait)
                : ($repositoryTrait === null ? null : false);

            $present = ($modelHas === true) || ($repositoryHas === true);

            $features[$featureKey] = [
                'present' => $present,
                'model' => $modelHas,
                'repository' => $repositoryHas,
            ];

            if ($modelTrait !== null && $repositoryTrait !== null) {
                if ($modelHas === true && $repositoryHas === false) {
                    $findings[] = new ModuleRouteInspectFinding(
                        'warning',
                        'missing_repository_companion',
                        sprintf(
                            'Model has %s but repository is missing companion %s (%s).',
                            class_basename($modelTrait),
                            class_basename($repositoryTrait),
                            $featureKey
                        ),
                        $featureKey,
                    );
                }

                if ($repositoryHas === true && $modelHas === false) {
                    $findings[] = new ModuleRouteInspectFinding(
                        'warning',
                        'missing_model_companion',
                        sprintf(
                            'Repository has %s but model is missing companion %s (%s).',
                            class_basename($repositoryTrait),
                            class_basename($modelTrait),
                            $featureKey
                        ),
                        $featureKey,
                    );
                }
            }
        }

        return [
            'features' => $features,
            'findings' => $findings,
        ];
    }

    /**
     * Extra CMR-specific check: Front controller should extend CmsController when IsCmr is present.
     *
     * @return list<ModuleRouteInspectFinding>
     */
    public function detectCmrFrontController(Module $module, string $routeName, array $features): array
    {
        $cmr = $features['cmr'] ?? null;
        if (! is_array($cmr) || ($cmr['present'] ?? false) !== true) {
            return [];
        }

        $cmsController = 'Modules\\Cms\\Http\\Controllers\\Front\\CmsController';
        if (! class_exists($cmsController)) {
            return [
                new ModuleRouteInspectFinding(
                    'info',
                    'cmr_cms_controller_unavailable',
                    'CMR is present but CmsController class is not available in this install.',
                    'cmr',
                ),
            ];
        }

        $studlyRoute = Str::studly($routeName);
        $className = $studlyRoute . 'Controller';
        $fqcn = $module->getTargetClassNamespace('front-controller', $className);

        if (! class_exists($fqcn)) {
            return [
                new ModuleRouteInspectFinding(
                    'warning',
                    'cmr_missing_front_controller',
                    "CMR is present but Front controller [{$fqcn}] does not exist.",
                    'cmr',
                ),
            ];
        }

        if (! is_subclass_of($fqcn, $cmsController, true)) {
            return [
                new ModuleRouteInspectFinding(
                    'warning',
                    'cmr_front_not_cms_controller',
                    "CMR is present but Front controller [{$fqcn}] does not extend CmsController.",
                    'cmr',
                ),
            ];
        }

        return [];
    }

    public function optionKeyToFeatureKey(string $optionKey): string
    {
        $withoutAdd = Str::after($optionKey, 'add');

        return Str::snake(lcfirst($withoutAdd !== '' ? $withoutAdd : $optionKey));
    }

    private function resolveTraitClass(mixed $value, string $defaultNamespace): ?string
    {
        if ($value === null || $value === false || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        if (class_exists($value) || trait_exists($value)) {
            return $value;
        }

        $fqcn = $defaultNamespace . ltrim($value, '\\');

        if (class_exists($fqcn) || trait_exists($fqcn)) {
            return $fqcn;
        }

        // Still return the expected FQCN so findings can name it even if missing from autoload.
        return $fqcn;
    }
}
