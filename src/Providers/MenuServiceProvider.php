<?php

namespace Unusual\CRM\Base\Providers;

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
        view()->composer('base::layouts.master', function ($view)
        {
            $configuration = [
                'current_url' => url()->current(),
                'sideMenu' => [],
                'breadcrumbs' => []
            ];

            $configs = array_merge(
                config($this->moduleNameLower . '.internal_modules'),
                array_map(
                    function($item){
                        return config($item->getLowerName());
                    },
                    array_filter(Module::all(), function($item){
                        return $item->getName() != 'Base' && $item->isStatus(true);
                    })
                )
            );

            $configuration['sideMenu'][] = [
                'text' => 'Modules',
                // 'icon' => '$modules'
            ];
            foreach ( $configs as $name => $config) {
                $name = $config['name'];
                $pr = $config['parent_route']; //  parent_route array|object
                // dd($config);
                // menu list element
                $array = [
                    'text' => $config['name'] ?? $pr['name'] ?? 'Items',
                    'icon' => $config['icon'] ?? $pr['icon'] ?? '',
                    'is_active' => 0
                ];

                if( isset($config['sub_routes']) && !empty($config['sub_routes']) ){
                    $array['items'] = [];

                    $parent_route_name = $pr['route_name'].".index";

                    if(isset($config['base_prefix']) && $config['base_prefix'])
                        $parent_route_name = strtolower(config($this->moduleNameLower . '.name')) . "." . $parent_route_name;

                    if( Route::has($parent_route_name) ){
                        $link = route( $parent_route_name );
                        $is_active = $link == $configuration['current_url'] ? 1 : 0;

                        $array['items'][] = [
                            'text' => Str::plural( $pr['name'] ),
                            'link' => route($parent_route_name),
                            'icon' => $config['parent_route']['icon'] ?? '',
                            'is_active' => $is_active
                        ];
                    }

                    foreach( $config['sub_routes'] as $sr){
                        // $sr sub route array|object
                        $route_name = $pr['route_name'] . '.' . $sr['route_name'] . ".index";

                        if(isset($config['base_prefix']) && $config['base_prefix'])
                            $route_name = strtolower(config($this->moduleNameLower . '.name')) . "." . $route_name;

                        if( Route::has($route_name) ){
                            $text = Str::plural( $sr['name'] );
                            $link = route( $route_name );
                            $is_active = $link == $configuration['current_url'] ? 1 : 0;

                            if($is_active){
                                $array['is_active'] = $is_active;

                                // Addition of breadcrumb for active links
                                $configuration['breadcrumbs'][] = [
                                    "text" => $array['text'],
                                    "disabled" => true,
                                    "href" => '',
                                ];
                                $configuration['breadcrumbs'][] = [
                                    "text" => $text,
                                    "disabled" => false,
                                    "href" => $link,
                                ];
                            }

                            $array['items'][] = [
                                'icon' => $sr['icon'] ?? '',
                                'text' => $text,
                                'link' => $link,
                                'is_active' => $is_active,
                            ];
                        }
                    }

                } else {

                    $route_name =  $pr['route_name'].".index";

                    if(isset($config['base_prefix']) && $config['base_prefix'])
                        $route_name = strtolower(config($this->moduleNameLower . '.name')) . "." . $route_name;

                    if( Route::has($route_name) ){
                        $link = route( $route_name );
                        $is_active = $link == $configuration['current_url'] ? 1 : 0;

                        $array['link'] =  $link;
                        $array['is_active'] = $is_active;
                    }

                }

                $configuration['sideMenu'][] = $array;
            }
            // $configuration['sideMenu'][] = [
            //     'text' => 'Media Library',
            //     'attr' => 'data-medialib-btn'
            //     // 'icon' => '$media'
            // ];
            $configuration['sideMenu'][] = [
                'text' => 'Media Library',
                'icon' => '$media',
                'attr' => 'data-medialib-btn',
                // 'event' => '_triggerOpenMediaLibrary',
                'event' => 'openFreeMediaLibrary',
            ];


            // dd($configuration);
            // dd($configuration, $configs);
            $view->with('configuration', $configuration);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
