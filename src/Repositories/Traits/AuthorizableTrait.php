<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Unusualify\Modularous\Entities\Model;

trait AuthorizableTrait
{
    /**
     * @param Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getFormFieldsAuthorizableTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema
        if (isset($schema['authorized_id']) && $object->authorization_record_exists) {
            $fields['authorized_id'] = $object->authorizationRecord->authorized_id;
            $fields['authorized_type'] = $object->authorizationRecord->authorized_type;
            if (! in_array('Unusualify\Modularous\Entities\Traits\HasUuid', class_uses_recursive($fields['authorized_type']))) {
                $fields['authorized_id'] = intval($fields['authorized_id']);
            }
        }

        return $fields;
    }

    public function getTableFiltersAuthorizableTrait($scope = null): array
    {
        $model = $this->getModel();

        $tableFilters = [];

        if ($model->hasAuthorizationUsage()) {
            $tableFilters[] = [
                'name' => ___('listing.filter.authorized'),
                'slug' => 'authorized',
                'methods' => 'getCountFor',
                'params' => ['hasAnyAuthorization'],
            ];

            $tableFilters[] = [
                'name' => ___('listing.filter.unauthorized'),
                'slug' => 'unauthorized',
                'methods' => 'getCountFor',
                'params' => ['unauthorized'],
            ];
        }

        $tableFilters[] = [
            'name' => ___('listing.filter.your-authorizations'),
            'slug' => 'your-authorizations',
            'methods' => 'getCountFor',
            'params' => ['isAuthorizedToYou'],
        ];

        return $tableFilters;
    }
}
