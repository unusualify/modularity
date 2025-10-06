<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Unusualify\Modularity\Facades\Modularity;

trait ManageInertia
{
    /**
     * Determine if the request should use Inertia
     */
    protected function shouldUseInertia(): bool
    {
        return Modularity::shouldUseInertia() || $this->isInertiaRequest();
    }

    /**
     * Determine if the request is an Inertia AJAX request
     */
    public function isInertiaAjaxRequest(): bool
    {
        return $this->request->ajax() && $this->isInertiaRequest();
    }

    /**
     * Determine if the request is an Inertia request
     */
    public function isInertiaRequest(): bool
    {
        return Request::inertia();

        return $this->request->header('X-Inertia') === 'true';
    }

    /**
     * Render index view with Inertia or Blade
     */
    protected function renderIndex(array $data): InertiaResponse|\Illuminate\View\View
    {
        if ($this->shouldUseInertia()) {
            return $this->renderInertiaIndex($data);
        }

        return $this->renderBladeIndex($data);
    }

    /**
     * Render form view with Inertia or Blade
     */
    protected function renderForm(array $data): InertiaResponse|\Illuminate\View\View
    {
        if ($this->shouldUseInertia()) {
            return $this->renderInertiaForm($data);
        }

        return $this->renderBladeForm($data);
    }

    /**
     * Render index with Inertia
     */
    protected function renderInertiaIndex(array $data): InertiaResponse
    {
        $this->shareInertiaStoreVariables();
        // Try module-specific page first, then fall back to generic
        $pageComponent = $this->getInertiaPageComponent('Index');

        return Inertia::render($pageComponent, [
            'tableAttributes' => $data['tableAttributes'] ?? [],
            // 'tableStore' => $data['tableStore'] ?? new \StdClass(),
            // 'formStore' => $data['formStore'] ?? new \StdClass(),
            'endpoints' => $data['endpoints'] ?? new \StdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    /**
     * Render form with Inertia
     */
    protected function renderInertiaForm(array $data): InertiaResponse
    {
        $this->shareInertiaStoreVariables();
        // Try module-specific page first, then fall back to generic
        $pageComponent = $this->getInertiaPageComponent('Form');

        return Inertia::render($pageComponent, [
            'formAttributes' => $data['formAttributes'] ?? [],
            // 'formStore' => $data['formStore'] ?? new \StdClass(),
            'endpoints' => $data['endpoints'] ?? new \StdClass,
            'mainConfiguration' => $this->getInertiaMainConfiguration($data),
            'headLayoutData' => $this->getHeadLayoutData($data),
        ]);
    }

    /**
     * Get the appropriate Inertia page component
     */
    protected function getInertiaPageComponent(string $type): string
    {
        // Try module-specific page first (e.g., Package/Index, Package/Form)
        if (isset($this->moduleName) && isset($this->routeName) && $this->module->hasInertiaPagesType($this->routeName, $type)) {
            $modulePage = $this->module->getInertiaPagesTypeName($this->routeName, $type);

            // The component resolution will be handled by the Inertia resolver
            // It will try: Module/Action -> Layouts/Action -> Action
            return $modulePage;
        }

        // Fall back to generic page (will try Layouts/Type then Type)
        return $type;
    }

    /**
     * Check if a Vue component exists (for future enhancement)
     */
    protected function componentExists(string $componentPath): bool
    {
        // This could be implemented to check if the Vue component file exists
        // For now, we rely on the Inertia resolver to handle fallbacks
        $vueFile = resource_path("js/Pages/{$componentPath}.vue");

        return file_exists($vueFile);
    }

    /**
     * Render index with Blade (original behavior)
     */
    protected function renderBladeIndex(array $data): \Illuminate\View\View
    {
        $view = Collection::make([
            "$this->viewPrefix.index",
            "$this->baseKey::" . $this->getSnakeCase($this->routeName) . '.index',
            "$this->baseKey::layouts.index",
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $data);
    }

    /**
     * Render form with Blade (original behavior)
     */
    protected function renderBladeForm(array $data): \Illuminate\View\View
    {
        $view = Collection::make([
            "$this->viewPrefix.form",
            "$this->baseKey::$this->routeName.form",
            "$this->baseKey::layouts.form",
        ])->first(function ($view) {
            return View::exists($view);
        });

        return View::make($view, $data);
    }

    /**
     * Get main configuration for Inertia
     */
    protected function getInertiaMainConfiguration(array $data): array
    {
        return get_modularity_inertia_main_configuration($data);
    }

    protected function getHeadLayoutData(array $data): array
    {
        return get_modularity_head_layout_config($data);
    }

    /**
     * Get the user
     */
    public function shareInertiaStoreVariables()
    {
        view()->composer(modularityBaseKey() . '::layouts.app-inertia', function ($view) {
            $user = $this->user;

            $userRepository = app()->make(\Modules\SystemUser\Repositories\UserRepository::class);
            $profileShortcutSchema = modularity_format_inputs(getFormDraft('profile_shortcut'));
            $profileShortcutModel = $userRepository->getFormFields($user, $profileShortcutSchema);
            $loginShortcutSchema = modularity_format_inputs(getFormDraft('login_shortcut'));

            $view->with(array_merge($view->getData(), [
                'profileShortcutModel' => $profileShortcutModel,
                'profileShortcutSchema' => $profileShortcutSchema,
                'loginShortcutModel' => [],
                'loginShortcutSchema' => $loginShortcutSchema,
                'authorization' => get_modularity_authorization_config(),
            ]));
        });
    }
}
