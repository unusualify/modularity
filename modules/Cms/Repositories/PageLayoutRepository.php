<?php

namespace Modules\Cms\Repositories;

use Illuminate\Validation\ValidationException;
use Modules\Cms\Entities\Concerns\HasPageLayout;
use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Support\LayoutSegmentAppends;
use Modules\Cms\Support\LayoutSegmentAppendsValidator;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Repositories\Repository;

class PageLayoutRepository extends Repository
{
    public function __construct(PageLayout $model)
    {
        $this->model = $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     * @param array<string, mixed>               $fields
     * @return array<string, mixed>
     */
    public function prepareFieldsBeforeSave($object, $fields)
    {
        if (array_key_exists('layout_builder_id', $fields)) {
            $lid = $fields['layout_builder_id'];
            if ($lid === '' || $lid === false) {
                $fields['layout_builder_id'] = null;
            }
        }

        if (array_key_exists('blade_source', $fields) || array_key_exists('blade_segments', $fields)) {
            $fields['blade_source'] = $this->normalizeBladeSource($fields['blade_source'] ?? null);
        }

        if (array_key_exists('blade_view_name', $fields)) {
            $fields['blade_view_name'] = $this->normalizeBladeViewName($fields['blade_view_name']);
        }

        if (array_key_exists('blade_segments', $fields)) {
            $segmentsPayload = $fields['blade_segments'];

            if (is_array($segmentsPayload)) {
                $normalizedSegments = LayoutSegmentAppends::normalize($segmentsPayload);
                LayoutSegmentAppendsValidator::enforceByteLimit($normalizedSegments);
                $fields['blade_segments'] = $normalizedSegments;
            } else {
                // Ignore the null payload that the UI posts when toggling storage sources so we keep the last saved fragments.
                unset($fields['blade_segments']);
            }
        }

        if (($fields['blade_source'] ?? null) === 'filesystem') {
            // Keep previously saved DB segments intact so toggling to filesystem doesn’t lose the draft.
        } else {
            $fields['blade_view_name'] = null;
        }

        if (! empty($fields['target_model_class'])) {
            if (Modularous::resolveTargetModuleRouteForModelClass((string) $fields['target_model_class']) === null) {
                throw ValidationException::withMessages([
                    'target_model_class' => [__('Unknown model class for a module route.')],
                ]);
            }
            $fields['target_model_class'] = (string) $fields['target_model_class'];

            if (! classHasTrait($fields['target_model_class'], HasPageLayout::class)) {
                throw ValidationException::withMessages([
                    'target_model_class' => [__('That model must use HasPageLayout (e.g. CMR routes).')],
                ]);
            }
        }

        return parent::prepareFieldsBeforeSave($object, $fields);
    }

    private function normalizeBladeSource(?string $source): string
    {
        $effective = $source !== null && trim($source) !== '' ? trim($source) : modularousConfig('cms_layout_builder.default_blade_source', 'db');

        return $effective === 'filesystem' ? 'filesystem' : 'db';
    }

    private function normalizeBladeViewName(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
