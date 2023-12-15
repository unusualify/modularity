<?php

namespace Modules\SystemUser\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unusualify\Modularity\Http\Requests\BaseFormRequest;

class RoleRequest extends BaseFormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [

        ];

        return $rules + parent::rules();
    }


    public function store()
    {
        $table_name = config('permission.table_names.roles');
        return [
            'name' => "required|unique:$table_name|min:4",
            // 'name' => ''
            // 'email' => 'required|email|unique:users',
            // 'password' => 'required|confirmed|min:8',
            //… more validation

        ];
    }

    public function update()
    {
        $table_name = config('permission.table_names.roles');

        return [
            'name' => "required|min:4|unique:{$table_name},name,{$this->id}",
            // 'guard_name' => 'sometimes|min:4',
            // 'email' => 'required|email|unique:users,email,'.$this->user()->id,
            // 'logo' => 'nullable|image|max:1024',
            // 'bio' => 'nullable|max:300',
            // 'github_url' => 'nullable|url'
            //… more validation

        ];
    }
}
