<?php
namespace OoBook\CRM\Base\Traits;

use Nwidart\Modules\Facades\Module;

trait Tabulable {

    use Routable, Searchable;

    public function getHeaders()
    {
        // $module = Module::find($this->module);
        // dd( config(  $module->getLowerName().'.sub_routes.'.strtolower($this->name)['table']['headers'] ) );
        return getModuleSubRoute($this->name.'.table.headers');
    }

    public function getInputs()
    {
        return getModuleSubRoute($this->name.'.table.inputs');

    }

    public function renderDataTable($data = [])
    {
        return view()->make('base::components.table',[
            'headers'           => $this->getHeaders(),
            'inputs'            => $this->getInputs(),
            'name'              => $data['name'] ?? $this->name,
            'itemsPerPage'      => $data['itemsPerPage'] ?? $this->itemsPerPage,
            'listEndPoint'      => $data['listEndPoint'] ?? $this->listRoute(),
            'storeEndPoint'     => $data['storeEndPoint'] ?? $this->storeRoute(),
            'updateEndPoint'    => $data['updateEndPoint'] ?? $this->updateRoute($this->name),
            'deleteEndPoint'    => $data['deleteEndPoint'] ?? $this->destroyRoute($this->name),
        ])->render();
    }

    public function rectifyInputs($data, $type = 'store')
    {

        foreach( $this->getInputs() as $input){

            if( !isset($data[$input['name']])){

                $data[$input['name']] = $input['default'];

            }else if( isset($input['extras']) && in_array('readonly', $input['extras']) ){

                if($type == 'store'){

                    $data[$input['name']] = $input['default'] ?? '';

                }else if( $type == 'update' && isset($data[$input['name']]) ){

                    unset($data[$input['name']]);

                }

            }
        }

        return $data;
    }
}
