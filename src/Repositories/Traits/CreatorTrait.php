<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Facades\Auth;

trait CreatorTrait
{
    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterCreatorTrait($query, &$scopes)
    {
        $scopes['hasAccessToCreation'] = true;
    }

    /**
     * @param \Unusualify\Modularity\Models\Model $object
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

            if (isset($creatorInput['allowedRoles'])) {
                $allowedRoles = $creatorInput['allowedRoles'];
                if (! (Auth::check() && Auth::user()->hasRole($allowedRoles))) {
                    $isAllowed = false;
                }
            }

            if ($isAllowed && $object->creator()->exists()) {
                $fields['custom_creator_id'] = $object?->creator?->id;
            }
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
