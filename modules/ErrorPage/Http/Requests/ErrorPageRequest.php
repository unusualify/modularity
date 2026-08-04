<?php

namespace Modules\ErrorPage\Http\Requests;

use Unusualify\Modularous\Http\Requests\Request;

class ErrorPageRequest extends Request
{
    /**
     * @return array<string, mixed>
     */
    public function rulesForAll()
    {
        $table = modularousConfig('tables.cms_error_pages', 'um_cms_error_pages');

        return [
            'name' => 'sometimes|required|string|max:255',
            'error_code' => 'sometimes|required|string|in:403,404,500|unique:' . $table . ',error_code',
            'published' => 'sometimes|nullable|boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForCreate()
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function rulesForUpdate()
    {
        $routeParam = $this->route('error_page') ?? $this->route('id');
        $id = is_object($routeParam) && method_exists($routeParam, 'getKey')
            ? $routeParam->getKey()
            : $routeParam;

        $table = modularousConfig('tables.cms_error_pages', 'um_cms_error_pages');

        return [
            'error_code' => 'sometimes|required|string|in:403,404,500|unique:' . $table . ',error_code,' . $id,
        ];
    }
}
