<?php

namespace Unusualify\Modularity\Http\Middleware;



class TeamsPermissionMiddleware {

    public function handle($request, \Closure $next){
        if(!empty(auth()->user())){
            // session value set on login

            setPermissionsTeamId(session('team_id'));
            // dd(
            //     session(),
            //     session('team_id')
            // );

        }
        // other custom ways to get team_id
        /*if(!empty(auth('api')->user())){
            // `getTeamIdFromToken()` example of custom method for getting the set team_id
            setPermissionsTeamId(auth('api')->user()->getTeamIdFromToken());
        }*/

        return $next($request);
    }
}
