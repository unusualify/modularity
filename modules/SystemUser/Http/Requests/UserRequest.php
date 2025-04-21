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

        ];
    }

    public function rulesForCreate()
    {
        return [
            'name' => "sometimes|required|min:4",
            'email' => "sometimes|required|email|unique_table",
        ];
    }

    public function rulesForUpdate()
    {
        return [
            'name' => "sometimes|required|min:4",
            'email' => "sometimes|required|email|unique_table",
        ];
    }
}
