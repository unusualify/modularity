<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Menus\GetSidebarMenu;
use App\Models\Menulist;
use App\Models\RoleHierarchy;
use Unusualify\Modularity\Facades\UNavigation;
use Spatie\Permission\Models\Role;

class NavigationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        app()->config->set([
            unusualBaseKey() . '-navigation.sidebar' => UNavigation::formatSidebarMenus(app()->config->get(unusualBaseKey() . '-navigation.sidebar'))
        ]);
        app()->config->set([
            unusualBaseKey().'.ui_settings.profileMenu' => UNavigation::formatSidebarMenus(app()->config->get(unusualBaseKey().'.ui_settings.profileMenu'))
        ]);

        view()->composer( [unusualBaseKey()."::layouts.master", 'translation::layout'], function ($view)
        {

            $navigation = [
                'current_url' => url()->current(),
                'sidebar' => [],
                'breadcrumbs' => [],
                'profileMenu' => []
            ];

            // dd(
            //     config(unusualBaseKey() .'-navigation.sidebar')
            // );

            $user = auth()->user();

            if(count($user->roles) > 0 && $user->isClient()){
                $navigation['sidebar'] = array_values( config(unusualBaseKey() .'-navigation.sidebar.client') );
                $navigation['profileMenu'] = array_values( app()->config->get(unusualBaseKey(). '.ui_settings.profileMenu.client' ,default: []));

            }else if($user->hasRole(1)) {
                $navigation['sidebar'] = array_values( config(unusualBaseKey() .'-navigation.sidebar.superadmin') );
                $navigation['profileMenu'] = array_values( app()->config->get(unusualBaseKey(). '.ui_settings.profileMenu.superadmin',default: []));
            }else{
                $navigation['sidebar'] = array_values( config(unusualBaseKey() .'-navigation.sidebar.default') );
                $navigation['profileMenu'] = array_values( app()->config->get(unusualBaseKey(). '.ui_settings.profileMenu.default',default: []));
            }
            // setActiveMenuItem($configuration['sidebar'], $configuration['current_url']);
            $view->with('navigation', $navigation);
        });

        return $next($request);

        if (Auth::check() && false){
            $role = 'guest';
            //$role =  Auth::user()->menuroles;
            $userRoles = Auth::user()->getRoleNames();
            //$userRoles = $userRoles['items'];
            $roleHierarchy = RoleHierarchy::select('role_hierarchy.role_id', 'roles.name')
            ->join('roles', 'roles.id', '=', 'role_hierarchy.role_id')
            ->orderBy('role_hierarchy.hierarchy', 'asc')->get();
            $flag = false;
            foreach($roleHierarchy as $roleHier){
                foreach($userRoles as $userRole){
                    if($userRole == $roleHier['name']){
                        $role = $userRole;
                        $flag = true;
                        break;
                    }
                }
                if($flag === true){
                    break;
                }
            }
        }else{
            $role = 'guest';
        }
        //session(['prime_user_role' => $role]);
        $menus = new GetSidebarMenu();
        $menulists = Menulist::all();
        $result = array();
        foreach($menulists as $menulist){
            // dd($role, $menulist->id);
            $result[ $menulist->name ] = $menus->get( $role, $menulist->id );
        }
        view()->share('appMenus', $result );
        return $next($request);
    }
}
