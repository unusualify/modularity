<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularous\Entities\Model;

trait CreatorTrait
{
    protected bool $hasUserAwareCacheCreatorTrait = true;

    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function filterCreatorTrait($query, &$scopes)
    {
        $scopes['hasAccessToCreation'] = true;
    }

    /**
     * @param Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getFormFieldsCreatorTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema
        if (isset($schema['custom_creator_id'])) {
            $creatorInput = $schema['custom_creator_id'];
            $isAllowed = true;

            // if (isset($creatorInput['allowedRoles'])) {
            //     $allowedRoles = $creatorInput['allowedRoles'];
            //     // if user is not logged in, return true
            //     // if user is logged in and not have allowedRoles, isAllowed is false
            //     if( Auth::check() && !Auth::user()->hasRole($allowedRoles)) {
            //         $isAllowed = false;
            //     }
            // }

            $fields['custom_creator_id'] = $object?->creator?->id;
            // if ($isAllowed && $object->creator()->exists()) {
            // }
        }

        return $fields;
    }

    public function prependFormSchemaCreatorTrait($scope = [])
    {
        return [
            (object) [
                'type' => 'creator',
            ],
        ];
    }
}
