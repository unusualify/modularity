<?php

namespace OoBook\CRM\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OoBook\CRM\Base\Http\Requests\BaseFormRequest;

class UserRequest extends BaseFormRequest
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
            'name' => 'required|unique:users|min:15',

        ];
    }

    public function update()
    {
        return [
            'name' => 'required|min:17|unique:users,name,'.$this->id,
        ];
    }
}
