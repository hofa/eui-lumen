<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IpWhite
{

    protected $ipWhitelist;

    protected $request;
    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->ipWhitelist = explode(',', getenv('IP_WHITE'));
        $this->request = $request;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ip = $this->request->ip();
        if (!in_array($ip, $this->ipWhitelist)) {
            return response('your ip not allow access [' . $ip . ']', 401);
        }

        return $next($request);
    }
}
