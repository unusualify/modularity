<?php

namespace Modules\SystemUser\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unusualify\Modularity\Http\Requests\BaseFormRequest;

class PermissionRequest extends BaseFormRequest
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
        return [
            'name' => 'required|unique:permissions,name|min:4',
        ];
    }

    public function update()
    {
        return [
            'name' => 'required|min:4|unique:permissions,name,'.$this->id,
            // 'guard_name' => 'sometimes|min:4',
            // 'email' => 'required|email|unique:users,email,'.$this->user()->id,
            // 'logo' => 'nullable|image|max:1024',
            // 'bio' => 'nullable|max:300',
            // 'github_url' => 'nullable|url'
            //… more validation

        ];
    }
}
