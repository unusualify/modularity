<?php

namespace Modules\SystemUser\Http\Requests;

use Unusualify\Modularity\Http\Requests\Request;

class UserRequest extends Request
{
    /**
     * Get the default validation rules that apply to the request.
     *
     * @return array
     */
    public function rulesForAll()
    {
        return [
            'roles' => [
                'sometimes',
                'required',
                function ($attribute, $value, $fail) {
                    $rolesTable = config('permission.table_names.roles', 'sp_roles');
                    // Get superadmin role ID
                    $superadminRole = \DB::table($rolesTable)
                        ->where('name', 'superadmin')
                        ->first();

                    if ($superadminRole) {
                        $superadminId = $superadminRole->id;

                        // Check if roles is an array
                        if (is_array($value)) {
                            if (in_array($superadminId, $value)) {
                                $fail('The roles field cannot contain the superadmin role.');
                            }
                        } else {
                            // Check if roles is a single integer ID
                            if ((int) $value === $superadminId) {
                                $fail('The roles field cannot be the superadmin role.');
                            }
                        }
                    }
                },
            ],
        ];
    }

    public function rulesForCreate()
    {
        return [
            'name' => 'sometimes|required|min:2',
            'email' => 'sometimes|required|email|unique_table',
            'country_id' => 'sometimes|required|exists:um_countries,id',
        ];
    }

    public function rulesForUpdate()
    {
        return [
            'name' => 'sometimes|required|min:2',
            'email' => 'sometimes|required|email|unique_table',
            'country_id' => 'sometimes|required|exists:um_countries,id',
            // 'roles' => 'missing',
        ];
    }

    public function messages()
    {
        return [
            'roles.unique' => 'The superadmin role is not allowed to be assigned to a user.',
        ];
    }
}
