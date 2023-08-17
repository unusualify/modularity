<?php

namespace OoBook\CRM\Base\Providers;

use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Str;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // dd(auth()->user());

        view()->composer("$this->baseKey::layouts.master", function ($view)
        {

            // dd(
            //     makeSidebarMenuItem([
            //         'name' => 'Dashboard',
            //         'route' => route('dashboard'),
            //     ]),
            //     ...unusualConfig('navigation.menu')
            // );
            $configuration = [
                'current_url' => url()->current(),
                'sideMenu' => [
                    makeSidebarMenuItem([
                        'name' => 'Dashboard',
                        'route' => route('dashboard'),
                    ]),
                    ...config(unusualBaseKey() .'-navigation.menu')
                ],
                'breadcrumbs' => []
            ];

            // dd($configuration);
            $view->with('configuration', $configuration);

            return;

            dd(
                $configuration,
                // unusualConfig('navigation.menu', [])
            );

            $configs = array_merge(
                config($this->baseKey . '.internal_modules'),
                array_map(
                    function($item){
                        return config(snakeCase($item->getName()));
                    },
                    array_filter(Module::all(), function($item){
                        return $item->getName() != 'Base' && $item->isStatus(true);
                    })
                )
            );
            dd(
                $configs
            );
            // $configuration['sideMenu'][] = [
            //     'name' => 'Modules',
            //     // 'icon' => '$modules'
            // ];
            foreach ( $configs as $name => $config) {
                try {
                    $name = $config['name'];
                    //code...
                } catch (\Throwable $th) {
                    dd(
                        $name,
                        $config,
                        $configs,
                    );
                }
                $pr = $config['parent_route']; //  parent_route array|object
                // dd($config);
                // menu list element
                $array = [
                    'name' => $config['headline'] ?? $pr['headline'] ?? pluralize($pr['name']) ?? pluralize($config['name']) ??'Items',
                    'icon' => '',
                    'is_active' => 0
                ];

                if( isset($config['sub_routes']) && !empty($config['sub_routes']) ){
                    $array['items'] = [];
                    $array['icon'] = $config['icon'] ?? '';

                    $parent_route_name = $pr['route_name'].".index";

                    if(isset($config['base_prefix']) && $config['base_prefix'])
                        $parent_route_name = strtolower(config($this->baseKey . '.name')) . "." . $parent_route_name;

                    if( Route::has($parent_route_name) ){
                        $route = route( $parent_route_name );
                        $is_active = $route == $configuration['current_url'] ? 1 : 0;

                        if($is_active){
                            $array['is_active'] = $is_active;
                        }

                        $array['items'][] = [
                            'name' => $pr['headline'] ?? pluralize($pr['name']) ?? pluralize($config['name']) ??'Items',
                            'route' => route($parent_route_name),
                            'icon' => $config['parent_route']['icon'] ?? '',
                            'is_active' => $is_active
                        ];
                    }

                    foreach( $config['sub_routes'] as $sr){
                        // $sr sub route array|object
                        $route_name = $pr['route_name'] . '.' . $sr['route_name'] . ".index";

                        if(isset($config['base_prefix']) && $config['base_prefix'])
                            $route_name = strtolower(config($this->baseKey . '.name')) . "." . $route_name;

                        if( Route::has($route_name) ){
                            $name = $sr['headline'] ?? pluralize($sr['name']);
                            $route = route( $route_name );
                            $is_active = $route == $configuration['current_url'] ? 1 : 0;

                            if($is_active){
                                $array['is_active'] = $is_active;

                                // Addition of breadcrumb for active routes
                                $configuration['breadcrumbs'][] = [
                                    "name" => $array['name'],
                                    "disabled" => true,
                                    "href" => '',
                                ];
                                $configuration['breadcrumbs'][] = [
                                    "name" => $name,
                                    "disabled" => false,
                                    "href" => $route,
                                ];
                            }

                            $array['items'][] = [
                                'name' => $name,
                                'icon' => $sr['icon'] ?? '',
                                'route' => $route,
                                'is_active' => $is_active,
                            ];
                        }
                    }

                } else {
                    $array['icon'] = $pr['icon'] ?? '';

                    $route_name =  $pr['route_name'].".index";

                    if(isset($config['base_prefix']) && $config['base_prefix'])
                        $route_name = strtolower(config($this->baseKey . '.name')) . "." . $route_name;

                    if( Route::has($route_name) ){
                        $route = route( $route_name );
                        $is_active = $route == $configuration['current_url'] ? 1 : 0;

                        $array['route'] =  $route;
                        $array['is_active'] = $is_active;
                    }

                }

                $configuration['sideMenu'][] = $array;
            }
            // $configuration['sideMenu'][] = [
            //     'name' => 'Media Library',
            //     'attr' => 'data-medialib-btn'
            //     // 'icon' => '$media'
            // ];
            $configuration['sideMenu'][] = [
                'name' => 'Media Library',
                // 'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ];

            // dd($configuration);
            $view->with('configuration', $configuration);
        });
    }
}
