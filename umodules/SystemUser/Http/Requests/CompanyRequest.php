<?php

namespace Modules\SystemUser\Http\Requests;

use Unusualify\Modularity\Http\Requests\Request;

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

        ];
    }

    public function rulesForCreate()
    {
        $table_name = $this->model()->getTable();

        return [
            'name' => 'required|min:3',
        ];
    }

    public function rulesForUpdate()
    {
        $table_name = $this->model()->getTable();

        return [
            'name' => 'required|min:3',
        ];
    }
}
