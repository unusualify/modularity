<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

trait ManagePrevious
{
    public $previousRoute = null;

    /**
     * Initializes the previous route property before constructing the class.
     *
     * @param \Illuminate\Foundation\Application $app The application instance.
     * @param \Illuminate\Http\Request $request The request instance.
     */
    protected function __beforeConstructManagePrevious($app, $request)
    {
        $this->previousRoute = $this->getPreviousRoute();
    }

    /**
     * Gets the previous route information.
     *
     * @return \Illuminate\Routing\Route|null Returns the previous route or null if not found.
     */
    public function getPreviousRoute()
    {
        return Route::getRoutes()->match(
            Request::create(URL::previous())
        );
    }

    /**
     * Gets the name of the previous route.
     *
     * @return string Returns the previous route name or an empty string if not found.
     */
    public function getPreviousRouteName() :string
    {
        return $this->previousRoute?->getName() ?? '';
    }

    /**
     * Checks if the previous route is the current class's route.
     *
     * @return bool Returns true if the previous route is the same as the current class's route, otherwise false.
     */
    public function isPreviousRouteSelf() :bool
    {
        return $this->previousRoute?->getControllerClass() == get_class($this);
    }

    /**
     * Gets the schema for the previous route if it's the current class's route.
     *
     * @return array Returns the schema for the previous route or the chunked form schema if not found.
     */
    protected function getPreviousRouteSchema() :array
    {
        if($this->isPreviousRouteSelf()){
            $parts = explode('.', $this->getPreviousRouteName());
            $action = array_pop($parts);

            $methodName = "get{$this->getStudlyName($action)}Schema";

            if(method_exists($this, $methodName)){
                return $this->{$methodName}();
            }
        }

        return $this->chunkInputs($this->formSchema, all:true);
    }
}
