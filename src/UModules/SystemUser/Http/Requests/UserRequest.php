<?php

namespace Modules\SystemUser\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unusualify\Modularity\Http\Requests\Request;

class UserRequest extends Request
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
            'name' => 'sometimes|required|unique:users|min:3',

        ];
    }

    public function update()
    {
        return [
            'name' => 'sometimes|required|min:3|unique:users,name,'.$this->id,

            'password' => 'sometimes|missing_with:name|min:6|confirmed',
            // 'password_confirmation' => 'missing_with:name|required|min:6'
        ];
    }
}
