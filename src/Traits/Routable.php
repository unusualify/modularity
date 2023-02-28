<?php
namespace OoBook\CRM\Base\Traits;

use Nwidart\Modules\Facades\Module;

trait Routable {

    public function listRoute()
    {
        // dd( getCurrentModuleName(), $this->name, debug_backtrace() );
        return $this->route('index');
    }

    public function storeRoute()
    {
        return $this->route('store');
    }

    public function updateRoute($name)
    {
        return $this->route('update', [lowerName($name) => ':id']);
    }

    public function destroyRoute($name)
    {
        return $this->route('destroy', [lowerName($name) => ':id']);
    }

    public function permanentlyDestroyRoute($name)
    {
        return $this->route('destroy', [lowerName($name) => ':id']);
    }

    public function route($ext, $parameters = []){
        return route( $this->baseString($ext), $parameters );
    }

    public function baseString($ext='')
    {
        $module_name = getCurrentModuleLowerName() != 'base' ? getCurrentModuleLowerName().'.' : '';

        return 'api.'.$module_name.$this->routeName($this->name).'.'.$ext;
    }

    public function routeName($name){
        return getModuleSubRoute($this->name.'.route_name') ?? lowerName($name);
    }

}
