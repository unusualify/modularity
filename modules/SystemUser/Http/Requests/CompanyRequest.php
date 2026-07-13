<?php

namespace Modules\SystemUser\Http\Requests;

use Unusualify\Modularous\Http\Requests\Request;

class CompanyRequest extends Request
{
    /**
     * Get the default validation rules that apply to the request.
     *
     * @return array
     */
    public function rulesForAll()
    {
        return [
            'is_personal' => 'required|boolean',
            'name' => 'sometimes|required_if:is_personal,false',
            'tax_id' => 'sometimes|required_if:is_personal,false',
            'address' => 'required|min:5',
            // 'city' => 'sometimes|required|min:3',
            // 'country' => 'sometimes|required|min:3',
            'country_id' => 'required|exists:um_countries,id',
            'state' => 'required|min:3',
            'zip_code' => 'required|min:5',
            'email' => 'sometimes|required_if:is_personal,false',
        ];
    }

    public function rulesForCreate()
    {
        $table_name = $this->model()->getTable();

        return [

        ];
    }

    public function rulesForUpdate()
    {
        $table_name = $this->model()->getTable();

        return [

        ];
    }

    public function rulesForDelete()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $company = $this->model()->find($value);

                    if ($company && $company->users()->exists()) {
                        $fail(__('This company has registered users and cannot be deleted.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required_if' => 'The name field is required when the company is not personal.',
            'tax_id.required_if' => 'The tax id field is required when the company is not personal.',
            'email.required_if' => 'The email field is required when the company is not personal.',
        ];
    }
}
