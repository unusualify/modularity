<?php

namespace Modules\SystemPayment\Http\Requests;

use Unusualify\Modularity\Http\Requests\Request;

class PaymentServiceRequest extends Request
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

    /**
     * Get the validation rules that apply to the post request.
     *
     * @return array
     */
    public function rulesForCreate()
    {
        return [];
    }

    /**
     * Get the validation rules that apply to the put/patch request.
     *
     * @return array
     */
    public function rulesForUpdate()
    {
        return [];
    }
}
