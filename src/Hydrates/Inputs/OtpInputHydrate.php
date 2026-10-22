<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

class OtpInputHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'length' => 6,
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'otp-input';

        // add your logic

        return $input;
    }
}
