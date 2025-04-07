<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Form;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Traits\Allowable;

trait FormActions
{
    use Allowable;

    /**
     * @return void
     */
    protected function __afterConstructFormActions($app, $request)
    {
        $this->setFormActions();
    }

    /**
     * @var array
     */
    public function setFormActions()
    {
        $this->defaultFormActions = (array) Config::get(modularityBaseKey() . '.default_form_actions', []);

        $formActions = [];

        if ((bool) $this->config) {
            try {
                $formActions = Collection::make(
                    array_merge_recursive_preserve($this->defaultFormActions, object_to_array($this->getConfigFieldsByRoute('form_actions', [])))
                )->toArray();
            } catch (\Throwable $th) {

            }
        }

        $this->formActions = array_merge_recursive_preserve($formActions, $this->formActions ?? []);
    }

    /**
     * @return array
     */
    public function getFormActions(): array
    {
        $default_action = (array) Config::get(modularityBaseKey() . '.default_form_action');

        return Collection::make($this->formActions)->reduce(function ($acc, $action, $key) use ($default_action) {

            $isAllowed = $this->isAllowedItem(
                $action,
                searchKey: 'allowedRoles',
                orClosure: fn($item) => $this->user->isSuperAdmin(),
            );

            if(!$isAllowed) {
                return $acc;
            }

            if (isset($action['endpoint']) && ($routeName = Route::hasAdmin($action['endpoint']))) {
                $parameters = Route::getRoutes()->getByName($routeName)->parameterNames();
                $action['endpoint'] = route($routeName, array_fill_keys($parameters, ':id'));
                // $action['endpoint'] = route($routeName, ['press_release' => ':id']);
                // dd($parameters, $action);
                // $action['endpoint'] = route($routeName, ['{id}' => '{id}']);
            }

            if (isset($action['schema'])) {
                $action['schema'] = $this->createFormSchema($action['schema']);
                // dd($action['schema']);
            }

            $acc[$key] = array_merge_recursive_preserve($default_action, $action);

            return $acc;
        }, []);
    }
}