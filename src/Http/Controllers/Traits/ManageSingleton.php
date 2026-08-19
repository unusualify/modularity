<?php

namespace Unusualify\Modularous\Http\Controllers\Traits;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

trait ManageSingleton
{
    protected $isSingleton = false;

    protected function __afterConstructManageSingleton($app, $request)
    {
        $routeName = $this->routeName ?? $this->moduleRouteName ?? $this->getRouteName();

        // Prefer Module::isSingleton on construct (no ModuleRoute required).
        // ModuleRoute is resolved later in preload — same shared memo when available.
        if ($this->module && is_string($routeName) && $routeName !== '') {
            $this->isSingleton = $this->module->isSingleton($routeName);
        } elseif ($this->moduleRoute) {
            $this->isSingleton = $this->moduleRoute->isSingleton();
        }
    }

    public function _edit($id = null)
    {
        $model = "App\\Models\\{$this->getModelName()}";

        // if (!class_exists($model)) {
        //     $model = TwillCapsules::getCapsuleForModel($this->modelName)->getModel();
        // }

        $item = app($model)->first();

        if (! $item) {
            if (config('twill.auto_seed_singletons', false)) {
                $this->seed();

                return $this->editSingleton();
            }

            throw new \Exception("$model is not seeded");
        }

        Session::put('pages_back_link', url()->current());

        $controllerForm = $this->getForm($item);

        if ($controllerForm->hasForm()) {
            $view = 'twill::layouts.form';
        } else {
            $view = "twill.{$this->moduleName}.form";
        }

        View::share('form', $this->form($item->id));

        return View::make($view, $this->form($item->id))->with(
            ['formBuilder' => $controllerForm->toFrontend($this->getSideFieldsets($item))]
        );
    }

    private function _seed(): void
    {
        $seederName = $this->getModelName() . 'Seeder';
        $seederNamespace = '\\Database\\Seeders\\';

        // if (!class_exists($seederNamespace . $seederName)) {
        //     $seederNamespace = TwillCapsules::getCapsuleForModel($this->modelName)->getSeedsNamespace() . '\\';
        // }

        $seederClass = $seederNamespace . $seederName;

        if (! class_exists($seederClass)) {
            throw new \Exception("$seederClass is missing");
        }

        $seeder = new $seederClass;
        $seeder->run();
    }
}
