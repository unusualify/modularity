<?php

namespace Unusual\CRM\Base\View;

use Illuminate\View\Component;

class Table extends Component
{

    /**
     * The headers.
     *
     * @var array
     */
    public $headers;


    /**
     * The inputs.
     *
     * @var array
     */
    public $inputs;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($headers, $inputs, $name)
    {
        $this->headers = $headers;
        $this->inputs = $inputs;
        $this->name = $name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('base::components.table');
    }
}
