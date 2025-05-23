<?php

namespace Unusualify\Modularity\Http\Middleware;

use App\Http\Menus\GetSidebarMenu;
use App\Models\Menulist;
use App\Models\RoleHierarchy;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Unusualify\Modularity\Facades\UNavigation;

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
        app()->config->set([
            modularityBaseKey() . '-navigation.sidebar' => UNavigation::formatSidebarMenus(app()->config->get(modularityBaseKey() . '-navigation.sidebar')),
        ]);
        app()->config->set([
            modularityBaseKey() . '.ui_settings.profileMenu' => UNavigation::formatSidebarMenus(app()->config->get(modularityBaseKey() . '.ui_settings.profileMenu')),
        ]);
        view()->composer([modularityBaseKey() . '::layouts.master', 'translation::layout'], function ($view) {

            $navigation = [
                'current_url' => url()->current(),
                'sidebar' => [],
                'breadcrumbs' => [],
                'profileMenu' => [],
            ];

            // dd(
            //     config(modularityBaseKey() .'-navigation.sidebar')
            // );
            if (Auth::guest()) {
                $navigation['sidebar'] = array_values(config(modularityBaseKey() . '-navigation.sidebar.guest'));
                $navigation['profileMenu'] = array_values(app()->config->get(modularityBaseKey() . '.ui_settings.profileMenu.guest', default: []));
                // $navigation['profileMenu'] = array_values( app()->config->get(modularityBaseKey(). '.ui_settings.profileMenu.client' ,default: []));

            } else {
                $user = auth()->user();
                if (count($user->roles) > 0 && $user->isClient()) {
                    $navigation['sidebar'] = array_values(config(modularityBaseKey() . '-navigation.sidebar.client'));
                    $navigation['profileMenu'] = array_values(app()->config->get(modularityBaseKey() . '.ui_settings.profileMenu.client', default: []));

                } elseif ($user->hasRole(1)) {
                    $navigation['sidebar'] = array_values(config(modularityBaseKey() . '-navigation.sidebar.superadmin'));
                    $navigation['profileMenu'] = array_values(app()->config->get(modularityBaseKey() . '.ui_settings.profileMenu.superadmin', default: []));
                } else {
                    $navigation['sidebar'] = array_values(config(modularityBaseKey() . '-navigation.sidebar.default'));
                    $navigation['profileMenu'] = array_values(app()->config->get(modularityBaseKey() . '.ui_settings.profileMenu.default', default: []));
                }

            }

            // setActiveMenuItem($configuration['sidebar'], $configuration['current_url']);
            $view->with('navigation', $navigation);
        });

        return $next($request);

        if (Auth::check() && false) {
            $role = 'guest';
            // $role =  Auth::user()->menuroles;
            $userRoles = Auth::user()->getRoleNames();
            // $userRoles = $userRoles['items'];
            $roleHierarchy = RoleHierarchy::select('role_hierarchy.role_id', 'roles.name')
                ->join('roles', 'roles.id', '=', 'role_hierarchy.role_id')
                ->orderBy('role_hierarchy.hierarchy', 'asc')->get();
            $flag = false;
            foreach ($roleHierarchy as $roleHier) {
                foreach ($userRoles as $userRole) {
                    if ($userRole == $roleHier['name']) {
                        $role = $userRole;
                        $flag = true;

                        break;
                    }
                }
                if ($flag === true) {
                    break;
                }
            }
        } else {
            $role = 'guest';
        }
        // session(['prime_user_role' => $role]);
        $menus = new GetSidebarMenu;
        $menulists = Menulist::all();
        $result = [];
        foreach ($menulists as $menulist) {
            // dd($role, $menulist->id);
            $result[$menulist->name] = $menus->get($role, $menulist->id);
        }
        view()->share('appMenus', $result);

        return $next($request);
    }
}
