<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Facades\Modularity;

class AuthorizeHydrate extends InputHydrate
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
        'label' => 'Authorize',
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'select';
        $input['name'] = 'authorized_id';
        $input['multiple'] = false;
        $input['returnObject'] = false;

        // $input['rules'] ??= 'sometimes|required';

        $authorizedModel = null;

        if (isset($input['authorized_type'])) {
            $authorizedModel = $input['authorized_type'];
            $authorizedModel = new $authorizedModel;
        } elseif ($input['_module'] && $input['_route']) {
            $module = Modularity::find($input['_module']);
            $selfModel = $module->getRouteClass($input['_routeName'], 'model');
            if (in_array('Unusualify\Modularity\Entities\Traits\HasAuthorizable', class_uses_recursive($selfModel))) {
                $selfModel = new $selfModel;
                $authorizedModel = $selfModel->getAuthorizedModel();
                $input['items'] = $selfModel::all();
            }
        } elseif (isset($input['routeName'])) {
            $selfModel = $this->module->getRouteClass($input['routeName'], 'model');
            if (in_array('Unusualify\Modularity\Entities\Traits\HasAuthorizable', class_uses_recursive($selfModel))) {
                $selfModel = new $selfModel;
                $authorizedModel = $selfModel->getAuthorizedModel();
                $input['items'] = $selfModel::all();
            }
        }

        if ($authorizedModel) {
            $q = $authorizedModel::query();

            if (isset($input['scopeRole'])) {
                $roles = Role::whereIn('name', $input['scopeRole'])->get('name');
                $q->role($roles->map(fn ($role) => $role->name)->toArray());
            }

            $input['items'] = $q->get(['id', 'name']);
            $input['noRecords'] = true;
        }

        // add your logic

        return $input;
    }

    public function afterHydrateRecords(&$input)
    {
        // if(!isset($input['items'])){
        //     dd($input);
        //     return;
        // }
        // dd($input);
    }
}
