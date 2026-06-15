<?php

namespace Modules\Cms\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Support\ParentSegmentBindingValidator;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Repositories\Repository;

class ParentSegmentRepository extends Repository
{
    public function __construct(ParentSegment $model)
    {
        $this->model = $model;
    }

    /**
     * Ensure {@code target_model_class} is a registered module route model (FQCN from the select).
     *
     * @param Model $object
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    public function prepareFieldsBeforeSave($object, $fields)
    {
        if (array_key_exists('normalized_prefix', $fields)) {
            $fields['normalized_prefix'] = trim((string) ($fields['normalized_prefix'] ?? ''));
        }

        if (! empty($fields['target_model_class'])) {
            if (Modularous::resolveTargetModuleRouteForModelClass((string) $fields['target_model_class']) === null) {
                throw ValidationException::withMessages([
                    'target_model_class' => [__('Unknown model class for a module route.')],
                ]);
            }
            $fields['target_model_class'] = (string) $fields['target_model_class'];
        }

        /** @var ParentSegment $object */
        $effectiveTargetClass = isset($fields['target_model_class'])
            ? trim((string) $fields['target_model_class'])
            : ($object->exists ? trim((string) $object->target_model_class) : '');

        if ($effectiveTargetClass === '') {
            return parent::prepareFieldsBeforeSave($object, $fields);
        }

        $effectiveLocale = trim((string) (array_key_exists('locale', $fields)
            ? ($fields['locale'] ?? '')
            : ($object->exists ? (string) ($object->locale ?? '') : '')));

        $effectivePrefix = array_key_exists('normalized_prefix', $fields)
            ? (string) $fields['normalized_prefix']
            : ($object->exists ? trim((string) ($object->normalized_prefix ?? '')) : '');

        $effectiveEnabled = array_key_exists('enabled', $fields)
            ? (bool) $fields['enabled']
            : ($object->exists ? (bool) $object->enabled : true);

        ParentSegmentBindingValidator::assertExclusiveEmptyPrefixAcrossTargetsIfEnabled(
            $effectiveEnabled,
            $effectiveTargetClass,
            $effectiveLocale,
            $effectivePrefix,
            $object->exists ? $object->getKey() : null,
        );

        return parent::prepareFieldsBeforeSave($object, $fields);
    }
}
