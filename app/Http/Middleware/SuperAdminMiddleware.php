<?php

namespace App\Http\Middleware;

use App\Traits\Helpers;
use Closure;

class SuperAdminMiddleware
{
    use Helpers;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param string $methodsStr
     * @return mixed
     */
    public function handle($request, Closure $next, $methodsStr)
    {
        $methods_to_check = explode("-", $methodsStr);
        $current_route_method = explode("@", $request->route()[1]["uses"])[1];

        if (!in_array($current_route_method, $methods_to_check) || (in_array($current_route_method, $methods_to_check) && auth()->check() && $this->superAdminCheck())) {
            return $next($request);
        }

        return response('Operation denied.', 401);
    }

    /** To use this middleware in any controller just call it in the constructor 
     * and pass the action names that we need to apply this middlware 
     * in as a string separated by dashes after the “:”, 
     * for example to apply this middlware in methods (store, update) 
     * we use $this->middleware(‘super_admin_check:store-update’).
     */
}
