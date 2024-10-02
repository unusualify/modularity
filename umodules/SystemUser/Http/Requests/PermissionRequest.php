<?php

namespace Modules\SystemUser\Http\Requests;

use Unusualify\Modularity\Http\Requests\Request;

class PermissionRequest extends Request
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
        $table_name = $this->model()->getTable();

        return [
            'name' => "required|unique:{$table_name},name|min:4",
        ];
    }

    public function rulesForUpdate()
    {
        $table_name = $this->model()->getTable();

        return [
            'name' => "required|min:4|unique:{$table_name},name," . $this->id,
            // 'guard_name' => 'sometimes|min:4',
            // 'email' => 'required|email|unique:users,email,'.$this->user()->id,
            // 'logo' => 'nullable|image|max:1024',
            // 'bio' => 'nullable|max:300',
            // 'github_url' => 'nullable|url'
            //… more validation

        ];
    }
}
