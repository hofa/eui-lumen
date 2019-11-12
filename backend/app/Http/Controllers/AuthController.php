<?php

namespace App\Http\Controllers;

use App\Events\LoginEvent;
use App\Exceptions\ValidatorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);
        $random = Str::random(10);
        if (!$token = $this->jwt->customClaims(['rand' => $random])->attempt($request->only('username', 'password'))) {
            ValidatorException::setError('username', '用户不存在或者密码错误');
        }
        Redis::setex('device:' . $this->jwt->user()->id, Auth::factory()->getTTL() * 60, $random);
        Event::fire(new LoginEvent($this->jwt->user()));
        return [
            'data' => [
                'token' => $token,
                'tokenType' => 'bearer',
                'expiresIn' => Auth::factory()->getTTL() * 60,
            ],
            'meta' => [
                'message' => '登录成功',
            ],
        ];
    }

    public function postLogout(Request $request)
    {
        try {
            $this->jwt->parseToken()->invalidate();
        } catch (\Exception $e) {

        }
        return ['meta' => ['message' => '退出成功']];
    }

    public function postRefresh(Request $request)
    {
        Redis::expired('device:' . $this->jwt->user()->id, Auth::factory()->getTTL() * 60);
        return [
            'data' => [
                'token' => $this->jwt->parseToken()->refresh(),
                'tokenType' => 'bearer',
                'expiresIn' => Auth::factory()->getTTL() * 60,
            ],
            'meta' => [
                'message' => '刷新TOKEN成功',
            ],
        ];
    }
}
