<?php

namespace Modules\SystemUser\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Unusualify\Modularity\Http\Requests\BaseFormRequest;

class CompanyRequest extends BaseFormRequest
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
            'name' => 'required|min:3',

        ];
    }

    public function update()
    {
        return [
            'name' => 'required|min:3',
        ];
    }
}
