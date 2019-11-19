<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Redis;

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
            throw new AuthorizationException('密钥串验证不通过', 401);
        }

        $array = $this->auth->payload()->jsonSerialize();
        $rand = $array['rand'] ?? '';
        if (empty($rand) || $rand != Redis::get('device:' . $this->auth->getUser()->id)) {
            throw new AuthorizationException('你的账号已经在其他设备登录，请及时更换密码', 401);
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
            throw new AuthorizationException('你还没有分配角色', 401);
        } else {
            $roleIds = $roleIds->pluck('id')->toArray();
        }
        $role = new Role;
        $data = $role->getCacheMenuByRoleId($roleIds);
        $menu = $data->where('request_type', ucfirst(strtolower($method)))->where('path', $pathInfo)->where('type', 'Node')->toArray();
        if (empty($menu)) {
            throw new AuthorizationException('权限不足' . $method . $pathInfo, 403);
        }
        $request['menu'] = current($menu);
        return $next($request);
    }
}
