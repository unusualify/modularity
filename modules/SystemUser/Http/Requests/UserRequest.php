<?php

namespace Modules\SystemUser\Http\Requests;

use Illuminate\Validation\Rule;
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

        ];
    }

    public function rulesForCreate()
    {
        $rolesTable = config('permission.table_names.roles', 'sp_roles');

        return [
            'name' => 'sometimes|required|min:4',
            'email' => 'sometimes|required|email|unique_table',
            'roles' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Get superadmin role ID
                    $superadminRole = \DB::table('sp_roles')
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
                            if ((int)$value === $superadminId) {
                                $fail('The roles field cannot be the superadmin role.');
                            }
                        }
                    }
                }
            ]
            // 'country_id' => 'required|exists:um_countries,id',
        ];
    }

    public function rulesForUpdate()
    {
        return [
            'name' => 'sometimes|required|min:4',
            'email' => 'sometimes|required|email|unique_table',
            'country_id' => 'required|exists:um_countries,id',
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
