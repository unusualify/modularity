<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Facades\Navigation;

class NavigationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        view()->composer([modularityBaseKey() . '::layouts.master', 'translation::layout'], function ($view) {

            $sidebarKey = 'default';
            $profileMenuKey = 'default';

            if (Auth::guest()) {
                $sidebarKey = 'guest';
                $profileMenuKey = 'guest';
            } else {
                $user = auth()->user();
                if ($user->hasRole('superadmin')) {
                    $sidebarKey = 'superadmin';
                    $profileMenuKey = 'superadmin';
                } elseif (count($user->roles) > 0 && $user->isClient()) {
                    $sidebarKey = 'client';
                    $profileMenuKey = 'client';
                }
            }

            $sidebarConfigKey = 'modularity-navigation.sidebar.' . $sidebarKey;
            $profileMenuConfigKey = 'modularity-navigation.profileMenu.' . $profileMenuKey;

            $navigation = [
                'current_url' => url()->current(),
                'sidebar' => array_values(Navigation::formatSidebarMenu(config($sidebarConfigKey, []))),
                'breadcrumbs' => [],
                'profileMenu' => array_values(Navigation::formatSidebarMenu(config($profileMenuConfigKey, []))),
            ];

            // setActiveMenuItem($configuration['sidebar'], $configuration['current_url']);
            $view->with('navigation', $navigation);
        });

        return $next($request);
    }
}
