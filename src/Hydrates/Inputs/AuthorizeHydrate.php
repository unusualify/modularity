<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Unusualify\Modularous\Facades\Modularous;

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
            $module = Modularous::find($input['_module']);
            $selfModel = $module->getRouteClass($input['_routeName'], 'model');
            if (in_array('Unusualify\Modularous\Entities\Traits\HasAuthorizable', class_uses_recursive($selfModel))) {
                $selfModel = new $selfModel;
                $authorizedModel = $selfModel->getAuthorizedModel();
                // $input['items'] = $selfModel::all();
            }
        } elseif (isset($input['routeName'])) {
            $selfModel = $this->module->getRouteClass($input['routeName'], 'model');
            if (in_array('Unusualify\Modularous\Entities\Traits\HasAuthorizable', class_uses_recursive($selfModel))) {
                $selfModel = new $selfModel;
                $authorizedModel = $selfModel->getAuthorizedModel();
                // $input['items'] = $selfModel::all();
            }
        }

        if ($authorizedModel) {
            $q = $authorizedModel::query();

            if (! $this->skipQueries && isset($input['scopeRole'])) {
                if (in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($authorizedModel))) {
                    $roleModel = config('permission.models.role');
                    $existingRoles = $roleModel::whereIn('name', $input['scopeRole'])->get();
                    $q->role($existingRoles->map(fn ($role) => $role->name)->toArray());
                }
            }

            $input['items'] = ! $this->skipQueries
                ? $q->get(['id', 'name'])
                : [];

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
