<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPayment\Repositories\PaymentServiceRepository;

class PaymentServiceHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'default' => [],
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;
        $input['type'] = 'input-payment-service';

        $ps = new PaymentService();
        $input['items'] = $ps->where('is_external', 1)->with('paymentCurrencies')->get()->all();
        //TODO : get current currency
        // currency doesn't go to payment modal so I need to do it in here

        $input['default_payment_service'] =  config('modularity.default_payment_service');



        return $input;
    }
}
