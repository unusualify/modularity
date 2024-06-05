<?php

namespace Modules\SystemUser\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
        $table_name = $this->model()->getTable();
        return [
            // 'name' => "sometimes|required|unique:$table_name|min:4",
        ];
    }

    public function rulesForUpdate()
    {
        $table_name = $this->model()->getTable();
        return [
            // 'name' => "sometimes|required|min:3|unique:{$table_name},name,".$this->id,
        ];
    }

}
