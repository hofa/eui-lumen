<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Factory as Auth;

class Permission
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            // return response('Unauthorized.', 401);
            throw new AuthorizationException('密钥串验证不通过');
        }

        $route = $request->route();
        $method = $request->getMethod();
        $pathInfo = $request->getPathInfo();
        foreach ($route[2] ?? [] as $k => $v) {
            $pathInfo = str_replace('/' . $v, '/{' . $k . '}', $pathInfo);
        }
        $user = $this->auth->getUser();
        $roleIds = $user->roles()->get();

        if (empty($roleIds)) {
            throw new AuthorizationException('你还没有分配角色');
        } else {
            $roleIds = $roleIds->pluck('id')->toArray();
        }
        $role = new Role;
        // $role->refreshCache();
        $data = $role->getCacheMenuByRoleId($roleIds);
        // dd($data);
        $menu = $data->where('request_type', ucfirst(strtolower($method)))->where('path', $pathInfo)->where('type', 'Node')->toArray();
        // $menu = Menu::where('request_type', $method)->where('path', $pathInfo)->where('type', 'Node');
        // if (!in_array(1, $roleIds)) {
        //     $menu = $menu->whereHas('roles', function ($obj) use ($roleIds) {
        //         $obj->whereIn('id', $roleIds);
        //     });
        // }
        // dd($data->toArray());
        if (empty($menu)) {
            throw new AuthorizationException('权限不足' . $method . $pathInfo);
        }

        // $request->getMethod();

        return $next($request);
    }
}
